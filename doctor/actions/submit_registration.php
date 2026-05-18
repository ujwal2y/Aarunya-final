<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
session_start();
require_once '../../server/config/database.php';

header('Content-Type: application/json');

try {
    $db = getDB();
    
    // Validate required fields
    $required_fields = ['full_name', 'gender', 'date_of_birth', 'mobile', 'email', 'address', 'city', 'state', 'pin_code',
                       'medical_license_number', 'medical_council_registration', 'primary_specialization', 'years_of_experience',
                       'hospital_name', 'workplace_address', 'consultation_fee', 'languages_spoken',
                       'degree_name', 'university', 'graduation_year', 'username', 'password'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => 'Missing required field: ' . $field]);
            exit;
        }
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM doctors WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    
    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM doctors WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already taken']);
        exit;
    }
    
    // Check if medical license number already exists (only if doctors table has this column)
    try {
        $stmt = $db->prepare("SELECT id FROM doctors WHERE medical_license_number = ?");
        $stmt->execute([$_POST['medical_license_number']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Medical license number already registered']);
            exit;
        }
    } catch (PDOException $e) {
        // Column might not exist yet, skip this check
    }
    
    // Handle file uploads
    $upload_dir = '../../uploads/doctor_documents/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $profile_photo = null;
    $license_certificate = null;
    $degree_certificate = null;
    $government_id = null;
    $experience_certificate = null;
    
    // Upload profile photo
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $profile_photo = uploadFile($_FILES['profile_photo'], $upload_dir, 'profile_');
    }
    
    // Upload license certificate
    if (isset($_FILES['license_certificate']) && $_FILES['license_certificate']['error'] === 0) {
        $license_certificate = uploadFile($_FILES['license_certificate'], $upload_dir, 'license_');
    }
    
    // Upload degree certificate
    if (isset($_FILES['degree_certificate']) && $_FILES['degree_certificate']['error'] === 0) {
        $degree_certificate = uploadFile($_FILES['degree_certificate'], $upload_dir, 'degree_');
    }
    
    // Upload government ID
    if (isset($_FILES['government_id']) && $_FILES['government_id']['error'] === 0) {
        $government_id = uploadFile($_FILES['government_id'], $upload_dir, 'govid_');
    }
    
    // Upload experience certificate
    if (isset($_FILES['experience_certificate']) && $_FILES['experience_certificate']['error'] === 0) {
        $experience_certificate = uploadFile($_FILES['experience_certificate'], $upload_dir, 'exp_');
    }
    
    // Process available days
    $available_days = isset($_POST['available_days']) ? implode(',', $_POST['available_days']) : '';
    
    // Process time slots
    $time_slots = '';
    if (!empty($_POST['start_time']) && !empty($_POST['end_time'])) {
        $time_slots = $_POST['start_time'] . ' - ' . $_POST['end_time'];
    }
    
    // Hash password
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Insert into database
    // First, check which columns exist in the doctors table
    $stmt = $db->query("SHOW COLUMNS FROM doctors");
    $existing_columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Build dynamic INSERT query based on existing columns
    $columns = ['name', 'email', 'phone', 'specialization', 'experience', 'qualification', 'availability', 'status', 'created_at'];
    $values = [
        $_POST['full_name'],
        $_POST['email'],
        $_POST['mobile'],
        $_POST['primary_specialization'],
        $_POST['years_of_experience'],
        $_POST['degree_name'],
        $time_slots,
        'pending',
        date('Y-m-d H:i:s')
    ];
    
    // Add optional columns if they exist
    $optional_fields = [
        'gender' => $_POST['gender'] ?? null,
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'mobile' => $_POST['mobile'] ?? null,
        'address' => $_POST['address'] ?? null,
        'city' => $_POST['city'] ?? null,
        'state' => $_POST['state'] ?? null,
        'pin_code' => $_POST['pin_code'] ?? null,
        'profile_photo' => $profile_photo,
        'medical_license_number' => $_POST['medical_license_number'] ?? null,
        'medical_council_registration' => $_POST['medical_council_registration'] ?? null,
        'secondary_specialization' => $_POST['secondary_specialization'] ?? null,
        'hospital_name' => $_POST['hospital_name'] ?? null,
        'workplace_address' => $_POST['workplace_address'] ?? null,
        'consultation_fee' => $_POST['consultation_fee'] ?? null,
        'languages_spoken' => $_POST['languages_spoken'] ?? null,
        'university' => $_POST['university'] ?? null,
        'graduation_year' => $_POST['graduation_year'] ?? null,
        'fellowship_details' => $_POST['fellowship_details'] ?? null,
        'additional_certifications' => $_POST['additional_certifications'] ?? null,
        'license_certificate' => $license_certificate,
        'degree_certificate' => $degree_certificate,
        'government_id' => $government_id,
        'experience_certificate' => $experience_certificate,
        'available_days' => $available_days,
        'time_slots' => $time_slots,
        'online_consultation' => isset($_POST['online_consultation']) ? 1 : 0,
        'in_person_consultation' => isset($_POST['in_person_consultation']) ? 1 : 0,
        'emergency_availability' => isset($_POST['emergency_availability']) ? 1 : 0,
        'username' => $_POST['username'] ?? null,
        'password' => $hashed_password,
        'registration_status' => 'pending'
    ];
    
    foreach ($optional_fields as $col => $val) {
        if (in_array($col, $existing_columns)) {
            $columns[] = $col;
            $values[] = $val;
        }
    }
    
    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $columns_str = implode(',', $columns);
    
    $sql = "INSERT INTO doctors ($columns_str) VALUES ($placeholders)";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($values);
    
    if ($result) {
        // Send confirmation email (optional - implement later)
        // sendConfirmationEmail($_POST['email'], $_POST['full_name']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful! Your application is under review.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to save registration. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    error_log('Doctor Registration Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred during registration. Please try again.',
        'debug' => $e->getMessage() // Remove this in production
    ]);
}

// File upload helper function
function uploadFile($file, $upload_dir, $prefix) {
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = $prefix . uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $new_filename;
    }
    
    return null;
}
?>
