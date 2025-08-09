<?php
session_start();
 include_once('../Database/db_connection.php');
 

 // ===========================
// ROOM CAT AND ID ASSIGN
// ===========================


if (isset($_POST['btn_assign_room']) || isset($_POST['btn_update_room'])) {
    $room_no = $_POST['room_no'];
    $idroom_cat = $_POST['idroom_cat'];

    if (isset($_POST['btn_assign_room'])) {
        // Check if the room number already exists
        $query = "SELECT COUNT(*) FROM rooms WHERE room_no = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $room_no);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            // Room number already exists
            $_SESSION['message'] = "Room number already exists. Please choose a different number.";
            header("Location: roomcatclk.php");  // Redirect back to the form
            exit();
        } else {
            // Insert new room assignment
            $query = "INSERT INTO rooms (room_no, idroom_cat) VALUES (?, ?)";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "si", $room_no, $idroom_cat);
            mysqli_stmt_execute($stmt);
            $_SESSION['message'] = "Room assigned successfully!";
            header("Location: roomcatclk.php");  // Redirect to success page
            exit();
        }
    } elseif (isset($_POST['btn_update_room'])) {
        // Handle update logic
        $room_id = intval($_POST['room_id']);
        
        // Check if the new room number already exists for other rooms (excluding current room being updated)
        $query = "SELECT COUNT(*) FROM rooms WHERE room_no = ? AND idroom != ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "si", $room_no, $room_id);  // Exclude the current room being updated by its ID
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if ($count > 0) {
            // Room number already exists
            $_SESSION['message'] = "Room number already exists. Please choose a different number.";
            header("Location: roomcatclk.php");  // Redirect back to the form
            exit();
        } else {
            // Update room assignment
            $query = "UPDATE rooms SET room_no=?, idroom_cat=? WHERE idroom=?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "sii", $room_no, $idroom_cat, $room_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['message'] = "Room updated successfully!";
            header("Location: roomcatclk.php");  // Redirect to success page
            exit();
        }
    }
}


// ===========================
// DELETE ROOM ASSIGNMENT
// ===========================
if (isset($_POST['btn_delete_room'])) {
    $room_id = intval($_POST['btn_delete_room']);

    $query = "DELETE FROM rooms WHERE idroom=?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);

    $_SESSION['message'] = "Room deleted successfully!";
    header("Location: roomcatclk.php");  // Redirect to the room assignment page
    exit();
}


// ===========================
// UPDATE and Add TRAVEL COMPANY
// ===========================
// ===========================
// UPDATE and Add TRAVEL COMPANY
// ===========================
if (isset($_POST['btn_add_travel'])) {
    $travel_name = trim(mysqli_real_escape_string($con, $_POST['travel_name']));
    $travel_contact_person = trim(mysqli_real_escape_string($con, $_POST['travel_contact_person']));
    $travel_contact_email = trim(mysqli_real_escape_string($con, $_POST['travel_contact_email']));
    $travel_phone = trim(mysqli_real_escape_string($con, $_POST['travel_phone']));
    $travel_address = trim(mysqli_real_escape_string($con, $_POST['travel_address']));
    $travel_registered_date = trim(mysqli_real_escape_string($con, $_POST['travel_registered_date']));
    $travel_credit_cardnum = trim(mysqli_real_escape_string($con, $_POST['travel_credit_cardnum']));
    $travel_password = password_hash(trim($_POST['travel_password']), PASSWORD_DEFAULT);

    // ✅ Check for existing travel_name OR travel_contact_email (not AND)
    $check_query = "SELECT * FROM travel_company WHERE travel_name = ? OR travel_contact_email = ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $travel_name, $travel_contact_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "❌ Travel company with this name OR email already exists.";
        $_SESSION['msg_type'] = "danger";
        header("Location: travel_regclk.php");
        exit();
    }

    // ✅ No duplicate, insert new travel company
    $insert_query = "INSERT INTO travel_company 
        (travel_name, travel_contact_person, travel_contact_email, travel_phone, travel_address, travel_registered_date, travel_credit_cardnum, travel_password)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssssssss", $travel_name, $travel_contact_person, $travel_contact_email, $travel_phone, $travel_address, $travel_registered_date, $travel_credit_cardnum, $travel_password);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "✅ Travel company registered successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ Insert failed: " . mysqli_error($con);
        $_SESSION['msg_type'] = "danger";
    }

    header("Location: travel_regclk.php");
    exit();
}

