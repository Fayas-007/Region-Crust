<?php
session_start();
include("Database/db_connection.php");
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

// Get latest reservation ID
if ($user_type === 'customer') {
    $stmt = $con->prepare("SELECT reservation_id FROM reservation WHERE idcus = ? ORDER BY check_out_date DESC LIMIT 1");
    $stmt->bind_param("i", $customer_id);
} else if ($user_type === 'travel_company') {
    $stmt = $con->prepare("SELECT reservation_id FROM reservation WHERE travel_company_id = ? ORDER BY check_out_date DESC LIMIT 1");
    $stmt->bind_param("i", $customer_id);
} else {
    echo "Invalid user type.";
    echo '<script>show_toast("Invalid user type!", "error");</script>';
    exit();
}
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$reservation_id = $result['reservation_id'] ?? null;
if (!$reservation_id) {
    echo '<script>show_toast("Please log in to submit feedback", "error");</script>';
    exit();
}

// Fetch reservation details
$stmt = $con->prepare("SELECT r.*, rc.price_room, rc.room_cat, c.cus_name, c.cus_email, t.travel_name, t.travel_contact_email
    FROM reservation r
    JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat
    LEFT JOIN customer c ON r.idcus = c.idcus
    LEFT JOIN travel_company t ON r.travel_company_id = t.travel_company_id
    WHERE r.reservation_id = ?");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    echo '<script>show_toast("Reservation not found!", "error");</script>';
    exit();
}

if ($res['status'] !== 'checked-out') {
     echo '<script>show_toast("This reservation is not checked out yet.!", "error");</script>';
    exit();
}

// Billing calculation
$nights = (strtotime($res['check_out_date']) - strtotime($res['check_in_date'])) / 86400;
$rate = floatval(str_replace(',', '', $res['price_room']));
$total = $rate * $res['num_rooms'] * $nights;

// Discount
$discount_stmt = $con->prepare("SELECT discount_percent FROM discounts WHERE user_type = ? AND active = 1 LIMIT 1");
$discount_stmt->bind_param("s", $user_type);
$discount_stmt->execute();
$discount_result = $discount_stmt->get_result()->fetch_assoc();
$discount_stmt->close();

$discount_percent = $discount_result['discount_percent'] ?? 0;
$discount_amount = ($total * $discount_percent) / 100;
$net_total = $total - $discount_amount;

// 🟩 If form is submitted (Pay Now clicked)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    // Prevent duplicate billing
    $check = $con->prepare("SELECT COUNT(*) as count FROM billing WHERE reservation_id = ?");
    $check->bind_param("i", $reservation_id);
    $check->execute();
    $already_billed = $check->get_result()->fetch_assoc()['count'];
    $check->close();

    if ($already_billed == 0) {
        $cus_id = $res['idcus'] ?? null;
        $tc_id = $res['travel_company_id'] ?? null;

        $insert = $con->prepare("INSERT INTO billing (reservation_id, customer_id, travel_company_id, user_type, amount, status, payment_method)
            VALUES (?, ?, ?, ?, ?, 'pending', NULL)");
        $insert->bind_param("iiisd", $reservation_id, $cus_id, $tc_id, $user_type, $net_total);
        $insert->execute();
        $insert->close();

        echo '<script>show_toast("Billing created successfully!", "success");</script>';
    } else {
        echo '<script>show_toast("Bill is sent to your mail!", "error");</script>';
    }
    header("Location: index.php");
     header("Location: reg_login.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include('Components/head.php'); ?>
<body>
<?php include('Components/header.php'); ?>

<div class="checkout-wrapper">
    <!-- Left: Reservation Details -->
    <div class="cart-section">
        <h2>Your Reservation</h2>
        <div class="cart-item-row"><strong>Reservation ID:</strong> <?= htmlspecialchars($res['reservation_id']) ?></div>
        <div class="cart-item-row"><strong>Name:</strong> <?= ($user_type === 'customer') ? htmlspecialchars($res['cus_name']) : htmlspecialchars($res['travel_name']) ?></div>
        <div class="cart-item-row"><strong>Room Type:</strong> <?= htmlspecialchars($res['room_cat']) ?></div>
        <div class="cart-item-row"><strong>Check-in:</strong> <?= htmlspecialchars($res['check_in_date']) ?></div>
        <div class="cart-item-row"><strong>Check-out:</strong> <?= htmlspecialchars($res['check_out_date']) ?></div>
        <div class="cart-item-row"><strong>Number of Rooms:</strong> <?= $res['num_rooms'] ?></div>
        <div class="cart-item-row"><strong>Nights:</strong> <?= $nights ?></div>
    </div>

    <!-- Right: Billing Summary -->
    <div class="summary-section">
        <h2>Order Summary</h2>
        <table>
            <tr><th>Price/Room/Day</th><td>$<?= number_format($rate, 2) ?></td></tr>
            <tr><th>Days</th><td><?= $nights ?> Days</td></tr>
            <tr><th>Subtotal</th><td>$<?= number_format($total, 2) ?></td></tr>
            <tr><th>Discount (<?= $discount_percent ?>%)</th><td>-$<?= number_format($discount_amount, 2) ?></td></tr>
        </table>

        <div class="total-line">
            Net Total: <strong>$<?= number_format($net_total, 2) ?></strong>
        </div>

        <!-- Only when this form is submitted, email and billing insert occurs -->
        <form method="POST">
            <button type="submit" class="pay-btn" name="pay_now">Pay Now</button>
        </form>

        <div class="note">
            * Please review your reservation before proceeding to payment.
        </div>
    </div>
</div>
<script src="js/scripts.js"></script>
</body>
</html>
