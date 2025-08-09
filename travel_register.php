<?php

include("Database/db_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])){
    // Sanitize inputs
    $travel_name = filter_input(INPUT_POST, "travel_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $contact_person = filter_input(INPUT_POST, "travel_contact_person", FILTER_SANITIZE_SPECIAL_CHARS);
    $travel_phone = filter_input(INPUT_POST, "travel_phone", FILTER_SANITIZE_NUMBER_INT);
    $travel_address = filter_input(INPUT_POST, "travel_address", FILTER_SANITIZE_SPECIAL_CHARS);
    $pass = filter_input(INPUT_POST, "travel_password", FILTER_SANITIZE_SPECIAL_CHARS);
    $cpass = filter_input(INPUT_POST, "confirmPassword", FILTER_SANITIZE_SPECIAL_CHARS);
    $email_raw = $_POST['travel_contact_email'] ?? '';
    $travel_email = strtolower(trim($email_raw));

    $phone_pattern = "/^07\d{8}$/"; // SL phone number format

    // Validation checks
    if (strlen($travel_name) < 4) {
        $_SESSION['register_error'] = "Company name must be at least 4 characters long";
    } elseif (strlen($contact_person) < 3) {
        $_SESSION['register_error'] = "Contact person name must be at least 3 characters long";
    } elseif (!preg_match($phone_pattern, $travel_phone)) {
        $_SESSION['register_error'] = "Please enter a valid phone number format! [07xxxxxxxx]";
    } elseif (!filter_var($travel_email, FILTER_VALIDATE_EMAIL) || substr($travel_email, -10) !== '@gmail.com') {
        $_SESSION['register_error'] = "Only Gmail addresses ending with @gmail.com are allowed";
    } elseif ($pass !== $cpass) {
        $_SESSION['register_error'] = "Passwords do not match";
    } else {
        // Check if email already exists in customer table
        $customer_check_stmt = $con->prepare("SELECT cus_email FROM customer WHERE cus_email = ?");
        $customer_check_stmt->bind_param("s", $travel_email);
        $customer_check_stmt->execute();
        $customer_check_result = $customer_check_stmt->get_result();

        if ($customer_check_result->num_rows > 0) {
            $_SESSION['register_error'] = "This email is already registered as a customer";
            $customer_check_stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        $customer_check_stmt->close();

        // Check duplicates in travel_company table
        $stmt = $con->prepare("SELECT travel_name, travel_contact_email, travel_phone FROM travel_company WHERE travel_name = ? OR travel_contact_email = ? OR travel_phone = ?");
        $stmt->bind_param("sss", $travel_name, $travel_email, $travel_phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                if ($row['travel_name'] === $travel_name) {
                    $_SESSION['register_error'] = "This company name is already taken";
                }
                if ($row['travel_contact_email'] === $travel_email) {
                    $_SESSION['register_error'] = "This email is already registered";
                }
                if ($row['travel_phone'] === $travel_phone) {
                    $_SESSION['register_error'] = "This contact number is already registered";
                }
            }
            $stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Insert new travel company
            $hashed_password = password_hash($cpass, PASSWORD_DEFAULT);
            $insert_stmt = $con->prepare("INSERT INTO travel_company (travel_name, travel_contact_person, travel_phone, travel_contact_email, travel_address, travel_password) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $travel_name, $contact_person, $travel_phone, $travel_email, $travel_address, $hashed_password);

            if ($insert_stmt->execute()) {
                $_SESSION['register_success'] = true;

                // Optionally log user in after registration or redirect to login page
                header("Location: travel_reg_login.php"); // or wherever you want
                exit();
            } else {
                $_SESSION['register_error'] = "Registration failed: " . $con->error;
            }
            $insert_stmt->close();
        }
    }

    // Redirect back if any error
    if (isset($_SESSION['register_error'])) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
