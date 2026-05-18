<?php
/**
 * OTP Handler
 * API endpoints for OTP operations
 */

session_start();
header('Content-Type: application/json');

require_once '../config/database.php';
require_once '../includes/OTPService.php';
require_once '../includes/validation.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Only allow POST requests
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
        case 'send':
            handleSendOTP($otpService, $input);
            break;
            
        case 'verify':
            handleVerifyOTP($otpService, $input);
            break;
            
        case 'resend':
            handleResendOTP($otpService, $input);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    error_log("OTP handler error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}

/**
 * Handle send OTP request
 */
function handleSendOTP($otpService, $input) {
    $email = AarunyaValidator::sanitize($input['email'] ?? '');
    $name = AarunyaValidator::sanitize($input['name'] ?? 'User');
    $purpose = $input['purpose'] ?? 'registration';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    // Validate purpose
    $validPurposes = ['registration', 'password_reset', '2fa', 'login'];
    if (!in_array($purpose, $validPurposes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid purpose']);
        return;
    }
    
    // Send OTP
    $result = $otpService->sendOTP($email, $name, $purpose);
    
    // Store email in session for verification
    if ($result['success']) {
        $_SESSION['otp_email'] = $email;
        $_SESSION['otp_purpose'] = $purpose;
    }
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}

/**
 * Handle verify OTP request
 */
function handleVerifyOTP($otpService, $input) {
    $email = AarunyaValidator::sanitize($input['email'] ?? '');
    $otpCode = $input['otp_code'] ?? '';
    $purpose = $input['purpose'] ?? 'registration';
    
    // Validate inputs
    if (empty($email) || empty($otpCode)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and OTP code are required']);
        return;
    }
    
    // Verify OTP
    $result = $otpService->verifyOTP($email, $otpCode, $purpose);
    
    // Store verification status in session
    if ($result['success']) {
        $_SESSION['otp_verified'] = true;
        $_SESSION['otp_verified_email'] = $email;
        $_SESSION['otp_verified_purpose'] = $purpose;
        $_SESSION['otp_verified_at'] = time();
    }
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}

/**
 * Handle resend OTP request
 */
function handleResendOTP($otpService, $input) {
    $email = AarunyaValidator::sanitize($input['email'] ?? '');
    $name = AarunyaValidator::sanitize($input['name'] ?? 'User');
    $purpose = $input['purpose'] ?? 'registration';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    // Resend OTP
    $result = $otpService->resendOTP($email, $name, $purpose);
    
    http_response_code($result['success'] ? 200 : 400);
    echo json_encode($result);
}
?>
