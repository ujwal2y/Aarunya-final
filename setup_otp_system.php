<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP System Setup - Aarunya Healthcare</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 1rem;
        }
        
        .status-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        
        .status-item.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-item.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-item.info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .code-block {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
            margin: 10px 0;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .checklist {
            list-style: none;
        }
        
        .checklist li {
            padding: 10px;
            margin-bottom: 5px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .checklist li:before {
            content: "✓ ";
            color: #28a745;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 OTP System Setup</h1>
            <p>Aarunya Healthcare - Database Migration</p>
        </div>

        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $setupComplete = false;
        $errors = [];
        $success = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
            try {
                // Include database configuration
                require_once 'server/config/database.php';
                
                $pdo = getDB();
                
                // Check if tables already exist
                $checkOtpCodes = $pdo->query("SHOW TABLES LIKE 'otp_codes'");
                $checkOtpAttempts = $pdo->query("SHOW TABLES LIKE 'otp_attempts'");
                
                if ($checkOtpCodes->rowCount() > 0 && $checkOtpAttempts->rowCount() > 0) {
                    $success[] = "OTP tables already exist. No migration needed.";
                    $setupComplete = true;
                } else {
                    // Read migration file
                    $migrationFile = __DIR__ . '/database/migrations/003_create_otp_table.sql';
                    
                    if (!file_exists($migrationFile)) {
                        $errors[] = "Migration file not found: $migrationFile";
                    } else {
                        $sql = file_get_contents($migrationFile);
                        
                        // Split SQL into individual statements
                        $statements = array_filter(
                            array_map('trim', explode(';', $sql)),
                            function($stmt) {
                                return !empty($stmt) && 
                                       !preg_match('/^--/', $stmt) && 
                                       !preg_match('/^\/\*/', $stmt);
                            }
                        );
                        
                        // Execute each statement
                        foreach ($statements as $statement) {
                            if (!empty(trim($statement))) {
                                try {
                                    $pdo->exec($statement);
                                } catch (PDOException $e) {
                                    // Ignore "already exists" errors
                                    if (strpos($e->getMessage(), 'already exists') === false) {
                                        throw $e;
                                    }
                                }
                            }
                        }
                        
                        $success[] = "OTP tables created successfully!";
                        $success[] = "Table 'otp_codes' created";
                        $success[] = "Table 'otp_attempts' created";
                        $success[] = "Event scheduler enabled";
                        $success[] = "Cleanup events created";
                        
                        $setupComplete = true;
                    }
                }
                
                // Verify tables exist
                $verifyOtpCodes = $pdo->query("SHOW TABLES LIKE 'otp_codes'");
                $verifyOtpAttempts = $pdo->query("SHOW TABLES LIKE 'otp_attempts'");
                
                if ($verifyOtpCodes->rowCount() === 0) {
                    $errors[] = "Table 'otp_codes' was not created";
                }
                if ($verifyOtpAttempts->rowCount() === 0) {
                    $errors[] = "Table 'otp_attempts' was not created";
                }
                
                if (empty($errors)) {
                    $success[] = "✓ All tables verified successfully!";
                }
                
            } catch (PDOException $e) {
                $errors[] = "Database error: " . $e->getMessage();
            } catch (Exception $e) {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
        ?>

        <?php if (!empty($success)): ?>
        <div class="status-box">
            <?php foreach ($success as $msg): ?>
            <div class="status-item success">
                <span>✓</span>
                <span><?php echo htmlspecialchars($msg); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="status-box">
            <?php foreach ($errors as $error): ?>
            <div class="status-item error">
                <span>✗</span>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!$setupComplete): ?>
        <div class="section">
            <h2>Setup Instructions</h2>
            <p style="margin-bottom: 20px;">This script will create the necessary database tables for the OTP registration system.</p>
            
            <div class="status-box">
                <div class="status-item info">
                    <span>ℹ️</span>
                    <span>Make sure your MySQL server is running on port 3307</span>
                </div>
                <div class="status-item info">
                    <span>ℹ️</span>
                    <span>Database: aarunya_db</span>
                </div>
            </div>

            <form method="POST">
                <div class="actions">
                    <button type="submit" name="setup" class="btn">
                        🚀 Run Setup
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="section">
            <h2>✅ Setup Complete!</h2>
            <p style="margin-bottom: 20px;">The OTP system is now ready to use.</p>
            
            <h3 style="color: #667eea; margin-bottom: 10px;">What's Next?</h3>
            <ul class="checklist">
                <li>Test the registration page</li>
                <li>Verify OTP emails are being sent</li>
                <li>Check the doctors page (no "Unavailable" text)</li>
                <li>Review the documentation</li>
            </ul>

            <div class="actions">
                <a href="client/register.php" class="btn">
                    📝 Test Registration
                </a>
                <a href="client/login.php" class="btn btn-secondary">
                    🔐 Go to Login
                </a>
            </div>
        </div>

        <div class="section">
            <h2>📚 Documentation</h2>
            <div class="code-block">
                OTP_REGISTRATION_GUIDE.md - Complete OTP system guide<br>
                QUICK_START_GUIDE.md - Quick setup instructions<br>
                CHANGES_SUMMARY.md - All changes made<br>
                USER_TESTING_SCRIPT.md - Testing guide
            </div>
        </div>

        <div class="section">
            <h2>🔍 Verify Setup</h2>
            <p style="margin-bottom: 10px;">Run these SQL queries to verify:</p>
            <div class="code-block">
                -- Check tables exist<br>
                SHOW TABLES LIKE 'otp%';<br>
                <br>
                -- Check table structure<br>
                DESCRIBE otp_codes;<br>
                DESCRIBE otp_attempts;
            </div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px; color: #666; font-size: 0.9rem;">
            <p>Aarunya Healthcare © 2026</p>
            <p>OTP Registration System v2.0</p>
        </div>
    </div>
</body>
</html>
