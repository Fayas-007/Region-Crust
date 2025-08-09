<?php
    session_start();
    session_unset();
    session_destroy();
    setcookie('remember_user', '', time() - 1, '/');

    header('location: ../index.php');
?>