if (isset($_POST['btn_update_travel'])) {
    $id = intval($_POST['travel_company_id']);
    $name = mysqli_real_escape_string($con, $_POST['travel_name']);
    $contactPerson = mysqli_real_escape_string($con, $_POST['travel_contact_person']);
    $email = mysqli_real_escape_string($con, $_POST['travel_contact_email']);
    $phone = mysqli_real_escape_string($con, $_POST['travel_phone']);
    $address = mysqli_real_escape_string($con, $_POST['travel_address']);
    $registeredDate = mysqli_real_escape_string($con, $_POST['travel_registered_date']);
    $creditCard = mysqli_real_escape_string($con, $_POST['travel_credit_cardnum']);
    $password = $_POST['travel_password'];

    // ✅ Check duplicates on travel_name OR travel_contact_email, excluding current record
    $check_query = "SELECT * FROM travel_company WHERE (travel_name = ? OR travel_contact_email = ?) AND travel_company_id != ?";
    $stmt = mysqli_prepare($con, $check_query);
    mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "❌ Another travel company already uses this name OR email.";
        $_SESSION['msg_type'] = "danger";
        header("Location: travel_regclk.php");
        exit();
    }

    // Update password only if new password is entered
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // Fetch existing password
        $pwQuery = "SELECT travel_password FROM travel_company WHERE travel_company_id = ?";
        $pwStmt = mysqli_prepare($con, $pwQuery);
        mysqli_stmt_bind_param($pwStmt, "i", $id);
        mysqli_stmt_execute($pwStmt);
        $pwResult = mysqli_stmt_get_result($pwStmt);
        $pwRow = mysqli_fetch_assoc($pwResult);
        $hashedPassword = $pwRow['travel_password'];
    }

    $query = "UPDATE travel_company 
              SET travel_name=?, travel_contact_person=?, travel_contact_email=?, travel_phone=?, travel_address=?, travel_registered_date=?, travel_credit_cardnum=?, travel_password=? 
              WHERE travel_company_id=?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $contactPerson, $email, $phone, $address, $registeredDate, $creditCard, $hashedPassword, $id);
    mysqli_stmt_execute($stmt);

    $_SESSION['message'] = "✅ Travel company updated successfully!";
    $_SESSION['msg_type'] = "success";
    header("Location: travel_regclk.php");
    exit();
}

// ===========================
// DELETE TRAVEL COMPANY
// ===========================

if (isset($_POST['btn_delete_travel'])) {
    $travelCompanyId = intval($_POST['btn_delete_travel']);

    $query = "DELETE FROM travel_company WHERE travel_company_id = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $travelCompanyId);

        try {
            mysqli_stmt_execute($stmt);
            $_SESSION['message'] = "Travel Company deleted successfully!";
            $_SESSION['msg_type'] = "success";
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                $_SESSION['message'] = "Cannot delete Travel Company: linked reservations exist.";
                $_SESSION['msg_type'] = "danger";
            } else {
                $_SESSION['message'] = "Error deleting Travel Company: " . $e->getMessage();
                $_SESSION['msg_type'] = "danger";
            }
        }

        mysqli_stmt_close($stmt);
        header("Location: travel_regclk.php");
        exit();

    } else {
        $_SESSION['message'] = "Error preparing delete operation: " . mysqli_error($con);
        $_SESSION['msg_type'] = "danger";
        header("Location: travel_regclk.php");
        exit();
    }
}


