<?php
session_start();
include("../Database/db_connection.php");
include("../Components/toast.php");
include("../Components/head.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// 1. Validate reservation_id input
if (!isset($_GET['reservation_id']) || empty($_GET['reservation_id'])) {
    echo '<script>show_toast("Missing Reservation ID!!", "error");</script>';
    exit;
}

$reservation_id = intval($_GET['reservation_id']);

// 2. Fetch reservation + customer/travel company info + room price
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
    echo '<script>show_toast("Reservation Not Found!!", "error");</script>';
    exit;
}

// 3. Check if reservation already checked-out or no-show
if ($res['status'] === 'checked-out' || $res['status'] === 'no-show') {
    echo '<script>show_toast("This reservation is already processed!", "error");</script>';
    exit;
}

// 4. Determine user type & recipient email/name
$user_type = $res['idcus'] ? 'customer' : 'travel_company';
$cus_id = $res['idcus'] ?? null;
$tc_id = $res['travel_company_id'] ?? null;

$email = $res['cus_email'] ?: $res['travel_contact_email'];
$name = $res['cus_name'] ?: $res['travel_name'];
if (empty($email)) {
    echo '<script>show_toast("Email not found!!", "error");</script>';
    exit;
}

// 5. Calculate no-show penalty
// Example: 50% of total booking price as penalty, customize as needed
$nights = (strtotime($res['check_out_date']) - strtotime($res['check_in_date'])) / 86400;
$rate = floatval(str_replace(',', '', $res['price_room']));
$total = $rate * $res['num_rooms'] * $nights;

$penalty_percent = 50; // 50% penalty for no-show
$penalty_amount = ($total * $penalty_percent) / 100;

// 6. Check for existing billing to avoid duplicates
$check = $con->prepare("SELECT COUNT(*) as count FROM billing WHERE reservation_id = ?");
$check->bind_param("i", $reservation_id);
$check->execute();
$already_billed = $check->get_result()->fetch_assoc()['count'];
$check->close();

if ($already_billed > 0) {
    header("reservation.php");
    echo '<script>show_toast("Billing already exists for this reservation!", "error");</script>';
    exit;
}

// 7. Insert billing record for no-show penalty
$insert = $con->prepare("INSERT INTO billing (reservation_id, customer_id, travel_company_id, user_type, amount, status, payment_method)
    VALUES (?, ?, ?, ?, ?, 'paid', 'credit_card')");
$insert->bind_param("iiisd", $reservation_id, $cus_id, $tc_id, $user_type, $penalty_amount);
$insert->execute();
$insert->close();

// 8. Update reservation status to no-show
$update = $con->prepare("UPDATE reservation SET status = 'no-show' WHERE reservation_id = ?");
$update->bind_param("i", $reservation_id);
$update->execute();
$update->close();

// 9. Send no-show penalty email invoice
$subject = "No-show Penalty Invoice - RegionCrust Hotel";
$billing_date = date("Y-m-d H:i:s");

$message = "
<html><body>
<h2>Important: No-show Penalty Invoice from RegionCrust Hotel</h2>
<p>Dear {$name},</p>
<p>You missed your reservation (ID: {$res['reservation_id']}) without notice.</p>
<p>As per our policy, a no-show penalty has been applied.</p>
<table border='1' cellpadding='8' cellspacing='0'>
<tr><th>Room Type</th><td>{$res['room_cat']}</td></tr>
<tr><th>Check-in</th><td>{$res['check_in_date']}</td></tr>
<tr><th>Check-out</th><td>{$res['check_out_date']}</td></tr>
<tr><th>Rooms</th><td>{$res['num_rooms']}</td></tr>
<tr><th>Price/Room</th><td>\${$rate}</td></tr>
<tr><th>Nights</th><td>{$nights}</td></tr>
<tr><th>Total Booking Price</th><td>\${$total}</td></tr>
<tr><th>No-show Penalty ({$penalty_percent}%)</th><td><strong>\${$penalty_amount}</strong></td></tr>
</table>
<p>Please contact us if you have any questions.</p>
<p>Regards,<br>RegionCrust Hotel Team</p>
</body></html>
";

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'fayasshibly123@gmail.com';  // your email
    $mail->Password = 'pibf apdb xrey szjb';       // your app password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('fayasshibly123@gmail.com', 'RegionCrust Hotel');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;

    $mail->send();
    echo '<script>show_toast("No-show penalty invoiced successfully!", "success");</script>';
    exit;

} catch (Exception $e) {
    echo '<script>show_toast("Email sending failed: ' . htmlspecialchars($mail->ErrorInfo) . '", "error");</script>';
    exit;
}
?>
