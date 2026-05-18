<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Settings';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">
            <i class="fas fa-cog"></i> Settings
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Manage your profile, preferences, and account settings
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- Profile Settings -->
        <div class="glass-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-user-edit"></i>
                    Profile Information
                </h2>
            </div>

            <form style="display: grid; gap: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($doctor['name']); ?>" placeholder="Dr. John Smith">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($doctor['email']); ?>" placeholder="doctor@example.com">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" value="<?php echo htmlspecialchars($doctor['phone'] ?? ''); ?>" placeholder="+1 (555) 123-4567">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Specialization</label>
                        <select class="form-control">
                            <option value="obstetrics" <?php echo ($doctor['specialization'] === 'obstetrics') ? 'selected' : ''; ?>>Obstetrics</option>
                            <option value="gynecology" <?php echo ($doctor['specialization'] === 'gynecology') ? 'selected' : ''; ?>>Gynecology</option>
                            <option value="maternal-fetal" <?php echo ($doctor['specialization'] === 'maternal-fetal') ? 'selected' : ''; ?>>Maternal-Fetal Medicine</option>
                            <option value="reproductive" <?php echo ($doctor['specialization'] === 'reproductive') ? 'selected' : ''; ?>>Reproductive Endocrinology</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Bio / About</label>
                    <textarea class="form-control" rows="4" placeholder="Tell patients about your experience and approach to care..."><?php echo htmlspecialchars($doctor['bio'] ?? ''); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" value="<?php echo htmlspecialchars($doctor['experience'] ?? ''); ?>" placeholder="10">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-rupee-sign"></i> Consultation Fee
                        </label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #C4A7FF; font-weight: 600;">₹</span>
                            <input type="number" class="form-control" value="<?php echo htmlspecialchars($doctor['consultation_fee'] ?? ''); ?>" placeholder="500" style="padding-left: 32px;" min="0" step="50">
                        </div>
                        <small style="color: #546e7a; font-size: 12px; margin-top: 4px;">Amount in Indian Rupees (INR)</small>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <button type="button" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Settings -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Notification Settings -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell"></i>
                        Notifications
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: #ffffff; margin-bottom: 4px;">Email Notifications</div>
                            <div style="font-size: 12px; color: #546e7a;">Receive appointment updates via email</div>
                        </div>
                        <label style="position: relative; display: inline-block; width: 48px; height: 24px;">
                            <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #C4A7FF; border-radius: 24px; transition: 0.2s;"></span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: #ffffff; margin-bottom: 4px;">SMS Alerts</div>
                            <div style="font-size: 12px; color: #546e7a;">Emergency notifications via SMS</div>
                        </div>
                        <label style="position: relative; display: inline-block; width: 48px; height: 24px;">
                            <input type="checkbox" checked style="opacity: 0; width: 0; height: 0;">
                            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #C4A7FF; border-radius: 24px; transition: 0.2s;"></span>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 500; color: #ffffff; margin-bottom: 4px;">Push Notifications</div>
                            <div style="font-size: 12px; color: #546e7a;">Browser notifications for urgent cases</div>
                        </div>
                        <label style="position: relative; display: inline-block; width: 48px; height: 24px;">
                            <input type="checkbox" style="opacity: 0; width: 0; height: 0;">
                            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #78909c; border-radius: 24px; transition: 0.2s;"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt"></i>
                        Security
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-mobile-alt"></i> Two-Factor Auth
                    </button>
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-history"></i> Login History
                    </button>
                </div>
            </div>

            <!-- Account Actions -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog"></i>
                        Account
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-pause"></i> Deactivate Account
                    </button>
                    <button class="btn btn-danger" style="width: 100%; justify-content: center;">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
