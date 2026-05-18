<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../../client/login.php');
        exit();
    }
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: ../../client/login.php');
    exit();
}
?>
