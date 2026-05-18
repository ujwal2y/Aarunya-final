<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Health Reports';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

// Dummy reports data
$reports = [
    [
        'id' => 1,
        'patient_name' => 'Sarah Parker',
        'report_type' => 'Prenatal Checkup Report',
        'report_date' => date('Y-m-d', strtotime('-2 days')),
        'pregnancy_week' => 24,
        'status' => 'completed',
        'findings' => 'Normal fetal development, healthy vitals'
    ],
    [
        'id' => 2,
        'patient_name' => 'Maria Johnson',
        'report_type' => 'Ultrasound Report',
        'report_date' => date('Y-m-d', strtotime('-1 week')),
        'pregnancy_week' => 16,
        'status' => 'completed',
        'findings' => 'Gender determination, normal anatomy scan'
    ],
    [
        'id' => 3,
        'patient_name' => 'Emily Wilson',
        'report_type' => 'Blood Test Results',
        'report_date' => date('Y-m-d'),
        'pregnancy_week' => 32,
        'status' => 'pending',
        'findings' => 'Lab results pending analysis'
    ]
];

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">
                <i class="fas fa-file-medical"></i> Health Reports
            </h1>
            <p style="color: #546e7a; font-size: 16px;">
                Generate and manage patient health reports and medical certificates
            </p>
        </div>
        <button class="btn btn-primary" onclick="alert('Create report feature coming soon!')">
            <i class="fas fa-plus"></i> Create Report
        </button>
    </div>

    <!-- Report Stats -->
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 32px;">
        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number">156</h3>
                    <p class="stat-label">Total Reports</p>
                </div>
                <div class="stat-icon pink">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number">12</h3>
                    <p class="stat-label">This Week</p>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-week"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number">3</h3>
                    <p class="stat-label">Pending Review</p>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="glass-card stat-card">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number">98%</h3>
                    <p class="stat-label">Completion Rate</p>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports List -->
    <div class="glass-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-clipboard-list"></i>
                Recent Reports
            </h2>
            <div style="display: flex; gap: 8px;">
                <button class="btn btn-secondary active">All</button>
                <button class="btn btn-secondary">Completed</button>
                <button class="btn btn-secondary">Pending</button>
                <button class="btn btn-secondary">Draft</button>
            </div>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Report Type</th>
                        <th>Date</th>
                        <th>Week</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($report['patient_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="color: #ffffff; font-weight: 500;">
                                        <?php echo htmlspecialchars($report['patient_name']); ?>
                                    </div>
                                    <div style="font-size: 12px; color: #78909c;">
                                        Report #<?php echo str_pad($report['id'], 4, '0', STR_PAD_LEFT); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($report['report_type']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($report['report_date'])); ?></td>
                        <td>Week <?php echo $report['pregnancy_week']; ?></td>
                        <td>
                            <span class="status-badge <?php echo $report['status']; ?>">
                                <?php echo ucfirst($report['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="action-btn view" title="View Report" onclick="alert('View report feature coming soon!')">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" title="Edit Report" onclick="alert('Edit report feature coming soon!')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn view" title="Download PDF" onclick="alert('Download feature coming soon!')">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Report Templates -->
    <div class="glass-card" style="margin-top: 24px;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-file-medical-alt"></i>
                Quick Report Templates
            </h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
            <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <i class="fas fa-heartbeat" style="color: #C4A7FF; font-size: 20px;"></i>
                    <h4 style="font-size: 16px; font-weight: 600; color: #ffffff; margin: 0;">Prenatal Checkup</h4>
                </div>
                <p style="font-size: 13px; color: #546e7a; margin: 0;">Standard prenatal examination report template</p>
            </div>

            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <i class="fas fa-baby" style="color: #3b82f6; font-size: 20px;"></i>
                    <h4 style="font-size: 16px; font-weight: 600; color: #ffffff; margin: 0;">Ultrasound Report</h4>
                </div>
                <p style="font-size: 13px; color: #546e7a; margin: 0;">Detailed ultrasound examination findings</p>
            </div>

            <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <i class="fas fa-vial" style="color: #22c55e; font-size: 20px;"></i>
                    <h4 style="font-size: 16px; font-weight: 600; color: #ffffff; margin: 0;">Lab Results</h4>
                </div>
                <p style="font-size: 13px; color: #546e7a; margin: 0;">Blood work and laboratory test results</p>
            </div>

            <div style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 8px; padding: 16px; cursor: pointer; transition: all 0.2s ease;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <i class="fas fa-certificate" style="color: #a855f7; font-size: 20px;"></i>
                    <h4 style="font-size: 16px; font-weight: 600; color: #ffffff; margin: 0;">Medical Certificate</h4>
                </div>
                <p style="font-size: 13px; color: #546e7a; margin: 0;">Official medical certification documents</p>
            </div>
        </div>
    </div>
</div>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
