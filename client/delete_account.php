<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../server/config/config.php';
require_once '../server/config/database.php';
require_once '../server/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin('../client/login.php');
requireRole(ROLE_USER, '../client/login.php');

$user = getCurrentUser();
$db = getDB();

try {
    // Delete user data (cascade will handle related records)
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Logout
    session_destroy();
    header('Location: login.php?deleted=1');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to delete account. Please contact support.';
    header('Location: settings.php');
    exit;
}
?>