// ===========================
// ADD OR UPDATE CUSTOMER
// ===========================
if (isset($_POST['btn_add_customer']) || isset($_POST['btn_update_customer'])) {
    $cus_name = trim($_POST['cus_name']);
    $address = trim($_POST['address']);
    $cus_contact = trim($_POST['cus_contact']);
    $cus_email = trim($_POST['cus_email']);
    $cus_password = $_POST['cus_password'];
    $credit_cardnum = trim($_POST['credit_cardnum']);
    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;

    // Duplicate check for name or email
    if (isset($_POST['btn_add_customer'])) {
        $checkQuery = "SELECT * FROM customer WHERE cus_name = ? OR cus_email = ?";
        $stmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($stmt, "ss", $cus_name, $cus_email);
    } else {
        // Exclude current customer from duplicate check
        $checkQuery = "SELECT * FROM customer WHERE (cus_name = ? OR cus_email = ?) AND idcus != ?";
        $stmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($stmt, "ssi", $cus_name, $cus_email, $customer_id);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['message'] = "Duplicate customer name or email found!";
        $_SESSION['msg_type'] = "danger";
        header("Location: customer_regclk.php");
        exit();
    }

    mysqli_stmt_close($stmt);

    // ===============================
    // INSERT customer
    // ===============================
    if (isset($_POST['btn_add_customer'])) {
        $hashedPassword = password_hash($cus_password, PASSWORD_DEFAULT);
        $query = "INSERT INTO customer (cus_name, address, cus_contact, cus_email, cus_password, credit_cardnum) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssssss", $cus_name, $address, $cus_contact, $cus_email, $hashedPassword, $credit_cardnum);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['message'] = "Customer added successfully!";
        $_SESSION['msg_type'] = "success";
    }

    // ===============================
    // UPDATE customer
    // ===============================
    elseif (isset($_POST['btn_update_customer'])) {
        if ($customer_id <= 0) {
            $_SESSION['message'] = "Invalid customer ID.";
            $_SESSION['msg_type'] = "danger";
            header("Location: customer_regclk.php");
            exit();
        }

        if (!empty($cus_password)) {
            $hashedPassword = password_hash($cus_password, PASSWORD_DEFAULT);
            $query = "UPDATE customer SET cus_name=?, address=?, cus_contact=?, cus_email=?, cus_password=?, credit_cardnum=? WHERE idcus=?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "ssssssi", $cus_name, $address, $cus_contact, $cus_email, $hashedPassword, $credit_cardnum, $customer_id);
        } else {
            $query = "UPDATE customer SET cus_name=?, address=?, cus_contact=?, cus_email=?, credit_cardnum=? WHERE idcus=?";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "sssssi", $cus_name, $address, $cus_contact, $cus_email, $credit_cardnum, $customer_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Customer updated successfully!";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating customer: " . mysqli_stmt_error($stmt);
            $_SESSION['msg_type'] = "danger";
        }
        mysqli_stmt_close($stmt);
    }

    header("Location: customer_regclk.php");
    exit();
}



// ===========================
// DELETE   cUSTOMER
// ===========================
if (isset($_POST['btn_delete_customer'])) {
    $customer_id = intval($_POST['btn_delete_customer']);

    // Check if customer has linked reservations
    $checkQuery = "SELECT COUNT(*) FROM reservation WHERE idcus = ?";
    $stmtCheck = mysqli_prepare($con, $checkQuery);
    mysqli_stmt_bind_param($stmtCheck, "i", $customer_id);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_bind_result($stmtCheck, $reservationCount);
    mysqli_stmt_fetch($stmtCheck);
    mysqli_stmt_close($stmtCheck);

    if ($reservationCount > 0) {
        // Set error message with type 'danger'
        $_SESSION['message'] = "Cannot delete customer: existing reservations found.";
        $_SESSION['msg_type'] = "danger"; // red alert
    } else {
        // Safe to delete customer
        $query = "DELETE FROM customer WHERE idcus = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $customer_id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = "Customer deleted successfully!";
        } else {
            $_SESSION['message'] = "Error deleting customer.";
        }
        mysqli_stmt_close($stmt);
    }

    header("Location: customer_regclk.php");
    exit();
}
// ===========================
// DELETE TRAVEL COMPANY
// ===========================

// Get POST data
$checkIn = $_POST['check_in_date'];
$checkOut = $_POST['check_out_date'];
$idroomCat = $_POST['idroom_cat'];
$numRequested = (int) $_POST['num_rooms'];

// 1. Get total rooms in the selected category
$sqlTotal = "SELECT COUNT(*) AS total_rooms FROM rooms WHERE idroom_cat = ?";
$stmtTotal = $con->prepare($sqlTotal);
$stmtTotal->bind_param("i", $idroomCat);
$stmtTotal->execute();
$resultTotal = $stmtTotal->get_result();
$totalRooms = $resultTotal->fetch_assoc()['total_rooms'] ?? 0;

// 2. Get rooms already reserved in the date range
$sqlReserved = "
    SELECT SUM(num_rooms) AS reserved_rooms
    FROM reservations
    WHERE idroom_cat = ?
    AND check_in_date < ?
    AND check_out_date > ?
