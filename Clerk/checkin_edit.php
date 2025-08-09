<?php
session_start();
include("../Database/db_connection.php");

if (!isset($_GET['reservation_id'])) {
    echo "Reservation ID is required.";
    exit;
}

$reservation_id = intval($_GET['reservation_id']);

// Fetch number of rooms required for this reservation
$sql_num_rooms = "SELECT num_rooms FROM reservation WHERE reservation_id = $reservation_id LIMIT 1";
$result_num_rooms = mysqli_query($con, $sql_num_rooms);
if ($result_num_rooms && mysqli_num_rows($result_num_rooms) > 0) {
    $row_num_rooms = mysqli_fetch_assoc($result_num_rooms);
    $num_rooms_required = intval($row_num_rooms['num_rooms']);
} else {
    echo "Reservation not found or number of rooms not set.";
    exit;
}

// Fetch currently assigned rooms for reservation
$sql_current = "SELECT room_id FROM reservation_rooms WHERE reservation_id = $reservation_id";
$result_current = mysqli_query($con, $sql_current);
$current_room_ids = [];
while ($row = mysqli_fetch_assoc($result_current)) {
    $current_room_ids[] = $row['room_id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_rooms = isset($_POST['rooms']) ? $_POST['rooms'] : [];

    // Must select exactly 2 rooms
    if (count($selected_rooms) != $num_rooms_required) {
       $error = "Please select exactly $num_rooms_required rooms.";
    } else {
        // Sanitize selected room IDs
        $selected_rooms = array_map('intval', $selected_rooms);

        // 1. Remove all existing room assignments for this reservation
        $delete_sql = "DELETE FROM reservation_rooms WHERE reservation_id = $reservation_id";
        mysqli_query($con, $delete_sql);

        // 2. Insert new assignments for selected rooms
        $values = [];
        foreach ($selected_rooms as $room_id) {
            $values[] = "($reservation_id, $room_id)";
        }
        $insert_sql = "INSERT INTO reservation_rooms (reservation_id, room_id) VALUES " . implode(", ", $values);
        mysqli_query($con, $insert_sql);

        // Redirect back to checkin.php after saving
        header("Location: checkin.php?reservation_id=$reservation_id");
        exit;
    }
}

// Fetch all rooms with info whether they are assigned to other reservations (excluding current reservation)
$sql_all_rooms = "
    SELECT r.idroom, r.room_no, rc.room_cat,
           CASE WHEN rr2.room_id IS NOT NULL THEN 1 ELSE 0 END AS is_assigned_to_other
    FROM rooms r
    JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat
    LEFT JOIN reservation_rooms rr2 
      ON r.idroom = rr2.room_id AND rr2.reservation_id != $reservation_id
    ORDER BY rc.room_cat, r.room_no
";
$result_all_rooms = mysqli_query($con, $sql_all_rooms);


?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Rooms for Reservation <?= htmlspecialchars($reservation_id) ?></title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Edit Rooms Assigned to Reservation ID: <?= htmlspecialchars($reservation_id) ?></h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <p><strong>Select exactly <?= htmlspecialchars($num_rooms_required) ?> rooms:</strong></p>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>Room Number</th>
                        <th>Room Category</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = mysqli_fetch_assoc($result_all_rooms)): ?>
                        <tr>
                            <td>
                                <input 
                                    type="checkbox" 
                                    name="rooms[]" 
                                    value="<?= htmlspecialchars($room['idroom']) ?>"
                                    <?= in_array($room['idroom'], $current_room_ids) ? 'checked' : '' ?>
                                    <?= ($room['is_assigned_to_other'] == 1 && !in_array($room['idroom'], $current_room_ids)) ? 'disabled' : '' ?>
                                >
                            </td>
                            <td><?= htmlspecialchars($room['room_no']) ?></td>
                            <td><?= htmlspecialchars($room['room_cat']) ?></td>
                            <td>
                                <?= $room['is_assigned_to_other'] == 1 ? '<span class="text-danger">Unavailable</span>' : '<span class="text-success">Available</span>' ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between mt-4">
                <a href="checkin.php?reservation_id=<?= htmlspecialchars($reservation_id) ?>" 
                class="btn btn-secondary" style="width: 220px; text-align: center;">Cancel</a>
                <button type="submit" class="btn btn-success" style="width: 220px; text-align: center;">Save</button>
                
            </div>
        </form>
    </div>

    <script>
    // Limit user to exactly for num_rooms checkboxes checked in UI
            const numRoomsRequired = <?= json_encode($num_rooms_required) ?>;

            function updateCheckboxes() {
                const checkedCount = [...checkboxes].filter(cb => cb.checked).length;
                checkboxes.forEach(cb => {
                    if (!cb.checked) {
                        cb.disabled = checkedCount >= numRoomsRequired;
                    } else {
                        cb.disabled = false;
                    }
                });
            }
    </script>
</body>
</html>
