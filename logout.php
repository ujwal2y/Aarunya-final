<?php
/**
 * Universal Logout Handler
 * Handles logout for both users and admins
 * Redirects to appropriate login page based on referrer
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine redirect location based on referrer
$redirect = 'client/login.php'; // Default to client login

if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    
    // If coming from admin area, redirect to admin dashboard
    if (strpos($referer, '/admin/') !== false) {
        $redirect = 'admin/pages/dashboard.php';
    }
}

// Clear all session data
session_unset();
session_destroy();

// Clear session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to appropriate page
header('Location: ' . $redirect);
exit();
?>