";
$stmtReserved = $con->prepare($sqlReserved);
$stmtReserved->bind_param("iss", $idroomCat, $checkOut, $checkIn);
$stmtReserved->execute();
$resultReserved = $stmtReserved->get_result();
$reservedRooms = $resultReserved->fetch_assoc()['reserved_rooms'] ?? 0;

// 3. Calculate available rooms
$availableRooms = $totalRooms - $reservedRooms;

// 4. Output result
if ($availableRooms >= $numRequested) {
    echo "<h3 style='color: green;'>Good news! $numRequested room(s) are available in the selected category.</h3>";
    echo "<a href='make_reservation.php?check_in=$checkIn&check_out=$checkOut&idroom_cat=$idroomCat&num_rooms=$numRequested'>Proceed to Reservation</a>";
} else {
    echo "<h3 style='color: red;'>Sorry, only $availableRooms room(s) available for this category on the selected dates.</h3>";
}
$html = '';

if ($availableRooms >= $numRequested) {
    $html .= "<h3 style='color: green;'>Good news! $numRequested room(s) are available in the selected category.</h3>";

    // Escape values to be safe
    $escapedRoomCat = htmlspecialchars($idroomCat);
    $escapedNumRooms = htmlspecialchars($numRequested);
    $escapedCheckIn = htmlspecialchars($checkIn);
    $escapedCheckOut = htmlspecialchars($checkOut);

    $html .= '
    <form action="save_reservation.php" method="POST" class="form-control mt-4">
        <input type="hidden" name="idroom_cat" value="' . $escapedRoomCat . '">
        <input type="hidden" name="num_rooms" value="' . $escapedNumRooms . '">
        <input type="hidden" name="check_in_date" value="' . $escapedCheckIn . '">
        <input type="hidden" name="check_out_date" value="' . $escapedCheckOut . '">

        <div class="form-group">
            <label>Customer ID:</label>
            <input type="number" name="idcus" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Travel Company ID (optional):</label>
            <input type="number" name="travel_company_id" class="form-control">
        </div>

        <div class="form-group">
            <label>Number of Occupants:</label>
            <input type="number" name="num_occupants" class="form-control" value="1" required>
        </div>

        <div class="form-group">
            <label>Is Residential Suite?</label>
            <select name="is_residential_suite" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>

        <div class="form-group">
            <label>Special Requests:</label>
            <textarea name="special_requests" class="form-control" rows="3"></textarea>
        </div>

        <div class="form-group text-center pt-3">
            <input type="submit" name="confirm_reservation" value="Confirm Reservation" class="btn btn-success px-5">
        </div>
    </form>';
} else {
    $html .= "<h3 style='color: red;'>Sorry, only $availableRooms room(s) are available for this category on the selected dates.</h3>";
}
echo $html;




if (isset($_POST['confirm_reservation'])) {
    // Sanitize and fetch input values
    $idcus = $_POST['idcus'];
    $travel_company_id = !empty($_POST['travel_company_id']) ? $_POST['travel_company_id'] : null;
    $idroom_cat = $_POST['idroom_cat'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $num_rooms = $_POST['num_rooms'];
    $num_occupants = $_POST['num_occupants'];
    $is_residential_suite = $_POST['is_residential_suite'];
    $special_requests = $_POST['special_requests'];

    // Status is default 'tentative'
    $status = 'tentative';

    try {
        // Prepare SQL query
        $sql = "INSERT INTO reservation (
                    idcus, travel_company_id, idroom_cat,
                    check_in_date, check_out_date, reservation_date,
                    is_residential_suite, status, special_requests,
                    num_rooms, num_occupants
                ) VALUES (
                    ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iiississii",
            $idcus,
            $travel_company_id,
            $idroom_cat,
            $check_in_date,
            $check_out_date,
            $is_residential_suite,
            $status,
            $special_requests,
            $num_rooms,
            $num_occupants
        );

        if ($stmt->execute()) {
            echo "<h3 style='color: green;'>Reservation successfully saved!</h3>";
            // Optional: redirect or show reservation details
        } else {
            echo "<h3 style='color: red;'>Error saving reservation: " . $stmt->error . "</h3>";
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo "<h3 style='color: red;'>Exception: " . $e->getMessage() . "</h3>";
    }
} else {
    echo "<h3 style='color: red;'>Invalid form submission.</h3>";
}
?>






