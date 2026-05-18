<?php
/**
 * Run OTP Migration Script
 */

try {
    // Connect to database
    $pdo = new PDO('mysql:host=localhost;dbname=aarunya_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully\n";
    
    // Read migration file
    $migrationFile = __DIR__ . '/migrations/003_create_otp_table.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    echo "Executing OTP migration...\n";
    
    // Split by semicolons and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   stripos($stmt, '--') !== 0 && 
                   stripos($stmt, '/*') !== 0;
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo ".";
            } catch (PDOException $e) {
                // Ignore "already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }
    
    echo "\n\nOTP migration executed successfully!\n";
    echo "Tables created:\n";
    echo "- otp_codes\n";
    echo "- otp_attempts\n";
    echo "Events created:\n";
    echo "- cleanup_expired_otps\n";
    echo "- cleanup_old_otp_attempts\n";
    
} catch (Exception $e) {
    echo "\nError: " . $e->getMessage() . "\n";
    exit(1);
}
?>
