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

// Get user settings
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Handle settings update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_notifications':
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $sms_alerts = isset($_POST['sms_alerts']) ? 1 : 0;
            $push_notifications = isset($_POST['push_notifications']) ? 1 : 0;
            
            try {
                $stmt = $db->prepare("UPDATE users SET email_notifications = ?, sms_alerts = ?, push_notifications = ? WHERE id = ?");
                $stmt->execute([$email_notifications, $sms_alerts, $push_notifications, $user['id']]);
                $success = 'Notification settings updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update settings.';
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = 'All password fields are required.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match.';
            } elseif (!password_verify($current_password, $userData['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user['id']]);
                $success = 'Password changed successfully!';
            }
            break;
    }
    
    // Refresh user data
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $userData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .settings-grid {
            display: grid;
            gap: 1.5rem;
            max-width: 900px;
        }
        
        .settings-section {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            transition: all var(--transition-base);
        }
        
        .settings-section:hover {
            border-color: var(--primary-purple);
            box-shadow: var(--shadow-glow);
        }
        
        .settings-section h2 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
            font-size: var(--font-xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
        }
        
        .settings-section h2 i {
            font-size: 1.5rem;
            color: var(--primary-purple);
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            background: rgba(196, 167, 255, 0.05);
            border: 1px solid rgba(196, 167, 255, 0.1);
            border-radius: var(--radius-lg);
            margin-bottom: 1rem;
            transition: all var(--transition-base);
        }
        
        .setting-item:hover {
            background: rgba(196, 167, 255, 0.08);
            border-color: rgba(196, 167, 255, 0.2);
        }
        
        .setting-info h3 {
            font-size: var(--font-base);
            font-weight: var(--font-semibold);
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .setting-info p {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            margin: 0;
        }
        
        .toggle-switch {
            position: relative;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.1);
            transition: .4s;
            border-radius: 34px;
            border: 1px solid var(--glass-border);
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        input:checked + .toggle-slider {
            background: var(--gradient-button);
            border-color: var(--primary-purple);
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-lg);
            background: rgba(196, 167, 255, 0.05);
            color: var(--text-primary);
            font-weight: var(--font-semibold);
            font-size: var(--font-base);
            cursor: pointer;
            transition: all var(--transition-base);
            text-decoration: none;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .action-btn:hover {
            background: rgba(196, 167, 255, 0.1);
            border-color: var(--primary-purple);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(127, 90, 240, 0.3);
        }
        
        .action-btn i {
            font-size: 1.125rem;
        }
        
        .action-btn.danger {
            background: rgba(239, 68, 68, 0.05);
            border-color: rgba(239, 68, 68, 0.3);
            color: var(--danger);
        }
        
        .action-btn.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: var(--danger);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
        }
        
        .modal-content {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            margin: 5% auto;
            padding: 2rem;
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-xl);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
        }
        
        .modal-header h3 {
            font-size: var(--font-xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }
        
        .modal-header h3 i {
            color: var(--primary-purple);
        }
        
        .close {
            color: var(--text-secondary);
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            transition: color var(--transition-fast);
            line-height: 1;
        }
        
        .close:hover {
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
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-purple);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(196, 167, 255, 0.1);
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
    </style>
</head>
<body>
    <div class="app-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <div>
                    <h1 class="page-title">Settings</h1>
                    <p class="page-subtitle">Manage your account preferences and security</p>
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

            <div class="settings-grid">
                <!-- Notifications Section -->
                <div class="settings-section">
                    <h2><i class="fas fa-bell"></i> Notifications</h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_notifications">
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Email Notifications</h3>
                                <p>Receive appointment updates via email</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="email_notifications" <?php echo ($userData['email_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>SMS Alerts</h3>
                                <p>Emergency notifications via SMS</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="sms_alerts" <?php echo ($userData['sms_alerts'] ?? 1) ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="setting-item">
                            <div class="setting-info">
                                <h3>Push Notifications</h3>
                                <p>Browser notifications for urgent cases</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="push_notifications" <?php echo ($userData['push_notifications'] ?? 0) ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                            <i class="fas fa-save"></i> Save Notification Settings
                        </button>
                    </form>
                </div>

                <!-- Security Section -->
                <div class="settings-section">
                    <h2><i class="fas fa-shield-alt"></i> Security</h2>
                    
                    <button class="action-btn" onclick="openPasswordModal()">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    
                    <button class="action-btn" onclick="alert('Two-Factor Authentication coming soon!')">
                        <i class="fas fa-mobile-alt"></i> Two-Factor Auth
                    </button>
                    
                    <button class="action-btn" onclick="alert('Login history feature coming soon!')">
                        <i class="fas fa-history"></i> Login History
                    </button>
                </div>

                <!-- Account Section -->
                <div class="settings-section">
                    <h2><i class="fas fa-user-circle"></i> Account</h2>
                    
                    <button class="action-btn" onclick="exportData()">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                    
                    <button class="action-btn" onclick="deactivateAccount()">
                        <i class="fas fa-pause-circle"></i> Deactivate Account
                    </button>
                    
                    <button class="action-btn danger" onclick="deleteAccount()">
                        <i class="fas fa-trash-alt"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> Change Password</h3>
                <span class="close" onclick="closePasswordModal()">&times;</span>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required>
                    <small style="color: var(--text-secondary);">Min 8 characters with uppercase, lowercase, number & special character</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Update Password
                </button>
            </form>
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

        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        function exportData() {
            if (confirm('Export your account data? This will download a JSON file with your information.')) {
                window.location.href = 'export_data.php';
            }
        }

        function deactivateAccount() {
            if (confirm('Are you sure you want to deactivate your account? You can reactivate it by logging in again.')) {
                window.location.href = 'deactivate_account.php';
            }
        }

        function deleteAccount() {
            const confirmation = prompt('This action is PERMANENT and cannot be undone. Type "DELETE" to confirm:');
            if (confirmation === 'DELETE') {
                window.location.href = 'delete_account.php';
            } else if (confirmation !== null) {
                alert('Account deletion cancelled. You must type "DELETE" exactly.');
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('passwordModal');
            if (event.target == modal) {
                closePasswordModal();
            }
        }
    </script>
</body>
</html>
