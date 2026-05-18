<?php
/**
 * OTP Handler API
 * Handles OTP sending and verification for registration
 */

header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/OTPService.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    $pdo = getDB();
    $otpService = new OTPService($pdo);
    
    switch ($action) {
        case 'send_otp':
            $email = trim($input['email'] ?? '');
            $name = trim($input['name'] ?? 'User');
            
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email address'
                ]);
                exit;
            }
            
            // Check if email already exists in users table
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This email is already registered. Please login instead.'
                ]);
                exit;
            }
            
            // Send OTP
            $result = $otpService->sendOTP($email, $name, 'registration');
            echo json_encode($result);
            break;
            
        case 'verify_otp':
            $email = trim($input['email'] ?? '');
            $otpCode = trim($input['otp_code'] ?? '');
            
            // Validate inputs
            if (empty($email) || empty($otpCode)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email and OTP code are required'
                ]);
                exit;
            }
            
            // Verify OTP
            $result = $otpService->verifyOTP($email, $otpCode, 'registration');
            
            // Store verification status in session
            if ($result['success']) {
                $_SESSION['otp_verified_email'] = $email;
                $_SESSION['otp_verified_at'] = time();
            }
            
            echo json_encode($result);
            break;
            
        case 'resend_otp':
            $email = trim($input['email'] ?? '');
            $name = trim($input['name'] ?? 'User');
            
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid email address'
                ]);
                exit;
            }
            
            // Resend OTP
            $result = $otpService->resendOTP($email, $name, 'registration');
            echo json_encode($result);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
    
} catch (Exception $e) {
    error_log("OTP Handler Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>
