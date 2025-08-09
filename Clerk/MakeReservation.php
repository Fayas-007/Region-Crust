<?php
session_start();
include("../Database/db_connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $user_type = $_POST['user_type']; // 'customer' or 'travel_company'
  $user_id = intval($_POST['user_id']); 
  $room_cat_id = intval($_POST['room_cat_id']);
  $check_in = $_POST['check_in'];
  $check_out = $_POST['check_out'];
  $num_rooms = intval($_POST['num_rooms']);
  $num_occupants = intval($_POST['num_occupants']);
  $special_requests = $_POST['special_requests'] ?? '';

  // Find available rooms
  $available_rooms = [];
  $stmt = $con->prepare("
    SELECT r.idroom
    FROM rooms r
    WHERE r.idroom_cat = ?
    AND r.idroom NOT IN (
      SELECT rr.room_id
      FROM reservation_rooms rr
      JOIN reservation res ON rr.reservation_id = res.reservation_id
      WHERE NOT (
        res.check_out_date < ? OR res.check_in_date > ?
      )
    )
    LIMIT ?
  ");
  $stmt->bind_param('issi', $room_cat_id, $check_in, $check_out, $num_rooms);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $available_rooms[] = $row['idroom'];
  }
  $stmt->close();

  if (count($available_rooms) < $num_rooms) {
    echo "<div class='alert alert-danger'>Not enough rooms available in this category for the selected dates.</div>";
  } else {
    // Determine reservation status for customer
    $status = 'tentative'; // default

    if ($user_type === 'customer') {
      $stmtCheckCard = $con->prepare("SELECT credit_cardnum FROM customer WHERE idcus = ?");
      $stmtCheckCard->bind_param('i', $user_id);
      $stmtCheckCard->execute();
      $resultCheckCard = $stmtCheckCard->get_result();
      $rowCard = $resultCheckCard->fetch_assoc();
      if (!empty($rowCard['credit_cardnum'])) {
        $status = 'confirmed';
      }
      $stmtCheckCard->close();
    } else {
      $status = 'tentative';
    }

    if ($user_type === 'customer') {
      $sqlInsert = "INSERT INTO reservation 
        (idcus, travel_company_id, idroom_cat, check_in_date, check_out_date, status, num_rooms, num_occupants, special_requests)
        VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)";
      $stmt2 = $con->prepare($sqlInsert);
      $stmt2->bind_param('iisssiis', $user_id, $room_cat_id, $check_in, $check_out, $status, $num_rooms, $num_occupants, $special_requests);
    } else {
      $sqlInsert = "INSERT INTO reservation 
        (idcus, travel_company_id, idroom_cat, check_in_date, check_out_date, status, num_rooms, num_occupants, special_requests)
        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt2 = $con->prepare($sqlInsert);
      $stmt2->bind_param('issssiis', $user_id, $room_cat_id, $check_in, $check_out, $status, $num_rooms, $num_occupants, $special_requests);
    }

    if (!$stmt2->execute()) {
      echo "<div class='alert alert-danger'>Reservation failed: " . $stmt2->error . "</div>";
      exit;
    }

    $reservation_id = $con->insert_id;

    $stmt3 = $con->prepare("INSERT INTO reservation_rooms (reservation_id, room_id) VALUES (?, ?)");
    foreach (array_slice($available_rooms, 0, $num_rooms) as $room_id) {
      $stmt3->bind_param('ii', $reservation_id, $room_id);
      $stmt3->execute();
    }
    $stmt3->close();

    echo "<div class='alert alert-success'>Reservation made successfully.</div>";
  }
}

$customers = $con->query("SELECT idcus, cus_name FROM customer ORDER BY cus_name");
$travel_companies = $con->query("SELECT travel_company_id, travel_name FROM travel_company ORDER BY travel_name");
$room_categories = $con->query("SELECT idroom_cat, room_cat FROM room_cat ORDER BY room_cat");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include("./components/head.php"); ?>
</head>
<body>
<?php include("components/sidebarclk.php"); ?>
<?php include("../Database/db_connection.php"); ?>

