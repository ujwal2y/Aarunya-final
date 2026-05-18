<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Other Doctors';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

$db = getDoctorDB();

// Get all doctors except current doctor
$stmt = $db->prepare("
    SELECT d.*, COUNT(a.id) as appointment_count 
    FROM doctors d 
    LEFT JOIN appointments a ON d.id = a.doctor_id 
    WHERE d.id != ?
    GROUP BY d.id 
    ORDER BY d.name ASC
");
$stmt->execute([$doctor['id']]);
$doctors = $stmt->fetchAll();

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 32px;">
        <i class="fas fa-user-doctor"></i> Other Doctors
    </h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        <?php foreach ($doctors as $doc): ?>
        <div class="glass-card">
            <div class="user-avatar" style="width: 80px; height: 80px; font-size: 32px; margin: 0 auto 16px;">
                <?php echo strtoupper(substr($doc['name'], 4, 1)); ?>
            </div>
            <div style="font-size: 20px; font-weight: 700; text-align: center; margin-bottom: 8px;">
                <?php echo htmlspecialchars($doc['name']); ?>
            </div>
            <div style="text-align: center; color: #C4A7FF; font-size: 14px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($doc['specialization']); ?>
            </div>
            
            <div style="padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1); font-size: 14px; color: #546e7a;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i class="fas fa-graduation-cap" style="color: #C4A7FF; width: 20px;"></i>
                    <span><?php echo htmlspecialchars($doc['qualification'] ?? 'Not specified'); ?></span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i class="fas fa-briefcase" style="color: #C4A7FF; width: 20px;"></i>
                    <span><?php echo $doc['experience'] ?? 0; ?> years experience</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i class="fas fa-calendar-check" style="color: #C4A7FF; width: 20px;"></i>
                    <span><?php echo $doc['appointment_count']; ?> appointments</span>
                </div>
                <?php if (!empty($doc['consultation_fee'])): ?>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i class="fas fa-rupee-sign" style="color: #C4A7FF; width: 20px;"></i>
                    <span>₹<?php echo number_format($doc['consultation_fee'], 0); ?> consultation</span>
                </div>
                <?php endif; ?>
                <?php if ($doc['email']): ?>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-envelope" style="color: #C4A7FF; width: 20px;"></i>
                    <span><?php echo htmlspecialchars($doc['email']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
