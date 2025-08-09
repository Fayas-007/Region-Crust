<?php
session_start();
include("../Database/db_connection.php");

if (!isset($_GET['reservation_id'])) {
    die("Reservation ID missing.");
}

$reservation_id = intval($_GET['reservation_id']);

// Fetch current reservation with room category name
$sql = "
SELECT r.*, rc.room_cat 
FROM reservation r 
JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat 
WHERE r.reservation_id = $reservation_id
";
$result = mysqli_query($con, $sql);
$reservation = mysqli_fetch_assoc($result);

if (!$reservation) {
    die("Reservation not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $idroom_cat = intval($_POST['idroom_cat'] ?? $reservation['idroom_cat']);
    $num_rooms = intval($_POST['num_rooms'] ?? $reservation['num_rooms']);
    $num_occupants = intval($_POST['num_occupants'] ?? $reservation['num_occupants']);
    $special_requests = mysqli_real_escape_string($con, $_POST['special_requests'] ?? $reservation['special_requests']);
    $credit_card = trim($_POST['credit_card'] ?? '');

    // Determine new status
    $status = (!empty($credit_card)) ? 'confirmed' : $reservation['status'];

    if (strtotime($check_in) > strtotime($check_out)) {
        echo "<script>alert('Check-out date must be after check-in date');</script>";
    } else {
        // Step 1: Get all rooms in this category that are available (status=0)
        $stmt = $con->prepare("SELECT idroom FROM rooms WHERE idroom_cat = ? AND status = 0");
        $stmt->bind_param("i", $idroom_cat);
        $stmt->execute();
        $result_rooms = $stmt->get_result();

        $available_rooms = [];
        while ($row = $result_rooms->fetch_assoc()) {
            $available_rooms[] = $row['idroom'];
        }
        $stmt->close();

        // Step 2: Get rooms assigned to current reservation (keep these assigned)
        $stmt = $con->prepare("SELECT room_id FROM reservation_rooms WHERE reservation_id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result_current_reservation_rooms = $stmt->get_result();

        $current_reservation_rooms = [];
        while ($row = $result_current_reservation_rooms->fetch_assoc()) {
            $current_reservation_rooms[] = $row['room_id'];
        }
        $stmt->close();

        // Step 3: Find rooms booked in overlapping reservations (exclude current)
        $sql_booked = "
            SELECT rr.room_id 
            FROM reservation_rooms rr 
            JOIN reservation r ON rr.reservation_id = r.reservation_id
            WHERE r.reservation_id != ?
            AND r.idroom_cat = ?
            AND (
                (r.check_in_date <= ? AND r.check_out_date > ?)
                OR (r.check_in_date < ? AND r.check_out_date >= ?)
                OR (r.check_in_date >= ? AND r.check_out_date <= ?)
            )
        ";
        $stmt = $con->prepare($sql_booked);
        $stmt->bind_param("iissssss", $reservation_id, $idroom_cat, $check_in, $check_in, $check_out, $check_out, $check_in, $check_out);
        $stmt->execute();
        $result_booked = $stmt->get_result();

        $booked_rooms = [];
        while ($row = $result_booked->fetch_assoc()) {
            $booked_rooms[] = $row['room_id'];
        }
        $stmt->close();

        // Step 4: Calculate free rooms = (available rooms - booked rooms) + currently assigned rooms
        $free_rooms = array_unique(array_merge(
            array_diff($available_rooms, $booked_rooms),
            $current_reservation_rooms
        ));

        if ($num_rooms > count($free_rooms)) {
            echo "<script>alert('Not enough rooms available in selected category for the chosen dates. Available rooms: " . count($free_rooms) . "');</script>";
            exit;
        }

        // Step 5: Update the reservation record
        $update_sql = "UPDATE reservation SET
            check_in_date = ?,
            check_out_date = ?,
            idroom_cat = ?,
            num_rooms = ?,
            num_occupants = ?,
            special_requests = ?,
            status = ?
            WHERE reservation_id = ?";

        $stmt = $con->prepare($update_sql);
        $stmt->bind_param(
            "ssiiissi",
            $check_in,
            $check_out,
            $idroom_cat,
            $num_rooms,
            $num_occupants,
            $special_requests,
            $status,
            $reservation_id
        );

        if ($stmt->execute()) {
            // Step 6: Update reservation_rooms table
            $stmt_del = $con->prepare("DELETE FROM reservation_rooms WHERE reservation_id = ?");
            $stmt_del->bind_param("i", $reservation_id);
            $stmt_del->execute();
            $stmt_del->close();

            $rooms_to_assign = array_slice($free_rooms, 0, $num_rooms);

            $stmt_ins = $con->prepare("INSERT INTO reservation_rooms (reservation_id, room_id) VALUES (?, ?)");
            foreach ($rooms_to_assign as $room_id) {
                $stmt_ins->bind_param("ii", $reservation_id, $room_id);
                $stmt_ins->execute();
            }
            $stmt_ins->close();

            echo "<script>alert('Reservation updated successfully'); window.location.href='reservation.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error updating reservation: " . $con->error . "');</script>";
        }
    }
$credit_card = trim($_POST['credit_card'] ?? '');
$status = $reservation['status'];
$save_card = false;

if (!empty($credit_card) && strlen($credit_card) >= 13 && strlen($credit_card) <= 19) {
    $status = 'confirmed';
    $save_card = true;
}

// ... your code to update reservation record here, with the updated $status ...

if ($stmt->execute()) {
    // Update reservation_rooms etc.

    // Now update credit card info in appropriate table
    if ($save_card) {
        if (!empty($reservation['idcus'])) {
            // Update individual customer credit card
            $idcus = $reservation['idcus'];
            $stmt_cus = $con->prepare("UPDATE customer SET credit_cardnum = ? WHERE idcus = ?");
            $stmt_cus->bind_param("si", $credit_card, $idcus);
            $stmt_cus->execute();
            $stmt_cus->close();
        } elseif (!empty($reservation['travel_company_id'])) {
            // Update travel company credit card
            $travel_company_id = $reservation['travel_company_id'];
            $stmt_travel = $con->prepare("UPDATE travel_company SET travel_credit_cardnum = ? WHERE travel_company_id = ?");
            $stmt_travel->bind_param("si", $credit_card, $travel_company_id);
            $stmt_travel->execute();
            $stmt_travel->close();
        }
    }

    echo "<script>alert('Reservation updated successfully'); window.location.href='reservation.php';</script>";
    exit;
} else {
    echo "<script>alert('Error updating reservation: " . $con->error . "');</script>";
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Reservation</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Reservation #<?= htmlspecialchars($reservation_id) ?></h2>
    <form method="POST">

        <div class="form-group">
            <label>Check-In Date</label>
            <input type="date" name="check_in" class="form-control" 
                   value="<?= htmlspecialchars(date('Y-m-d', strtotime($reservation['check_in_date']))) ?>" required>
        </div>

        <div class="form-group">
            <label>Check-Out Date</label>
            <input type="date" name="check_out" class="form-control" 
                   value="<?= htmlspecialchars(date('Y-m-d', strtotime($reservation['check_out_date']))) ?>" required>
        </div>

        <div class="form-group">
            <label>Room Category</label>
            <select name="idroom_cat" class="form-control" required>
                <?php
                $cats = mysqli_query($con, "SELECT * FROM room_cat");
                while ($cat = mysqli_fetch_assoc($cats)) {
                    $selected = $cat['idroom_cat'] == $reservation['idroom_cat'] ? 'selected' : '';
                    echo "<option value='{$cat['idroom_cat']}' $selected>" . htmlspecialchars($cat['room_cat']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Number of Rooms</label>
            <input type="number" name="num_rooms" class="form-control" value="<?= htmlspecialchars($reservation['num_rooms']) ?>" min="1" required>
        </div>

        <div class="form-group">
            <label>Number of Occupants</label>
            <input type="number" name="num_occupants" class="form-control" value="<?= htmlspecialchars($reservation['num_occupants']) ?>" min="1" required>
        </div>

        <div class="form-group">
            <label>Special Requests</label>
            <textarea name="special_requests" class="form-control"><?= htmlspecialchars($reservation['special_requests']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Credit Card Number (optional)</label>
            <input type="text" name="credit_card" class="form-control" minlength="13" maxlength="19" placeholder="Enter credit card number">
        </div>

        <button type="submit" class="btn btn-primary">Update Reservation</button>
        <a href="reservation.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
