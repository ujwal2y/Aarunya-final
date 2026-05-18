<?php
/**
 * Authentication & Authorization Functions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout (1 hour)
if (!isset($_SESSION['LAST_ACTIVITY'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
} else {
    // Check if session has expired (1 hour = 3600 seconds)
    if (time() - $_SESSION['LAST_ACTIVITY'] > 3600) {
        session_unset();
        session_destroy();
        session_start();
    }
}
$_SESSION['LAST_ACTIVITY'] = time();

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
}

/**
 * Get current user role
 */
function getUserRole() {
    return $_SESSION['user_type'] ?? null;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return getUserRole() === $role;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        // Handle relative paths better
        if (strpos($redirectTo, '/') !== 0 && strpos($redirectTo, 'http') !== 0) {
            // If it's a relative path, make sure it works from current location
            $redirectTo = $redirectTo;
        }
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Require specific role
 */
function requireRole($role, $redirectTo = '/login.php') {
    if (!hasRole($role)) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireRole(ROLE_ADMIN, '/login.php');
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    if (isset($_SESSION['admin_id'])) {
        return $_SESSION['admin_id'];
    }
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 */
function getCurrentUser() {
    return [
        'id' => getCurrentUserId(),
        'name' => $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? $_SESSION['admin_email'] ?? null,
        'role' => getUserRole()
    ];
}

/**
 * Login user
 */
function loginUser($userId, $userData, $role = ROLE_USER) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userData['name'];
    $_SESSION['user_email'] = $userData['email'];
    $_SESSION['user_type'] = $role;
    $_SESSION['LAST_ACTIVITY'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Login admin
 */
function loginAdmin($adminId, $adminData) {
    $_SESSION['admin_id'] = $adminId;
    $_SESSION['admin_name'] = $adminData['name'];
    $_SESSION['admin_email'] = $adminData['email'];
    $_SESSION['user_type'] = ROLE_ADMIN;
    $_SESSION['LAST_ACTIVITY'] = time();
    
    // Regenerate session ID for security
    session_regenerate_id(true);
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
