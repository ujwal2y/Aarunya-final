<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Toggle Doctor Active/Inactive Status
 * Admin action to activate or deactivate doctors
 */

session_start();
require_once '../../server/config/database.php';
require_once '../includes/auth.php';

// Check admin authentication
requireAdminLogin();

header('Content-Type: application/json');

try {
    // Validate request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    if (!isset($_POST['doctor_id']) || !is_numeric($_POST['doctor_id'])) {
        throw new Exception('Invalid doctor ID');
    }
    
    $doctorId = intval($_POST['doctor_id']);
    $adminId = $_SESSION['admin_id'];
    
    $db = getDB();
    
    // Get current status
    $stmt = $db->prepare("SELECT id, name, is_active FROM doctors WHERE id = ?");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        throw new Exception('Doctor not found');
    }
    
    // Toggle status
    $newStatus = !$doctor['is_active'];
    
    // Update doctor status
    $stmt = $db->prepare("
        UPDATE doctors 
        SET is_active = ?,
            status_updated_at = NOW(),
            status_updated_by = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$newStatus, $adminId, $doctorId]);
    
    // Log the action
    $action = $newStatus ? 'activated' : 'deactivated';
    error_log("Admin {$adminId} {$action} doctor {$doctorId} ({$doctor['name']})");
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Doctor status updated successfully',
        'doctor_id' => $doctorId,
        'doctor_name' => $doctor['name'],
        'is_active' => $newStatus,
        'status_text' => $newStatus ? 'Active' : 'Inactive',
        'action' => $action
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
