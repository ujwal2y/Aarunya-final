<?php
/**
 * Aarunya Healthcare - Unified Login Handler
 * Supports login via Email OR Phone Number
 */

// Start output buffering to prevent header issues
ob_start();

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error display for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/MailService.php';

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = dirname(dirname(dirname($_SERVER['PHP_SELF'])));
    header('Location: ' . $protocol . '://' . $host . $baseUrl . '/client/login.php?error=invalid_request');
    exit;
}

// Get and sanitize inputs
$identifier = trim($_POST['email'] ?? $_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';
$role = trim($_POST['role'] ?? 'user');

// Basic validation
if (empty($identifier) || empty($password)) {
    header('Location: ../../client/login.php?error=empty_fields');
    exit;
}

try {
    // Get database connection
    $pdo = getDB();
    
    // Determine if identifier is email or phone
    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $isPhone = ctype_digit($identifier) && strlen($identifier) === 10;
    
    // Determine table and redirect paths based on role
    $table = 'users';
    $dashboardPath = '../../client/dashboard.php';
    $loginPath = '../../client/login.php';
    $sessionPrefix = 'user';
    
    if ($role === 'doctor') {
        $table = 'doctors';
        $dashboardPath = '../../doctor/dashboard.php';
        $loginPath = '../../client/login.php?role=doctor';
        $sessionPrefix = 'doctor';
    } elseif ($role === 'admin') {
        $table = 'admins';
        $dashboardPath = '../../admin/pages/dashboard.php';
        $loginPath = '../../client/login.php';
        $sessionPrefix = 'admin';
    }
    
    // Build and execute query
    if ($isEmail) {
        $sql = "SELECT * FROM {$table} WHERE email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier]);
    } elseif ($isPhone) {
        $sql = "SELECT * FROM {$table} WHERE phone = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier]);
    } else {
        // Try both
        $sql = "SELECT * FROM {$table} WHERE email = ? OR phone = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$identifier, $identifier]);
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists
    if (!$user) {
        header("Location: {$loginPath}?error=invalid_credentials");
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        header("Location: {$loginPath}?error=invalid_credentials");
        exit;
    }
    
    // Check account status
    if ($role === 'doctor') {
        // For doctors, check status field (should be 'approved')
        if (isset($user['status']) && $user['status'] !== 'approved' && $user['status'] !== 'active') {
            header("Location: {$loginPath}?error=account_not_approved");
            exit;
        }
    } else {
        // For users and admins, check status
        if (isset($user['status']) && $user['status'] !== 'active') {
            header("Location: {$loginPath}?error=account_inactive");
            exit;
        }
    }
    
    // Password verified - Login successful, create session directly
    $_SESSION["{$sessionPrefix}_id"] = $user['id'];
    $_SESSION["{$sessionPrefix}_email"] = $user['email'];
    $_SESSION["{$sessionPrefix}_name"] = $user['name'] ?? 'User';
    $_SESSION["{$sessionPrefix}_role"] = $role;
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['user_type'] = $role;
    
    // Set generic session variables for auth.php compatibility
    if ($role === 'admin') {
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['name'] ?? 'Admin';
        $_SESSION['admin_email'] = $user['email'];
    } elseif ($role === 'doctor') {
        $_SESSION['doctor_id'] = $user['id'];
        $_SESSION['doctor_name'] = $user['name'] ?? 'Doctor';
        $_SESSION['doctor_email'] = $user['email'];
        $_SESSION['user_id'] = $user['id'];
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'] ?? 'User';
        $_SESSION['user_email'] = $user['email'];
    }
    
    // Store phone if exists
    if (isset($user['phone'])) {
        $_SESSION["{$sessionPrefix}_phone"] = $user['phone'];
    }
    
    // Update last login (ignore errors if column doesn't exist)
    try {
        $updateSql = "UPDATE {$table} SET last_login = NOW() WHERE id = ?";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([$user['id']]);
    } catch (Exception $e) {
        // Ignore if column doesn't exist
    }
    
    // Send login alert email (non-blocking)
    try {
        $mailService = new MailService();
        $loginTime = date('F j, Y, g:i a');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $browser = 'Unknown';
        
        // Extract browser info from user agent
        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            $browser = 'Opera';
        }
        
        $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .info-box { background: white; border-left: 4px solid #C4A7FF; padding: 15px; margin: 15px 0; }
                .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 15px 0; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Login Alert - Aarunya Healthcare</h1>
                </div>
                <div class='content'>
                    <p>Hello <strong>{$user['name']}</strong>,</p>
                    <p>A successful login was detected on your account.</p>
                    
                    <div class='info-box'>
                        <p><strong>📋 Login Details:</strong></p>
                        <p><strong>👤 User:</strong> {$user['name']} ({$user['email']})</p>
                        <p><strong>🕐 Time:</strong> {$loginTime}</p>
                        <p><strong>🌐 IP Address:</strong> {$ipAddress}</p>
                        <p><strong>💻 Browser:</strong> {$browser}</p>
                        <p><strong>👔 Role:</strong> " . ucfirst($role) . "</p>
                    </div>
                    
                    <div class='alert-box'>
                        <p><strong>⚠️ Security Notice:</strong></p>
                        <p>If this wasn't you, please contact support immediately and change your password.</p>
                    </div>
                    
                    <p>Thank you for using Aarunya Healthcare!</p>
                </div>
                <div class='footer'>
                    <p>&copy; 2026 Aarunya Healthcare. All rights reserved.</p>
                    <p>This is an automated security notification.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send to user's email
        $mailService->send($user['email'], 'Login Alert - Aarunya Healthcare', $emailBody);
        
        // Send to monitoring email (killekarakash468@gmail.com)
        $monitoringEmail = 'killekarakash468@gmail.com';
        $mailService->send($monitoringEmail, 'Login Alert - Aarunya Healthcare', $emailBody);
        
        error_log("Login alert emails sent to {$user['email']} and {$monitoringEmail}");
    } catch (Exception $e) {
        // Log error but don't block login
        error_log("Failed to send login alert email: " . $e->getMessage());
    }
    
    // Log successful login
    error_log("Login successful - User ID: {$user['id']}, Email: {$user['email']}, Role: $role");
    
    // Force session write
    session_write_close();
    
    // Clear output buffer
    ob_end_clean();
    
    // Redirect to dashboard with absolute URL
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
    
} catch (PDOException $e) {
    // Database error
    error_log("Login error: " . $e->getMessage());
    header('Location: ../../client/login.php?error=database_error');
    exit;
} catch (Exception $e) {
    // General error
    error_log("Login error: " . $e->getMessage());
    header('Location: ../../client/login.php?error=system_error');
    exit;
}
?>
