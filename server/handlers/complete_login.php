<?php
/**
 * Complete Login After OTP Verification
 * This handler completes the login process after OTP is verified
 */

session_start();

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header('Location: ../../client/login.php?error=otp_not_verified');
    exit;
}

// Check if pending login data exists
if (!isset($_SESSION['pending_login'])) {
    header('Location: ../../client/login.php?error=session_expired');
    exit;
}

// Get pending login data
$loginData = $_SESSION['pending_login'];
$role = $loginData['role'];
$sessionPrefix = $role === 'admin' ? 'admin' : ($role === 'doctor' ? 'doctor' : 'user');

try {
    require_once '../config/database.php';
    $pdo = getDB();
    
    // Create session variables
    $_SESSION["{$sessionPrefix}_id"] = $loginData['user_id'];
    $_SESSION["{$sessionPrefix}_email"] = $loginData['email'];
    $_SESSION["{$sessionPrefix}_name"] = $loginData['name'];
    $_SESSION["{$sessionPrefix}_role"] = $role;
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['user_type'] = $role;
    
    // Set generic session variables for auth.php compatibility
    if ($role === 'admin') {
        $_SESSION['admin_id'] = $loginData['user_id'];
        $_SESSION['admin_name'] = $loginData['name'];
        $_SESSION['admin_email'] = $loginData['email'];
    } elseif ($role === 'doctor') {
        $_SESSION['doctor_id'] = $loginData['user_id'];
        $_SESSION['doctor_name'] = $loginData['name'];
        $_SESSION['doctor_email'] = $loginData['email'];
        $_SESSION['user_id'] = $loginData['user_id'];
    } else {
        $_SESSION['user_id'] = $loginData['user_id'];
        $_SESSION['user_name'] = $loginData['name'];
        $_SESSION['user_email'] = $loginData['email'];
    }
    
    // Store phone if exists
    if (!empty($loginData['phone'])) {
        $_SESSION["{$sessionPrefix}_phone"] = $loginData['phone'];
    }
    
    // Update last login
    try {
        $updateSql = "UPDATE {$loginData['table']} SET last_login = NOW() WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$loginData['user_id']]);
    } catch (Exception $e) {
        // Ignore if column doesn't exist
        error_log("Last login update error: " . $e->getMessage());
    }
    
    // Clear OTP and pending login data
    unset($_SESSION['otp_verified']);
    unset($_SESSION['otp_verified_email']);
    unset($_SESSION['otp_verified_purpose']);
    unset($_SESSION['otp_verified_at']);
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_purpose']);
    unset($_SESSION['pending_login']);
    
    // Log successful login
    error_log("Login completed successfully - User ID: {$loginData['user_id']}, Email: {$loginData['email']}, Role: $role");
    
    // Redirect to appropriate dashboard
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = dirname(dirname(dirname($_SERVER['PHP_SELF'])));
    
    if ($role === 'admin') {
        $redirectUrl = $protocol . '://' . $host . $baseUrl . '/admin/pages/dashboard.php';
    } elseif ($role === 'doctor') {
        $redirectUrl = $protocol . '://' . $host . $baseUrl . '/doctor/dashboard.php';
    } else {
        $redirectUrl = $protocol . '://' . $host . $baseUrl . '/client/dashboard.php';
    }
    
    header("Location: " . $redirectUrl);
    exit;
    
} catch (Exception $e) {
    error_log("Login completion error: " . $e->getMessage());
    header('Location: ../../client/login.php?error=login_failed');
    exit;
}
?>
