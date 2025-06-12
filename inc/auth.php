<?php
// Redirect users to the home page if they access this file directly
if(basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header('Location: ../');
    exit();
  }

session_start();
if(!isset($_SESSION['user_id'])) {
  // user is not logged in, redirect to login page
  header('Location: login');
  exit();
}
?>
