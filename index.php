<?php 
session_start();
include('Components/toast.php');
include('Database/db_connection.php');

//  Show toast if feedback was successfully submitted
if (isset($_SESSION['feedback_success']) && $_SESSION['feedback_success'] === true) {
    echo '<script>show_toast("message sent successfully!", "success");</script>';
    unset($_SESSION['feedback_success']);
}

//  Get user ID and type from session or cookie
$user_id = '';
$user_type = '';

if (isset($_SESSION["id"]) && isset($_SESSION["user_type"])) {
    $user_id = $_SESSION["id"];
    $user_type = $_SESSION["user_type"];
echo $user_id ;
} else if (isset($_COOKIE['remember_user'])) {
    $remember_data = json_decode($_COOKIE['remember_user'], true);
    if (isset($remember_data['id']) && isset($remember_data['type'])) {
        $user_id = $remember_data['id'];
        $user_type = $remember_data['type'];
    }
}
    echo $user_type;
//  Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["send"])) {
    $name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $message = filter_input(INPUT_POST, "message", FILTER_SANITIZE_SPECIAL_CHARS);

    if (!empty($user_type) && !empty($user_id)) {
        $stmt = $con->prepare("INSERT INTO feedback (user_type, user_id, name, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $user_type, $user_id, $name, $message);
        $stmt->execute();
        $stmt->close();

        $_SESSION['feedback_success'] = true;
        header("Location: " . $_SERVER['PHP_SELF'] . "#feedback");
        exit();
    } else {
        // Handle error: user not logged in
        echo '<script>show_toast("Please log in to submit feedback", "error");</script>';
    
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  
<?php include('Components/head.php');  ?>

<body>
<?php include_once('Components/header.php'); ?>

<div class="home">
        
  <div class="swiper home-slider">
            <div class="swiper-wrapper">

                <div class="swiper-slide slide" style="background: url(assets/img/Slider1.jpg);">
                    <div class="content">
                        <span>Easy Online Booking</span>
                        <h3>Reserve your perfect room in just a few clicks</h3>
                        <a href="Reservation.php" class="btn">get started <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="swiper-slide slide" style="background: url(assets/img/Slider2.jpg);">
                    <div class="content">
                        <span>Best Price Guarantee</span>
                        <h3>Get exclusive rates when you book directly with us</h3>
                        <a href="Reservation.php" class="btn">View rooms <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                <div class="swiper-slide slide" style="background: url(assets/img/Slide3.jpg);">
                    <div class="content">
                        <span>Flexible Cancellation</span>
                        <h3>Plan with peace of mind — free cancellation up to 24 hours before check-in</h3>
                        <a href="Reservation.php" class="btn">get started <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

            </div>
            <div class="swiper-pagination"></div>
        </div>

    </div>


<section class="about-container" id="about">
  <div class="about-content">
    <h2 class="section-header">The Regent Crest Hotel</h2>
    <p class="section-description">
      Welcome to The Regent Crest – a premium hotel chain offering seamless online reservation experiences. Our intelligent hotel management system simplifies booking, check-in/out, billing, and real-time reporting for both guests and management. Whether you're reserving a single room or an extended suite stay, our platform delivers efficiency and excellence.
    </p>
    
    
  <div class="about-image">
  <img src="assets/img/Hotel_Interior.jpg" alt="Hotel exterior" />
  <img src="assets/img/Hotel_Interior2.jpg.jpg" alt="Hotel interior" />

  
</div>




    <div class="about-grid">
      <div class="about-card">
        <span><i class="ri-calendar-check-line"></i></span>
        <div>
          <h4>Flexible Reservations</h4>
          <p>
            Guests can easily make, modify, or cancel reservations through our website or via reservation clerks—using credit card details or without. Non-guaranteed bookings are auto-cancelled by 7 PM.
          </p>
        </div>
      </div>
      
      <div class="about-card">
        <span><i class="ri-hotel-line"></i></span>
        <div>
          <h4>Smart Check-In/Out</h4>
          <p>
            Check in with or without prior bookings. Rooms are assigned on the spot, and guests can check out with multiple payment options. Late check-outs incur additional charges.
          </p>
        </div>
      </div>
      
      <div class="about-card">
        <span><i class="ri-bar-chart-line"></i></span>
        <div>
          <h4>Automated Reports</h4>
          <p>
            Daily reports are generated for managers, including occupancy stats and revenue insights. No-show penalties and financials are processed automatically for streamlined operations.
          </p>
        </div>
      </div>
    </div>
  </div>


</section>
        
  <!--  Reservation box  -->
  <div class="container-reservation">
      <div class="box-reservation">
          <h3 class="title">Qucik go reserve!!</h3>
          <img src="assets/img/reservation_box.jpg" alt="Image Description" class="box-image">
          <button type="button" onclick="window.location.href='Reservation.php'" class="btn"><em>Reserve now!</em></button>
      </div>
  </div>
  <!--  Reservation box -->


<section class="contact" id="feedback">
    <div class="row">

        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
            <h3>Give us your Thoughts!</h3>
            <input type="text" name="name" maxlength="50" class="box" 
            placeholder="Enter your name" required>
            <textarea name="message" class="box" cols="30" rows="10" maxlength="500" placeholder="Enter your message" required></textarea>
            <input type="submit" value="send message" class="btn" name="send">
        </form>
        
    </div>
</section>


<?php
    include('Components/footer.php');
?> 


    <Script src="js/scripts.js"></Script>
</body>

</html>
