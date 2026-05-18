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
    // Deactivate account
    $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Logout
    session_destroy();
    header('Location: login.php?deactivated=1');
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = 'Failed to deactivate account. Please try again.';
    header('Location: settings.php');
    exit;
}
?>
