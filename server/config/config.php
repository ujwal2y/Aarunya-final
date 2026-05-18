<?php
/**
 * Application Configuration
 */

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Application Settings
define('APP_NAME', 'Aarunya');
define('APP_VERSION', '2.0.0');
define('BASE_URL', 'http://localhost/Aarunya');

// Paths
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('SERVER_PATH', ROOT_PATH . '/server');
define('CLIENT_PATH', ROOT_PATH . '/client');

// Security
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_LIFETIME', 3600); // 1 hour

// Roles
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_DOCTOR', 'doctor');

// CORS Headers for API
function setCorsHeaders() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=UTF-8');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// JSON Response Helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

// Error Response Helper
function errorResponse($message, $statusCode = 400) {
    jsonResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

// Success Response Helper
function successResponse($data, $message = null) {
    $response = [
        'success' => true,
        'data' => $data
    ];
    
    if ($message) {
        $response['message'] = $message;
    }
    
    jsonResponse($response);
}
?>
