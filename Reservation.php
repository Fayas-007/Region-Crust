<?php  
session_start(); 
include 'Database/db_connection.php'; 
include('Components/toast.php'); 

// --- Session or Cookie-based Authentication ---
if (isset($_SESSION["id"]) && isset($_SESSION["user_type"])) {
    $customer_id = $_SESSION["id"];
    $user_type = $_SESSION["user_type"];
} elseif (isset($_COOKIE['remember_user'])) {
    $remember_data = json_decode($_COOKIE['remember_user'], true);
    $customer_id = $remember_data['id'] ?? '';
    $user_type = $remember_data['type'] ?? '';
    $_SESSION["id"] = $customer_id;
    $_SESSION["user_type"] = $user_type;
} else {
    header("Location: reg_login.php");
    exit();
}
    echo $user_type;
    
// --- DELETE RESERVATION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['delete_reservation'])) {
    $reservation_id = (int)$_POST['id'];

    if ($user_type === 'customer') {
        $stmt = $con->prepare("SELECT idcus FROM reservation WHERE reservation_id = ?");
    } elseif ($user_type === 'travel_company') {
        $stmt = $con->prepare("SELECT travel_company_id FROM reservation WHERE reservation_id = ?");
    } else {
        $_SESSION['form_message'] = '<span style="color: red;">Invalid user type.</span>';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $stmt->bind_param("i", $reservation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $is_owner = ($user_type === 'customer' && $row['idcus'] == $customer_id) || 
                ($user_type === 'travel_company' && $row['travel_company_id'] == $customer_id);

    if ($row && $is_owner) {
        // Delete related entries in reservation_rooms
        $con->prepare("DELETE FROM reservation_rooms WHERE reservation_id = ?")->execute([$reservation_id]);

        // Delete the reservation
        $del_stmt = $con->prepare("DELETE FROM reservation WHERE reservation_id = ?");
        $del_stmt->bind_param("i", $reservation_id);
        if ($del_stmt->execute()) {
            $_SESSION['form_message'] = '<span style="color: green;">Reservation deleted successfully.</span>';
        } else {
            $_SESSION['form_message'] = '<span style="color: red;">Error deleting reservation.</span>';
        }
        $del_stmt->close();
    } else {
        $_SESSION['form_message'] = '<span style="color: red;">Unauthorized action.</span>';
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- DEFAULT FORM & AVAILABILITY LOGIC ---
$availability_msg = $_SESSION['form_message'] ?? '';
$form_cleared = $_SESSION['form_cleared'] ?? false;
unset($_SESSION['form_message'], $_SESSION['form_cleared']);

$date_in = date('Y-m-d');
$date_out = date('Y-m-d', strtotime('+2 days'));


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $room_cat = (int)($_POST['room_cat'] ?? 0);
    $check_in = $_POST['date_in'] ?? $date_in;
    $check_out = $_POST['date_out'] ?? $date_out;
    $num_rooms = (int)($_POST['num_rooms'] ?? 1);
    $num_occupants = (int)($_POST['num_occupants'] ?? 1);

    $date_in = $check_in;
    $date_out = $check_out;

    if ($check_in > $check_out) {
        $availability_msg = '<span style="color: red;">Check-out date must be after Check-in date.</span>';
    } else {
        // Get all rooms in selected category
        $stmt = $con->prepare("SELECT idroom FROM rooms WHERE idroom_cat = ?");
        $stmt->bind_param("i", $room_cat);
        $stmt->execute();
        $result = $stmt->get_result();

        $available_rooms = [];

        while ($row = $result->fetch_assoc()) {
            $room_id = $row['idroom'];

            // Check if room is free (not in any overlapping reservation)
            $check_stmt = $con->prepare("
                SELECT 1 FROM reservation_rooms rr
                JOIN reservation r ON rr.reservation_id = r.reservation_id
                WHERE rr.room_id = ?
                AND NOT (r.check_out_date <= ? OR r.check_in_date >= ?)
            ");
            $check_stmt->bind_param("iss", $room_id, $check_in, $check_out);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows === 0) {
                $available_rooms[] = $room_id;
            }

            $check_stmt->close();
        }

        $stmt->close();

        if (count($available_rooms) < $num_rooms) {
            $availability_msg = '<span style="color: red;">Only ' . count($available_rooms) . ' room(s) are available for the selected category and dates.</span>';
        } else {
            // Book Now Logic
           if (isset($_POST['book_now'])) {
            $credit_cardnum = null;
            $status = "tentative";
            $is_residential_suite = 0;
            $special_requests = "";

            $idcus = null;
            $idtravel_company = null;

            if ($user_type === 'customer') {
                $idcus = $customer_id;
                $credit_check = $con->prepare("SELECT credit_cardnum FROM customer WHERE idcus = ?");
                $credit_check->bind_param("i", $idcus);
                $credit_check->execute();
                $credit_cardnum = $credit_check->get_result()->fetch_assoc()['credit_cardnum'] ?? null;
                $credit_check->close();
            } elseif ($user_type === 'travel_company') {
                $idtravel_company = $customer_id;
                $credit_check = $con->prepare("SELECT travel_credit_cardnum FROM travel_company WHERE travel_company_id = ?");
                $credit_check->bind_param("i", $idtravel_company);
                $credit_check->execute();
                $credit_check->bind_result($credit_cardnum);
                $credit_check->fetch();
                $credit_check->close();
            }

            if (!empty($credit_cardnum)) {
                $status = "confirmed";
            }

            // Check if selected room category is a residential suite
            $cat_stmt = $con->prepare("SELECT room_cat FROM room_cat WHERE idroom_cat = ?");
            $cat_stmt->bind_param("i", $room_cat);
            $cat_stmt->execute();
            $cat_result = $cat_stmt->get_result();
            if ($cat_row = $cat_result->fetch_assoc()) {
                if ($cat_row['room_cat'] === 'Suit Per/Month' || $cat_row['room_cat'] === 'Suit Per/Week') {
                    $is_residential_suite = 1;
                }
            }
            $cat_stmt->close();

            $insert = $con->prepare("INSERT INTO reservation 
                (idcus, travel_company_id, idroom_cat, check_in_date, check_out_date, is_residential_suite, `status`, special_requests, num_rooms, num_occupants)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $insert->bind_param("iiissssiii",
                $idcus,
                $idtravel_company,
                $room_cat,
                $check_in,
                $check_out,
                $is_residential_suite,
                $status,
                $special_requests,
                $num_rooms,
                $num_occupants
            );

            if ($insert->execute()) {
                $new_reservation_id = $insert->insert_id;

                // Assign rooms from available list
                for ($i = 0; $i < $num_rooms; $i++) {
                    $room_id = $available_rooms[$i];
                    $room_insert = $con->prepare("INSERT INTO reservation_rooms (reservation_id, room_id) VALUES (?, ?)");
                    $room_insert->bind_param("ii", $new_reservation_id, $room_id);
                    $room_insert->execute();
                    $room_insert->close();
                }

                if ($status === "confirmed") {
                    $availability_msg = '<span style="color: green;">Reservation successful! ' . $num_rooms . ' room(s) booked. Status: Confirmed.</span>';
                } else {
                    $due_time = date('g:i A', strtotime('7pm'));
                    $availability_msg = '<span style="color: orange;">Your booking is tentative. Please provide visa card details before the night of ' . htmlspecialchars($check_in) . ' at ' . $due_time . ', or it may be canceled.</span>';
                }
                $form_cleared = true;
            } else {
                $availability_msg = '<span style="color: red;">Error: Reservation failed. ' . htmlspecialchars($insert->error) . '</span>';
            }

            $insert->close();

        } else {
            $availability_msg = '<span style="color: green;">' . count($available_rooms) . ' room(s) available for the selected category and dates.</span>';
        }

        }
    }

    $_SESSION['form_message'] = $availability_msg;
    $_SESSION['form_cleared'] = $form_cleared;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();

}
  
//DISCOUNT
$user_type = $_SESSION['user_type'] ?? 'guest'; // Example fallback to guest nothing happends
$query = "SELECT discount_percent FROM discounts WHERE active = 1 AND user_type = ? LIMIT 1";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $user_type);
$stmt->execute();
$stmt->bind_result($discount_percent);
$stmt->fetch();
$stmt->close();
$discount_percent = $discount_percent ?? 0;  // fallback if none found
?>
<!-- passing the discount to javascript to work with discount amount to get betetr design  -->
<script>
  const discountPercent = <?php echo $discount_percent; ?>;
  const userType = "<?php echo $user_type; ?>";
</script>
<!DOCTYPE html>
<html lang="en">
<?php include('Components/head.php'); ?>
<body>
<?php include('Components/header.php'); ?>
<?php
// Detect user type and set ID accordingly
if (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'customer' || $_SESSION['user_type'] === 'travel_company')) {
    $user_type = $_SESSION['user_type'];
    $user_id = $_SESSION['id'];  // Unified ID used for both types

    // Prepare query based on user type
    if ($user_type === 'customer') {
        $stmt = $con->prepare("SELECT * FROM reservation r 
                               JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat 
                               WHERE r.idcus = ?");
    } elseif ($user_type === 'travel_company') {
        $stmt = $con->prepare("SELECT * FROM reservation r 
                               JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat 
                               WHERE r.travel_company_id = ?");
    }
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        echo '<div class="reservation-section">';
        if ($result->num_rows > 0) {
            echo '<p>Your Reservations</p>';
            while ($reservation = $result->fetch_assoc()) {
                ?>
                <div class="reservation-box">
                    <div class="reservation-content">
                        <div class="reservation-details">
                            <p><strong>Room Category:</strong> <?php echo htmlspecialchars($reservation['room_cat']); ?></p>
                            <p><strong>Check-in Date:</strong> <?php echo htmlspecialchars($reservation['check_in_date']); ?></p>
                            <p><strong>Check-out Date:</strong> <?php echo htmlspecialchars($reservation['check_out_date']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($reservation['status'])); ?></p>
                            <p><strong>Reservation ID:</strong> <?php echo htmlspecialchars($reservation['reservation_id']); ?></p>
                            <p><strong>Number of Occupants:</strong> <?php echo htmlspecialchars($reservation['num_occupants']); ?></p>
                            <p><strong>Number of rooms:</strong> <?php echo htmlspecialchars($reservation['num_rooms']); ?></p>
                        </div>

                    <div class="reservation-actions">
                        <?php 
                        $status = strtolower($reservation['status']);
                        
                        if ($status === 'checked-in'): ?>
                            <!-- Only show Add Card for checked-in -->
                            <form action="profile.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $reservation['reservation_id']; ?>">
                                <button type="submit" class="btn" style="background-color: #0F52BA;">Add Card</button>
                            </form>

                        <?php elseif ($status === 'checked-out'): ?>
                            <!-- Only show Check Bill for checked-out -->
                            <form action="checkout.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $reservation['reservation_id']; ?>">
                                <button type="submit" class="btn danger-btn">Check Bill</button>
                            </form>

                        <?php else: ?>
                            <!-- Show Edit, Delete, and Add Card for other statuses -->
                            <form action="edit_reservation.php" method="GET">
                                <input type="hidden" name="Edit_id" value="<?php echo $reservation['reservation_id']; ?>">
                                <button type="submit" class="btn">Edit</button>
                            </form>

                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this reservation?');">
                                <input type="hidden" name="id" value="<?php echo $reservation['reservation_id']; ?>">
                                <input type="hidden" name="delete_reservation" value="1">
                                <button type="submit" class="btn delete-btn">Delete</button>
                            </form>

                            <form action="profile.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $reservation['reservation_id']; ?>">
                                <button type="submit" class="btn" style="background-color: #0F52BA;">Add Card</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p>You have no reservations yet.</p>';
        }
        echo '</div>'; // close reservation-section
        $stmt->close();
    } else {
        echo "Error preparing statement.";
    }
} else {
    echo "You must be logged in as a customer or travel company to view reservations.";
}
?>

<section id="rooms" class="bg-dark text-white">
  <div class="booking-form-container">
    
    <div id="availability_msg" style="margin-top: 15px; font-weight: bold;">
      <?php echo $availability_msg; ?>
    </div>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="filter" method="POST">
      <div class="form-group">
        <label for="room_cat">Select Room Category</label>
        <select class="form-control" name="room_cat" id="room_cat" required>
            <option value="" disabled <?php echo !isset($_POST['room_cat']) ? 'selected' : ''; ?>>-- Choose Category --</option>
            <?php
            $result = $con->query("SELECT idroom_cat, room_cat, rate_type FROM room_cat ORDER BY room_cat ASC");
            while ($cat = $result->fetch_assoc()):
            $selected = (isset($_POST['room_cat']) && $_POST['room_cat'] == $cat['idroom_cat']) ? 'selected' : '';
            $room_cat_name = htmlspecialchars($cat['room_cat']);
            $rate_type = htmlspecialchars($cat['rate_type']);
            ?>
            <option value="<?php echo $cat['idroom_cat']; ?>" <?php echo $selected; ?>>
                <?php echo $room_cat_name . " - " . $rate_type; ?>
            </option>
            <?php endwhile; ?>
        </select>
        </div>
      
      <!-- Number of rooms -->
      <div class="form-group">
        <label for="num_rooms">Number of Rooms</label>
        <input type="number" class="form-control" name="num_rooms" id="num_rooms" min="1" required
         value="<?php echo isset($_POST['num_rooms']) ? (int)$_POST['num_rooms'] : 1; ?>">
      </div>
          <div class="discount_msg" id="discount_msg">
            <?php echo $discount_msg ?? ''; ?>
          </div>
        <!-- Number of occupants -->
      <div class="form-group">
        <label for="num_occupants">Number of occupants</label>
        <input type="number" class="form-control" name="num_occupants" id="num_occupants" min="1" required
         value="<?php echo isset($_POST['num_occupants']) ? (int)$_POST['num_occupants'] : 1; ?>">
      </div>

      <!-- Checkin date -->
      <div class="form-group">
        <label for="date_in">Check-in Date</label>
        <input type="date" class="form-control" name="date_in" id="date_in" required
               min="<?php echo date('Y-m-d'); ?>"
               value="<?php echo htmlspecialchars($date_in); ?>">
      </div>

      <!-- Checkout date -->
      <div class="form-group">
        <label for="date_out">Check-out Date</label>
        <input type="date" class="form-control" name="date_out" id="date_out" required
               min="<?php echo date('Y-m-d', strtotime($date_in)); ?>"
               value="<?php echo htmlspecialchars($date_out); ?>">
      </div>
      <!-- Buttons -->
      <div class="button-row">
        <button type="submit" class="btn btn-yellow" name="check_availability">Check Availability</button>
        <button type="submit" class="btn btn-green" name="book_now">Book Now</button>
      </div>
    </form>
  </div>

<div id="portfolio">
  <div class="container-fluid p-0">
    <div class="row no-gutters">
      <?php
      $query = $con->query("SELECT * FROM room_cat");
      while ($row = $query->fetch_assoc()):
        $img_path = 'Manager/upload/' . basename($row['img_room']);
        $room_name = htmlspecialchars($row['room_cat']);
        $raw_price = str_replace(',', '', $row['price_room']);
        $price = number_format((float)$raw_price, 2);
        $rate_type = htmlspecialchars($row['rate_type']); // Fetching rate type from the database
      ?>
        <div class="col-lg-4 col-md-6 col-sm-6">
          <div class="portfolio-box">
            <img src="<?php echo $img_path; ?>" alt="<?php echo $room_name; ?>" class="img-fluid">
            <div class="portfolio-box-caption">
              <h5><b><?php echo $room_name; ?></b></h5>
              <p>LKR <?php echo $price; ?> / <?php echo $rate_type; ?></p> <!-- Displaying price and rate type -->
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>
</section>
<script>
 
</script>
<script src="js/scripts.js"></script>
</body>
</html>
