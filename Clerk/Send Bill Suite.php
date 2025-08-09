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
    WHERE r.reservation_id = ? AND r.is_residential_suite = 1
");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    $_SESSION['toast'] = ['message' => 'Suite Reservation Not Found!', 'type' => 'error'];
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

// Fixed rate per room per week
$rate = 60000;

$check_in_date = $res['check_in_date'];
$today = date('Y-m-d');
$diff_days = ceil((strtotime($today) - strtotime($check_in_date)) / 86400);

// Determine billing cycle (weekly or monthly)
$billing_cycle = $diff_days <= 30 ? 'weekly' : 'monthly';
$cycle_days = $billing_cycle === 'weekly' ? 7 : 30;
$cycles = ceil($diff_days / $cycle_days);

$num_rooms = intval($res['num_rooms']);

// Prevent duplicate billing before cycle completes
$lastBillStmt = $con->prepare("SELECT billing_date FROM billing WHERE reservation_id = ? ORDER BY billing_date DESC LIMIT 1");
$lastBillStmt->bind_param("i", $reservation_id);
$lastBillStmt->execute();
$lastBillResult = $lastBillStmt->get_result()->fetch_assoc();
$lastBillStmt->close();

if ($lastBillResult) {
    $last_billed_date = strtotime($lastBillResult['billing_date']);
    $now = strtotime(date('Y-m-d H:i:s'));
    $diff = ($now - $last_billed_date) / 86400;

    if ($diff < $cycle_days) {
        $_SESSION['toast'] = ['message' => "You can only send the suite bill once every {$cycle_days} days. Last billed on " . date("Y-m-d", $last_billed_date), 'type' => 'warning'];
        header("Location: reservation.php");
        exit;
    }
}

// Calculate total: rate × number of rooms × billing cycles
$total = $rate * $num_rooms * $cycles;

// Discount calculation
$discount_stmt = $con->prepare("SELECT discount_percent FROM discounts WHERE user_type = ? AND active = 1 LIMIT 1");
$discount_stmt->bind_param("s", $user_type);
$discount_stmt->execute();
$discount_result = $discount_stmt->get_result()->fetch_assoc();
$discount_stmt->close();
$discount_percent = $discount_result['discount_percent'] ?? 0;
$discount_amount = ($total * $discount_percent) / 100;
$net_total = $total - $discount_amount;

$billing_date = date("Y-m-d H:i:s");
$subject = "Residential Suite Invoice - RegionCrust Hotel";
$message = "
<html><body>
<h2>Your Suite Billing Summary</h2>
<p><strong>Reservation ID:</strong> {$res['reservation_id']}</p>
<p><strong>Name:</strong> {$name}</p>
<p><strong>Billing Cycle:</strong> {$billing_cycle}</p>
<p><strong>Billing Date:</strong> {$billing_date}</p>
<table border='1' cellpadding='8' cellspacing='0'>
<tr><th>Room Type</th><td>{$res['room_cat']} (Residential Suite)</td></tr>
<tr><th>Check-in</th><td>{$res['check_in_date']}</td></tr>
<tr><th>Today</th><td>{$today}</td></tr>
<tr><th>Billing Cycles</th><td>{$cycles} {$billing_cycle}(s)</td></tr>
<tr><th>Rooms</th><td>{$num_rooms}</td></tr>
<tr><th>Rate/Room/{$billing_cycle}</th><td>LKR {$rate}</td></tr>
<tr><th>Subtotal</th><td>LKR {$total}</td></tr>
<tr><th>Discount ({$discount_percent}%)</th><td>-LKR {$discount_amount}</td></tr>
<tr><th><strong>Total</strong></th><td><strong>LKR {$net_total}</strong></td></tr>
</table>
<p>Thank you for staying with RegionCrust Hotel!</p>
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

    // Insert billing record
    $insert = $con->prepare("INSERT INTO billing (reservation_id, customer_id, travel_company_id, user_type, amount, status, payment_method)
        VALUES (?, ?, ?, ?, ?, 'paid', 'auto-email')");
    $insert->bind_param("iiisd", $reservation_id, $cus_id, $tc_id, $user_type, $net_total);
    $insert->execute();
    $insert->close();

    $_SESSION['toast'] = ['message' => 'Suite invoice sent successfully!', 'type' => 'success'];
} catch (Exception $e) {
    $_SESSION['toast'] = ['message' => "Failed to send invoice: {$mail->ErrorInfo}", 'type' => 'error'];
}

header("Location: reservation.php");
exit;
?>
