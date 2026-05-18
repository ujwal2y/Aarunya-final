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

$success = '';
$error = '';

// Get user details
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$userData = $stmt->fetch();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $pregnancy_week = intval($_POST['pregnancy_week'] ?? 0);
    $due_date = $_POST['due_date'] ?? null;
    
    if (empty($name)) {
        $error = 'Name is required';
    } else {
        try {
            $stmt = $db->prepare("UPDATE users SET name = ?, age = ?, pregnancy_week = ?, due_date = ? WHERE id = ?");
            $stmt->execute([$name, $age, $pregnancy_week, $due_date, $user['id']]);
            
            // Update session
            $_SESSION['user_name'] = $name;
            
            $success = 'Profile updated successfully!';
            
            // Refresh user data
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch();
        } catch(Exception $e) {
            error_log($e->getMessage());
            $error = 'Failed to update profile';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
        }
        
        .profile-avatar-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 1.5rem;
        }
        
        .profile-avatar-large {
            width: 140px;
            height: 140px;
            background: var(--gradient-button);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            font-weight: var(--font-extrabold);
            object-fit: cover;
            border: 4px solid var(--primary-purple);
            box-shadow: 0 8px 32px rgba(127, 90, 240, 0.4);
        }
        
        .photo-upload-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--gradient-button);
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all var(--transition-base);
            border: 3px solid var(--bg-dark);
            box-shadow: 0 4px 12px rgba(127, 90, 240, 0.4);
        }
        
        .photo-upload-overlay:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(127, 90, 240, 0.6);
        }
        
        .upload-btn {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            color: white;
            font-size: 1.125rem;
        }
        
        .upload-btn span {
            display: none;
        }
        
        .upload-progress {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            padding: 2rem;
            border-radius: var(--radius-xl);
            border: 1px solid var(--glass-border);
            z-index: 1000;
            display: none;
            text-align: center;
            min-width: 300px;
            box-shadow: var(--shadow-xl);
        }
        
        .upload-progress.active {
            display: block;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--glass-border);
            border-top-color: var(--primary-purple);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .profile-card {
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all var(--transition-base);
        }
        
        .profile-card:hover {
            border-color: var(--primary-purple);
            box-shadow: var(--shadow-glow);
        }
        
        .card-title {
            font-size: var(--font-xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--divider);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .card-title i {
            color: var(--primary-purple);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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
        
        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.875rem;
            font-size: var(--font-sm);
            font-weight: var(--font-semibold);
            border-radius: var(--radius-full);
        }
        
        .badge-success {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
            border: 1px solid rgba(34, 197, 94, 0.3);
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
            .form-row {
                grid-template-columns: 1fr;
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
                    <h1 class="page-title">My Profile</h1>
                    <p class="page-subtitle">Manage your account and personal information</p>
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
            <div class="profile-header">
                <div class="profile-avatar-container">
                    <?php if (!empty($userData['profile_photo']) && file_exists($userData['profile_photo'])): ?>
                        <img src="<?php echo htmlspecialchars($userData['profile_photo']); ?>?t=<?php echo time(); ?>" alt="Profile Photo" class="profile-avatar-large" id="profileImage">
                    <?php else: ?>
                        <div class="profile-avatar-large" id="profileAvatar">
                            <?php echo strtoupper(substr($userData['name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="photo-upload-overlay">
                        <label for="photoUpload" class="upload-btn">
                            <i class="fas fa-camera"></i>
                            <span>Change Photo</span>
                        </label>
                        <input type="file" id="photoUpload" accept="image/*" style="display: none;">
                    </div>
                </div>
                <h1><?php echo htmlspecialchars($userData['name']); ?></h1>
                <p style="color: var(--text-secondary);"><?php echo htmlspecialchars($userData['email']); ?></p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <h2 class="card-title">
                    <i class="fas fa-user-edit"></i>
                    Personal Information
                </h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" 
                               value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                        <small style="color: var(--text-muted); font-size: var(--font-size-xs);">Email cannot be changed</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="age">Age</label>
                            <input type="number" id="age" name="age" class="form-control" 
                                   value="<?php echo $userData['age']; ?>" min="18" max="60">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="pregnancy_week">Pregnancy Week</label>
                            <input type="number" id="pregnancy_week" name="pregnancy_week" class="form-control" 
                                   value="<?php echo $userData['pregnancy_week']; ?>" min="1" max="42">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date" class="form-control" 
                               value="<?php echo $userData['due_date']; ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <div class="profile-card">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Account Information
                </h2>
                
                <div style="display: grid; gap: var(--spacing-md);">
                    <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary);">Member Since</span>
                        <span style="font-weight: 600;"><?php echo date('F d, Y', strtotime($userData['created_at'])); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0; border-bottom: 1px solid var(--border);">
                        <span style="color: var(--text-secondary);">Account Status</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: var(--spacing-sm) 0;">
                        <span style="color: var(--text-secondary);">User ID</span>
                        <span style="font-weight: 600;">#<?php echo str_pad($userData['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upload Progress Modal -->
    <div class="upload-progress" id="uploadProgress">
        <div class="spinner"></div>
        <p>Uploading photo...</p>
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
    
    // Profile photo upload script
        // Profile photo upload
        const photoUpload = document.getElementById('photoUpload');
        const uploadProgress = document.getElementById('uploadProgress');
        
        photoUpload.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please upload JPG, PNG, GIF, or WEBP image.');
                return;
            }
            
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('File too large. Maximum size is 5MB.');
                return;
            }
            
            // Show progress
            uploadProgress.classList.add('active');
            
            // Create form data
            const formData = new FormData();
            formData.append('profile_photo', file);
            
            try {
                const response = await fetch('upload_profile_photo.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Get the container
                    const container = document.querySelector('.profile-avatar-container');
                    const existingElement = container.querySelector('.profile-avatar-large');
                    
                    // Create new image element
                    const img = document.createElement('img');
                    img.src = result.photo_url + '?t=' + Date.now();
                    img.alt = 'Profile Photo';
                    img.className = 'profile-avatar-large';
                    img.id = 'profileImage';
                    
                    // Replace existing element
                    existingElement.replaceWith(img);
                    
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;
                    alertDiv.style.marginBottom = 'var(--spacing-lg)';
                    
                    const mainContent = document.querySelector('.main-content');
                    const profileHeader = document.querySelector('.profile-header');
                    mainContent.insertBefore(alertDiv, profileHeader.nextSibling);
                    
                    // Remove alert after 3 seconds
                    setTimeout(() => alertDiv.remove(), 3000);
                    
                    // Reload page to update navbar
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('Failed to upload photo. Please try again.');
            } finally {
                uploadProgress.classList.remove('active');
                photoUpload.value = ''; // Reset input
            }
        });
    </script>

    <!-- Aarunya Chatbot -->
    <?php include 'includes/chatbot.php'; ?>
</body>
</html>

