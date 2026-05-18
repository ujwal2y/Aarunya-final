<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Emergency Cases';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

// Dummy emergency cases
$emergencies = [
    [
        'id' => 1,
        'patient_name' => 'Jennifer Smith',
        'patient_phone' => '+1-555-0199',
        'emergency_type' => 'Severe Bleeding',
        'description' => 'Patient experiencing heavy bleeding at 28 weeks pregnancy',
        'priority' => 'critical',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
        'pregnancy_week' => 28
    ],
    [
        'id' => 2,
        'patient_name' => 'Amanda Davis',
        'patient_phone' => '+1-555-0188',
        'emergency_type' => 'Severe Pain',
        'description' => 'Intense abdominal pain, possible complications',
        'priority' => 'high',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
        'pregnancy_week' => 35
    ],
    [
        'id' => 3,
        'patient_name' => 'Rachel Green',
        'patient_phone' => '+1-555-0177',
        'emergency_type' => 'Premature Labor',
        'description' => 'Signs of premature labor at 32 weeks',
        'priority' => 'critical',
        'status' => 'resolved',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'pregnancy_week' => 32
    ]
];

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">
            <i class="fas fa-exclamation-triangle"></i> Emergency Cases
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Monitor and respond to emergency maternal care situations
        </p>
    </div>

    <!-- Emergency Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 32px;">
        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number" style="color: #ef4444;">2</h3>
                    <p class="stat-label">Active Emergencies</p>
                </div>
                <div class="stat-icon red">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number" style="color: #f59e0b;">8.5m</h3>
                    <p class="stat-label">Avg Response Time</p>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number" style="color: #22c55e;">15</h3>
                    <p class="stat-label">Resolved Today</p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Alert -->
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
        <h3 style="color: #ffffff; margin-bottom: 8px; font-size: 16px;">
            <i class="fas fa-bell" style="color: #ef4444; animation: pulse 2s infinite;"></i> Emergency Protocol
        </h3>
        <p style="color: #546e7a; font-size: 14px; line-height: 1.6; margin: 0;">
            All emergency cases require immediate attention. Critical cases should be responded to within 5 minutes. 
            Contact emergency services (911) for life-threatening situations.
        </p>
    </div>

    <!-- Emergency Cases -->
    <div class="glass-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-ambulance"></i>
                Emergency Cases
            </h2>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-secondary active">All</button>
                <button class="btn btn-secondary">Critical</button>
                <button class="btn btn-secondary">High Priority</button>
                <button class="btn btn-secondary">Resolved</button>
            </div>
        </div>

        <?php foreach ($emergencies as $emergency): ?>
        <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 12px; padding: 20px; margin-bottom: 16px; cursor: pointer; transition: all 0.2s ease;">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(239, 68, 68, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 16px;"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 600; color: #ffffff; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($emergency['patient_name']); ?>
                        </h3>
                        <p style="font-size: 12px; color: #546e7a; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($emergency['patient_phone']); ?> • Week <?php echo $emergency['pregnancy_week']; ?>
                        </p>
                        <p style="font-size: 11px; color: #78909c;">
                            <?php echo date('M d, Y g:i A', strtotime($emergency['created_at'])); ?>
                        </p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                    <span class="status-badge <?php echo $emergency['priority']; ?>" style="
                        <?php if ($emergency['priority'] === 'critical'): ?>
                            background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);
                        <?php elseif ($emergency['priority'] === 'high'): ?>
                            background: rgba(251, 191, 36, 0.2); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);
                        <?php endif; ?>
                    ">
                        <?php echo strtoupper($emergency['priority']); ?> PRIORITY
                    </span>
                    <span class="status-badge <?php echo $emergency['status']; ?>">
                        <?php echo strtoupper($emergency['status']); ?>
                    </span>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; color: #C4A7FF; margin-bottom: 8px;">
                    <?php echo htmlspecialchars($emergency['emergency_type']); ?>
                </h4>
                <p style="font-size: 14px; color: #e2e8f0; line-height: 1.6;">
                    <?php echo htmlspecialchars($emergency['description']); ?>
                </p>
            </div>

            <div style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid rgba(239, 68, 68, 0.2);">
                <?php if ($emergency['status'] === 'active'): ?>
                    <button class="btn btn-danger">
                        <i class="fas fa-phone"></i> Call Patient
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-ambulance"></i> Dispatch Ambulance
                    </button>
                    <button class="btn btn-secondary">
                        <i class="fas fa-notes-medical"></i> Add Notes
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn btn-secondary">
                        <i class="fas fa-file-medical"></i> View Report
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
