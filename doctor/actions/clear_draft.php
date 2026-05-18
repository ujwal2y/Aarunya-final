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
    
    $stmt = $db->prepare("DELETE FROM doctor_registration_drafts WHERE session_id = ?");
    $stmt->execute([$session_id]);
    
    unset($_SESSION['doctor_reg_session']);
    
    echo json_encode(['success' => true, 'message' => 'Draft cleared']);
    
} catch (Exception $e) {
    error_log('Clear Draft Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to clear draft']);
}
?>
