<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../server/config/config.php';
require_once '../server/config/database.php';
require_once '../server/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
requireLogin('../client/login.php');
requireRole(ROLE_USER, '../client/login.php');

$user = getCurrentUser();
$db = getDB();

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Get health records
$stmt = $db->prepare("SELECT * FROM health_records WHERE user_id = ? ORDER BY recorded_at DESC");
$stmt->execute([$user['id']]);
$healthRecords = $stmt->fetchAll();

// Set headers for HTML download (can be printed to PDF)
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename="health_report_' . date('Y-m-d') . '.html"');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Health Report - <?php echo htmlspecialchars($userData['name']); ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #C4A7FF;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #C4A7FF;
            margin-bottom: 10px;
        }
        
        .report-title {
            font-size: 24px;
            margin: 10px 0;
            color: #333;
        }
        
        .patient-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #C4A7FF;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .info-value {
            color: #333;
        }
        
        .records-section {
            margin-top: 30px;
        }
        
        .section-title {
            font-size: 20px;
            color: #C4A7FF;
            border-bottom: 2px solid #C4A7FF;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }
        
        .record {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #fff;
        }
        
        .record-date {
            font-weight: bold;
            color: #C4A7FF;
            font-size: 16px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .vital-signs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .vital {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid #C4A7FF;
        }
        
        .vital-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        
        .vital-value {
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        
        .notes-section {
            margin-top: 15px;
            padding: 12px;
            background: #f1f3f4;
            border-radius: 6px;
        }
        
        .notes-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 2px solid #ddd;
            padding-top: 20px;
        }
        
        .print-btn {
            background: #C4A7FF;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin: 10px;
        }
        
        .print-btn:hover {
            background: #7F5AF0;
        }
        
        .no-records {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" class="print-btn">🖨️ Print Report</button>
        <button onclick="window.close()" class="print-btn" style="background: #6b7280;">❌ Close</button>
    </div>

    <div class="header">
        <div class="logo">🤱 Aarunya</div>
        <div class="report-title">Maternal Health Report</div>
        <p style="margin: 5px 0; color: #666;">Generated on <?php echo date('F d, Y \a\t g:i A'); ?></p>
    </div>

    <div class="patient-info">
        <h3 style="margin-top: 0; color: #C4A7FF;">Patient Information</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Full Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($userData['name']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Age:</span>
                <span class="info-value"><?php echo $userData['age'] ?? 'Not specified'; ?> years</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($userData['email']); ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Pregnancy Week:</span>
                <span class="info-value">Week <?php echo $userData['pregnancy_week'] ?? 0; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Due Date:</span>
                <span class="info-value">
                    <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                    if ($userData['due_date']) {
                        echo date('F d, Y', strtotime($userData['due_date']));
                    } else {
                        echo 'Not set';
                    }
                    ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Report Generated:</span>
                <span class="info-value"><?php echo date('F d, Y'); ?></span>
            </div>
        </div>
    </div>

    <div class="records-section">
        <h3 class="section-title">📋 Health Records History</h3>
        
        <?php if (count($healthRecords) > 0): ?>
            <?php foreach($healthRecords as $index => $record): ?>
            <div class="record">
                <div class="record-date">
                    📅 Record #<?php echo $index + 1; ?> - <?php echo date('F d, Y \a\t g:i A', strtotime($record['recorded_at'])); ?>
                </div>
                
                <div class="vital-signs">
                    <?php if ($record['blood_pressure']): ?>
                    <div class="vital">
                        <div class="vital-label">Blood Pressure</div>
                        <div class="vital-value"><?php echo htmlspecialchars($record['blood_pressure']); ?> mmHg</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($record['hemoglobin']): ?>
                    <div class="vital">
                        <div class="vital-label">Hemoglobin</div>
                        <div class="vital-value"><?php echo htmlspecialchars($record['hemoglobin']); ?> g/dL</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($record['pulse_rate']): ?>
                    <div class="vital">
                        <div class="vital-label">Heart Rate</div>
                        <div class="vital-value"><?php echo htmlspecialchars($record['pulse_rate']); ?> bpm</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($record['weight']): ?>
                    <div class="vital">
                        <div class="vital-label">Weight</div>
                        <div class="vital-value"><?php echo htmlspecialchars($record['weight']); ?> kg</div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($record['temperature']): ?>
                    <div class="vital">
                        <div class="vital-label">Temperature</div>
                        <div class="vital-value"><?php echo htmlspecialchars($record['temperature']); ?> °C</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($record['notes']): ?>
                <div class="notes-section">
                    <div class="notes-label">📝 Medical Notes:</div>
                    <div><?php echo nl2br(htmlspecialchars($record['notes'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-records">
                <h4>📄 No Health Records Available</h4>
                <p>No health records have been added to your profile yet.<br>
                Records will appear here once your healthcare provider adds them.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p><strong>🏥 Aarunya Maternal Health Platform</strong></p>
        <p>📞 Emergency Hotline: 108 | 🩺 Maternal Helpline: 102</p>
        <p style="margin-top: 10px; font-size: 11px;">
            ⚠️ <strong>Medical Disclaimer:</strong> This report is generated automatically from recorded data and should be reviewed by a qualified healthcare professional. 
            For medical emergencies, please contact your healthcare provider immediately or call emergency services.
        </p>
        <p style="margin-top: 10px; font-size: 10px; color: #999;">
            Report generated by Aarunya Health System • <?php echo date('Y-m-d H:i:s'); ?>
        </p>
    </div>
</body>
</html>
