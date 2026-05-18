<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Module - Prescription Actions Handler
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

try {
    // Get form data
    $patient_id = $_POST['patient_id'] ?? 0;
    $prescription_date = $_POST['prescription_date'] ?? date('Y-m-d');
    $diagnosis = $_POST['diagnosis'] ?? '';
    $instructions = $_POST['instructions'] ?? '';
    $follow_up_date = $_POST['follow_up_date'] ?? null;

    // Validate required fields
    if (empty($patient_id) || empty($diagnosis)) {
        throw new Exception('Patient and diagnosis are required');
    }

    // Build medications array
    $med_names = $_POST['med_name'] ?? [];
    $med_dosages = $_POST['med_dosage'] ?? [];
    $med_frequencies = $_POST['med_frequency'] ?? [];
    $med_durations = $_POST['med_duration'] ?? [];

    if (empty($med_names)) {
        throw new Exception('At least one medication is required');
    }

    $medications = [];
    for ($i = 0; $i < count($med_names); $i++) {
        if (!empty($med_names[$i])) {
            $medications[] = [
                'name' => $med_names[$i],
                'dosage' => $med_dosages[$i] ?? '',
                'frequency' => $med_frequencies[$i] ?? '',
                'duration' => $med_durations[$i] ?? ''
            ];
        }
    }

    if (empty($medications)) {
        throw new Exception('At least one valid medication is required');
    }

    $medications_json = json_encode($medications);

    // Insert prescription
    $db = getDoctorDB();
    $stmt = $db->prepare("
        INSERT INTO prescriptions 
        (patient_id, doctor_id, prescription_date, diagnosis, medications, instructions, follow_up_date, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    $stmt->execute([
        $patient_id,
        $doctor_id,
        $prescription_date,
        $diagnosis,
        $medications_json,
        $instructions,
        $follow_up_date
    ]);

    $prescription_id = $db->lastInsertId();

    // Log activity
    executeQuery(
        "INSERT INTO doctor_activity_logs (doctor_id, action, description, related_type, related_id, ip_address) 
         VALUES (?, 'create_prescription', 'Created new prescription', 'prescription', ?, ?)",
        [$doctor_id, $prescription_id, $_SERVER['REMOTE_ADDR'] ?? null]
    );

    echo json_encode([
        'success' => true,
        'message' => 'Prescription created successfully',
        'prescription_id' => $prescription_id
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
