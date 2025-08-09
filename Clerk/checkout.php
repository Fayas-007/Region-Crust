<?php
session_start();
include("../Database/db_connection.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$reservation_id = intval($_GET['reservation_id'] ?? 0);

// Fetch reservation + customer/travel company info
$stmt = $con->prepare("
    SELECT 
        r.*, 
        rc.price_room, rc.room_cat,
        c.cus_email, c.cus_name,
        tc.travel_contact_email, tc.travel_name
    FROM reservation r
    JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat
    LEFT JOIN customer c ON r.idcus = c.idcus
    LEFT JOIN travel_company tc ON r.travel_company_id = tc.travel_company_id
    WHERE r.reservation_id = ?
");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    $_SESSION['toast'] = ['message' => 'Reservation Not Found!', 'type' => 'error'];
    header("Location: reservation.php");
    exit;
}

$user_type = $res['idcus'] ? 'customer' : 'travel_company';
$cus_id = $res['idcus'] ?? null;
$tc_id = $res['travel_company_id'] ?? null;
$email = $res['cus_email'] ?: $res['travel_contact_email'];
$name = $res['cus_name'] ?: $res['travel_name'];

if (empty($email)) {
    $_SESSION['toast'] = ['message' => 'Email not found!', 'type' => 'error'];
    header("Location: reservation.php");
    exit;
}

$planned_nights = (strtotime($res['check_out_date']) - strtotime($res['check_in_date'])) / 86400;
$rate = floatval(str_replace(',', '', $res['price_room']));

$discount_stmt = $con->prepare("SELECT discount_percent FROM discounts WHERE user_type = ? AND active = 1 LIMIT 1");
$discount_stmt->bind_param("s", $user_type);
$discount_stmt->execute();
$discount_result = $discount_stmt->get_result()->fetch_assoc();
$discount_stmt->close();
$discount_percent = $discount_result['discount_percent'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actual_days_stayed'], $_POST['payment_method'])) {

    $actual_days_stayed = intval($_POST['actual_days_stayed']);
    $payment_method = $_POST['payment_method'];
    $additional_charges = floatval($_POST['additional_charges'] ?? 0);
    
    if ($actual_days_stayed < 1) {
        $_SESSION['toast'] = ['message' => 'Days stayed must be at least 1.', 'type' => 'error'];
        header("Location: checkout.php?reservation_id=" . $reservation_id);
        exit;
    }
    

    // Calculate new checkout date
    $check_in_timestamp = strtotime($res['check_in_date']);
    $new_checkout_date = date('Y-m-d', strtotime("+".($actual_days_stayed)." days", $check_in_timestamp));

    // Calculate total price
    $total = $rate * $res['num_rooms'] * $actual_days_stayed;
    $discount_amount = ($total * $discount_percent) / 100;
    $net_total = $total - $discount_amount + $additional_charges;

    // Check existing billing
    $check = $con->prepare("SELECT COUNT(*) as count FROM billing WHERE reservation_id = ?");
    $check->bind_param("i", $reservation_id);
    $check->execute();
    $already_billed = $check->get_result()->fetch_assoc()['count'];
    $check->close();

    if ($already_billed == 0) {
        // Update reservation's check_out_date to new one
        $update_res = $con->prepare("UPDATE reservation SET check_out_date = ?, status = 'checked-out' WHERE reservation_id = ?");
        $update_res->bind_param("si", $new_checkout_date, $reservation_id);
        $update_res->execute();
        $update_res->close();

        // Insert billing with new amount
        $insert = $con->prepare("INSERT INTO billing (reservation_id, customer_id, travel_company_id, user_type, amount, status, payment_method, additional_charges)
            VALUES (?, ?, ?, ?, ?, 'paid', ?, ?)");
        $insert->bind_param("iiisdss", $reservation_id, $cus_id, $tc_id, $user_type, $net_total, $payment_method, $additional_charges);

        $insert->execute();
        $insert->close();

        // Send invoice email
        $billing_date = date("Y-m-d H:i:s");
        $subject = "Your Reservation Invoice - RegionCrust Hotel";
        $message = "
        <html><body>
        <h2>Thank you for staying with RegionCrust Hotel!</h2>
        <p><strong>Reservation ID:</strong> {$res['reservation_id']}</p>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Billing Date:</strong> {$billing_date}</p>
        <p><strong>Payment Method:</strong> {$payment_method}</p>
        <table border='1' cellpadding='8' cellspacing='0'>
        <tr><th>Room Type</th><td>{$res['room_cat']}</td></tr>
        <tr><th>Check-in</th><td>{$res['check_in_date']}</td></tr>
        <tr><th>New Check-out</th><td>{$new_checkout_date}</td></tr>
        <tr><th>Actual Days Stayed</th><td>{$actual_days_stayed}</td></tr>
        <tr><th>Rooms</th><td>{$res['num_rooms']}</td></tr>
        <tr><th>Price/Room/Night</th><td>LKR {$rate}</td></tr>
        <tr><th>Subtotal</th><td>LKR{$total}</td></tr>
        <tr><th>Discount ({$discount_percent}%)</th><td>-LKR " . number_format($discount_amount, 2) . "</td></tr>
        <tr><th>Additional Charges</th><td>LKR " . number_format($additional_charges, 2) . "</td></tr>
        <tr><th><strong>Total</strong></th><td><strong>LKR " . number_format($net_total, 2) . "</strong></td></tr>
        </table>
        <p>Warm regards,<br>RegionCrust Hotel Team</p>
        </body></html>
        ";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'fayasshibly123@gmail.com';
            $mail->Password = 'pibf apdb xrey szjb';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('fayasshibly123@gmail.com', 'RegionCrust Hotel');
            $mail->addAddress($email, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->send();

            $_SESSION['toast'] = ['message' => 'Checkout completed and invoice sent!', 'type' => 'success'];
        } catch (Exception $e) {
            $_SESSION['toast'] = ['message' => "Checkout done, but email failed: {$mail->ErrorInfo}", 'type' => 'error'];
        }
        header("Location: reservation.php");
        exit;

    } else {
        $_SESSION['toast'] = ['message' => 'Billing already exists for this reservation.', 'type' => 'error'];
        header("Location: reservation.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout Reservation #<?= $reservation_id ?></title>
    <?php include("components/head.php"); ?>
    <script>
        // Run on page load and when days input changes
        function updateCheckoutInfo() {
            const checkInDate = new Date("<?= $res['check_in_date'] ?>");
            let daysStayed = parseInt(document.getElementById('actual_days_stayed').value);
            if (isNaN(daysStayed) || daysStayed < 1) daysStayed = 1;

            // Calculate new checkout date
            let newCheckout = new Date(checkInDate);
            newCheckout.setDate(newCheckout.getDate() + daysStayed);
            let yyyy = newCheckout.getFullYear();
            let mm = (newCheckout.getMonth() + 1).toString().padStart(2, '0');
            let dd = newCheckout.getDate().toString().padStart(2, '0');
            let newCheckoutStr = yyyy + '-' + mm + '-' + dd;

            document.getElementById('new_checkout_date').innerText = newCheckoutStr;

            // Calculate total
            let rate = <?= $rate ?>;
            let rooms = <?= $res['num_rooms'] ?>;
            let discountPercent = <?= $discount_percent ?>;

            let total = rate * rooms * daysStayed;
            let discountAmount = total * discountPercent / 100;
            let netTotal = total - discountAmount;

            document.getElementById('total_amount').innerText = netTotal.toFixed(2);
        }

        window.onload = updateCheckoutInfo;
    </script>
</head>
<body>
<div class="container mt-5">
    <h3>Checkout Reservation #<?= $reservation_id ?></h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
    <p><strong>Planned Check-in:</strong> <?= htmlspecialchars($res['check_in_date']) ?></p>
    <p><strong>Original Check-out:</strong> <?= htmlspecialchars($res['check_out_date']) ?></p>
    <p><strong>New Check-out Date:</strong> <span id="new_checkout_date"></span></p>
    <p><strong>Room Type:</strong> <?= htmlspecialchars($res['room_cat']) ?></p>
    <p><strong>Rooms Booked:</strong> <?= htmlspecialchars($res['num_rooms']) ?></p>
    <p><strong>Rate Per Room Per Night:</strong>LKR <?= number_format($rate, 2) ?></p>
    <p><strong>Total Amount (after discount): LKR <span id="total_amount"></span></strong></p>

    <form method="POST">
        <div class="form-group">
            <label for="actual_days_stayed">Actual Days Stayed:</label>
            <input type="number" name="actual_days_stayed" id="actual_days_stayed" min="1" max="365" value="<?= max(1, round($planned_nights)) ?>" required class="form-control" onchange="updateCheckoutInfo()">
        </div>
        <div class="form-group mt-3">
            <label for="additional_charges">Additional Charges (LKR):</label>
            <input type="number" step="0.01" name="additional_charges" id="additional_charges" class="form-control" value="0">
        </div>
        <div class="form-group mt-3">
            <label for="payment_method">Select Payment Method:</label>
            <select name="payment_method" id="payment_method" class="form-control" required>
                <option value="">-- Choose --</option>
                <option value="cash">Cash</option>
                <option value="credit_card">Card</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Confirm Checkout & Send Invoice</button>
    </form>
</div>
</body>
</html>
