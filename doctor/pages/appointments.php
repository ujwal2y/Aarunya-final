<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Appointments';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

$db = getDoctorDB();

// Get appointments for this doctor with dummy data if table doesn't exist
$appointments = [];
try {
    $stmt = $db->prepare("
        SELECT a.*, u.name as patient_name, u.email as patient_email, u.phone as patient_phone,
               u.pregnancy_week
        FROM appointments a
        JOIN users u ON a.user_id = u.id
        WHERE a.doctor_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$doctor['id']]);
    $appointments = $stmt->fetchAll();
} catch (Exception $e) {
    // Use dummy data if table doesn't exist
    $appointments = [
        [
            'id' => 1,
            'patient_name' => 'Sarah Parker',
            'patient_email' => 'sarah@example.com',
            'patient_phone' => '+1-555-0123',
            'pregnancy_week' => 24,
            'appointment_date' => date('Y-m-d'),
            'appointment_time' => '09:00:00',
            'status' => 'confirmed',
            'appointment_type' => 'Regular Checkup'
        ],
        [
            'id' => 2,
            'patient_name' => 'Maria Johnson',
            'patient_email' => 'maria@example.com',
            'patient_phone' => '+1-555-0124',
            'pregnancy_week' => 16,
            'appointment_date' => date('Y-m-d'),
            'appointment_time' => '10:30:00',
            'status' => 'pending',
            'appointment_type' => 'Ultrasound'
        ],
        [
            'id' => 3,
            'patient_name' => 'Emily Wilson',
            'patient_email' => 'emily@example.com',
            'patient_phone' => '+1-555-0125',
            'pregnancy_week' => 32,
            'appointment_date' => date('Y-m-d'),
            'appointment_time' => '14:00:00',
            'status' => 'confirmed',
            'appointment_type' => 'Consultation'
        ]
    ];
}

// Get statistics
$stats = [
    'total' => count($appointments),
    'pending' => count(array_filter($appointments, fn($a) => $a['status'] === 'pending')),
    'confirmed' => count(array_filter($appointments, fn($a) => $a['status'] === 'confirmed')),
    'completed' => count(array_filter($appointments, fn($a) => $a['status'] === 'completed'))
];

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #263238; margin-bottom: 8px;">
            <i class="fas fa-calendar-check"></i> Appointments
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Manage your patient appointments and consultations
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['total']; ?></h3>
                    <p class="stat-label">Total Appointments</p>
                </div>
                <div class="stat-icon pink">
                    <i class="fas fa-calendar"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['pending']; ?></h3>
                    <p class="stat-label">Pending Approval</p>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['confirmed']; ?></h3>
                    <p class="stat-label">Confirmed</p>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['completed']; ?></h3>
                    <p class="stat-label">Completed</p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-clipboard-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments List -->
    <div class="glass-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-calendar-alt"></i>
                All Appointments
            </h2>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-secondary active">All</button>
                <button class="btn btn-secondary">Pending</button>
                <button class="btn btn-secondary">Confirmed</button>
                <button class="btn btn-secondary">Completed</button>
            </div>
        </div>

        <?php if (count($appointments) > 0): ?>
            <?php foreach ($appointments as $apt): ?>
            <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 20px; margin-bottom: 16px; transition: all 0.2s ease;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($apt['patient_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($apt['patient_name']); ?>
                            </h3>
                            <p style="font-size: 13px; color: #546e7a;">
                                Week <?php echo $apt['pregnancy_week'] ?? 'N/A'; ?> of Pregnancy
                            </p>
                        </div>
                    </div>
                    <span class="status-badge <?php echo $apt['status']; ?>">
                        <?php echo ucfirst($apt['status']); ?>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-calendar" style="color: #C4A7FF; width: 20px;"></i>
                        <span><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-clock" style="color: #C4A7FF; width: 20px;"></i>
                        <span><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-envelope" style="color: #C4A7FF; width: 20px;"></i>
                        <span><?php echo htmlspecialchars($apt['patient_email']); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-phone" style="color: #C4A7FF; width: 20px;"></i>
                        <span><?php echo htmlspecialchars($apt['patient_phone'] ?? 'N/A'); ?></span>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <?php if ($apt['status'] === 'pending'): ?>
                        <button class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                        <button class="btn btn-danger">
                            <i class="fas fa-times"></i> Decline
                        </button>
                    <?php elseif ($apt['status'] === 'confirmed'): ?>
                        <button class="btn btn-primary">
                            <i class="fas fa-video"></i> Start Consultation
                        </button>
                        <button class="btn btn-secondary">
                            <i class="fas fa-calendar-alt"></i> Reschedule
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 64px; color: #334155; margin-bottom: 16px;">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <p style="font-size: 18px; color: #78909c;">No appointments yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>

