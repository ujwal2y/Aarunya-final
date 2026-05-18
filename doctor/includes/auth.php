<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Authentication & Authorization
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if doctor is logged in
function isDoctorLoggedIn() {
    return isset($_SESSION['doctor_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'doctor';
}

// Require doctor login
function requireDoctorLogin($redirectTo = null) {
    if (!isDoctorLoggedIn()) {
        // Determine the correct redirect path based on current location
        if ($redirectTo === null) {
            // Check if we're in the root doctor folder or pages subfolder
            $currentDir = basename(dirname($_SERVER['PHP_SELF']));
            if ($currentDir === 'pages') {
                $redirectTo = '../../client/login.php';
            } else {
                $redirectTo = '../client/login.php';
            }
        }
        header('Location: ' . $redirectTo);
        exit();
    }
}

// Check if doctor is approved
function isDoctorApproved() {
    if (!isDoctorLoggedIn()) {
        return false;
    }
    
    // Handle hardcoded dummy doctor - always approved
    if ($_SESSION['doctor_id'] == 999 && $_SESSION['doctor_email'] == 'dr.demo@aarunya.com') {
        return true;
    }
    
    require_once __DIR__ . '/../../server/config/database.php';
    $db = getDB();
    
    $stmt = $db->prepare("SELECT registration_status FROM doctors WHERE id = ?");
    $stmt->execute([$_SESSION['doctor_id']]);
    $doctor = $stmt->fetch();
    
    return $doctor && $doctor['registration_status'] === 'approved';
}

// Require approved doctor
function requireApprovedDoctor($redirectTo = '../pending-approval.php') {
    requireDoctorLogin();
    
    if (!isDoctorApproved()) {
        header('Location: ' . $redirectTo);
        exit();
    }
}

// Get current doctor data
function getCurrentDoctor() {
    if (!isDoctorLoggedIn()) {
        return null;
    }
    
    // Handle hardcoded dummy doctor
    if ($_SESSION['doctor_id'] == 999 && $_SESSION['doctor_email'] == 'dr.demo@aarunya.com') {
        return [
            'id' => 999,
            'name' => 'Dr. Demo Doctor',
            'email' => 'dr.demo@aarunya.com',
            'phone' => '+1 (555) 123-4567',
            'specialization' => 'Maternal Health',
            'registration_status' => 'approved',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    require_once __DIR__ . '/../../server/config/database.php';
    $db = getDB();
    
    $stmt = $db->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([$_SESSION['doctor_id']]);
    return $stmt->fetch();
}

// Logout doctor
function logoutDoctor() {
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}
?>
