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

// Include appointment notification popup
include 'includes/appointment_notification.php';

$user = getCurrentUser();
$db = getDB();

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Get health records
$stmt = $db->prepare("SELECT * FROM health_records WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 10");
$stmt->execute([$user['id']]);
$healthRecords = $stmt->fetchAll();

// Get latest health metrics from health_metrics table
$latestMetrics = null;
try {
    $stmt = $db->prepare("SELECT * FROM health_metrics WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 1");
    $stmt->execute([$user['id']]);
    $latestMetrics = $stmt->fetch();
} catch (PDOException $e) {
    // Table might not exist yet
    $latestMetrics = null;
}

// Default values if no metrics found
$bloodPressure = $latestMetrics ? $latestMetrics['blood_pressure_systolic'] . '/' . $latestMetrics['blood_pressure_diastolic'] : '120/80';
$hemoglobin = $latestMetrics ? $latestMetrics['hemoglobin'] : '12.5';
$heartRate = $latestMetrics ? $latestMetrics['heart_rate'] : '75';
$weight = $latestMetrics ? $latestMetrics['weight'] : '65';
$lastUpdated = $latestMetrics ? date('M d, Y h:i A', strtotime($latestMetrics['recorded_at'])) : 'Not recorded yet';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Tracking - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .health-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .health-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            transition: all var(--transition-base);
        }
        
        .health-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-glow);
            border-color: var(--primary-purple);
        }
        
        .health-icon {
            width: 56px;
            height: 56px;
            background: var(--gradient-button);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .health-label {
            color: var(--text-secondary);
            font-size: var(--font-sm);
            margin-bottom: 0.5rem;
            font-weight: var(--font-medium);
        }
        
        .health-value {
            font-size: 2.25rem;
            font-weight: var(--font-extrabold);
            color: var(--text-primary);
            line-height: 1;
        }
        
        .health-unit {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            margin-left: 0.5rem;
            font-weight: var(--font-normal);
        }
        
        .section-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: var(--font-2xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
        }
        
        .record-item {
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            transition: all var(--transition-base);
        }
        
        .record-item:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary-purple);
        }
        
        .record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .record-header h4 {
            font-size: var(--font-lg);
            font-weight: var(--font-semibold);
            color: var(--text-primary);
            margin: 0;
        }
        
        .record-date {
            color: var(--text-muted);
            font-size: var(--font-sm);
        }
        
        .record-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .record-detail {
            display: flex;
            flex-direction: column;
        }
        
        .record-detail-label {
            color: var(--text-muted);
            font-size: var(--font-xs);
            margin-bottom: 0.25rem;
            font-weight: var(--font-medium);
        }
        
        .record-detail-value {
            font-weight: var(--font-semibold);
            color: var(--text-primary);
            font-size: var(--font-base);
        }
        
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 56px;
            height: 56px;
            background: var(--gradient-button);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.25rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(127, 90, 240, 0.4);
            z-index: 999;
        }
        
        @media (max-width: 1024px) {
            .mobile-menu-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <div>
                    <h1 class="page-title">Health Tracking</h1>
                    <p class="page-subtitle">Monitor your vital signs and pregnancy progress</p>
                </div>
                <div class="header-actions">
                    <div style="text-align: right; margin-right: 1rem;">
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Last Updated</div>
                        <div style="font-size: 0.875rem; color: var(--primary-purple); font-weight: 600;"><?php echo $lastUpdated; ?></div>
                    </div>
                    <div class="user-badge">
                        <div class="user-avatar-small">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Current Health Stats -->
            <div class="health-grid">
                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="health-label">Blood Pressure</div>
                    <div class="health-value">
                        <?php echo $bloodPressure; ?>
                        <span class="health-unit">mmHg</span>
                    </div>
                </div>

                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-droplet"></i>
                    </div>
                    <div class="health-label">Hemoglobin</div>
                    <div class="health-value">
                        <?php echo $hemoglobin; ?>
                        <span class="health-unit">g/dL</span>
                    </div>
                </div>

                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="health-label">Heart Rate</div>
                    <div class="health-value">
                        <?php echo $heartRate; ?>
                        <span class="health-unit">bpm</span>
                    </div>
                </div>

                <div class="health-card">
                    <div class="health-icon">
                        <i class="fas fa-weight-scale"></i>
                    </div>
                    <div class="health-label">Weight</div>
                    <div class="health-value">
                        <?php echo $weight; ?>
                        <span class="health-unit">kg</span>
                    </div>
                </div>
            </div>

            <!-- Pregnancy Progress -->
            <div class="section-card">
                <h2 class="section-title">Pregnancy Progress</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <div style="color: var(--text-secondary); font-size: var(--font-sm); margin-bottom: 0.5rem; font-weight: var(--font-medium);">Current Week</div>
                        <div style="font-size: var(--font-2xl); font-weight: var(--font-bold); color: var(--primary-purple);">
                            Week <?php echo $userData['pregnancy_week'] ?? 0; ?>
                        </div>
                    </div>
                    
                    <div>
                        <div style="color: var(--text-secondary); font-size: var(--font-sm); margin-bottom: 0.5rem; font-weight: var(--font-medium);">Due Date</div>
                        <div style="font-size: var(--font-lg); font-weight: var(--font-semibold); color: var(--text-primary);">
                            <?php 
                            if (isset($userData['due_date']) && $userData['due_date']) {
                                echo date('F d, Y', strtotime($userData['due_date']));
                            } else {
                                echo 'Not set';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div>
                        <div style="color: var(--text-secondary); font-size: var(--font-sm); margin-bottom: 0.5rem; font-weight: var(--font-medium);">Days Remaining</div>
                        <div style="font-size: var(--font-lg); font-weight: var(--font-semibold); color: var(--text-primary);">
                            <?php 
                            if (isset($userData['due_date']) && $userData['due_date']) {
                                $due = new DateTime($userData['due_date']);
                                $now = new DateTime();
                                $diff = $now->diff($due);
                                echo $diff->days . ' days';
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Health Records -->
            <div class="section-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 class="section-title" style="margin-bottom: 0;">Health Records</h2>
                    <a href="download_health_report.php" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-download"></i> Download Report
                    </a>
                </div>
                
                <?php if (count($healthRecords) > 0): ?>
                    <?php foreach($healthRecords as $record): ?>
                    <div class="record-item">
                        <div class="record-header">
                            <h4>Health Record</h4>
                            <span class="record-date">
                                <?php echo date('M d, Y h:i A', strtotime($record['recorded_at'])); ?>
                            </span>
                        </div>
                        <div class="record-details">
                            <?php if ($record['blood_pressure']): ?>
                            <div class="record-detail">
                                <span class="record-detail-label">Blood Pressure</span>
                                <span class="record-detail-value"><?php echo htmlspecialchars($record['blood_pressure']); ?> mmHg</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($record['hemoglobin']): ?>
                            <div class="record-detail">
                                <span class="record-detail-label">Hemoglobin</span>
                                <span class="record-detail-value"><?php echo htmlspecialchars($record['hemoglobin']); ?> g/dL</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($record['pulse_rate']): ?>
                            <div class="record-detail">
                                <span class="record-detail-label">Pulse Rate</span>
                                <span class="record-detail-value"><?php echo htmlspecialchars($record['pulse_rate']); ?> bpm</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($record['weight']): ?>
                            <div class="record-detail">
                                <span class="record-detail-label">Weight</span>
                                <span class="record-detail-value"><?php echo htmlspecialchars($record['weight']); ?> kg</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($record['temperature']): ?>
                            <div class="record-detail">
                                <span class="record-detail-label">Temperature</span>
                                <span class="record-detail-value"><?php echo htmlspecialchars($record['temperature']); ?> °C</span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($record['notes']): ?>
                            <div class="record-detail" style="grid-column: 1 / -1;">
                                <span class="record-detail-label">Notes</span>
                                <span class="record-detail-value"><?php echo htmlspecialchars($record['notes']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <i class="fas fa-file-medical" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3; color: var(--text-muted);"></i>
                        <h3 style="font-size: var(--font-xl); font-weight: var(--font-semibold); color: var(--text-primary); margin-bottom: 0.5rem;">No Health Records Yet</h3>
                        <p style="margin: 0;">Your health records will appear here once added by your doctor</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <script>
    function toggleMobileMenu() {
        document.querySelector('.sidebar').classList.toggle('mobile-open');
    }

    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(event.target) && 
            !toggle.contains(event.target) &&
            sidebar.classList.contains('mobile-open')) {
            sidebar.classList.remove('mobile-open');
        }
    });
    </script>

    <!-- Aarunya Chatbot -->
    <?php include 'includes/chatbot.php'; ?>
</body>
</html>

