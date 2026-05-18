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

$success = '';
$error = '';

// Handle emergency request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $priority = $_POST['priority'] ?? 'high';
    
    if (empty($message) || empty($location) || empty($phone)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $stmt = $db->prepare("
                INSERT INTO emergency_requests (user_id, message, location, phone, status, priority, created_at) 
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $stmt->execute([$user['id'], $message, $location, $phone, $priority]);
            $success = 'Emergency request submitted successfully! Our team will contact you shortly.';
        } catch (Exception $e) {
            $error = 'Failed to submit request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .emergency-alert-banner {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.1) 100%);
            border: 2px solid rgba(239, 68, 68, 0.4);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            animation: pulse-border 2s ease-in-out infinite;
        }
        
        @keyframes pulse-border {
            0%, 100% {
                border-color: rgba(239, 68, 68, 0.4);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }
            50% {
                border-color: rgba(239, 68, 68, 0.8);
                box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
            }
        }
        
        .emergency-alert-banner i {
            font-size: 4rem;
            color: var(--danger);
            margin-bottom: 1rem;
            animation: pulse-icon 2s ease-in-out infinite;
        }
        
        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .emergency-alert-banner h2 {
            font-size: var(--font-3xl);
            font-weight: var(--font-extrabold);
            color: var(--danger);
            margin-bottom: 0.5rem;
        }
        
        .emergency-alert-banner p {
            font-size: var(--font-lg);
            color: var(--text-secondary);
            margin: 0;
        }
        
        .emergency-contact-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 2px solid var(--danger);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            text-align: center;
            transition: all var(--transition-base);
            box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3);
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        
        .emergency-contact-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 48px rgba(239, 68, 68, 0.5);
        }
        
        .contact-icon {
            font-size: 4rem;
            color: var(--danger);
            margin-bottom: 1.5rem;
            animation: pulse-icon 2s ease-in-out infinite;
        }
        
        .contact-title {
            font-size: var(--font-2xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .contact-number {
            font-size: 4rem;
            font-weight: var(--font-black);
            color: var(--danger);
            margin-bottom: 1.5rem;
            letter-spacing: 4px;
            text-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #DC2626 0%, #EF4444 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: var(--font-lg);
            font-weight: var(--font-bold);
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: all var(--transition-base);
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.4);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.6);
        }
        
        .form-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            transition: all var(--transition-base);
        }
        
        .form-card:hover {
            border-color: var(--primary-purple);
            box-shadow: var(--shadow-glow);
        }
        
        .form-card h2 {
            font-size: var(--font-2xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .form-card h2 i {
            color: var(--primary-purple);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-size: var(--font-sm);
            font-weight: var(--font-medium);
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: var(--font-base);
            color: var(--text-primary);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
            font-family: inherit;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(196, 167, 255, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: var(--font-sm);
            font-weight: var(--font-medium);
        }
        
        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: var(--success);
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger);
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
        
        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
            
            .contact-number {
                font-size: 3rem;
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
                    <h1 class="page-title">Emergency Assistance</h1>
                    <p class="page-subtitle">Quick access to emergency medical support 24/7</p>
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
            <!-- Emergency Alert Banner -->
            <div class="emergency-alert-banner">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>Emergency Assistance</h2>
                <p>Quick access to emergency medical support 24/7</p>
            </div>

            <!-- Emergency Contact Card -->
            <div class="emergency-contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone-volume"></i>
                </div>
                <h3 class="contact-title">Emergency Hotline</h3>
                <div class="contact-number">108</div>
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: var(--font-base);">
                    24/7 Ambulance & Emergency Medical Service
                </p>
                <a href="tel:108" class="btn-danger">
                    <i class="fas fa-phone"></i> Call Emergency Now
                </a>
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--divider);">
                    <p style="color: var(--text-secondary); font-size: var(--font-sm);">
                        <i class="fas fa-info-circle"></i> Available 24/7 for immediate medical assistance
                    </p>
                </div>
            </div>

            <!-- Emergency Request Form -->
            <div class="form-card">
                <h2>
                    <i class="fas fa-file-medical"></i>
                    Submit Emergency Request
                </h2>
                <p style="color: var(--text-secondary); margin-bottom: 2rem;">
                    Fill out this form if you need immediate medical assistance. Our team will contact you as soon as possible.
                </p>

                <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Emergency Description *</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Describe your emergency situation..." required></textarea>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Your Location *</label>
                            <input type="text" name="location" class="form-control" placeholder="Enter your current location" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" placeholder="+91-XXXXXXXXXX" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Priority Level</label>
                        <select name="priority" class="form-control">
                            <option value="high">High - Immediate Attention Required</option>
                            <option value="medium">Medium - Urgent but Stable</option>
                            <option value="low">Low - Non-Critical</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-danger" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Submit Emergency Request
                    </button>
                </form>
            </div>

            <!-- Back to Dashboard -->
            <div style="text-align: center; margin-top: 2rem;">
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
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
