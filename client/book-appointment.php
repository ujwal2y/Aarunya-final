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

$success = '';
$error = '';
$doctorId = intval($_GET['doctor_id'] ?? 0);

// Get doctor details with availability status
if ($doctorId > 0) {
    $stmt = $db->prepare("
        SELECT * FROM doctors 
        WHERE id = ? 
        AND status = 'approved' 
        AND is_active = 1
    ");
    $stmt->execute([$doctorId]);
    $doctor = $stmt->fetch();

    if (!$doctor) {
        header('Location: doctors.php?error=doctor_unavailable');
        exit;
    }

    // Fetch real availability (column may not exist yet)
    $hasAvailCol = $db->query("SHOW COLUMNS FROM doctors LIKE 'is_available'")->rowCount() > 0;
    $doctorAvailable = $hasAvailCol ? (bool)($doctor['is_available'] ?? 1) : true;
    $doctorNote = $doctor['availability_note'] ?? '';
} else {
    header('Location: doctors.php');
    exit();
}

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Block booking for unavailable doctors
    if (!$doctorAvailable) {
        $error = 'This doctor is currently unavailable. Please choose another doctor.';
    } else {
        $appointmentDate = $_POST['appointment_date'] ?? '';
        $appointmentTime = $_POST['appointment_time'] ?? '';
        $notes = sanitizeInput($_POST['notes'] ?? '');

        if (empty($appointmentDate) || empty($appointmentTime)) {
            $error = 'Please select date and time';
        } else {
            try {
                $stmt = $db->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, notes, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
                $stmt->execute([$user['id'], $doctorId, $appointmentDate, $appointmentTime, $notes]);
                $success = 'Appointment booked successfully!';
            } catch(Exception $e) {
                error_log($e->getMessage());
                $error = 'Failed to book appointment. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Aarunya</title>
    <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .booking-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            margin-bottom: var(--space-6);
        }
        
        .doctor-info-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: var(--space-6);
            margin-bottom: var(--space-8);
            display: flex;
            align-items: center;
            gap: var(--space-6);
            transition: all var(--transition-base);
        }

        .doctor-info-card:hover {
            border-color: var(--border-glow);
            box-shadow: var(--shadow-glow);
        }
        
        .doctor-avatar-large {
            width: 80px;
            height: 80px;
            background: var(--gradient-button);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 800;
            flex-shrink: 0;
        }
        
        .doctor-details h2 {
            font-size: var(--font-2xl);
            margin-bottom: var(--space-2);
            color: var(--text-primary);
        }
        
        .doctor-specialization {
            color: var(--primary-purple);
            font-weight: 600;
            margin-bottom: var(--space-3);
        }
        
        .doctor-meta {
            display: flex;
            gap: var(--space-6);
            flex-wrap: wrap;
            color: var(--text-secondary);
            font-size: var(--font-sm);
        }
        
        .doctor-meta-item {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }
        
        .doctor-meta-item i {
            color: var(--primary-purple);
        }
        
        .form-group {
            margin-bottom: var(--space-6);
        }

        .form-label {
            display: block;
            font-size: var(--font-sm);
            font-weight: var(--font-medium);
            color: var(--text-secondary);
            margin-bottom: var(--space-2);
        }

        .form-control {
            width: 100%;
            padding: var(--space-4);
            font-size: var(--font-base);
            color: var(--text-primary);
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple);
            box-shadow: 0 0 0 3px rgba(196, 167, 255, 0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-6);
        }

        .alert {
            padding: var(--space-4) var(--space-6);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-6);
            display: flex;
            align-items: flex-start;
            gap: var(--space-3);
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22C55E;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #EF4444;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .doctor-info-card {
                flex-direction: column;
                text-align: center;
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
                    <h1 class="page-title">Book Appointment</h1>
                    <p class="page-subtitle">Schedule a consultation with our specialists</p>
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

            <div style="margin-bottom: var(--space-6);">
                <a href="doctors.php" style="color: var(--text-secondary); display: inline-flex; align-items: center; gap: var(--space-2);">
                    <i class="fas fa-arrow-left"></i> Back to Doctors
                </a>
            </div>

            <!-- Doctor Info -->
            <div class="doctor-info-card">
                <div class="doctor-avatar-large">
                    <?php if (!empty($doctor['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($doctor['profile_image']); ?>" 
                             alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($doctor['name'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div class="doctor-details">
                    <h2><?php echo htmlspecialchars($doctor['name']); ?></h2>
                    <div class="doctor-specialization"><?php echo htmlspecialchars($doctor['specialization']); ?></div>
                    <div class="doctor-meta">
                        <div class="doctor-meta-item">
                            <i class="fas fa-briefcase"></i>
                            <span><?php echo htmlspecialchars($doctor['experience']); ?> years experience</span>
                        </div>
                        <div class="doctor-meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?php echo htmlspecialchars($doctor['availability']); ?></span>
                        </div>
                        <div class="doctor-meta-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo htmlspecialchars($doctor['contact']); ?></span>
                        </div>
                        <?php if (!empty($doctor['consultation_fee'])): ?>
                        <div class="doctor-meta-item">
                            <i class="fas fa-rupee-sign"></i>
                            <span>₹<?php echo number_format($doctor['consultation_fee'], 0); ?> consultation fee</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    <br><br>
                    <a href="appointments.php" class="btn btn-primary">View My Appointments</a>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!$doctorAvailable): ?>
            <!-- Doctor Unavailable Banner -->
            <div style="background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.4); border-radius: var(--radius-xl); padding: var(--space-8); text-align: center; margin-bottom: var(--space-6);">
                <div style="font-size: 3rem; margin-bottom: var(--space-4);">🚫</div>
                <h3 style="color: #ef4444; font-size: var(--font-2xl); margin-bottom: var(--space-3);">Doctor Currently Unavailable</h3>
                <p style="color: var(--text-secondary); font-size: var(--font-base); margin-bottom: var(--space-2);">
                    <?php echo htmlspecialchars($doctor['name']); ?> is not accepting appointments right now.
                </p>
                <?php if (!empty($doctorNote)): ?>
                <div style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2); border-radius: var(--radius-lg); padding: var(--space-4); margin: var(--space-4) auto; max-width: 480px;">
                    <p style="color: #fca5a5; margin: 0; font-size: var(--font-sm);">
                        <i class="fas fa-info-circle" style="margin-right: var(--space-2);"></i>
                        <?php echo htmlspecialchars($doctorNote); ?>
                    </p>
                </div>
                <?php endif; ?>
                <a href="doctors.php" class="btn btn-primary" style="margin-top: var(--space-4);">
                    <i class="fas fa-user-md"></i> Choose Another Doctor
                </a>
            </div>
            <?php elseif (!$success): ?>
            <!-- Booking Form -->
            <div class="booking-card">
                <h3 style="font-size: var(--font-xl); margin-bottom: var(--space-6);">Select Date &amp; Time</h3>
                
                <form method="POST" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="appointment_date">Appointment Date *</label>
                            <input type="date" id="appointment_date" name="appointment_date" class="form-control" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="appointment_time">Appointment Time *</label>
                            <input type="time" id="appointment_time" name="appointment_time" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="notes">Notes (Optional)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="4" 
                                  placeholder="Any specific concerns or symptoms you'd like to discuss..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-calendar-check"></i> Confirm Booking
                    </button>
                </form>
            </div>
            <?php endif; ?>
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
