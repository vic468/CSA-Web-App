<?php
require_once 'models/User.php';

// Initialize user model
$user = new User();

// Perform logout
$user->logout();

// Redirect to login page
header('Location: login.php');
exit();
?>
