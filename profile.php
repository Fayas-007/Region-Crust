<?php 

session_start();
include("Database/db_connection.php");
include('Components/toast.php');
$customer_id = '';
$user_type = '';

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

// Handle credit card form submission
$msg = ''; // for feedback messages

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['card_number'])) {
    $card_number = trim($_POST['card_number']);
    $expiry = trim($_POST['expiry']);
    $cvv = trim($_POST['cvv']);
    $card_name = trim($_POST['card_name']);

    // Basic validation for card number (you can improve this)
    if (empty($card_number) || strlen($card_number) < 13 || strlen($card_number) > 16 || !ctype_digit($card_number)) {
         echo '<script>show_toast("Invalid card number.", "error");</script>';
    } else {
        if ($user_type === "customer") {
            $stmt = $con->prepare("UPDATE customer SET credit_cardnum = ? WHERE idcus = ?");
        } elseif ($user_type === "travel_company") {
            $stmt = $con->prepare("UPDATE travel_company SET travel_credit_cardnum = ? WHERE travel_company_id = ?");
        } else {
             echo '<script>show_toast("Invalid user type.", "error");</script>';
        }

        if (isset($stmt)) {
            $stmt->bind_param("si", $card_number, $customer_id);
            if ($stmt->execute()) {
                echo '<script>show_toast("Successfully inserted the card!.", "success");</script>';
            } else {
                echo '<script>Database error: '.$stmt->error .'", "error");</script>';
            }
            $stmt->close();
        }
    }
}

// Fetch user data
if ($user_type === "customer") {
    $stmt = $con->prepare("SELECT * FROM customer WHERE idcus = ?");
} elseif ($user_type === "travel_company") {
    $stmt = $con->prepare("SELECT * FROM travel_company WHERE travel_company_id = ?");
} else {
    header("Location: reg_login.php");
    exit();
}

$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $fetch_Info = $result->fetch_assoc();
} else {
    if ($user_type === "customer") {
        $fetch_Info = [
            'cus_name' => 'Unknown',
            'cus_email' => 'N/A',
            'cus_contact' => 'N/A',
            'credit_cardnum' => ''
        ];
    } else {
        $fetch_Info = [
            'travel_name' => 'Unknown',
            'travel_contact_email' => 'N/A',
            'travel_phone' => 'N/A',
            'travel_credit_cardnum' => ''
        ];
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<?php include('Components/head.php'); ?>
<body>
<?php include("Components/header.php"); ?>    

<div class="profile-wrapper">
  <div class="profile-container">
    <div class="profile-card">
      <h2><?= $user_type === "customer" ? "Customer" : "Travel Company" ?> Profile</h2>

      <div class="profile-section"> 
        <p><strong>Name:</strong> <?= htmlspecialchars(
            $user_type === "customer" ? $fetch_Info['cus_name'] : $fetch_Info['travel_name']
        ) ?></p>

        <p><strong>Email:</strong> <?= htmlspecialchars(
            $user_type === "customer" ? $fetch_Info['cus_email'] : $fetch_Info['travel_contact_email']
        ) ?></p>

        <p><strong>Phone:</strong> <?= htmlspecialchars(
            $user_type === "customer" ? $fetch_Info['cus_contact'] : $fetch_Info['travel_phone']
        ) ?></p>

        <p><strong>Credit Card:</strong> 
            <?php 
            $card = $user_type === "customer" ? $fetch_Info['credit_cardnum'] : $fetch_Info['travel_credit_cardnum'];
            echo $card ? htmlspecialchars(substr($card, 0, 4) . str_repeat('*', strlen($card) - 4)) : 'Not added';
            ?>
        </p>
      </div>

      <div class="profile-section">
        <form action="forgot_password.php" method="POST">
          <button type="submit" class="btn">Forgot Password</button>
        </form>
        <form action="update_email.php" method="POST" class="profile-form-inline">
          <input type="email" name="new_email" placeholder="Enter new email to update" required>
          <button type="submit" class="btn">Update Email</button>
        </form>
      </div>

      <div class="profile-section">
        <button id="toggleAddCardBtn" class="btn">Add Card</button>
        <form id="addCardForm" action="" method="POST" class="hidden-form">
          <h3>Add Credit Card</h3>
          <input type="text" name="card_number" placeholder="Card Number" maxlength="16" required />
          <div class="profile-form-row">
            <input type="text" name="expiry" placeholder="MM/YY" required />
            <input type="text" name="cvv" placeholder="CVV" maxlength="4" required />
          </div>
          <input type="text" name="card_name" placeholder="Name on Card" required />
          <button type="submit" class="btn">Save Card</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
// Show/hide Add Card form
const toggleBtn = document.getElementById('toggleAddCardBtn');
const addCardForm = document.getElementById('addCardForm');

toggleBtn.addEventListener('click', () => {
  if (addCardForm.classList.contains('show')) {
    addCardForm.classList.remove('show');
    toggleBtn.textContent = 'Add Card';
  } else {
    addCardForm.classList.add('show');
    toggleBtn.textContent = 'Cancel';
  }
});
</script>

<script src="../js/script.js"></script>

</body>
</html>
