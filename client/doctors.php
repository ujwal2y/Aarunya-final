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

// Check if availability columns exist, fetch real values
$hasAvailCol = $db->query("SHOW COLUMNS FROM doctors LIKE 'is_available'")->rowCount() > 0;
if ($hasAvailCol) {
    $availSelect = "COALESCE(d.is_available, 1) as is_available, COALESCE(d.availability_note, '') as availability_note";
} else {
    $availSelect = "1 as is_available, '' as availability_note";
}

// Get all active doctors with real availability status
$stmt = $db->query("
    SELECT d.*,
           CASE WHEN d.is_verified = 1 THEN 'verified' ELSE 'pending' END as verification_status,
           $availSelect
    FROM doctors d
    WHERE d.status = 'approved'
    AND d.is_active = 1
    ORDER BY is_available DESC, d.is_verified DESC, d.name ASC
");
$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors - Aarunya</title>
    <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }
        
        .doctor-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(196, 167, 255, 0.2);
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }
        
        .doctor-card:hover {
            transform: translateY(-0.5rem);
            box-shadow: 0 12px 40px rgba(196, 167, 255, 0.3);
            border-color: rgba(244, 114, 182, 0.5);
        }
        
        .doctor-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .doctor-avatar {
            width: 4rem;
            height: 4rem;
            background: linear-gradient(135deg, #C4A7FF 0%, #C4A7FF 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: #000;
            flex-shrink: 0;
        }
        
        .doctor-info h3 {
            font-size: 1.125rem;
            margin-bottom: 0.25rem;
            color: #ffffff;
        }
        
        .doctor-specialization {
            color: #C4A7FF;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .doctor-details {
            margin-bottom: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: #546e7a;
            font-size: 0.875rem;
        }
        
        .detail-item i {
            color: #C4A7FF;
            width: 1.25rem;
            text-align: center;
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
                    <h1 class="page-title">Our Doctors</h1>
                    <p class="page-subtitle">Connect with experienced maternal care specialists</p>
                </div>
                <div class="header-actions">
                    <div class="user-badge">
                        <div class="user-avatar-small">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                </div>
            </div>

            <div class="doctors-grid">
                <?php foreach($doctors as $doctor): ?>
                <div class="doctor-card">
                    <div class="doctor-header">
                        <div class="doctor-avatar">
                            <?php if (!empty($doctor['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($doctor['profile_image']); ?>" 
                                     alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <?php echo strtoupper(substr($doctor['name'], 4, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="doctor-info">
                            <h3>
                                <?php echo htmlspecialchars($doctor['name']); ?>
                                <?php if ($doctor['is_verified']): ?>
                                <i class="fas fa-check-circle" style="color: #10b981; font-size: 1rem; margin-left: 0.5rem;" title="Verified Doctor"></i>
                                <?php endif; ?>
                                <?php if (isset($doctor['is_available']) && !$doctor['is_available']): ?>
                                <span style="display: inline-block; background: #ef4444; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; margin-left: 0.5rem; font-weight: 600;">UNAVAILABLE</span>
                                <?php elseif (isset($doctor['is_available']) && $doctor['is_available']): ?>
                                <span style="display: inline-block; background: #10b981; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.25rem; margin-left: 0.5rem; font-weight: 600;">AVAILABLE</span>
                                <?php endif; ?>
                            </h3>
                            <div class="doctor-specialization">
                                <?php echo htmlspecialchars($doctor['specialization']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($doctor['is_available']) && !$doctor['is_available'] && !empty($doctor['availability_note'])): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <i class="fas fa-info-circle" style="color: #ef4444;"></i>
                            <strong style="color: #ef4444; font-size: 0.875rem;">Currently Unavailable</strong>
                        </div>
                        <p style="color: #fca5a5; font-size: 0.875rem; margin: 0;">
                            <?php echo htmlspecialchars($doctor['availability_note']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="doctor-details">
                        <?php if (!empty($doctor['hospital_affiliation'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-hospital"></i>
                            <span><?php echo htmlspecialchars($doctor['hospital_affiliation']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($doctor['qualification'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-graduation-cap"></i>
                            <span><?php echo htmlspecialchars($doctor['qualification']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <i class="fas fa-briefcase"></i>
                            <span><?php echo htmlspecialchars($doctor['experience']); ?> years experience</span>
                        </div>
                        <?php if (!empty($doctor['medical_council_registration'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-id-card"></i>
                            <span>Reg: <?php echo htmlspecialchars($doctor['medical_council_registration']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($doctor['consultation_fee'])): ?>
                        <div class="detail-item">
                            <i class="fas fa-rupee-sign"></i>
                            <span>â‚¹<?php echo number_format($doctor['consultation_fee'], 0); ?> consultation fee</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($doctor['bio'])): ?>
                    <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem;">
                        <p style="color: #546e7a; font-size: 0.875rem; margin: 0; line-height: 1.5;">
                            <?php echo htmlspecialchars($doctor['bio']); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($doctor['is_available']): ?>
                    <a href="book-appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="action-btn" style="width: 100%; justify-content: center;">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                    <?php else: ?>
                    <button class="action-btn" style="width: 100%; justify-content: center; background: #78909c; cursor: not-allowed; opacity: 0.6;" disabled title="Doctor is currently unavailable">
                        <i class="fas fa-ban"></i> Currently Unavailable
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($doctors)): ?>
                <div class="section-card" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <i class="fas fa-user-doctor" style="font-size: 4rem; color: #78909c; margin-bottom: 1rem;"></i>
                    <h3>No Doctors Available</h3>
                    <p style="color: #546e7a;">Please check back later</p>
                </div>
                <?php endif; ?>
            </div>
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


