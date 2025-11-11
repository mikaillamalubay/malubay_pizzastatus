<?php
require_once 'config.php';
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit();
?>