<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
session_start();
require_once '../../server/config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    $session_id = $_SESSION['doctor_reg_session'] ?? session_id();
    
    $stmt = $db->prepare("SELECT step_number, form_data FROM doctor_registration_drafts WHERE session_id = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute([$session_id]);
    $draft = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($draft) {
        echo json_encode([
            'success' => true,
            'step' => $draft['step_number'],
            'data' => json_decode($draft['form_data'], true)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No draft found']);
    }
    
} catch (Exception $e) {
    error_log('Load Draft Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load draft']);
}
?>
