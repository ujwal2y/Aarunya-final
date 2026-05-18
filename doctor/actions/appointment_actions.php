<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Module - Appointment Actions Handler
 * Handles: confirm, reject, reschedule, complete appointments
 */

session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Check if doctor is logged in
if (!isDoctorLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$doctor_id = $_SESSION['doctor_id'];
$action = $_POST['action'] ?? '';
$appointment_id = $_POST['appointment_id'] ?? 0;

// Validate appointment belongs to this doctor
function validateAppointment($appointment_id, $doctor_id) {
    $appointment = fetchOne(
        "SELECT * FROM appointments WHERE id = ? AND doctor_id = ?",
        [$appointment_id, $doctor_id]
    );
    return $appointment !== false;
}

// Log activity
function logActivity($doctor_id, $action, $description, $related_id = null) {
    executeQuery(
        "INSERT INTO doctor_activity_logs (doctor_id, action, description, related_type, related_id, ip_address) 
         VALUES (?, ?, ?, 'appointment', ?, ?)",
        [$doctor_id, $action, $description, $related_id, $_SERVER['REMOTE_ADDR'] ?? null]
    );
}

// Create notification for patient
function notifyPatient($patient_id, $title, $message) {
    // This would integrate with a notification system
    // For now, we'll just log it
    return true;
}

try {
    switch ($action) {
        case 'confirm':
            if (!validateAppointment($appointment_id, $doctor_id)) {
                throw new Exception('Invalid appointment');
            }

            executeQuery(
                "UPDATE appointments SET status = 'confirmed', updated_at = NOW() WHERE id = ?",
                [$appointment_id]
            );

            // Get patient info
            $appointment = fetchOne(
                "SELECT a.*, u.name as patient_name, u.id as patient_id 
                 FROM appointments a 
                 JOIN users u ON a.user_id = u.id 
                 WHERE a.id = ?",
                [$appointment_id]
            );

            logActivity($doctor_id, 'confirm_appointment', 
                "Confirmed appointment with {$appointment['patient_name']}", $appointment_id);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment confirmed successfully',
                'status' => 'confirmed'
            ]);
            break;

        case 'reject':
            if (!validateAppointment($appointment_id, $doctor_id)) {
                throw new Exception('Invalid appointment');
            }

            $reason = $_POST['reason'] ?? 'Not specified';

            executeQuery(
                "UPDATE appointments SET status = 'cancelled', notes = ?, updated_at = NOW() WHERE id = ?",
                ["Cancelled by doctor: $reason", $appointment_id]
            );

            $appointment = fetchOne(
                "SELECT a.*, u.name as patient_name 
                 FROM appointments a 
                 JOIN users u ON a.user_id = u.id 
                 WHERE a.id = ?",
                [$appointment_id]
            );

            logActivity($doctor_id, 'reject_appointment', 
                "Rejected appointment with {$appointment['patient_name']}: $reason", $appointment_id);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
                'status' => 'cancelled'
            ]);
            break;

        case 'reschedule':
            if (!validateAppointment($appointment_id, $doctor_id)) {
                throw new Exception('Invalid appointment');
            }

            $new_date = $_POST['new_date'] ?? '';
            $new_time = $_POST['new_time'] ?? '';

            if (empty($new_date) || empty($new_time)) {
                throw new Exception('Date and time are required');
            }

            // Validate date format
            $date = DateTime::createFromFormat('Y-m-d', $new_date);
            $time = DateTime::createFromFormat('H:i', $new_time);

            if (!$date || !$time) {
                throw new Exception('Invalid date or time format');
            }

            executeQuery(
                "UPDATE appointments 
                 SET appointment_date = ?, appointment_time = ?, status = 'confirmed', updated_at = NOW() 
                 WHERE id = ?",
                [$new_date, $new_time, $appointment_id]
            );

            logActivity($doctor_id, 'reschedule_appointment', 
                "Rescheduled appointment to $new_date at $new_time", $appointment_id);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment rescheduled successfully',
                'new_date' => $new_date,
                'new_time' => $new_time
            ]);
            break;

        case 'complete':
            if (!validateAppointment($appointment_id, $doctor_id)) {
                throw new Exception('Invalid appointment');
            }

            executeQuery(
                "UPDATE appointments SET status = 'completed', updated_at = NOW() WHERE id = ?",
                [$appointment_id]
            );

            logActivity($doctor_id, 'complete_appointment', 
                "Marked appointment as completed", $appointment_id);

            echo json_encode([
                'success' => true,
                'message' => 'Appointment marked as completed',
                'status' => 'completed'
            ]);
            break;

        case 'add_notes':
            if (!validateAppointment($appointment_id, $doctor_id)) {
                throw new Exception('Invalid appointment');
            }

            $notes = $_POST['notes'] ?? '';

            executeQuery(
                "UPDATE appointments SET notes = ?, updated_at = NOW() WHERE id = ?",
                [$notes, $appointment_id]
            );

            logActivity($doctor_id, 'add_appointment_notes', 
                "Added notes to appointment", $appointment_id);

            echo json_encode([
                'success' => true,
                'message' => 'Notes added successfully'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
