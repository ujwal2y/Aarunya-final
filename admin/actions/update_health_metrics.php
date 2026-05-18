<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

// Create health_metrics table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `health_metrics` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `blood_pressure_systolic` int(11) DEFAULT NULL,
            `blood_pressure_diastolic` int(11) DEFAULT NULL,
            `hemoglobin` decimal(4,2) DEFAULT NULL,
            `heart_rate` int(11) DEFAULT NULL,
            `weight` decimal(5,2) DEFAULT NULL,
            `temperature` decimal(4,2) DEFAULT NULL,
            `glucose_level` int(11) DEFAULT NULL,
            `notes` text DEFAULT NULL,
            `recorded_by` int(11) DEFAULT NULL,
            `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `recorded_at` (`recorded_at`),
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table might already exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $user_id = intval($_POST['user_id']);
        $bp_systolic = intval($_POST['bp_systolic']);
        $bp_diastolic = intval($_POST['bp_diastolic']);
        $hemoglobin = floatval($_POST['hemoglobin']);
        $heart_rate = intval($_POST['heart_rate']);
        $weight = floatval($_POST['weight']);
        $temperature = !empty($_POST['temperature']) ? floatval($_POST['temperature']) : null;
        $glucose = !empty($_POST['glucose']) ? intval($_POST['glucose']) : null;
        $notes = trim($_POST['notes'] ?? '');
        $recorded_by = $_SESSION['admin_id'] ?? 1;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO health_metrics 
                (user_id, blood_pressure_systolic, blood_pressure_diastolic, hemoglobin, heart_rate, weight, temperature, glucose_level, notes, recorded_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $user_id, $bp_systolic, $bp_diastolic, $hemoglobin, $heart_rate, $weight, $temperature, $glucose, $notes, $recorded_by
            ]);
            
            header('Location: ../pages/users.php?view=' . $user_id . '&success=metrics_added');
            exit();
        } catch (PDOException $e) {
            header('Location: ../pages/users.php?error=add_failed&user_id=' . $user_id);
            exit();
        }
    }
    
    if ($action === 'update') {
        $metric_id = intval($_POST['metric_id']);
        $bp_systolic = intval($_POST['bp_systolic']);
        $bp_diastolic = intval($_POST['bp_diastolic']);
        $hemoglobin = floatval($_POST['hemoglobin']);
        $heart_rate = intval($_POST['heart_rate']);
        $weight = floatval($_POST['weight']);
        $temperature = !empty($_POST['temperature']) ? floatval($_POST['temperature']) : null;
        $glucose = !empty($_POST['glucose']) ? intval($_POST['glucose']) : null;
        $notes = trim($_POST['notes'] ?? '');
        
        try {
            $stmt = $pdo->prepare("
                UPDATE health_metrics 
                SET blood_pressure_systolic = ?, blood_pressure_diastolic = ?, hemoglobin = ?, 
                    heart_rate = ?, weight = ?, temperature = ?, glucose_level = ?, notes = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $bp_systolic, $bp_diastolic, $hemoglobin, $heart_rate, $weight, $temperature, $glucose, $notes, $metric_id
            ]);
            
            // Get user_id for redirect
            $stmt = $pdo->prepare("SELECT user_id FROM health_metrics WHERE id = ?");
            $stmt->execute([$metric_id]);
            $user_id = $stmt->fetchColumn();
            
            header('Location: ../pages/users.php?view=' . $user_id . '&success=metrics_updated');
            exit();
        } catch (PDOException $e) {
            header('Location: ../pages/users.php?error=update_failed');
            exit();
        }
    }
    
    if ($action === 'delete') {
        $metric_id = intval($_POST['metric_id']);
        
        try {
            // Get user_id before deleting
            $stmt = $pdo->prepare("SELECT user_id FROM health_metrics WHERE id = ?");
            $stmt->execute([$metric_id]);
            $user_id = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("DELETE FROM health_metrics WHERE id = ?");
            $stmt->execute([$metric_id]);
            
            header('Location: ../pages/users.php?view=' . $user_id . '&success=metrics_deleted');
            exit();
        } catch (PDOException $e) {
            header('Location: ../pages/users.php?error=delete_failed');
            exit();
        }
    }
}

header('Location: ../pages/users.php');
exit();
?>
