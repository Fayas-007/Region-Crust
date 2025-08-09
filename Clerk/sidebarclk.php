<?php
$currentPage = basename($_SERVER['PHP_SELF']); // e.g., roomcat.php
?>

<div class="sidebar" data-color="white" data-active-color="danger">
  <div class="logo">
     <a href="https://www.creative-tim.com" class="simple-text   text-center">
          
          <div class="">
            <img src="../image/logo_small.png">
            <span>Dashboard</span>
          </div>
          <div style="font-size: 8px;">Clerk</div>

        </a>
  </div>
  <div class="sidebar-wrapper">
    <ul class="nav">
      <li class="<?= $currentPage == 'dashboard.html' ? 'active' : '' ?>">
        <a href="./dashboard.html">
          <i class="nc-icon nc-bank"></i>
          <p>Dashboard</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'roomcat.php' ? 'active' : '' ?>">
        <a href="./roomcat.php">
          <i class="nc-icon nc-diamond"></i>
          <p>Reservation</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'map.html' ? 'active' : '' ?>">
        <a href="./map.html">
          <i class="nc-icon nc-pin-3"></i>
          <p>Customer Register</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'notifications.html' ? 'active' : '' ?>">
        <a href="./notifications.html">
          <i class="nc-icon nc-bell-55"></i>
          <p>Notifications</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'user.html' ? 'active' : '' ?>">
        <a href="./user.html">
          <i class="nc-icon nc-single-02"></i>
          <p>User Profile</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'tables.html' ? 'active' : '' ?>">
        <a href="./tables.html">
          <i class="nc-icon nc-tile-56"></i>
          <p>Table List</p>
        </a>
      </li>
      <li class="<?= $currentPage == 'typography.html' ? 'active' : '' ?>">
        <a href="./typography.html">
          <i class="nc-icon nc-caps-small"></i>
          <p>Typography</p>
        </a>
      </li>
      <li class="active-pro <?= $currentPage == 'upgrade.html' ? 'active' : '' ?>">
        <a href="./upgrade.html">
          <i class="nc-icon nc-spaceship"></i>
          <p>Upgrade to PRO</p>
        </a>
      </li>
    </ul>
  </div>
</div>
