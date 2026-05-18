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

// Get user's appointments
$stmt = $db->prepare("
    SELECT a.*, d.name as doctor_name, d.specialization, d.email as contact
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$user['id']]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .appointment-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(196, 167, 255, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s ease;
        }
        
        .appointment-card:hover {
            transform: translateY(-0.25rem);
            box-shadow: 0 8px 32px rgba(196, 167, 255, 0.3);
            border-color: rgba(244, 114, 182, 0.5);
        }
        
        .appointment-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .appointment-doctor {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .doctor-avatar {
            width: 3rem;
            height: 3rem;
            background: linear-gradient(135deg, #C4A7FF 0%, #C4A7FF 100%);
            color: #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .doctor-info h3 {
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }
        
        .doctor-specialization {
            color: #546e7a;
            font-size: 0.875rem;
        }
        
        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 0.75rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #546e7a;
            font-size: 0.875rem;
        }
        
        .detail-item i {
            color: #C4A7FF;
            width: 1.25rem;
        }
    </style>
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
                    <h1 class="page-title">My Appointments</h1>
                    <p class="page-subtitle">View and manage your appointments</p>
                </div>
                <div class="header-actions">
                    <a href="doctors.php" class="action-btn">
                        <i class="fas fa-plus"></i> Book New Appointment
                    </a>
                </div>
            </div>

            <?php if (count($appointments) > 0): ?>
                <?php foreach($appointments as $apt): ?>
                <div class="appointment-card">
                    <div class="appointment-header">
                        <div class="appointment-doctor">
                            <div class="doctor-avatar">
                                <?php echo strtoupper(substr($apt['doctor_name'], 0, 2)); ?>
                            </div>
                            <div class="doctor-info">
                                <h3><?php echo htmlspecialchars($apt['doctor_name']); ?></h3>
                                <div class="doctor-specialization">
                                    <?php echo htmlspecialchars($apt['specialization']); ?>
                                </div>
                            </div>
                        </div>
                        <span class="badge badge-<?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                            echo $apt['status'] == 'completed' ? 'success' : 
                                ($apt['status'] == 'cancelled' ? 'danger' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($apt['status']); ?>
                        </span>
                    </div>
                    
                    <div class="appointment-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <span><?php echo date('l, F d, Y', strtotime($apt['appointment_date'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($apt['contact']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="section-card" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-calendar-times" style="font-size: 4rem; color: #78909c; margin-bottom: 1rem;"></i>
                    <h3>No Appointments Yet</h3>
                    <p style="color: #546e7a; margin-bottom: 1.5rem;">Book your first appointment with our specialists</p>
                    <a href="doctors.php" class="action-btn">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
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
</body>
</html>

