<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
session_start();
require_once '../../server/config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }
    
    $session_id = $_SESSION['doctor_reg_session'] ?? session_id();
    $step_number = $data['step'] ?? 1;
    $form_data = json_encode($data['data'] ?? []);
    $email = $data['data']['email'] ?? '';
    
    // Check if draft exists
    $stmt = $db->prepare("SELECT id FROM doctor_registration_drafts WHERE session_id = ?");
    $stmt->execute([$session_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update existing draft
        $stmt = $db->prepare("UPDATE doctor_registration_drafts SET step_number = ?, form_data = ?, email = ?, updated_at = NOW() WHERE session_id = ?");
        $stmt->execute([$step_number, $form_data, $email, $session_id]);
    } else {
        // Insert new draft
        $stmt = $db->prepare("INSERT INTO doctor_registration_drafts (session_id, email, step_number, form_data) VALUES (?, ?, ?, ?)");
        $stmt->execute([$session_id, $email, $step_number, $form_data]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Draft saved']);
    
} catch (Exception $e) {
    error_log('Save Draft Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to save draft']);
}
?>
