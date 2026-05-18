<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
/**
 * Doctor Availability Toggle
 */
require_once '../server/config/database.php';

session_start();

// Check if doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('Location: ../client/login.php?role=doctor');
    exit;
}

$doctor_id = $_SESSION['doctor_id'];
$db = getDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $availability_note = trim($_POST['availability_note'] ?? '');
    
    $stmt = $db->prepare("
        UPDATE doctors 
        SET is_available = ?, 
            availability_note = ? 
        WHERE id = ?
    ");
    $stmt->execute([$is_available, $availability_note, $doctor_id]);
    
    $success = true;
}

// Get current availability status
$stmt = $db->prepare("SELECT is_available, availability_note FROM doctors WHERE id = ?");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Availability Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #fff;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
        }
        
        h1 {
            margin-bottom: 10px;
            color: #10b981;
        }
        
        .subtitle {
            color: #546e7a;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #f1f5f9;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #78909c;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #10b981;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-label {
            display: inline-block;
            margin-left: 15px;
            font-size: 18px;
            font-weight: 600;
            vertical-align: middle;
        }
        
        .status-available {
            color: #10b981;
        }
        
        .status-unavailable {
            color: #ef4444;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 8px;
            color: #f1f5f9;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
        }
        
        textarea:focus {
            outline: none;
            border-color: #10b981;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 16px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }
        
        .btn-secondary {
            background: rgba(148, 163, 184, 0.2);
            color: #f1f5f9;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: rgba(148, 163, 184, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
        }
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .info-box p {
            color: #546e7a;
            font-size: 14px;
            line-height: 1.6;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-toggle-on"></i> Availability Settings</h1>
            <p class="subtitle">Control when patients can book appointments with you</p>
            
            <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>Availability status updated successfully!</span>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>
                        <i class="fas fa-power-off"></i> Availability Status
                    </label>
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_available" id="availabilityToggle" 
                               <?php echo $doctor['is_available'] ? 'checked' : ''; ?>
                               onchange="updateStatusLabel()">
                        <span class="slider"></span>
                    </label>
                    <span id="statusLabel" class="status-label <?php echo $doctor['is_available'] ? 'status-available' : 'status-unavailable'; ?>">
                        <?php echo $doctor['is_available'] ? 'Available' : 'Unavailable'; ?>
                    </span>
                </div>
                
                <div class="form-group">
                    <label for="availability_note">
                        <i class="fas fa-comment"></i> Unavailability Note (Optional)
                    </label>
                    <textarea name="availability_note" id="availability_note" 
                              placeholder="e.g., On vacation until May 20th, Attending conference, Medical leave"><?php echo htmlspecialchars($doctor['availability_note'] ?? ''); ?></textarea>
                    <small style="color: #546e7a; font-size: 12px; display: block; margin-top: 5px;">
                        This message will be shown to patients when you're unavailable
                    </small>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
            
            <div class="info-box">
                <p><strong><i class="fas fa-info-circle"></i> How it works:</strong></p>
                <p style="margin-top: 10px;">
                    • When <strong style="color: #10b981;">Available</strong>: Patients can book appointments with you<br>
                    • When <strong style="color: #ef4444;">Unavailable</strong>: Patients will see you're unavailable and cannot book<br>
                    • Your unavailability note helps patients understand when you'll be back
                </p>
            </div>
        </div>
    </div>
    
    <script>
        function updateStatusLabel() {
            const toggle = document.getElementById('availabilityToggle');
            const label = document.getElementById('statusLabel');
            
            if (toggle.checked) {
                label.textContent = 'Available';
                label.className = 'status-label status-available';
            } else {
                label.textContent = 'Unavailable';
                label.className = 'status-label status-unavailable';
            }
        }
    </script>
</body>
</html>

