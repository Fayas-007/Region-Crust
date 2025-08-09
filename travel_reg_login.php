<?php 
session_start();
include('Database/db_connection.php');
include('travel_register.php');
include('travel_login.php');
include('Components/toast.php');

// ✅ Check session or cookie
if (isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_COOKIE['remember_user'])) {
    // Decode the JSON cookie
    $remember_data = json_decode($_COOKIE['remember_user'], true);

    // Optional: Validate and set session (if you want to auto-login)
    if ($remember_data && isset($remember_data['id']) && $remember_data['type'] === 'travel_company') {
        $_SESSION['id'] = $remember_data['id']; // Auto-login
        header("Location: index.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include('Components/head.php'); ?>
<body>
    <div class="wrapper">
        <div class="container" id="container">
            <!-- Sign Up Form --> 
            <div class="form-container sign-up-container">
                <form id="signUpForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="return validatePasswords()">
                    <h1>Create Travel Company Account</h1>

                    <!-- Travel Company Name -->
                    <input type="text" name="travel_name" placeholder="Company Name" required minlength="4" />

                    <!-- Contact Person -->
                    <input type="text" name="travel_contact_person" placeholder="Contact Person" required minlength="3" />

                    <!-- Email -->
                    <input type="email" name="travel_contact_email" placeholder="Email" required />

                    <!-- Phone -->
                    <input type="text" name="travel_phone" placeholder="Phone Number"  title="Valid phone number" />

                    <!-- Address -->
                    <input type="text" name="travel_address" placeholder="Company Address" required />

                    <!-- Password -->
                    <input type="password" name="travel_password" id="password" placeholder="Password (min 4 chars)" required minlength="4" />

                    <!-- Confirm Password -->
                    <input type="password" id="confirmPassword"  name="confirmPassword" placeholder="Confirm Password" required />

                    <button type="submit" name="register">Sign Up</button>
                </form>
        </div>

            <!-- Sign In Form -->
            <div class="form-container sign-in-container">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <h1>Sign In</h1>
                    <div class="social-container">
                        <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                        <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                    </div>

                    <!-- Travel Company Email or name-->
                    <input type="text" name="travel_identifier" placeholder="Email or name" required />

                    <!-- Travel Company Password -->
                    <input type="password" name="travel_password" placeholder="Password" required />

                    <div style="margin: 10px 0;">
                        <label><input type="checkbox" name="remember_user" /> Remember Me</label>
                    </div>
                    <a href="#">Forgot your password?</a>
                    <button type="submit" name="travel_login">Sign In</button>
                </form>
            </div>
            <!-- Overlay Panels -->
            <div class="overlay-container">
                <div class="overlay">
                    <!-- Left Panel (Sign In) -->
                    <div class="overlay-panel overlay-left">
                        <h1>Welcome Back traveler! </h1>
                        <p>To keep connected with us please login with your personal info</p>
                        <button class="ghost" id="signIn">Sign In</button>
                        <br/><br/>
                        <!-- To go to user side -->
                        <a href="reg_login.php" class="toggle-link sticky-bottom-link" style="margin-top: 10px; display: inline-block; font-size: 14px;">
                            Signin / SignUp as user
                        </a>
                    </div>

                    <!-- Right Panel (Sign Up) -->
                    <div class="overlay-panel overlay-right">
                        <h1>Hello, traveler!</h1>
                        <p>Enter your personal details and start your journey with us</p>
                        <button class="ghost" id="signUp">Sign Up</button>
                        <br/><br>  <br/><br>  
                        <!-- To go to user side -->
                        <a href="reg_login.php" class="toggle-link sticky-bottom-link" style="margin-top: 10px; display: inline-block; font-size: 14px;">
                            Signin / SignUp as user
                        </a>
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

    <!-- Toast and script -->
    <script src="js/scripts.js"></script>
</body>
</html>