<div class="main-panel">
<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-header bg-success text-white">
      <h3 class="mb-0">Make Reservation</h3>
    </div>
    <div class="card-body">
      <form method="POST" action="" class="row g-3">
        <div class="col-md-4">
          <label for="user_type" class="form-label">User Type</label>
          <select name="user_type" id="user_type" class="form-select" required onchange="toggleUserDropdown()">
            <option value="">-- Select --</option>
            <option value="customer">Customer</option>
            <option value="travel_company">Travel Company</option>
          </select>
        </div>

        <div class="col-md-4" id="customer_div" style="display:none;">
          <label for="customer_select" class="form-label">Select Customer</label>
          <select name="user_id" id="customer_select" class="form-select">
            <?php while ($row = $customers->fetch_assoc()) { ?>
              <option value="<?= $row['idcus'] ?>"><?= htmlspecialchars($row['cus_name']) ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4" id="travel_div" style="display:none;">
          <label for="travel_select" class="form-label">Select Travel Company</label>
          <select name="user_id" id="travel_select" class="form-select">
            <?php while ($row = $travel_companies->fetch_assoc()) { ?>
              <option value="<?= $row['travel_company_id'] ?>"><?= htmlspecialchars($row['travel_name']) ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4">
          <label for="room_cat_id" class="form-label">Room Category</label>
          <select name="room_cat_id" id="room_cat_id" class="form-select" required>
            <?php while ($row = $room_categories->fetch_assoc()) { ?>
              <option value="<?= $row['idroom_cat'] ?>"><?= htmlspecialchars($row['room_cat']) ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-4">
          <label for="check_in" class="form-label">Check-In Date</label>
          <input type="date" id="check_in" name="check_in" class="form-control" required />
        </div>

        <div class="col-md-4">
          <label for="check_out" class="form-label">Check-Out Date</label>
          <input type="date" id="check_out" name="check_out" class="form-control" required />
        </div>

        <div class="col-md-4">
          <label for="num_rooms" class="form-label">Number of Rooms</label>
          <input type="number" id="num_rooms" name="num_rooms" min="1" value="1" class="form-control" required />
        </div>

        <div class="col-md-4">
          <label for="num_occupants" class="form-label">Number of Occupants</label>
          <input type="number" id="num_occupants" name="num_occupants" min="1" value="1" class="form-control" required />
        </div>

        <div class="col-md-12">
          <label for="special_requests" class="form-label">Special Requests</label>
          <textarea id="special_requests" name="special_requests" rows="3" class="form-control" placeholder="Any special requests?"></textarea>
        </div>

        <div class="col-12 d-flex justify-content-between align-items-center">
        <button type="button" class="btn btn-secondary" onclick="history.back();">
            ← Back
        </button>
        <button type="submit" class="btn btn-success">
            Make Reservation
        </button>
        </div>
      </form>
    </div>
  </div>
</div>
</div>

<script>
  function toggleUserDropdown() {
    const userType = document.getElementById('user_type').value;
    const customerDiv = document.getElementById('customer_div');
    const travelDiv = document.getElementById('travel_div');

    if (userType === 'customer') {
      customerDiv.style.display = 'block';
      travelDiv.style.display = 'none';
      document.getElementById('customer_select').disabled = false;
      document.getElementById('travel_select').disabled = true;
    } else if (userType === 'travel_company') {
      customerDiv.style.display = 'none';
      travelDiv.style.display = 'block';
      document.getElementById('customer_select').disabled = true;
      document.getElementById('travel_select').disabled = false;
    } else {
      customerDiv.style.display = 'none';
      travelDiv.style.display = 'none';
      document.getElementById('customer_select').disabled = true;
      document.getElementById('travel_select').disabled = true;
    }
  }

  window.onload = toggleUserDropdown;
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
