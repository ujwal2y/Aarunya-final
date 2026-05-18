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

requireLogin('login.php');
requireRole(ROLE_USER, 'login.php');

$user = getCurrentUser();
$db = getDB();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['profile_photo'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed']);
    exit();
}

// Validate file size
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB']);
    exit();
}

// Create uploads directory if it doesn't exist
$uploadDir = '../uploads/profile_photos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $user['id'] . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit();
}

// Get old photo to delete
$stmt = $db->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$oldPhoto = $stmt->fetchColumn();

// Delete old photo if exists
if ($oldPhoto && file_exists($oldPhoto)) {
    unlink($oldPhoto);
}

// Update database - store path relative to project root
$dbPath = 'uploads/profile_photos/' . $filename;
$stmt = $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");

if ($stmt->execute([$dbPath, $user['id']])) {
    // Verify the update
    $verifyStmt = $db->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $verifyStmt->execute([$user['id']]);
    $savedPath = $verifyStmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated successfully',
        'photo_url' => $dbPath,
        'saved_path' => $savedPath,
        'file_exists' => file_exists($filepath)
    ]);
} else {
    // Delete uploaded file if database update fails
    unlink($filepath);
    echo json_encode(['success' => false, 'message' => 'Failed to update database']);
}
?>
