<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$id = $_GET['id'] ?? $_POST['id'] ?? 0;

switch($action) {
    case 'view_user':
        viewUser($id);
        break;
    case 'delete_user':
        deleteUser($id);
        break;
    case 'view_appointment':
        viewAppointment($id);
        break;
    case 'approve_appointment':
        approveAppointment($id);
        break;
    case 'reject_appointment':
        rejectAppointment($id);
        break;
    case 'view_emergency':
        viewEmergency($id);
        break;
    case 'resolve_emergency':
        resolveEmergency($id);
        break;
    case 'add_doctor':
        addDoctor();
        break;
    default:
        header('Location: ../pages/dashboard.php');
        exit();
}

function viewUser($id) {
    header("Location: ../pages/users.php?view=$id");
    exit();
}

function deleteUser($id) {
    global $pdo;
    
    if ($id > 0) {
        try {
            // Check if user has appointments
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ?");
            $stmt->execute([$id]);
            $appointmentCount = $stmt->fetchColumn();
            
            if ($appointmentCount > 0) {
                $_SESSION['error'] = "Cannot delete user with existing appointments. Please cancel appointments first.";
            } else {
                // Delete user
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $_SESSION['success'] = "User deleted successfully.";
                } else {
                    $_SESSION['error'] = "Failed to delete user.";
                }
            }
        } catch(Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    
    header('Location: ../pages/dashboard.php');
    exit();
}

function viewAppointment($id) {
    header("Location: ../pages/appointments.php?view=$id");
    exit();
}

function approveAppointment($id) {
    global $pdo;
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Appointment approved successfully.";
            } else {
                $_SESSION['error'] = "Failed to approve appointment.";
            }
        } catch(Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    
    header('Location: ../pages/dashboard.php');
    exit();
}

function rejectAppointment($id) {
    global $pdo;
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Appointment rejected successfully.";
            } else {
                $_SESSION['error'] = "Failed to reject appointment.";
            }
        } catch(Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    
    header('Location: ../pages/dashboard.php');
    exit();
}

function viewEmergency($id) {
    header("Location: ../pages/emergency.php?view=$id");
    exit();
}

function resolveEmergency($id) {
    global $pdo;
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE emergency_requests SET status = 'resolved', resolved_at = NOW() WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Emergency request resolved successfully.";
            } else {
                $_SESSION['error'] = "Failed to resolve emergency request.";
            }
        } catch(Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
    }
    
    header('Location: ../pages/dashboard.php');
    exit();
}

function addDoctor() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        global $pdo;
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $experience = intval($_POST['experience'] ?? 0);
        $qualification = trim($_POST['qualification'] ?? '');
        $availability = trim($_POST['availability'] ?? '');
        
        if ($name && $specialization) {
            try {
                $stmt = $pdo->prepare("INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $phone, $specialization, $experience, $qualification, $availability])) {
                    $_SESSION['success'] = "Doctor added successfully.";
                } else {
                    $_SESSION['error'] = "Failed to add doctor.";
                }
            } catch(Exception $e) {
                $_SESSION['error'] = "Error: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Name and specialization are required.";
        }
    }
    
    header('Location: ../pages/doctors.php');
    exit();
}
?>
