<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/db.php';

try {
    // Create health_metrics table
    $sql = "CREATE TABLE IF NOT EXISTS `health_metrics` (
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
        KEY `recorded_at` (`recorded_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    
    echo "✅ Success! The health_metrics table has been created successfully.<br><br>";
    echo "You can now:<br>";
    echo "1. <a href='../pages/users.php'>Go to Users Management</a><br>";
    echo "2. Click 'View Details' on any user<br>";
    echo "3. Add health metrics<br><br>";
    echo "<a href='../pages/dashboard.php'>← Back to Dashboard</a>";
    
} catch (PDOException $e) {
    echo "❌ Error creating table: " . $e->getMessage();
}
?>
