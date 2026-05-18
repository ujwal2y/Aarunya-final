<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Prescriptions';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

$db = getDoctorDB();

// Check if prescriptions table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'prescriptions'");
    $tableExists = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $tableExists = false;
}

$prescriptions = [];
if ($tableExists) {
    try {
        $stmt = $db->prepare("
            SELECT p.*, u.name as patient_name, u.email as patient_email, u.pregnancy_week
            FROM prescriptions p
            JOIN users u ON p.patient_id = u.id
            WHERE p.doctor_id = ?
            ORDER BY p.prescription_date DESC, p.created_at DESC
        ");
        $stmt->execute([$doctor['id']]);
        $prescriptions = $stmt->fetchAll();
    } catch (Exception $e) {
        $prescriptions = [];
    }
}

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin: 0;">
            <i class="fas fa-prescription"></i> Prescriptions
        </h1>
        <button class="btn btn-primary" onclick="alert('Prescription creation feature coming soon!')">
            <i class="fas fa-plus"></i> New Prescription
        </button>
    </div>

    <?php if (count($prescriptions) > 0): ?>
        <div style="display: grid; gap: 20px;">
            <?php foreach ($prescriptions as $rx): 
                $medications = json_decode($rx['medications'], true) ?? [];
            ?>
            <div class="glass-card">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <div>
                        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($rx['patient_name']); ?>
                        </h3>
                        <p style="font-size: 14px; color: #546e7a;">
                            <?php echo htmlspecialchars($rx['patient_email']); ?> • Week <?php echo $rx['pregnancy_week'] ?? 'N/A'; ?>
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <strong style="display: block; font-size: 16px; color: #C4A7FF; margin-bottom: 4px;">
                            <?php echo date('M d, Y', strtotime($rx['prescription_date'])); ?>
                        </strong>
                        <span style="font-size: 14px; color: #546e7a;">
                            Rx #<?php echo str_pad($rx['id'], 6, '0', STR_PAD_LEFT); ?>
                        </span>
                    </div>
                </div>

                <?php if ($rx['diagnosis']): ?>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 14px; font-weight: 600; color: #C4A7FF; margin-bottom: 8px; text-transform: uppercase;">
                        Diagnosis
                    </div>
                    <div style="font-size: 15px; color: #ffffff; line-height: 1.6;">
                        <?php echo htmlspecialchars($rx['diagnosis']); ?>
                    </div>
                </div>
                <?php endif; ?>

                <div style="margin-bottom: 20px;">
                    <div style="font-size: 14px; font-weight: 600; color: #C4A7FF; margin-bottom: 8px; text-transform: uppercase;">
                        Medications
                    </div>
                    <?php foreach ($medications as $med): ?>
                    <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 12px; margin-bottom: 8px;">
                        <div style="font-size: 15px; font-weight: 600; color: #ffffff; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($med['name']); ?>
                        </div>
                        <div style="font-size: 13px; color: #546e7a;">
                            <?php echo htmlspecialchars($med['dosage']); ?> • 
                            <?php echo htmlspecialchars($med['frequency']); ?> • 
                            <?php echo htmlspecialchars($med['duration']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <button class="btn btn-secondary" onclick="alert('View feature coming soon!')">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-secondary" onclick="alert('Download feature coming soon!')">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 64px; color: #334155; margin-bottom: 16px;">
                <i class="fas fa-prescription"></i>
            </div>
            <p style="font-size: 18px; color: #78909c;">No prescriptions yet. Create your first prescription above.</p>
        </div>
    <?php endif; ?>
</div>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
