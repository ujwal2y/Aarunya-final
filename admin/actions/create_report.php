<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

// Create manual_reports table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `manual_reports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `report_type` varchar(100) NOT NULL,
            `patient_id` int(11) DEFAULT NULL,
            `doctor_id` int(11) DEFAULT NULL,
            `report_data` longtext,
            `file_path` varchar(500) DEFAULT NULL,
            `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
            `created_by` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `patient_id` (`patient_id`),
            KEY `doctor_id` (`doctor_id`),
            KEY `created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table might already exist, continue
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $report_type = $_POST['report_type'] ?? '';
    $patient_id = !empty($_POST['patient_id']) ? intval($_POST['patient_id']) : null;
    $doctor_id = !empty($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
    $report_data = $_POST['report_data'] ?? '';
    $status = $_POST['status'] ?? 'draft';
    $created_by = $_SESSION['admin_id'] ?? 1; // Use session admin_id or default to 1

    // Validate required fields
    if (empty($title) || empty($report_type)) {
        header('Location: ../pages/reports.php?error=missing_fields');
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO manual_reports (title, description, report_type, patient_id, doctor_id, report_data, status, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $title,
            $description,
            $report_type,
            $patient_id,
            $doctor_id,
            $report_data,
            $status,
            $created_by
        ]);

        header('Location: ../pages/reports.php?success=report_created');
        exit();

    } catch (PDOException $e) {
        error_log("Error creating report: " . $e->getMessage());
        header('Location: ../pages/reports.php?error=creation_failed');
        exit();
    }
}

// If not POST request, redirect back
header('Location: ../pages/reports.php');
exit();
?>
