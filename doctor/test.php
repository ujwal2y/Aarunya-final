<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Module - Test Page
 * Simple test to verify the doctor directory is accessible
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Module Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #F3E8FF;
            color: #fff;
            padding: 40px;
            text-align: center;
        }
        .success {
            background: #10b981;
            padding: 20px;
            border-radius: 12px;
            display: inline-block;
            margin: 20px;
        }
        .info {
            background: #E9D5FF;
            padding: 20px;
            border-radius: 12px;
            margin: 20px auto;
            max-width: 600px;
            text-align: left;
        }
        a {
            color: #10b981;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>✅ Doctor Module is Working!</h1>
    
    <div class="success">
        <h2>Success!</h2>
        <p>The doctor directory is accessible and PHP is working correctly.</p>
    </div>
    
    <div class="info">
        <h3>📁 File Information:</h3>
        <p><strong>Current File:</strong> <?php echo __FILE__; ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></p>
        <p><strong>Script Name:</strong> <?php echo $_SERVER['SCRIPT_NAME'] ?? 'Not set'; ?></p>
        <p><strong>Request URI:</strong> <?php echo $_SERVER['REQUEST_URI'] ?? 'Not set'; ?></p>
        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
        
        <h3>🔗 Available Pages:</h3>
        <ul>
            <li><a href="login.php">Doctor Login</a></li>
            <li><a href="register.php">Doctor Registration</a></li>
            <li><a href="dashboard.php">Doctor Dashboard</a></li>
        </ul>
        
        <h3>📂 Directory Check:</h3>
        <p><strong>Files in doctor directory:</strong></p>
        <ul>
            <?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
            $files = scandir(__DIR__);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "<li>$file</li>";
                }
            }
            ?>
        </ul>
    </div>
    
    <div style="margin-top: 40px;">
        <a href="login.php" style="background: #10b981; padding: 15px 30px; border-radius: 8px; display: inline-block;">
            Go to Doctor Login →
        </a>
    </div>
</body>
</html>

