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

// Get all medical documents for this patient
$stmt = $db->prepare("
    SELECT md.*, d.name as doctor_name, d.specialization 
    FROM medical_documents md
    JOIN doctors d ON md.doctor_id = d.id
    WHERE md.patient_id = ? AND md.is_visible_to_patient = TRUE
    ORDER BY md.upload_date DESC
");
$stmt->execute([$user['id']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group documents by type
$documentsByType = [];
foreach ($documents as $doc) {
    $type = $doc['document_type'];
    if (!isset($documentsByType[$type])) {
        $documentsByType[$type] = [];
    }
    $documentsByType[$type][] = $doc;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Documents - Aarunya</title>
        <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
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
                    <h1 class="page-title">My Medical Documents</h1>
                    <p class="page-subtitle">View and download your medical reports, scans, and test results</p>
                </div>
            </div>

            <?php if (count($documents) === 0): ?>
            <!-- Empty State -->
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 64px; color: rgba(244, 114, 182, 0.3); margin-bottom: 20px;">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h2 style="color: #ffffff; font-size: 24px; margin-bottom: 12px;">No Documents Yet</h2>
                <p style="color: #546e7a; font-size: 16px; max-width: 500px; margin: 0 auto;">
                    Your doctor will upload medical documents like CT scans, X-rays, and lab reports here. 
                    They will appear once uploaded.
                </p>
            </div>
            <?php else: ?>

            <!-- Document Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: linear-gradient(135deg, rgba(244, 114, 182, 0.2) 0%, rgba(196, 167, 255, 0.1) 100%); border: 1px solid rgba(244, 114, 182, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #C4A7FF; margin-bottom: 8px;">
                        <?php echo count($documents); ?>
                    </div>
                    <div style="color: #546e7a; font-size: 0.9rem;">Total Documents</div>
                </div>

                <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.1) 100%); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #10b981; margin-bottom: 8px;">
                        <?php echo count($documentsByType); ?>
                    </div>
                    <div style="color: #546e7a; font-size: 0.9rem;">Document Types</div>
                </div>

                <div style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.1) 100%); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                    <div style="font-size: 2rem; font-weight: 800; color: #3b82f6; margin-bottom: 8px;">
                        <?php echo date('M Y', strtotime($documents[0]['upload_date'])); ?>
                    </div>
                    <div style="color: #546e7a; font-size: 0.9rem;">Latest Upload</div>
                </div>
            </div>

            <!-- Documents by Type -->
            <?php foreach ($documentsByType as $type => $docs): ?>
            <div class="section-card" style="margin-bottom: 30px;">
                <div class="section-header">
                    <h2 class="section-title">
                        <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                        $icons = [
                            'ct_scan' => 'fa-x-ray',
                            'xray' => 'fa-x-ray',
                            'mri' => 'fa-brain',
                            'ultrasound' => 'fa-heartbeat',
                            'lab_report' => 'fa-flask',
                            'blood_test' => 'fa-vial',
                            'prescription' => 'fa-prescription',
                            'other' => 'fa-file-medical'
                        ];
                        $icon = $icons[$type] ?? 'fa-file-medical';
                        ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                        <?php echo ucwords(str_replace('_', ' ', $type)); ?>
                    </h2>
                    <span style="color: #78909c; font-size: 14px;"><?php echo count($docs); ?> documents</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <?php foreach ($docs as $doc): ?>
                    <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 12px; padding: 20px; transition: all 0.3s ease;">
                        <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 15px;">
                            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, rgba(244, 114, 182, 0.2) 0%, rgba(196, 167, 255, 0.1) 100%); border: 1px solid rgba(244, 114, 182, 0.3); border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas <?php echo $icon; ?>" style="color: #C4A7FF; font-size: 20px;"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h4 style="color: #ffffff; font-size: 16px; font-weight: 600; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($doc['document_name']); ?>
                                </h4>
                                <p style="color: #78909c; font-size: 12px; margin: 0;">
                                    <i class="fas fa-user-md"></i> <?php echo htmlspecialchars($doc['doctor_name']); ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($doc['description']): ?>
                        <p style="color: #546e7a; font-size: 14px; margin-bottom: 12px; line-height: 1.5;">
                            <?php echo htmlspecialchars($doc['description']); ?>
                        </p>
                        <?php endif; ?>

                        <?php if ($doc['notes']): ?>
                        <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                            <p style="color: #546e7a; font-size: 13px; margin: 0; line-height: 1.5;">
                                <strong style="color: #3b82f6;">Medical Notes:</strong><br>
                                <?php echo nl2br(htmlspecialchars($doc['notes'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(196, 167, 255, 0.1);">
                            <span style="color: #78909c; font-size: 12px;">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($doc['upload_date'])); ?>
                            </span>
                            <div style="display: flex; gap: 8px;">
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" 
                                   style="padding: 8px 16px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.1) 100%); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 6px; color: #3b82f6; font-size: 13px; text-decoration: none; transition: all 0.2s ease;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" download 
                                   style="padding: 8px 16px; background: linear-gradient(135deg, rgba(244, 114, 182, 0.2) 0%, rgba(196, 167, 255, 0.1) 100%); border: 1px solid rgba(244, 114, 182, 0.3); border-radius: 6px; color: #C4A7FF; font-size: 13px; text-decoration: none; transition: all 0.2s ease;">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php endif; ?>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Aarunya Chatbot -->
    <?php include 'includes/chatbot.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleMobileMenu() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
