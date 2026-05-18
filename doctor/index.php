<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Module - Index/Entry Point
 * Redirects to login if not logged in, otherwise to dashboard
 */

session_start();

// Check if doctor is logged in
if (isset($_SESSION['doctor_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'doctor') {
    // Already logged in, redirect to dashboard
    header('Location: dashboard.php');
    exit();
}

// Not logged in, redirect to login page
header('Location: login.php');
exit();
?>
