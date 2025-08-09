<?php
include('Database/db_connection.php');
include("Components/toast.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["customer_login"])) {
    $user_input = trim(filter_input(INPUT_POST, "user_identifier", FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST["password"] ?? '');

    if (empty($user_input) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in all fields";
        header("Location: reg_login.php");
        exit;
    }

    $stmt = $con->prepare("SELECT * FROM customer WHERE cus_name = ? OR cus_email = ?");
    $stmt->bind_param("ss", $user_input, $user_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['cus_password'])) {
            $_SESSION["id"] = $user["idcus"];
            $_SESSION['user_type'] = 'customer';

            // Remember me
            if (isset($_POST['remember_user'])) {
                setcookie('remember_user', json_encode(['id' => $user["idcus"], 'type' => 'customer']), time() + (86400 * 30), "/");
            }

            $_SESSION['login_success'] = true;

            // Redirect back to same page (login/register page)
            header("Location: reg_login.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid password.";
            header("Location: reg_login.php");
            exit;
        }
    } else {
        $_SESSION['login_error'] = "No account found with that username or email.";
        header("Location: reg_login.php");
        exit;
    }

    $stmt->close();
}
?>
