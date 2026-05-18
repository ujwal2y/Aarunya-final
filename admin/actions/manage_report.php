<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$report_id = intval($_POST['report_id'] ?? $_GET['report_id'] ?? 0);

if (!$report_id) {
    header('Location: ../pages/reports.php?error=invalid_report');
    exit();
}

try {
    switch ($action) {
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $report_type = $_POST['report_type'] ?? '';
                $patient_id = !empty($_POST['patient_id']) ? intval($_POST['patient_id']) : null;
                $doctor_id = !empty($_POST['doctor_id']) ? intval($_POST['doctor_id']) : null;
                $report_data = $_POST['report_data'] ?? '';
                $status = $_POST['status'] ?? 'draft';

                if (empty($title) || empty($report_type)) {
                    header('Location: ../pages/reports.php?error=missing_fields');
                    exit();
                }

                $stmt = $pdo->prepare("
                    UPDATE manual_reports 
                    SET title = ?, description = ?, report_type = ?, patient_id = ?, doctor_id = ?, report_data = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                $stmt->execute([
                    $title, $description, $report_type, $patient_id, $doctor_id, $report_data, $status, $report_id
                ]);

                header('Location: ../pages/reports.php?success=report_updated');
                exit();
            }
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM manual_reports WHERE id = ?");
            $stmt->execute([$report_id]);
            
            header('Location: ../pages/reports.php?success=report_deleted');
            exit();
            break;

        case 'toggle_status':
            // Toggle between draft and published
            $stmt = $pdo->prepare("SELECT status FROM manual_reports WHERE id = ?");
            $stmt->execute([$report_id]);
            $current_status = $stmt->fetchColumn();
            
            $new_status = ($current_status === 'draft') ? 'published' : 'draft';
            
            $stmt = $pdo->prepare("UPDATE manual_reports SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $report_id]);
            
            header('Location: ../pages/reports.php?success=status_updated');
            exit();
            break;

        default:
            header('Location: ../pages/reports.php?error=invalid_action');
            exit();
    }

} catch (PDOException $e) {
    error_log("Error managing report: " . $e->getMessage());
    header('Location: ../pages/reports.php?error=operation_failed');
    exit();
}
?>
