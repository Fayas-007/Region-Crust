<?php
session_start();
include("../Database/db_connection.php");

if (!isset($_GET['reservation_id'])) {
    echo "Reservation ID is required.";
    exit;
}

$reservation_id = intval($_GET['reservation_id']);

// Handle confirm button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkin'])) {
    $updateSql = "UPDATE reservation SET status = 'checked-in' WHERE reservation_id = $reservation_id";
    mysqli_query($con, $updateSql);
    
    header("Location: reservation.php");
    exit;
}

$sql = "
    SELECT 
        rr.room_id,
        r.room_no,
        r.status,
        rc.room_cat,
        rc.price_room
    FROM reservation_rooms rr
    JOIN rooms r ON rr.room_id = r.idroom
    JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat
    WHERE rr.reservation_id = $reservation_id
";

$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check-In Room Details</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Rooms Assigned for Reservation ID: <?= htmlspecialchars($reservation_id) ?></h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Category</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= htmlspecialchars($room['room_no']) ?></td>
                            <td><?= htmlspecialchars($room['room_cat']) ?></td>
                            <td><?= htmlspecialchars($room['price_room']) ?></td>
                            <td><?= $room['status'] == 0 ? "Available" : "Unavailable" ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No rooms assigned to this reservation yet.</p>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="row mt-4">
            <div class="col-md-4 mb-2">
                <a href="reservation.php" class="btn btn-secondary w-100">Back to Reservations</a>
            </div>
            <div class="col-md-4 mb-2">
                <a href="checkin_edit.php?reservation_id=<?= $reservation_id ?>" class="btn btn-primary w-100">Edit</a>
            </div>
            <div class="col-md-4 mb-2">
                <form method="POST">
                    <button type="submit" name="confirm_checkin" class="btn btn-success w-100">Confirm</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
