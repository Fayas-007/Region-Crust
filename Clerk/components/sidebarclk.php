<?php
$currentPage = basename($_SERVER['PHP_SELF']); // e.g., roomcatclk.php
?>

<div class="sidebar" data-color="white" data-active-color="danger" id="sidebar">
  <div class="sidebar-wrapper">
    <div class="text-center pt-2">
      <img src="../image/logo_small.png">
      <span class="" style="font-size:30px">Dashboard</span>
      <div>Clerk</div>
    </div>
    <ul class="nav">
       <li class="<?= $currentPage == 'reservation.php' ? 'active' : '' ?>">
        <a href="reservation.php">
          <i class="nc-icon nc-book-bookmark"></i>
          <p>Reservations</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'customer_regclk.php' ? 'active' : '' ?>">
        <a href="customer_regclk.php">
          <i class="nc-icon nc-single-02"></i>
          <p>Customer Register</p>
        </a>
      </li>
            <li class="<?= $currentPage == 'travel_regclk.php' ? 'active' : '' ?>">
        <a href="travel_regclk.php">
         <i class="nc-icon nc-single-02"></i>
          <p>Travel Cmpany Register</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'roomcatclk.php' ? 'active' : '' ?>">
        <a href="roomcatclk.php">

          <i class="nc-icon nc-app"></i>
          <p>manage Rooms</p>
        </a>
      </li>

    </ul>
  </div>
</div>
