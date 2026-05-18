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
requireLogin('login.php');
requireRole(ROLE_USER, 'login.php');

// Include appointment notification popup
include 'includes/appointment_notification.php';

$user = getCurrentUser();
$db = getDB();

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Get user's appointments
$stmt = $db->prepare("
    SELECT a.*, d.name as doctor_name, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date DESC 
    LIMIT 5
");
$stmt->execute([$user['id']]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Content Header -->
            <div class="content-header">
                <div>
                    <h1 class="page-title">Maternal Care Dashboard</h1>
                    <p class="page-subtitle">Stay on your maternal care monitoring stats</p>
                </div>
                <div class="header-actions">
                    <div class="user-badge">
                        <div class="user-avatar-small">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($userData['name']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Interactive Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <!-- Pregnancy Week Card -->
                <div class="interactive-metric-card" 
                     data-metric="pregnancy-week"
                     onclick="openMetricReport('pregnancy-week')"
                     style="background: linear-gradient(135deg, rgba(244, 114, 182, 0.2) 0%, rgba(196, 167, 255, 0.1) 100%); border: 1px solid rgba(244, 114, 182, 0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                    <div class="card-glow-effect"></div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #C4A7FF; margin-bottom: 8px;"><?php echo $userData['pregnancy_week'] ?? 0; ?></div>
                    <div style="color: #546e7a; font-size: 0.9rem; margin-bottom: 4px;">Pregnancy Week</div>
                    <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-heart"></i> Growing Strong</div>
                    <div class="card-click-indicator">Click for detailed timeline</div>
                </div>
                
                <!-- Total Appointments Card -->
                <div class="interactive-metric-card" 
                     data-metric="total-appointments"
                     onclick="openMetricReport('total-appointments')"
                     style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.1) 100%); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                    <div class="card-glow-effect"></div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #10b981; margin-bottom: 8px;"><?php echo count($appointments); ?></div>
                    <div style="color: #546e7a; font-size: 0.9rem; margin-bottom: 4px;">Total Appointments</div>
                    <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-up"></i> Stay Connected</div>
                    <div class="card-click-indicator">Click for appointment analytics</div>
                </div>
                
                <!-- Days to Due Date Card -->
                <div class="interactive-metric-card" 
                     data-metric="days-to-due"
                     onclick="openMetricReport('days-to-due')"
                     style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.1) 100%); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                    <div class="card-glow-effect"></div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #3b82f6; margin-bottom: 8px;">
                        <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                        if ($userData['due_date']) {
                            $due = new DateTime($userData['due_date']);
                            $now = new DateTime();
                            $diff = $now->diff($due);
                            echo $diff->days;
                        } else {
                            echo '-';
                        }
                        ?>
                    </div>
                    <div style="color: #546e7a; font-size: 0.9rem; margin-bottom: 4px;">Days to Due Date</div>
                    <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-baby"></i> Almost There</div>
                    <div class="card-click-indicator">Click for countdown details</div>
                </div>
                
                <!-- Current Trimester Card -->
                <div class="interactive-metric-card" 
                     data-metric="current-trimester"
                     onclick="openMetricReport('current-trimester')"
                     style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.1) 100%); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                    <div class="card-glow-effect"></div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b; margin-bottom: 8px;">
                        <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                        $trimester = 1;
                        if ($userData['pregnancy_week']) {
                            if ($userData['pregnancy_week'] > 27) $trimester = 3;
                            elseif ($userData['pregnancy_week'] > 13) $trimester = 2;
                        }
                        echo $trimester;
                        ?>
                    </div>
                    <div style="color: #546e7a; font-size: 0.9rem; margin-bottom: 4px;">Current Trimester</div>
                    <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-calendar-alt"></i> On Track</div>
                    <div class="card-click-indicator">Click for trimester guide</div>
                </div>
                
                <!-- Next Appointment Card -->
                <div class="interactive-metric-card" 
                     data-metric="next-appointment"
                     onclick="openMetricReport('next-appointment')"
                     style="background: linear-gradient(135deg, rgba(139, 69, 19, 0.2) 0%, rgba(120, 53, 15, 0.1) 100%); border: 1px solid rgba(139, 69, 19, 0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                    <div class="card-glow-effect"></div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #8b4513; margin-bottom: 8px;">
                        <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                        $nextAppointment = 0;
                        foreach($appointments as $apt) {
                            if (strtotime($apt['appointment_date']) > time()) {
                                $nextAppointment = ceil((strtotime($apt['appointment_date']) - time()) / (60*60*24));
                                break;
                            }
                        }
                        echo $nextAppointment > 0 ? $nextAppointment : '-';
                        ?>
                    </div>
                    <div style="color: #546e7a; font-size: 0.9rem; margin-bottom: 4px;">Next Appointment</div>
                    <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-clock"></i> <?php echo $nextAppointment > 0 ? 'Days Away' : 'Schedule Soon'; ?></div>
                    <div class="card-click-indicator">Click for appointment details</div>
                </div>
                
                <!-- Health Score Card -->
                <div class="interactive-metric-card" 
                     data-metric="health-score"
                     onclick="openMetricReport('health-score')"
                     style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.2) 0%, rgba(147, 51, 234, 0.1) 100%); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden;">
                    <div class="card-glow-effect"></div>
                    <div style="font-size: 2.5rem; font-weight: 800; color: #a855f7; margin-bottom: 8px;">98.5%</div>
                    <div style="color: #546e7a; font-size: 0.9rem; margin-bottom: 4px;">Health Score</div>
                    <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-star"></i> Excellent</div>
                    <div class="card-click-indicator">Click for health analytics</div>
                </div>
            </div>

            <!-- Pregnancy Journey Timeline -->
            <div class="section-card" style="margin-bottom: 30px;">
                <div class="section-header">
                    <h2 class="section-title">🌸 Your Pregnancy Journey</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <!-- Current Week Info -->
                    <div style="background: linear-gradient(135deg, rgba(244, 114, 182, 0.1) 0%, rgba(196, 167, 255, 0.05) 100%); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 12px; padding: 20px;">
                        <h3 style="color: #C4A7FF; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-calendar-week"></i> Week <?php echo $userData['pregnancy_week'] ?? 0; ?> Development
                        </h3>
                        <div style="color: #546e7a; line-height: 1.6;">
                            <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                            $week = $userData['pregnancy_week'] ?? 0;
                            $developments = [
                                1 => "Your baby is the size of a poppy seed. Neural tube development begins.",
                                8 => "Baby is now the size of a raspberry. Major organs are forming.",
                                12 => "Baby is the size of a lime. Reflexes are developing.",
                                16 => "Baby is the size of an avocado. You might feel first movements.",
                                20 => "Baby is the size of a banana. Anatomy scan time!",
                                24 => "Baby is the size of an ear of corn. Hearing is developing.",
                                28 => "Baby is the size of an eggplant. Brain development accelerates.",
                                32 => "Baby is the size of a squash. Bones are hardening.",
                                36 => "Baby is the size of a romaine lettuce. Lungs are maturing.",
                                40 => "Baby is full-term! Ready to meet the world."
                            ];
                            
                            $currentDev = "Your pregnancy journey is beginning!";
                            foreach($developments as $w => $dev) {
                                if ($week >= $w) $currentDev = $dev;
                            }
                            echo $currentDev;
                            ?>
                        </div>
                    </div>
                    
                    <!-- Trimester Milestones -->
                    <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 12px; padding: 20px;">
                        <h3 style="color: #10b981; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-trophy"></i> Trimester Milestones
                        </h3>
                        <div style="color: #546e7a;">
                            <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                            $week = $userData['pregnancy_week'] ?? 0;
                            if ($week <= 13) {
                                echo "🌱 <strong>First Trimester</strong><br>";
                                echo "• Morning sickness may occur<br>";
                                echo "• Take folic acid supplements<br>";
                                echo "• First prenatal appointment";
                            } elseif ($week <= 27) {
                                echo "🌸 <strong>Second Trimester</strong><br>";
                                echo "• Energy levels increase<br>";
                                echo "• Baby bump becomes visible<br>";
                                echo "• Anatomy scan (18-22 weeks)";
                            } else {
                                echo "🌺 <strong>Third Trimester</strong><br>";
                                echo "• Prepare for delivery<br>";
                                echo "• Baby shower time<br>";
                                echo "• Pack hospital bag";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Tips & Reminders -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <!-- Today's Tip -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">💡 Today's Pregnancy Tip</h2>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 15px;">
                        <p style="color: #546e7a; margin: 0; line-height: 1.6;">
                            <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                            $tips = [
                                "Stay hydrated! Aim for 8-10 glasses of water daily to support increased blood volume.",
                                "Take short walks daily to improve circulation and reduce swelling.",
                                "Practice deep breathing exercises to reduce stress and prepare for labor.",
                                "Eat small, frequent meals to help with nausea and maintain energy levels.",
                                "Get plenty of rest - your body is working hard to grow your baby!",
                                "Talk to your baby - they can hear your voice and find it comforting.",
                                "Keep a pregnancy journal to track symptoms and memorable moments."
                            ];
                            echo $tips[date('w')]; // Different tip for each day of week
                            ?>
                        </p>
                    </div>
                </div>

                <!-- Nutrition Focus -->
                <div class="section-card">
                    <div class="section-header">
                        <h2 class="section-title">🥗 Nutrition Focus</h2>
                    </div>
                    <div style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 8px; padding: 15px;">
                        <div style="color: #546e7a; line-height: 1.6;">
                            <strong style="color: #f59e0b;">Essential Nutrients:</strong><br>
                            • <strong>Folic Acid:</strong> 400-800 mcg daily<br>
                            • <strong>Iron:</strong> 27 mg daily<br>
                            • <strong>Calcium:</strong> 1000 mg daily<br>
                            • <strong>DHA:</strong> 200-300 mg daily
                        </div>
                    </div>
                </div>
            </div>

            <!-- Symptom Tracker -->
            <div class="section-card" style="margin-bottom: 30px;">
                <div class="section-header">
                    <h2 class="section-title">📊 Weekly Symptom Tracker</h2>
                    <button class="btn btn-secondary btn-sm" onclick="toggleSymptomTracker()">
                        <i class="fas fa-plus"></i> Log Symptoms
                    </button>
                </div>
                
                <div id="symptomTracker" style="display: none; margin-top: 20px;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; padding: 15px;">
                            <label style="color: #546e7a; font-size: 0.9rem;">Nausea Level (1-10)</label>
                            <input type="range" min="0" max="10" value="0" style="width: 100%; margin-top: 8px;">
                        </div>
                        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; padding: 15px;">
                            <label style="color: #546e7a; font-size: 0.9rem;">Energy Level (1-10)</label>
                            <input type="range" min="0" max="10" value="5" style="width: 100%; margin-top: 8px;">
                        </div>
                        <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; padding: 15px;">
                            <label style="color: #546e7a; font-size: 0.9rem;">Sleep Quality (1-10)</label>
                            <input type="range" min="0" max="10" value="7" style="width: 100%; margin-top: 8px;">
                        </div>
                    </div>
                    <button class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-save"></i> Save Today's Log
                    </button>
                </div>
                
                <!-- Recent Symptoms Summary -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">
                    <div style="text-align: center; padding: 15px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">Good</div>
                        <div style="color: #546e7a; font-size: 0.8rem;">Overall Feeling</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: #3b82f6;">7.5</div>
                        <div style="color: #546e7a; font-size: 0.8rem;">Avg Energy</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: #a855f7;">8.2</div>
                        <div style="color: #546e7a; font-size: 0.8rem;">Sleep Score</div>
                    </div>
                    <div style="text-align: center; padding: 15px; background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: #C4A7FF;">2.1</div>
                        <div style="color: #546e7a; font-size: 0.8rem;">Avg Nausea</div>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <?php if (count($appointments) > 0): ?>
            <div class="section-card">
                <div class="section-header">
                    <h2 class="section-title">Recent Appointments</h2>
                    <a href="appointments.php" class="btn btn-secondary btn-sm">View All</a>
                </div>
                
                <?php foreach($appointments as $apt): ?>
                <div class="list-item">
                    <div class="list-item-info">
                        <h4><?php echo htmlspecialchars($apt['doctor_name']); ?></h4>
                        <p>
                            <i class="fas fa-stethoscope"></i> <?php echo htmlspecialchars($apt['specialization']); ?> •
                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?> •
                            <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                        </p>
                    </div>
                    <span class="badge badge-<?php echo $apt['status'] == 'completed' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($apt['status']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Aarunya Chatbot -->
    <?php include 'includes/chatbot.php'; ?>

    <!-- Metric Report Modal -->
    <div id="metricReportModal" class="metric-modal">
        <div class="modal-overlay" onclick="closeMetricReport()"></div>
        <div class="modal-container">
            <div class="modal-header">
                <div class="modal-title-section">
                    <div class="modal-icon" id="modalIcon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div>
                        <h2 id="modalTitle">Pregnancy Week</h2>
                        <p id="modalSubtitle">Detailed pregnancy progress analytics</p>
                    </div>
                </div>
                <div class="modal-controls">
                    <div class="time-filter-dropdown">
                        <select id="timeFilter" onchange="updateReportPeriod()">
                            <option value="weekly">Weekly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <button class="modal-close-btn" onclick="closeMetricReport()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="modal-body">
                <!-- Current Value Highlight -->
                <div class="current-value-section">
                    <div class="current-value" id="currentValue">20</div>
                    <div class="value-label" id="valueLabel">Weeks</div>
                    <div class="trend-badge" id="trendBadge">
                        <i class="fas fa-arrow-up"></i> Growing Strong
                    </div>
                </div>
                
                <!-- Dynamic Content Area -->
                <div id="reportContent" class="report-content">
                    <!-- Content will be dynamically loaded here -->
                </div>
                
                <!-- Chart Section -->
                <div class="chart-section">
                    <div class="chart-header">
                        <h3 id="chartTitle">Progress Timeline</h3>
                        <div class="chart-legend" id="chartLegend"></div>
                    </div>
                    <div class="chart-container">
                        <canvas id="reportChart"></canvas>
                    </div>
                </div>
                
                <!-- AI Insights Section -->
                <div class="insights-section">
                    <div class="insights-header">
                        <h3><i class="fas fa-brain"></i> AI Health Insights</h3>
                    </div>
                    <div id="aiInsights" class="ai-insights-content">
                        <!-- AI insights will be loaded here -->
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="modal-actions">
                    <button class="btn-secondary" onclick="exportReport()">
                        <i class="fas fa-download"></i> Export PDF
                    </button>
                    <button class="btn-secondary" onclick="shareReport()">
                        <i class="fas fa-share"></i> Share Report
                    </button>
                    <button class="btn-primary" onclick="scheduleReminder()">
                        <i class="fas fa-bell"></i> Set Reminder
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Interactive Card Styles */
        .interactive-metric-card {
            position: relative;
            overflow: hidden;
        }
        
        .interactive-metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .interactive-metric-card:hover {
            transform: scale(1.05) translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px rgba(244, 114, 182, 0.4);
            border-color: rgba(244, 114, 182, 0.6);
        }
        
        .interactive-metric-card:hover::before {
            opacity: 1;
        }
        
        .interactive-metric-card:active {
            transform: scale(1.02) translateY(-4px);
            transition: transform 0.1s ease;
        }
        
        .card-glow-effect {
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(244, 114, 182, 0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .interactive-metric-card:hover .card-glow-effect {
            opacity: 1;
        }
        
        .card-click-indicator {
            position: absolute;
            bottom: 8px;
            left: 0;
            right: 0;
            font-size: 10px;
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .interactive-metric-card:hover .card-click-indicator {
            opacity: 1;
        }
        
        /* Modal Styles */
        .metric-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .metric-modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .modal-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
            border: 1px solid rgba(196, 167, 255, 0.3);
            border-radius: 24px;
            width: 95%;
            max-width: 1200px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        .metric-modal.active .modal-container {
            transform: scale(1) translateY(0);
        }
        
        .modal-header {
            padding: 32px;
            border-bottom: 1px solid rgba(196, 167, 255, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, rgba(244, 114, 182, 0.1), rgba(196, 167, 255, 0.1));
        }
        
        .modal-title-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            background: rgba(244, 114, 182, 0.15);
            color: #C4A7FF;
        }
        
        .modal-title-section h2 {
            color: #263238;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        
        .modal-title-section p {
            color: #546e7a;
            font-size: 14px;
            margin: 4px 0 0 0;
        }
        
        .modal-controls {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .time-filter-dropdown select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 8px 16px;
            color: #263238;
            font-size: 14px;
        }
        
        .modal-close-btn {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #263238;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 18px;
        }
        
        .modal-close-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
            color: #ef4444;
            transform: rotate(90deg);
        }
        
        .modal-body {
            padding: 32px;
        }
        
        .current-value-section {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .current-value {
            font-size: 4rem;
            font-weight: 900;
            color: #C4A7FF;
            margin-bottom: 8px;
        }
        
        .value-label {
            font-size: 18px;
            color: #546e7a;
            margin-bottom: 12px;
        }
        
        .trend-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .report-content {
            margin-bottom: 40px;
        }
        
        .chart-section {
            margin-bottom: 40px;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .chart-header h3 {
            color: #263238;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }
        
        .chart-container {
            height: 400px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }
        
        .insights-section {
            margin-bottom: 40px;
        }
        
        .insights-header h3 {
            color: #263238;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .ai-insights-content {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 24px;
        }
        
        .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-secondary, .btn-primary {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: none;
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #546e7a;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #263238;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #C4A7FF, #C4A7FF);
            color: #ffffff;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(244, 114, 182, 0.4);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .modal-container {
                width: 98%;
                margin: 10px;
                max-height: 95vh;
            }
            
            .modal-header {
                padding: 20px;
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .modal-body {
                padding: 20px;
            }
            
            .current-value {
                font-size: 3rem;
            }
            
            .modal-actions {
                flex-direction: column;
            }
        }

        /* Report Content Styles */
        .info-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .info-card h4 {
            color: #263238;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .development-stage p {
            color: #546e7a;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .milestone-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .milestone {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            color: #546e7a;
        }

        .milestone.completed {
            color: #10b981;
        }

        .milestone.current {
            color: #f59e0b;
        }

        .preparation-checklist {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .checklist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #546e7a;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #546e7a;
            font-size: 14px;
        }

        .appointment-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
            margin-top: 16px;
        }

        .appointment-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .appointment-date {
            font-weight: 600;
            color: #C4A7FF;
            min-width: 100px;
        }

        .appointment-details {
            color: #546e7a;
            line-height: 1.5;
        }

        .countdown-visual {
            display: grid;
            gap: 24px;
        }

        .progress-bar-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            height: 20px;
            margin: 16px 0;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, #C4A7FF, #C4A7FF);
            height: 100%;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: width 0.3s ease;
        }

        .progress-text {
            color: #263238;
            font-size: 12px;
            font-weight: 600;
        }

        .trimester-markers {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
        }

        .marker {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            color: #546e7a;
        }

        .marker.completed {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .marker.current {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .timeline-items {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
        }

        .timeline-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            color: #546e7a;
        }

        .timeline-item.completed {
            color: #10b981;
        }

        .timeline-item.current {
            color: #f59e0b;
        }

        .trimester-overview {
            display: grid;
            gap: 24px;
        }

        .symptoms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .symptom-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .symptom-item.positive {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .symptom-item.neutral {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .nutrition-exercise {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 24px;
        }

        .nutrition-section, .exercise-section {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
        }

        .nutrition-section ul, .exercise-section ul {
            margin-top: 12px;
            padding-left: 20px;
        }

        .nutrition-section li, .exercise-section li {
            color: #546e7a;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .doctor-card {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
        }

        .doctor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #C4A7FF, #C4A7FF);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #263238;
            font-weight: 700;
            font-size: 20px;
        }

        .doctor-details strong {
            color: #263238;
            font-size: 18px;
        }

        .doctor-details p {
            color: #546e7a;
            margin: 4px 0;
            font-size: 14px;
        }

        .appointment-schedule {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .schedule-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #546e7a;
        }

        .instructions-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 16px;
        }

        .instruction-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            color: #546e7a;
        }

        .health-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-category {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .metric-score {
            font-size: 2rem;
            font-weight: 700;
            margin: 12px 0;
        }

        .metric-score.excellent {
            color: #10b981;
        }

        .metric-score.good {
            color: #f59e0b;
        }

        .metric-details p {
            color: #546e7a;
            font-size: 12px;
            margin: 4px 0;
        }

        .risk-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 16px;
        }

        .risk-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .risk-badge.low {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .risk-badge.normal {
            background: rgba(59, 130, 246, 0.2);
            color: #3b82f6;
        }

        .risk-badge.excellent {
            background: rgba(168, 85, 247, 0.2);
            color: #a855f7;
        }

        .ai-insight-item {
            display: flex;
            gap: 12px;
            padding: 16px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border-left: 3px solid #C4A7FF;
        }

        .ai-insight-item i {
            color: #C4A7FF;
            margin-top: 2px;
        }

        .ai-insight-item p {
            color: #546e7a;
            margin: 0;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .nutrition-exercise {
                grid-template-columns: 1fr;
            }
            
            .health-metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .appointment-item {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>

    <script>
        // Global variables for metric reports
        let currentMetric = null;
        let reportChart = null;
        let currentPeriod = 'monthly';

        // Metric configurations with healthcare data
        const metricConfigs = {
            'pregnancy-week': {
                title: 'Pregnancy Week Progress',
                subtitle: 'Detailed pregnancy timeline and fetal development',
                icon: 'fas fa-heart',
                color: '#C4A7FF',
                currentValue: <?php echo $userData['pregnancy_week'] ?? 0; ?>,
                unit: 'Weeks',
                trend: 'Growing Strong',
                chartData: {
                    weekly: {
                        labels: ['Week 16', 'Week 17', 'Week 18', 'Week 19', 'Week 20', 'Week 21', 'Week 22'],
                        data: [16, 17, 18, 19, 20, 21, 22],
                        milestones: ['Heart chambers formed', 'Gender visible', 'Hearing develops', 'Movements felt', 'Anatomy scan', 'Taste buds form', 'Brain growth spurt']
                    },
                    monthly: {
                        labels: ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5'],
                        data: [4, 8, 12, 16, 20],
                        milestones: ['Implantation', 'Organ formation', 'First trimester end', 'Gender determination', 'Halfway point']
                    }
                },
                content: `
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px;">
                        <div class="info-card">
                            <h4><i class="fas fa-baby"></i> Fetal Development</h4>
                            <div class="development-stage">
                                <p><strong>Size:</strong> About the size of a banana (6.5 inches)</p>
                                <p><strong>Weight:</strong> Approximately 10.2 ounces</p>
                                <p><strong>Key Development:</strong> Baby can hear sounds and may respond to music</p>
                            </div>
                        </div>
                        <div class="info-card">
                            <h4><i class="fas fa-calendar-check"></i> Milestones Reached</h4>
                            <div class="milestone-list">
                                <div class="milestone completed"><i class="fas fa-check"></i> Neural tube closure</div>
                                <div class="milestone completed"><i class="fas fa-check"></i> Heart beating</div>
                                <div class="milestone completed"><i class="fas fa-check"></i> Limbs formed</div>
                                <div class="milestone current"><i class="fas fa-clock"></i> Hearing development</div>
                            </div>
                        </div>
                    </div>
                    <div class="preparation-checklist">
                        <h4><i class="fas fa-list-check"></i> This Week's Focus</h4>
                        <div class="checklist-grid">
                            <div class="checklist-item">
                                <input type="checkbox" checked> Schedule anatomy scan
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox"> Start prenatal yoga
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox"> Research childbirth classes
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" checked> Take prenatal vitamins
                            </div>
                        </div>
                    </div>
                `,
                aiInsights: [
                    "Your baby's hearing is developing rapidly this week. Consider playing soft music or reading aloud.",
                    "This is an ideal time for the anatomy scan to check baby's development and health.",
                    "Your energy levels should be at their peak during this second trimester period.",
                    "Start thinking about childbirth education classes - many begin around week 24-28."
                ]
            },
            'total-appointments': {
                title: 'Appointment Analytics',
                subtitle: 'Comprehensive appointment tracking and trends',
                icon: 'fas fa-calendar-check',
                color: '#10b981',
                currentValue: <?php echo count($appointments); ?>,
                unit: 'Appointments',
                trend: 'Stay Connected',
                chartData: {
                    weekly: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        data: [1, 0, 2, 1],
                        types: ['Routine', 'Emergency', 'Follow-up', 'Specialist']
                    },
                    monthly: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                        data: [2, 3, 1, 4, 2],
                        types: ['Routine', 'Ultrasound', 'Blood work', 'Consultation', 'Check-up']
                    }
                },
                content: `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div class="stat-card">
                            <div class="stat-value">95%</div>
                            <div class="stat-label">Attendance Rate</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">18 min</div>
                            <div class="stat-label">Avg Wait Time</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">4.8/5</div>
                            <div class="stat-label">Satisfaction</div>
                        </div>
                    </div>
                    <div class="upcoming-appointments">
                        <h4><i class="fas fa-clock"></i> Upcoming Appointments</h4>
                        <div class="appointment-list">
                            <div class="appointment-item">
                                <div class="appointment-date">Mar 15, 2024</div>
                                <div class="appointment-details">
                                    <strong>Dr. Sarah Johnson</strong> - Routine Checkup<br>
                                    <small>10:00 AM • Obstetrics Department</small>
                                </div>
                            </div>
                            <div class="appointment-item">
                                <div class="appointment-date">Mar 22, 2024</div>
                                <div class="appointment-details">
                                    <strong>Dr. Michael Chen</strong> - Ultrasound<br>
                                    <small>2:30 PM • Imaging Center</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                aiInsights: [
                    "Your appointment attendance rate is excellent at 95% - keep up the consistent care!",
                    "Consider scheduling your next appointment 2-3 weeks in advance for better availability.",
                    "Your average wait time is below the clinic average - great job with punctuality!",
                    "Based on your pregnancy stage, you'll need bi-weekly appointments starting next month."
                ]
            },
            'days-to-due': {
                title: 'Countdown to Delivery',
                subtitle: 'Due date tracking and delivery preparation',
                icon: 'fas fa-baby',
                color: '#3b82f6',
                currentValue: <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                    if ($userData['due_date']) {
                        $due = new DateTime($userData['due_date']);
                        $now = new DateTime();
                        $diff = $now->diff($due);
                        echo $diff->days;
                    } else {
                        echo 0;
                    }
                ?>,
                unit: 'Days',
                trend: 'Almost There',
                chartData: {
                    weekly: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        data: [140, 133, 126, 119],
                        milestones: ['Third trimester', 'Baby shower', 'Hospital bag', 'Final preparations']
                    },
                    monthly: {
                        labels: ['5 months', '4 months', '3 months', '2 months', '1 month'],
                        data: [150, 120, 90, 60, 30],
                        milestones: ['Nursery setup', 'Baby shower', 'Hospital tour', 'Birth plan', 'Ready to go!']
                    }
                },
                content: `
                    <div class="countdown-visual">
                        <div class="trimester-progress">
                            <h4><i class="fas fa-chart-line"></i> Pregnancy Progress</h4>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: 70%;">
                                    <span class="progress-text">70% Complete</span>
                                </div>
                            </div>
                            <div class="trimester-markers">
                                <div class="marker completed">1st Trimester</div>
                                <div class="marker completed">2nd Trimester</div>
                                <div class="marker current">3rd Trimester</div>
                            </div>
                        </div>
                        <div class="preparation-timeline">
                            <h4><i class="fas fa-tasks"></i> Delivery Preparation</h4>
                            <div class="timeline-items">
                                <div class="timeline-item completed">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Choose pediatrician</span>
                                </div>
                                <div class="timeline-item current">
                                    <i class="fas fa-clock"></i>
                                    <span>Pack hospital bag</span>
                                </div>
                                <div class="timeline-item pending">
                                    <i class="fas fa-circle"></i>
                                    <span>Install car seat</span>
                                </div>
                                <div class="timeline-item pending">
                                    <i class="fas fa-circle"></i>
                                    <span>Finalize birth plan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                aiInsights: [
                    "You're in the home stretch! Start preparing your hospital bag around week 35-36.",
                    "Consider taking a hospital tour to familiarize yourself with the maternity ward.",
                    "This is a great time to finalize your birth plan and discuss it with your doctor.",
                    "Make sure your car seat is properly installed before the due date approaches."
                ]
            },
            'current-trimester': {
                title: 'Trimester Guide',
                subtitle: 'Current trimester insights and recommendations',
                icon: 'fas fa-calendar-alt',
                color: '#f59e0b',
                currentValue: <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                    $trimester = 1;
                    if ($userData['pregnancy_week']) {
                        if ($userData['pregnancy_week'] > 27) $trimester = 3;
                        elseif ($userData['pregnancy_week'] > 13) $trimester = 2;
                    }
                    echo $trimester;
                ?>,
                unit: 'Trimester',
                trend: 'On Track',
                content: `
                    <div class="trimester-overview">
                        <div class="trimester-info">
                            <h4><i class="fas fa-info-circle"></i> Second Trimester Overview</h4>
                            <p>Often called the "golden period" of pregnancy, the second trimester typically brings increased energy and reduced nausea.</p>
                        </div>
                        <div class="symptoms-tracking">
                            <h4><i class="fas fa-heartbeat"></i> Common Symptoms</h4>
                            <div class="symptoms-grid">
                                <div class="symptom-item positive">
                                    <i class="fas fa-smile"></i>
                                    <span>Increased energy</span>
                                </div>
                                <div class="symptom-item positive">
                                    <i class="fas fa-heart"></i>
                                    <span>Reduced nausea</span>
                                </div>
                                <div class="symptom-item neutral">
                                    <i class="fas fa-expand"></i>
                                    <span>Growing belly</span>
                                </div>
                                <div class="symptom-item neutral">
                                    <i class="fas fa-bed"></i>
                                    <span>Back pain</span>
                                </div>
                            </div>
                        </div>
                        <div class="nutrition-exercise">
                            <div class="nutrition-section">
                                <h4><i class="fas fa-apple-alt"></i> Nutrition Focus</h4>
                                <ul>
                                    <li>Increase protein intake (75-100g daily)</li>
                                    <li>Continue folic acid and iron supplements</li>
                                    <li>Add calcium-rich foods</li>
                                    <li>Stay hydrated (8-10 glasses water)</li>
                                </ul>
                            </div>
                            <div class="exercise-section">
                                <h4><i class="fas fa-dumbbell"></i> Safe Exercises</h4>
                                <ul>
                                    <li>Prenatal yoga (20-30 minutes)</li>
                                    <li>Swimming or water aerobics</li>
                                    <li>Walking (30 minutes daily)</li>
                                    <li>Pelvic floor exercises</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `,
                aiInsights: [
                    "The second trimester is ideal for travel and major preparations - take advantage of your energy boost!",
                    "This is the perfect time to start or continue a safe exercise routine with your doctor's approval.",
                    "Your baby bump will become more noticeable - consider maternity photos around week 28-32.",
                    "Start researching and booking childbirth classes, as popular ones fill up quickly."
                ]
            },
            'next-appointment': {
                title: 'Next Appointment Details',
                subtitle: 'Upcoming appointment information and preparation',
                icon: 'fas fa-clock',
                color: '#8b4513',
                currentValue: <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                    $nextAppointment = 0;
                    foreach($appointments as $apt) {
                        if (strtotime($apt['appointment_date']) > time()) {
                            $nextAppointment = ceil((strtotime($apt['appointment_date']) - time()) / (60*60*24));
                            break;
                        }
                    }
                    echo $nextAppointment > 0 ? $nextAppointment : 0;
                ?>,
                unit: 'Days Away',
                trend: <?php echo $nextAppointment > 0 ? '"Days Away"' : '"Schedule Soon"'; ?>,
                content: `
                    <div class="appointment-details">
                        <div class="next-appointment-card">
                            <h4><i class="fas fa-calendar-check"></i> Upcoming Appointment</h4>
                            <div class="appointment-info">
                                <div class="doctor-card">
                                    <div class="doctor-avatar">DS</div>
                                    <div class="doctor-details">
                                        <strong>Dr. Sarah Johnson</strong>
                                        <p>Obstetrics & Gynecology</p>
                                        <p>15+ years experience</p>
                                    </div>
                                </div>
                                <div class="appointment-schedule">
                                    <div class="schedule-item">
                                        <i class="fas fa-calendar"></i>
                                        <span>March 15, 2024</span>
                                    </div>
                                    <div class="schedule-item">
                                        <i class="fas fa-clock"></i>
                                        <span>10:00 AM - 10:30 AM</span>
                                    </div>
                                    <div class="schedule-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span>Room 205, Obstetrics Wing</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="preparation-instructions">
                            <h4><i class="fas fa-clipboard-list"></i> Preparation Instructions</h4>
                            <div class="instructions-list">
                                <div class="instruction-item">
                                    <i class="fas fa-tint"></i>
                                    <span>Drink plenty of water before your visit</span>
                                </div>
                                <div class="instruction-item">
                                    <i class="fas fa-notes-medical"></i>
                                    <span>Bring your prenatal vitamins and any medications</span>
                                </div>
                                <div class="instruction-item">
                                    <i class="fas fa-question-circle"></i>
                                    <span>Prepare a list of questions or concerns</span>
                                </div>
                                <div class="instruction-item">
                                    <i class="fas fa-id-card"></i>
                                    <span>Bring insurance card and ID</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                aiInsights: [
                    "Arrive 15 minutes early to complete any necessary paperwork or updates.",
                    "This appointment will likely include routine measurements and fetal heart rate monitoring.",
                    "Don't hesitate to ask questions - your healthcare team is here to support you!",
                    "Consider bringing your partner or support person to important appointments."
                ]
            },
            'health-score': {
                title: 'Health Analytics Dashboard',
                subtitle: 'Comprehensive health tracking and wellness insights',
                icon: 'fas fa-heartbeat',
                color: '#a855f7',
                currentValue: '98.5',
                unit: '%',
                trend: 'Excellent',
                chartData: {
                    weekly: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        data: [96.2, 97.1, 98.0, 98.5],
                        metrics: ['Sleep', 'Nutrition', 'Activity', 'Wellness']
                    },
                    monthly: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
                        data: [94.5, 96.2, 97.8, 98.1, 98.5],
                        metrics: ['Overall Health', 'Prenatal Care', 'Lifestyle', 'Mental Health']
                    }
                },
                content: `
                    <div class="health-metrics-grid">
                        <div class="metric-category">
                            <h4><i class="fas fa-bed"></i> Sleep Quality</h4>
                            <div class="metric-score excellent">9.2/10</div>
                            <div class="metric-details">
                                <p>Average: 8.5 hours per night</p>
                                <p>Quality: Deep sleep 85%</p>
                            </div>
                        </div>
                        <div class="metric-category">
                            <h4><i class="fas fa-tint"></i> Hydration</h4>
                            <div class="metric-score good">8.8/10</div>
                            <div class="metric-details">
                                <p>Daily intake: 2.3L</p>
                                <p>Goal: 2.5L per day</p>
                            </div>
                        </div>
                        <div class="metric-category">
                            <h4><i class="fas fa-walking"></i> Activity Level</h4>
                            <div class="metric-score excellent">9.5/10</div>
                            <div class="metric-details">
                                <p>Daily steps: 8,500</p>
                                <p>Exercise: 4x per week</p>
                            </div>
                        </div>
                        <div class="metric-category">
                            <h4><i class="fas fa-apple-alt"></i> Nutrition</h4>
                            <div class="metric-score excellent">9.7/10</div>
                            <div class="metric-details">
                                <p>Balanced meals: 95%</p>
                                <p>Prenatal vitamins: Daily</p>
                            </div>
                        </div>
                    </div>
                    <div class="risk-indicators">
                        <h4><i class="fas fa-shield-alt"></i> Health Risk Assessment</h4>
                        <div class="risk-badges">
                            <div class="risk-badge low">
                                <i class="fas fa-check-circle"></i>
                                <span>Low Risk Pregnancy</span>
                            </div>
                            <div class="risk-badge normal">
                                <i class="fas fa-heart"></i>
                                <span>Normal Blood Pressure</span>
                            </div>
                            <div class="risk-badge excellent">
                                <i class="fas fa-star"></i>
                                <span>Excellent Nutrition</span>
                            </div>
                        </div>
                    </div>
                `,
                aiInsights: [
                    "Your health metrics are excellent! Continue your current wellness routine.",
                    "Consider increasing water intake slightly to reach the optimal 2.5L daily goal.",
                    "Your sleep quality is outstanding - this is crucial for both you and baby's health.",
                    "Your activity level is perfect for this stage of pregnancy. Keep up the great work!"
                ]
            }
        };

        // Open metric report modal
        function openMetricReport(metricType) {
            currentMetric = metricType;
            const config = metricConfigs[metricType];
            
            if (!config) return;
            
            // Update modal content
            document.getElementById('modalTitle').textContent = config.title;
            document.getElementById('modalSubtitle').textContent = config.subtitle;
            document.getElementById('modalIcon').innerHTML = `<i class="${config.icon}"></i>`;
            document.getElementById('modalIcon').style.color = config.color;
            
            // Update current value section
            document.getElementById('currentValue').textContent = config.currentValue;
            document.getElementById('currentValue').style.color = config.color;
            document.getElementById('valueLabel').textContent = config.unit;
            document.getElementById('trendBadge').innerHTML = `<i class="fas fa-arrow-up"></i> ${config.trend}`;
            
            // Update report content
            document.getElementById('reportContent').innerHTML = config.content;
            
            // Update AI insights
            const aiInsightsHtml = config.aiInsights.map(insight => 
                `<div class="ai-insight-item">
                    <i class="fas fa-lightbulb"></i>
                    <p>${insight}</p>
                </div>`
            ).join('');
            document.getElementById('aiInsights').innerHTML = aiInsightsHtml;
            
            // Create chart if data exists
            if (config.chartData) {
                createReportChart(config);
            }
            
            // Show modal
            document.getElementById('metricReportModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close metric report modal
        function closeMetricReport() {
            document.getElementById('metricReportModal').classList.remove('active');
            document.body.style.overflow = 'auto';
            
            // Destroy chart
            if (reportChart) {
                reportChart.destroy();
                reportChart = null;
            }
        }

        // Create report chart
        function createReportChart(config) {
            const ctx = document.getElementById('reportChart').getContext('2d');
            const chartData = config.chartData[currentPeriod];
            
            if (reportChart) {
                reportChart.destroy();
            }
            
            reportChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: config.title,
                        data: chartData.data,
                        borderColor: config.color,
                        backgroundColor: config.color + '20',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: config.color,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: config.color,
                            borderWidth: 1,
                            cornerRadius: 12,
                            padding: 12,
                            callbacks: {
                                afterBody: function(context) {
                                    const index = context[0].dataIndex;
                                    if (chartData.milestones && chartData.milestones[index]) {
                                        return 'Milestone: ' + chartData.milestones[index];
                                    }
                                    return '';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#546e7a'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(148, 163, 184, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#546e7a'
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        // Update report period
        function updateReportPeriod() {
            const newPeriod = document.getElementById('timeFilter').value;
            currentPeriod = newPeriod;
            
            if (currentMetric && metricConfigs[currentMetric].chartData) {
                createReportChart(metricConfigs[currentMetric]);
            }
        }

        // Modal action functions
        function exportReport() {
            alert('📊 Exporting your personalized health report...\n\nThis would generate a comprehensive PDF including:\n• Current health metrics\n• Pregnancy progress timeline\n• Appointment history\n• AI health insights\n• Personalized recommendations');
        }

        function shareReport() {
            alert('📤 Share Report\n\nThis would allow you to:\n• Share with your healthcare provider\n• Send to family members\n• Email to your partner\n• Save to cloud storage');
        }

        function scheduleReminder() {
            alert('🔔 Schedule Reminder\n\nThis would help you:\n• Set appointment reminders\n• Schedule medication alerts\n• Plan prenatal vitamin reminders\n• Set up weekly check-in notifications');
        }

        // Close modal on outside click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                closeMetricReport();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('metricReportModal').classList.contains('active')) {
                closeMetricReport();
            }
        });

        // Existing functions
        function toggleMobileMenu() {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
        }
        
        function toggleSymptomTracker() {
            const tracker = document.getElementById('symptomTracker');
            const button = event.target.closest('button');
            
            if (tracker.style.display === 'none' || tracker.style.display === '') {
                tracker.style.display = 'block';
                button.innerHTML = '<i class="fas fa-minus"></i> Hide Tracker';
            } else {
                tracker.style.display = 'none';
                button.innerHTML = '<i class="fas fa-plus"></i> Log Symptoms';
            }
        }
        
        // Close mobile menu when clicking outside
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
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>


