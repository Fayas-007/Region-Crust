<?php
session_start();

// Auto-login from cookie
if (isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_COOKIE['remember_user'])) {
    $remember_data = json_decode($_COOKIE['remember_user'], true);
    if ($remember_data && isset($remember_data['id']) && $remember_data['type'] === 'customer') {
        $_SESSION['id'] = $remember_data['id'];
        header("Location: index.php");
        exit();
    }
}

include('Database/db_connection.php');
include("Components/toast.php");
include('c_register.php');
include('c_login.php');
?>

<!DOCTYPE html>
<html lang="en">
<?php include('Components/head.php'); ?>
<body>
    <div class="wrapper">
        <div class="container" id="container">
            <!-- Sign Up -->
            <div class="form-container sign-up-container">
                <form method="post" action="">
                    <h1>Create Account</h1>
                    <input type="text" name="cus_name" placeholder="Full Name" required minlength="4" />
                    <input type="text" name="address" placeholder="Address" required />
                    <input type="text" name="cus_contact" placeholder="Contact Number" required pattern="[0-9+ ]{7,}" />
                    <input type="email" name="cus_email" placeholder="Email" required />
                    <input type="password" name="cus_password" id="password" placeholder="Password (min 4 chars)" required minlength="4" />
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required />
                    <button type="submit" name="register">Sign Up</button>
                </form>
            </div>

            <!-- Sign In -->
            <div class="form-container sign-in-container">
                <form method="post" action="">
                    <h1>Sign In</h1>
                    <input type="text" name="user_identifier" placeholder="Email or Username" required />
                    <input type="password" name="password" placeholder="Password" required />
                    <label><input type="checkbox" name="remember_user" /> Remember Me</label>
                    <a href="#">Forgot your password?</a>
                    <button type="submit" name="customer_login">Sign In</button>
                </form>
            </div>

            <!-- Overlay -->
            <div class="overlay-container">
                <div class="overlay">
                    <div class="overlay-panel overlay-left">
                        <h1>Welcome Back!</h1>
                        <p>To keep connected with us please login with your personal info</p>
                        <button class="ghost" id="signIn">Sign In</button>
                        <a href="travel_reg_login.php" class="ghost">Sign in as Travel Company</a>
                    </div>
                    <div class="overlay-panel overlay-right">
                        <h1>Hello, Friend!</h1>
                        <p>Enter your personal details and start your journey with us</p>
                        <button class="ghost" id="signUp">Sign Up</button>
                        <a href="travel_reg_login.php" class="ghost">Sign in as Travel Company</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side validation -->
    <script>
        function showToast(message, type = "success") {
            show_toast(message, type); // You already have this
        }
    </script>

    <script src="js/scripts.js"></script>

    <!-- ✅ Toast Display from Session -->
    <script>
        <?php
        if (isset($_SESSION['register_success'])) {
            echo 'show_toast("Registration successful!", "success");';
            unset($_SESSION['register_success']);
        }
        if (isset($_SESSION['register_error'])) {
            echo 'show_toast("' . $_SESSION['register_error'] . '", "error");';
            unset($_SESSION['register_error']);
        }
        ?>

        <?php if (isset($_SESSION['login_success'])): ?>
            show_toast("Login successful! Redirecting...", "success");
            setTimeout(() => {
                window.location.href = "index.php";
                  }, 3000);
        <?php unset($_SESSION['login_success']); endif; ?>

        <?php if (isset($_SESSION['login_error'])): ?>

            show_toast("<?= $_SESSION['login_error']; ?>", "error");
            
        <?php unset($_SESSION['login_error']); endif; ?>
    </script>
</body>
</html>
