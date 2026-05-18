<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Client/User Logout Handler
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session data
session_unset();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to client login page
header('Location: login.php');
exit();
?>
