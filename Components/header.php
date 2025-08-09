<?php
include("Database/db_connection.php");

$customer_id = '';
$user_type = '';

// Session or Cookie-based login check
if (isset($_SESSION["id"]) && isset($_SESSION["user_type"])) {
    $customer_id = $_SESSION["id"];
    $user_type = $_SESSION["user_type"];
} elseif (isset($_COOKIE['remember_user'])) {
    $remember_data = json_decode($_COOKIE['remember_user'], true);
    $customer_id = $remember_data['id'] ?? '';
    $user_type = $remember_data['type'] ?? '';
    $_SESSION["id"] = $customer_id;
    $_SESSION["user_type"] = $user_type;
} else {
    header("Location: reg_login.php");
    exit();
}
?>

<header class="Navigation">
    <a href="index.php" class="logo"><span>The Region Crust</span> Hotels</a>

    <?php
    function getCurrentPageClass($page) {
        $currentPage = basename($_SERVER['PHP_SELF']);
        return ($currentPage == $page) ? 'selected' : '';
    }
    ?>
    <nav class="navbar">
        <a href="index.php" class="<?= getCurrentPageClass('index.php'); ?>">Home</a>
        <a href="Reservation.php" class="<?= getCurrentPageClass('Reservation.php'); ?>">Reservation</a>
        <a href="index.php#feedback">Feedback</a>
        <a href="index.php#about">About Us</a>
    </nav>

    <div>
        <div id="user-btn" class="fa-solid fa-user"></div>
        <div id="menu-btn" class="fas fa-bars"></div>
    </div>

    <div class="profile">
        <?php
        try {
            if (!empty($customer_id)) {
                // Prepare query based on user type
                if ($user_type === "customer") {
                    $stmt = $con->prepare("SELECT cus_name AS name FROM customer WHERE idcus = ?");
                } elseif ($user_type === "travel_company") {
                    $stmt = $con->prepare("SELECT 	travel_name AS name FROM travel_company WHERE travel_company_id = ?");
                } else {
                    throw new Exception("Invalid user type.");
                }

                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $con->error);
                }

                $stmt->bind_param("i", $customer_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $fetch_profile = $result->fetch_assoc();
        ?>
                    <p class="name" style="text-transform: capitalize;">
                        <?= htmlspecialchars($fetch_profile['name']) ?>
                    </p>
                    <div class="flex">
                        <a href="profile.php" class="btn">Profile</a>
                        <a href="Components/logout.php" onclick="return confirm('Logout from this website?');" class="btn delete-btn">Logout</a>
                    </div>
        <?php
                } else {
        ?>
                    <p class="name">Please login first</p>
                    <a href="reg_login.php" class="btn">Login</a>
        <?php
                }
                $stmt->close();
            } else {
        ?>
                <p class="name">Please login first</p>
                <a href="reg_login.php" class="btn">Login</a>
        <?php
            }
        } catch (Exception $e) {
            echo '<script>show_toast("Error: ' . addslashes($e->getMessage()) . '", "error");</script>';
        }
        ?>
    </div>
</header>
