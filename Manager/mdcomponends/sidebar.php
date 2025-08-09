<?php
$currentPage = basename($_SERVER['PHP_SELF']); // e.g., roomcatclk.php
?>

<div class="sidebar" data-color="white" data-active-color="danger" id="sidebar">
  <div class="sidebar-wrapper">
    <div class="text-center pt-2">
      <img src="../image/logo_small.png">
      <span class="" style="font-size:30px">Dashboard</span>
      <div>MANAGER</div>
    </div>
    <ul class="nav">
       <li class="<?= $currentPage == 'home.php' ? 'active' : '' ?>">
        <a href="home.php">
          <i class="nc-icon nc-bank"></i>
          <p>Home</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'reservation.php' ? 'active' : '' ?>">
        <a href="reservation.php">
          <i class="nc-icon nc-book-bookmark"></i>
          <p>Reservations</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'customer_regclk.php' ? 'active' : '' ?>">
        <a href="customer_regclk.php">
          <i class="nc-icon nc-single-02"></i>
          <p>Manage Employees</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'roomcat.php' ? 'active' : '' ?>">
        <a href="roomcat.php">
          <i class="nc-icon nc-app"></i>
          <p>Manage Rooms</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'login.php' ? 'active' : '' ?>">
        <a href="login.php">
          <i class="nc-icon nc-lock-circle-open"></i>
          <p>Login</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'tables.php' ? 'active' : '' ?>">
        <a href="tables.php">
          <i class="nc-icon nc-tile-56"></i>
          <p>Table List</p>
        </a>
      </li>
      <li class="active-pro <?= $currentPage == 'upgrade.html' ? 'active' : '' ?>">
        <a href="upgrade.html">
          <i class="nc-icon nc-spaceship"></i>
          <p>Upgrade</p>
        </a>
      </li>
 
 
       
    </ul>
  </div>
</div>
