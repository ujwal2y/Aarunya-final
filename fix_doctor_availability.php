<?php
/**
 * Fix Doctor Availability Columns
 * Adds is_available and availability_note columns to doctors table if they don't exist
 */

require_once 'server/config/database.php';

try {
    $db = getDB();
    
    echo "<h1>Doctor Availability Fix</h1>";
    echo "<style>body { font-family: Arial; padding: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; }</style>";
    
    // Check if is_available column exists
    $checkAvailable = $db->query("SHOW COLUMNS FROM doctors LIKE 'is_available'");
    $hasAvailable = $checkAvailable->rowCount() > 0;
    
    if (!$hasAvailable) {
        echo "<p class='info'>Adding is_available column...</p>";
        $db->exec("ALTER TABLE doctors ADD COLUMN is_available TINYINT(1) DEFAULT 1 AFTER active");
        echo "<p class='success'>✓ Added is_available column</p>";
    } else {
        echo "<p class='success'>✓ is_available column already exists</p>";
    }
    
    // Check if availability_note column exists
    $checkNote = $db->query("SHOW COLUMNS FROM doctors LIKE 'availability_note'");
    $hasNote = $checkNote->rowCount() > 0;
    
    if (!$hasNote) {
        echo "<p class='info'>Adding availability_note column...</p>";
        $db->exec("ALTER TABLE doctors ADD COLUMN availability_note TEXT NULL AFTER is_available");
        echo "<p class='success'>✓ Added availability_note column</p>";
    } else {
        echo "<p class='success'>✓ availability_note column already exists</p>";
    }
    
    // Set all doctors to available by default
    $db->exec("UPDATE doctors SET is_available = 1 WHERE is_available IS NULL");
    echo "<p class='success'>✓ Set all doctors to available by default</p>";
    
    echo "<hr>";
    echo "<h2>Verification</h2>";
    
    // Show current doctors and their availability
    $stmt = $db->query("SELECT id, name, email, is_available, availability_note FROM doctors");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($doctors) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Name</th><th>Email</th><th>Available</th><th>Note</th></tr>";
        foreach ($doctors as $doctor) {
            $available = $doctor['is_available'] ? 'Yes' : 'No';
            $availableColor = $doctor['is_available'] ? 'green' : 'red';
            $note = $doctor['availability_note'] ? htmlspecialchars($doctor['availability_note']) : '-';
            echo "<tr>";
            echo "<td>{$doctor['id']}</td>";
            echo "<td>{$doctor['name']}</td>";
            echo "<td>{$doctor['email']}</td>";
            echo "<td style='color: $availableColor; font-weight: bold;'>$available</td>";
            echo "<td>$note</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>No doctors found in database</p>";
    }
    
    echo "<hr>";
    echo "<h2>✅ Fix Complete!</h2>";
    echo "<p>The doctor availability system is now ready to use.</p>";
    echo "<p><a href='doctor/dashboard.php'>Go to Doctor Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
