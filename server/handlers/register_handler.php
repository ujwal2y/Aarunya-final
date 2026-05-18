<?php
/**
 * Aarunya Healthcare - Unified Registration Handler
 * Handles user registration with comprehensive validation
 */

session_start();

require_once '../config/database.php';
require_once '../includes/validation.php';
require_once '../includes/OTPService.php';
require_once '../includes/MailService.php';

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../client/register.php');
    exit;
}

// Get and sanitize inputs
$name = AarunyaValidator::sanitize($_POST['name'] ?? '');
$email = AarunyaValidator::sanitize($_POST['email'] ?? '');
$phone = AarunyaValidator::sanitize($_POST['phone'] ?? '');
$password = $_POST['password'] ?? '';
$age = intval($_POST['age'] ?? 0);
$blood_group = AarunyaValidator::sanitize($_POST['blood_group'] ?? '');
$lmp_date = $_POST['lmp_date'] ?? null;
$role = AarunyaValidator::sanitize($_POST['role'] ?? 'user');

try {
    $pdo = getDB();
    
    // CRITICAL: Check if OTP was verified for this email
    $otpVerified = false;
    if (isset($_SESSION['otp_verified_email']) && 
        $_SESSION['otp_verified_email'] === $email &&
        isset($_SESSION['otp_verified_at'])) {
        
        // Check if verification is still valid (within 10 minutes)
        $verificationAge = time() - $_SESSION['otp_verified_at'];
        if ($verificationAge <= 600) { // 10 minutes
            $otpVerified = true;
        }
    }
    
    if (!$otpVerified) {
        $_SESSION['registration_errors'] = ['general' => 'Email verification required. Please verify your email with OTP first.'];
        $_SESSION['registration_data'] = $_POST;
        header('Location: ../../client/register.php?error=otp_not_verified');
        exit;
    }
    
    // Basic validation first
    $errors = [];
    
    // Validate name
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // Validate phone
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = 'Phone number must be exactly 10 digits';
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/', $password)) {
        $errors[] = 'Password must contain uppercase, lowercase, number, and special character';
    }
    
    // Check if email already exists in users table
    $checkEmail = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $checkEmail->execute([$email]);
    $existingUser = $checkEmail->fetch();
    if ($existingUser) {
        // More helpful error message
        $errors[] = 'The email "' . $email . '" is already registered. Please <a href="../../client/login.php" style="color: var(--accent-cyan); text-decoration: underline;">login here</a> or use a different email.';
    }
    
    // Check if phone already exists in users table
    if (!empty($phone)) {
        $checkPhone = $pdo->prepare("SELECT id, name FROM users WHERE phone = ?");
        $checkPhone->execute([$phone]);
        $existingUser = $checkPhone->fetch();
        if ($existingUser) {
            $errors[] = 'The phone number "' . $phone . '" is already registered. Please <a href="../../client/login.php" style="color: var(--accent-cyan); text-decoration: underline;">login here</a> or use a different phone number.';
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        $_SESSION['registration_data'] = $_POST;
        header('Location: ../../client/register.php?error=validation');
        exit;
    }
    
    // Capitalize name properly
    $name = ucwords(strtolower(trim($name)));
    
    // Clean phone number (remove any spaces)
    $phone = preg_replace('/\s+/', '', $phone);
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    // Calculate pregnancy week and due date from LMP
    $pregnancy_week = 0;
    $due_date = null;
    
    if ($lmp_date) {
        $lmp = new DateTime($lmp_date);
        $today = new DateTime();
        $interval = $lmp->diff($today);
        $days_since_lmp = $interval->days;
        
        // Calculate pregnancy week (weeks since LMP)
        $pregnancy_week = floor($days_since_lmp / 7);
        
        // Calculate due date (LMP + 280 days or 40 weeks)
        $due_date_obj = clone $lmp;
        $due_date_obj->add(new DateInterval('P280D'));
        $due_date = $due_date_obj->format('Y-m-d');
    }
    
    // Determine table based on role
    $table = 'users';
    $redirectSuccess = '../../client/login.php?registered=success';
    
    switch ($role) {
        case 'doctor':
            $table = 'doctors';
            $redirectSuccess = '../../client/login.php?registered=success&role=doctor';
            break;
        case 'admin':
            $table = 'admins';
            $redirectSuccess = '../../client/login.php?registered=success';
            break;
    }
    
    // Prepare INSERT query - check which columns exist first
    try {
        $stmt = $pdo->query("DESCRIBE $table");
        $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Failed to check table structure: " . $e->getMessage());
        $existingColumns = ['name', 'email', 'phone', 'password', 'status', 'created_at'];
    }
    
    if ($role === 'user') {
        // Build dynamic SQL based on existing columns
        $fields = ['name', 'email', 'password', 'status', 'created_at'];
        $values = [$name, $email, $hashedPassword, 'active'];
        $placeholders = ['?', '?', '?', '?', 'NOW()'];
        
        // Add phone if provided
        if (!empty($phone)) {
            $fields[] = 'phone';
            $values[] = $phone;
            $placeholders[] = '?';
        }
        
        // Add optional fields if they exist in table
        if (in_array('age', $existingColumns) && !empty($age)) {
            $fields[] = 'age';
            $values[] = $age;
            $placeholders[] = '?';
        }
        
        if (in_array('blood_group', $existingColumns) && !empty($blood_group)) {
            $fields[] = 'blood_group';
            $values[] = $blood_group;
            $placeholders[] = '?';
        }
        
        if (in_array('lmp_date', $existingColumns) && !empty($lmp_date)) {
            $fields[] = 'lmp_date';
            $values[] = $lmp_date;
            $placeholders[] = '?';
        }
        
        if (in_array('pregnancy_week', $existingColumns)) {
            $fields[] = 'pregnancy_week';
            $values[] = $pregnancy_week;
            $placeholders[] = '?';
        }
        
        if (in_array('due_date', $existingColumns) && !empty($due_date)) {
            $fields[] = 'due_date';
            $values[] = $due_date;
            $placeholders[] = '?';
        }
        
        $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        $params = $values;
    } else {
        $sql = "INSERT INTO $table (name, email, phone, password, status, created_at) 
                VALUES (?, ?, ?, ?, 'active', NOW())";
        $params = [$name, $email, $phone ?: null, $hashedPassword];
    }
    
    // Log the registration attempt
    error_log("Attempting registration - Email: $email, Role: $role, Table: $table");
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($params);
    
    if (!$result) {
        error_log("Registration failed - SQL Error: " . print_r($stmt->errorInfo(), true));
        throw new Exception("Failed to create account");
    }
    
    $userId = $pdo->lastInsertId();
    
    // Log successful registration
    error_log("Registration successful - User ID: $userId, Email: $email, Role: $role");
    
    // Clear OTP verification session data
    unset($_SESSION['otp_verified_email']);
    unset($_SESSION['otp_verified_at']);
    
    // Send welcome email
    try {
        $mailService = new MailService();
        $mailService->sendWelcome($email, $name);
    } catch (Exception $e) {
        error_log("Welcome email error: " . $e->getMessage());
    }
    
    // Clear any stored registration data
    unset($_SESSION['registration_errors']);
    unset($_SESSION['registration_data']);
    
    // Redirect to login page with success message
    header("Location: $redirectSuccess");
    exit;
    
} catch (PDOException $e) {
    error_log("Database error during registration: " . $e->getMessage());
    
    // Check for duplicate entry error
    if ($e->getCode() == 23000) {
        $_SESSION['registration_errors'] = ['general' => 'Email or phone number already exists'];
    } else {
        $_SESSION['registration_errors'] = ['general' => 'Registration failed. Please try again.'];
    }
    
    $_SESSION['registration_data'] = $_POST;
    header('Location: ../../client/register.php?error=database');
    exit;
    
} catch (Exception $e) {
    error_log("Unexpected error during registration: " . $e->getMessage());
    $_SESSION['registration_errors'] = ['general' => 'An unexpected error occurred. Please try again.'];
    $_SESSION['registration_data'] = $_POST;
    header('Location: ../../client/register.php?error=system');
    exit;
}
?>
