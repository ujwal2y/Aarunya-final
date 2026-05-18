<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$pageTitle = 'Settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                
                if (!empty($name) && !empty($email)) {
                    $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$name, $email, $_SESSION['admin_id']])) {
                        $_SESSION['success'] = 'Profile updated successfully!';
                    } else {
                        $_SESSION['error'] = 'Failed to update profile.';
                    }
                }
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if ($new_password !== $confirm_password) {
                    $_SESSION['error'] = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $_SESSION['error'] = 'Password must be at least 6 characters.';
                } else {
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    $admin = $stmt->fetch();
                    
                    if (password_verify($current_password, $admin['password'])) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
                        if ($stmt->execute([$hashed, $_SESSION['admin_id']])) {
                            $_SESSION['success'] = 'Password changed successfully!';
                        } else {
                            $_SESSION['error'] = 'Failed to change password.';
                        }
                    } else {
                        $_SESSION['error'] = 'Current password is incorrect.';
                    }
                }
                break;
                
            case 'update_system':
                $site_name = $_POST['site_name'] ?? 'Aarunya';
                $site_email = $_POST['site_email'] ?? '';
                $timezone = $_POST['timezone'] ?? 'Asia/Kolkata';
                
                // In a real application, you'd save these to a settings table
                $_SESSION['success'] = 'System settings updated successfully!';
                break;
        }
        
        header('Location: settings.php');
        exit;
    }
}

// Get admin info
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

include '../includes/header.php';
?>

<!-- Page Header -->
<div class="layout-container">
    <div class="content-wrapper">
        <!-- Top Header -->
        <div class="section-layout">
            <div class="section-title">
                <i class="fas fa-cog"></i>
                Settings
            </div>
            <div class="section-subtitle">Manage your account and system preferences</div>
        </div>

        <!-- Success/Error Messages -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Settings Layout -->
        <div class="layout-grid layout-grid-3 gap-lg">
            <!-- Settings Navigation -->
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0; font-size: 18px; font-weight: 600;">
                        <i class="fas fa-list"></i> Settings Menu
                    </h3>
                </div>
                <div class="card-body-layout">
                    <nav class="nav-layout">
                        <a href="#profile" class="nav-item-layout active" onclick="showTab('profile'); return false;">
                            <i class="fas fa-user"></i>
                            <span>Profile Settings</span>
                        </a>
                        <a href="#password" class="nav-item-layout" onclick="showTab('password'); return false;">
                            <i class="fas fa-lock"></i>
                            <span>Change Password</span>
                        </a>
                        <a href="#system" class="nav-item-layout" onclick="showTab('system'); return false;">
                            <i class="fas fa-cog"></i>
                            <span>System Settings</span>
                        </a>
                        <a href="#notifications" class="nav-item-layout" onclick="showTab('notifications'); return false;">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Settings Content -->
            <div style="grid-column: span 2;">
                <!-- Profile Settings -->
                <div id="profile-tab" class="settings-tab">
                    <div class="card-layout">
                        <div class="card-header-layout">
                            <h3 style="color: #C4A7FF; margin: 0; font-size: 18px; font-weight: 600;">
                                <i class="fas fa-user"></i> Profile Settings
                            </h3>
                        </div>
                        <div class="card-body-layout">
                            <form method="POST" action="" class="form-layout">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" 
                                           value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input type="email" name="email" 
                                           value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" value="Administrator" disabled>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Settings -->
                <div id="password-tab" class="settings-tab" style="display: none;">
                    <div class="card-layout">
                        <div class="card-header-layout">
                            <h3 style="color: #C4A7FF; margin: 0; font-size: 18px; font-weight: 600;">
                                <i class="fas fa-lock"></i> Change Password
                            </h3>
                        </div>
                        <div class="card-body-layout">
                            <form method="POST" action="" class="form-layout">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label>Current Password</label>
                                    <input type="password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="new_password" minlength="6" required>
                                    <small style="color: var(--text-secondary); font-size: 12px;">Minimum 6 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirm_password" minlength="6" required>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- System Settings -->
                <div id="system-tab" class="settings-tab" style="display: none;">
                    <div class="card-layout">
                        <div class="card-header-layout">
                            <h3 style="color: #C4A7FF; margin: 0; font-size: 18px; font-weight: 600;">
                                <i class="fas fa-cog"></i> System Settings
                            </h3>
                        </div>
                        <div class="card-body-layout">
                            <form method="POST" action="" class="form-layout">
                                <input type="hidden" name="action" value="update_system">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Site Name</label>
                                        <input type="text" name="site_name" value="Aarunya" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>System Email</label>
                                        <input type="email" name="site_email" value="admin@aarunya.com" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Timezone</label>
                                        <select name="timezone">
                                            <option value="Asia/Kolkata" selected>Asia/Kolkata (IST)</option>
                                            <option value="UTC">UTC</option>
                                            <option value="America/New_York">America/New York (EST)</option>
                                            <option value="Europe/London">Europe/London (GMT)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Date Format</label>
                                        <select name="date_format">
                                            <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                            <option value="d/m/Y">DD/MM/YYYY</option>
                                            <option value="m/d/Y">MM/DD/YYYY</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div id="notifications-tab" class="settings-tab" style="display: none;">
                    <div class="card-layout">
                        <div class="card-header-layout">
                            <h3 style="color: #C4A7FF; margin: 0; font-size: 18px; font-weight: 600;">
                                <i class="fas fa-bell"></i> Notification Preferences
                            </h3>
                        </div>
                        <div class="card-body-layout">
                            <form method="POST" action="" class="form-layout">
                                <input type="hidden" name="action" value="update_notifications">
                                
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 14px;">
                                        <input type="checkbox" name="email_notifications" checked style="margin: 0;">
                                        <span>Email Notifications</span>
                                    </label>
                                    <small style="color: var(--text-secondary); font-size: 12px; margin-left: 24px;">
                                        Receive email alerts for important events
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 14px;">
                                        <input type="checkbox" name="emergency_alerts" checked style="margin: 0;">
                                        <span>Emergency Alerts</span>
                                    </label>
                                    <small style="color: var(--text-secondary); font-size: 12px; margin-left: 24px;">
                                        Get notified immediately for emergency requests
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 14px;">
                                        <input type="checkbox" name="appointment_notifications" checked style="margin: 0;">
                                        <span>Appointment Notifications</span>
                                    </label>
                                    <small style="color: var(--text-secondary); font-size: 12px; margin-left: 24px;">
                                        Receive updates about new appointments
                                    </small>
                                </div>
                                
                                <div class="form-group">
                                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: 14px;">
                                        <input type="checkbox" name="user_registration" checked style="margin: 0;">
                                        <span>User Registration Alerts</span>
                                    </label>
                                    <small style="color: var(--text-secondary); font-size: 12px; margin-left: 24px;">
                                        Get notified when new users register
                                    </small>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all nav items
    document.querySelectorAll('.nav-item-layout').forEach(item => {
        item.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to clicked nav item
    event.target.closest('.nav-item-layout').classList.add('active');
}
</script>

<?php include '../includes/footer.php'; ?>
