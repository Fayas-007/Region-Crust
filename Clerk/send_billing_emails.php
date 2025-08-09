<?php
// Include your database connection (adjust path as needed)
include("../Database/db_connection.php");

// PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/Exception.php';
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';

// Select reservations that are not no-show yet, but eligible for no-show billing
$sql = "
    SELECT 
        r.reservation_id,
        r.status,
        r.check_in_date,
        r.check_out_date,
        r.num_rooms,
        r.idcus,
        r.travel_company_id,
        rc.price_room,
        rc.room_cat,
        c.cus_email,
        c.cus_name,
        tc.travel_contact_email,
        tc.travel_name
    FROM reservation r
    JOIN room_cat rc ON r.idroom_cat = rc.idroom_cat
    LEFT JOIN customer c ON r.idcus = c.idcus
    LEFT JOIN travel_company tc ON r.travel_company_id = tc.travel_company_id
    WHERE 
        r.status NOT IN ('no-show', 'checked-out')
        AND r.check_in_date < CURDATE()
";

$result = $con->query($sql);
if (!$result) {
    error_log("DB Query Failed: " . $con->error);
    exit("Database query error.");
}

while ($res = $result->fetch_assoc()) {
    $reservation_id = $res['reservation_id'];
    
    $nights = (strtotime($res['check_out_date']) - strtotime($res['check_in_date'])) / 86400;
    $rate = floatval(str_replace(',', '', $res['price_room']));
    $total = $rate * $res['num_rooms'] * $nights;
    $penalty_percent = 50;
    $penalty_amount = ($total * $penalty_percent) / 100;
    
    $user_type = $res['idcus'] ? 'customer' : 'travel_company';
    $cus_id = $res['idcus'] ?? null;
    $tc_id = $res['travel_company_id'] ?? null;

    $email = $res['cus_email'] ?: $res['travel_contact_email'];
    $name = $res['cus_name'] ?: $res['travel_name'];
    if (empty($email)) {
        error_log("Email not found for reservation ID $reservation_id");
        continue;
    }

    // Check if billing already exists
    $check_stmt = $con->prepare("SELECT COUNT(*) as count FROM billing WHERE reservation_id = ?");
    $check_stmt->bind_param("i", $reservation_id);
    $check_stmt->execute();
    $count = $check_stmt->get_result()->fetch_assoc()['count'];
    $check_stmt->close();

    if ($count > 0) {
        continue;
    }

    $insert = $con->prepare("INSERT INTO billing 
        (reservation_id, customer_id, travel_company_id, user_type, amount, status, payment_method, notes, email_sent)
        VALUES (?, ?, ?, ?, ?, 'paid', 'credit_card', ?, 1)");

    $notes = 'No-show penalty (50%) automatically billed due to guest absence on scheduled check-in date.';
    $insert->bind_param("iiisds", $reservation_id, $cus_id, $tc_id, $user_type, $penalty_amount, $notes);
    $insert->execute();
    $insert->close();

    // Update reservation status
    $update_stmt = $con->prepare("UPDATE reservation SET status = 'no-show' WHERE reservation_id = ?");
    $update_stmt->bind_param("i", $reservation_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Compose email (same style as your first script)
    $subject = "No-show Penalty Invoice - RegionCrust Hotel";
    $message = "
    <html><body>
    <h2>Important: No-show Penalty Invoice from RegionCrust Hotel</h2>
    <p>Dear {$name},</p>
    <p>You missed your reservation (ID: {$reservation_id}) without notice.</p>
    <p>As per our policy, a no-show penalty has been applied.</p>
    <table border='1' cellpadding='8' cellspacing='0'>
        <tr><th>Room Type</th><td>{$res['room_cat']}</td></tr>
        <tr><th>Check-in</th><td>{$res['check_in_date']}</td></tr>
        <tr><th>Check-out</th><td>{$res['check_out_date']}</td></tr>
        <tr><th>Rooms</th><td>{$res['num_rooms']}</td></tr>
        <tr><th>Price/Room</th><td>LKR " . number_format($rate, 2) . "</td></tr>
        <tr><th>Nights</th><td>{$nights}</td></tr>
        <tr><th>Total Booking Price</th><td>LKR " . number_format($total, 2) . "</td></tr>
        <tr><th>No-show Penalty ({$penalty_percent}%)</th><td><strong>LKR " . number_format($penalty_amount, 2) . "</strong></td></tr>
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

        error_log("No-show penalty email sent for reservation ID $reservation_id");

    } catch (Exception $e) {
        error_log("Email sending failed for reservation ID $reservation_id: " . $mail->ErrorInfo);
    }
}

$con->close();
?>

