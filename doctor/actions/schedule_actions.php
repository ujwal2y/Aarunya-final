<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Module - Schedule Actions Handler
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

try {
    switch ($action) {
        case 'add':
            $day_of_week = $_POST['day_of_week'] ?? null;
            $start_time = $_POST['start_time'] ?? '';
            $end_time = $_POST['end_time'] ?? '';
            $slot_duration = $_POST['slot_duration'] ?? 30;

            if ($day_of_week === null || empty($start_time) || empty($end_time)) {
                throw new Exception('All fields are required');
            }

            // Validate time format
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $start_time) ||
                !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $end_time)) {
                throw new Exception('Invalid time format. Use HH:MM format');
            }

            // Check if slot already exists
            $existing = fetchOne(
                "SELECT id FROM doctor_schedule 
                 WHERE doctor_id = ? AND day_of_week = ? AND start_time = ?",
                [$doctor_id, $day_of_week, $start_time]
            );

            if ($existing) {
                throw new Exception('A schedule already exists for this time slot');
            }

            executeQuery(
                "INSERT INTO doctor_schedule (doctor_id, day_of_week, start_time, end_time, slot_duration, is_available)
                 VALUES (?, ?, ?, ?, ?, TRUE)",
                [$doctor_id, $day_of_week, $start_time, $end_time, $slot_duration]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Schedule added successfully'
            ]);
            break;

        case 'delete':
            $slot_id = $_POST['slot_id'] ?? 0;

            // Verify slot belongs to this doctor
            $slot = fetchOne(
                "SELECT * FROM doctor_schedule WHERE id = ? AND doctor_id = ?",
                [$slot_id, $doctor_id]
            );

            if (!$slot) {
                throw new Exception('Schedule not found or unauthorized');
            }

            executeQuery(
                "DELETE FROM doctor_schedule WHERE id = ?",
                [$slot_id]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ]);
            break;

        case 'toggle':
            $slot_id = $_POST['slot_id'] ?? 0;

            // Verify slot belongs to this doctor
            $slot = fetchOne(
                "SELECT * FROM doctor_schedule WHERE id = ? AND doctor_id = ?",
                [$slot_id, $doctor_id]
            );

            if (!$slot) {
                throw new Exception('Schedule not found or unauthorized');
            }

            $new_status = !$slot['is_available'];

            executeQuery(
                "UPDATE doctor_schedule SET is_available = ? WHERE id = ?",
                [$new_status, $slot_id]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Schedule status updated',
                'is_available' => $new_status
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
