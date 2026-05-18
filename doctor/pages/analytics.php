<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
requireDoctorLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Doctor Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 50%, #1a0e2e 100%);
            color: #263238;
            min-height: 100vh;
        }
        .header {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(16, 185, 129, 0.15);
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 800;
            color: #10b981;
            text-decoration: none;
        }
        .btn-back {
            padding: 10px 20px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 32px;
            text-align: center;
        }
        .coming-soon {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(16, 185, 129, 0.15);
            border-radius: 16px;
            padding: 60px 40px;
            margin-top: 60px;
        }
        .icon {
            font-size: 80px;
            color: #10b981;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p {
            color: #546e7a;
            font-size: 18px;
            margin-bottom: 32px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="../dashboard.php" class="logo">
            <i class="fas fa-user-md"></i>
            <span>Aarunya Doctor</span>
        </a>
        <a href="../dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <div class="container">
        <div class="coming-soon">
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <h1>Analytics & Insights</h1>
            <p>Track your performance, patient satisfaction, and consultation statistics.</p>
            <p style="font-size: 14px; color: #78909c;">Analytics dashboard is under development.</p>
        </div>
    </div>
</body>
</html>

