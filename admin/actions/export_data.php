<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$type = $_GET['type'] ?? '';
$format = $_GET['format'] ?? 'csv';

if (empty($type)) {
    die('Export type not specified');
}

try {
    switch ($type) {
        case 'users':
            exportUsers($format);
            break;
        case 'doctors':
            exportDoctors($format);
            break;
        case 'appointments':
            exportAppointments($format);
            break;
        case 'emergency':
            exportEmergency($format);
            break;
        case 'health_records':
            exportHealthRecords($format);
            break;
        default:
            die('Invalid export type');
    }
} catch (Exception $e) {
    die('Export error: ' . $e->getMessage());
}

function exportUsers($format) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT id, name, email, phone, age, pregnancy_week, due_date, status, created_at FROM users ORDER BY created_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'users_' . date('Y-m-d_H-i-s');
    $headers = ['ID', 'Name', 'Email', 'Phone', 'Age', 'Pregnancy Week', 'Due Date', 'Status', 'Registered'];
    
    exportData($data, $headers, $filename, $format);
}

function exportDoctors($format) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT id, name, email, phone, specialization, experience, qualification, availability, status, created_at FROM doctors ORDER BY created_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'doctors_' . date('Y-m-d_H-i-s');
    $headers = ['ID', 'Name', 'Email', 'Phone', 'Specialization', 'Experience', 'Qualification', 'Availability', 'Status', 'Added'];
    
    exportData($data, $headers, $filename, $format);
}

function exportAppointments($format) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT a.id, u.name as patient_name, u.email as patient_email, d.name as doctor_name, 
                         a.appointment_date, a.appointment_time, a.status, a.notes, a.created_at 
                         FROM appointments a 
                         JOIN users u ON a.user_id = u.id 
                         JOIN doctors d ON a.doctor_id = d.id 
                         ORDER BY a.created_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'appointments_' . date('Y-m-d_H-i-s');
    $headers = ['ID', 'Patient Name', 'Patient Email', 'Doctor Name', 'Date', 'Time', 'Status', 'Notes', 'Booked'];
    
    exportData($data, $headers, $filename, $format);
}

function exportEmergency($format) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT e.id, u.name as patient_name, u.email, e.message, e.location, e.phone, 
                         e.status, e.priority, e.created_at 
                         FROM emergency_requests e 
                         JOIN users u ON e.user_id = u.id 
                         ORDER BY e.created_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'emergency_requests_' . date('Y-m-d_H-i-s');
    $headers = ['ID', 'Patient Name', 'Email', 'Message', 'Location', 'Phone', 'Status', 'Priority', 'Created'];
    
    exportData($data, $headers, $filename, $format);
}

function exportHealthRecords($format) {
    global $pdo;
    
    $stmt = $pdo->query("SELECT h.id, u.name as patient_name, h.blood_pressure, h.hemoglobin, h.pulse_rate, 
                         h.weight, h.temperature, h.notes, h.recorded_at 
                         FROM health_records h 
                         JOIN users u ON h.user_id = u.id 
                         ORDER BY h.recorded_at DESC");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $filename = 'health_records_' . date('Y-m-d_H-i-s');
    $headers = ['ID', 'Patient Name', 'Blood Pressure', 'Hemoglobin', 'Pulse Rate', 'Weight', 'Temperature', 'Notes', 'Recorded'];
    
    exportData($data, $headers, $filename, $format);
}

function exportData($data, $headers, $filename, $format) {
    if ($format === 'csv') {
        exportCSV($data, $headers, $filename);
    } else {
        exportJSON($data, $filename);
    }
}

function exportCSV($data, $headers, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write headers
    fputcsv($output, $headers);
    
    // Write data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

function exportJSON($data, $filename) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit();
}
?>
