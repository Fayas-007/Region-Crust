<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    include("Database/db_connection.php");

    $username = filter_input(INPUT_POST, "cus_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $phone = filter_input(INPUT_POST, "cus_contact", FILTER_SANITIZE_NUMBER_INT);
    $address = filter_input(INPUT_POST, "address", FILTER_SANITIZE_SPECIAL_CHARS);
    $pass = filter_input(INPUT_POST, "cus_password", FILTER_SANITIZE_SPECIAL_CHARS);
    $cpass = filter_input(INPUT_POST, "confirmPassword", FILTER_SANITIZE_SPECIAL_CHARS);
    $email_raw = $_POST['cus_email'] ?? '';
    $email_lower = strtolower(trim($email_raw));
    $pattern = "/^07\d{8}$/"; // SL format

    // Validate inputs
    if (strlen($username) < 4) {
        $_SESSION['register_error'] = "Username must be at least 4 characters long.";
    } elseif (!preg_match($pattern, $phone)) {
        $_SESSION['register_error'] = "Please enter a valid phone number format! [07xxxxxxxx]";
    } elseif (!filter_var($email_lower, FILTER_VALIDATE_EMAIL) || substr($email_lower, -10) !== '@gmail.com') {
        $_SESSION['register_error'] = "Only Gmail addresses ending with @gmail.com are allowed.";
    } elseif ($pass !== $cpass) {
        $_SESSION['register_error'] = "Passwords do not match.";
    } else {
        // Check for duplicates
        $stmt = $con->prepare("SELECT cus_name, cus_email, cus_contact FROM customer WHERE cus_name = ? OR cus_email = ? OR cus_contact = ?");
        $stmt->bind_param("sss", $username, $email_lower, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['cus_name'] === $username) $_SESSION['register_error'] = "This username is already taken.";
                if ($row['cus_email'] === $email_lower) $_SESSION['register_error'] = "This email is already registered.";
                if ($row['cus_contact'] === $phone) $_SESSION['register_error'] = "This contact number is already registered.";
            }
        } else {
            $hashed_password = password_hash($cpass, PASSWORD_DEFAULT);
            $insert_stmt = $con->prepare("INSERT INTO customer (cus_name, address, cus_contact, cus_email, cus_password) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $username, $address, $phone, $email_lower, $hashed_password);

            if ($insert_stmt->execute()) {
                $_SESSION['register_success'] = true;
            } else {
                $_SESSION['register_error'] = "Registration failed: " . $con->error;
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }

    // Redirect back to avoid form re-submission and show toast
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
