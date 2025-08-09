<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["travel_login"]))  {
    include('Database/db_connection.php');

    // Sanitize user input
    $user_input = trim(filter_input(INPUT_POST, "travel_identifier", FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST["travel_password"] ?? '');

    // Validation
    if (empty($user_input) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    // Find user by username or email
    $stmt = $con->prepare("SELECT * FROM travel_company WHERE travel_name = ? OR travel_contact_email = ?");
    $stmt->bind_param("ss", $user_input, $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['travel_password'])) {
            $_SESSION["id"] = $user["travel_company_id"];
            $_SESSION['user_type'] = 'travel_company';

            // Remember Me cookie for 30 days
            if (isset($_POST['remember_user'])) {
                setcookie('remember_user', json_encode([
                    'id' => $user["travel_company_id"],
                    'type' => 'travel_company'
                ]), time() + 86400 * 30, "/");
            }

            $_SESSION['login_success'] = true;
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid password";
        }
    } else {
        $_SESSION['login_error'] = "No account found with that username or email";
    }

    $stmt->close();

    // Redirect back with error
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
