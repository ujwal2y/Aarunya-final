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

// Get all user data
$stmt = $db->prepare("SELECT id, name, email, phone, age, blood_group, lmp_date, pregnancy_week, due_date, created_at FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

// Get appointments
$stmt = $db->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC");
$stmt->execute([$user['id']]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare export data
$exportData = [
    'export_date' => date('Y-m-d H:i:s'),
    'user_info' => $userData,
    'appointments' => $appointments,
    'total_appointments' => count($appointments)
];

// Set headers for download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="aarunya_data_' . date('Y-m-d') . '.json"');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// Output JSON
echo json_encode($exportData, JSON_PRETTY_PRINT);
exit;
?>
