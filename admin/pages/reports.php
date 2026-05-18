<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$pageTitle = 'Reports & Analytics';

// Create manual_reports table if it doesn't exist
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `manual_reports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `description` text,
            `report_type` varchar(100) NOT NULL,
            `patient_id` int(11) DEFAULT NULL,
            `doctor_id` int(11) DEFAULT NULL,
            `report_data` longtext,
            `file_path` varchar(500) DEFAULT NULL,
            `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
            `created_by` int(11) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `patient_id` (`patient_id`),
            KEY `doctor_id` (`doctor_id`),
            KEY `created_by` (`created_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Table might already exist, continue
}

// Get manual reports
$manualReports = [];
try {
    // First check if manual_reports table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'manual_reports'");
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("
            SELECT mr.*, 
                   u.name as patient_name, 
                   d.name as doctor_name, 
                   COALESCE(a.name, 'Admin') as created_by_name
            FROM manual_reports mr
            LEFT JOIN users u ON mr.patient_id = u.id
            LEFT JOIN doctors d ON mr.doctor_id = d.id
            LEFT JOIN admins a ON mr.created_by = a.id
            ORDER BY mr.created_at DESC
        ");
        $stmt->execute();
        $manualReports = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("Error fetching manual reports: " . $e->getMessage());
    $manualReports = []; // Ensure it's an empty array on error
}

// Get users and doctors for dropdowns
$users = [];
$doctors = [];
try {
    $stmt = $pdo->query("SELECT id, name, email FROM users WHERE status = 'active' ORDER BY name");
    $users = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT id, name, specialization FROM doctors ORDER BY name");
    $doctors = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching users/doctors: " . $e->getMessage());
    $users = [];
    $doctors = [];
}

// Generate realistic dummy metrics for maternal healthcare
function generateDummyMetrics() {
    $months = [];
    for ($i = 11; $i >= 0; $i--) {
        $months[] = date('Y-m', strtotime("-$i months"));
    }
    
    return [
        'months' => $months,
        'userRegistrations' => [45, 52, 38, 67, 71, 59, 84, 92, 78, 103, 89, 95],
        'appointments' => [156, 178, 142, 203, 189, 167, 234, 251, 198, 276, 243, 289],
        'emergencyCalls' => [12, 8, 15, 6, 11, 9, 14, 7, 13, 10, 16, 8],
        'prenatalVisits' => [134, 156, 118, 189, 172, 145, 208, 231, 187, 254, 221, 243]
    ];
}

// Generate comprehensive dummy metrics
$dummyMetrics = generateDummyMetrics();

// Key Performance Indicators (KPIs)
$kpis = [
    'totalPatients' => 1247,
    'activePregnancies' => 342,
    'patientSatisfaction' => 94.7,
    'emergencyResponseTime' => '8.5',
    'successfulDeliveries' => 98.7,
    'appointmentCompletionRate' => 92.1
];

// Patient Demographics
$demographics = [
    'ageGroups' => [
        '18-25' => 28.4,
        '26-30' => 35.7,
        '31-35' => 24.1,
        '36-40' => 9.8,
        '40+' => 2.0
    ],
    'riskLevels' => [
        'Low Risk' => 67.8,
        'Moderate Risk' => 18.5,
        'High Risk' => 13.7
    ]
];

// Health Outcomes
$healthOutcomes = [
    'deliveryTypes' => [
        'Natural Delivery' => 72.4,
        'C-Section' => 24.1,
        'Assisted Delivery' => 3.5
    ]
];

// Operational Metrics
$operationalMetrics = [
    'avgConsultationTime' => 28.5,
    'bedOccupancyRate' => 76.8,
    'staffEfficiency' => 91.5,
    'costPerPatient' => 2847
];

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="content-container">
    <style>
        .dropdown {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            z-index: 9999 !important;
            background: rgba(15, 23, 42, 0.98) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2) !important;
            border-radius: 8px;
            padding: 8px;
            min-width: 200px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            margin-top: 8px;
        }
        
        .dropdown-item {
            display: block !important;
            padding: 8px 12px !important;
            color: #fff !important;
            text-decoration: none !important;
            border-radius: 4px !important;
            margin-bottom: 4px !important;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: rgba(244, 114, 182, 0.2) !important;
            color: #C4A7FF !important;
        }
        
        .dropdown-item:last-child {
            margin-bottom: 0 !important;
        }
    </style>
    <!-- Page Header -->
    <div class="section-block">
        <div class="section-header">
            <h3>📊 Maternal Health Analytics Dashboard</h3>
            <div style="display: flex; gap: 12px;">
                <select id="reportPeriod" class="form-control" style="width: auto; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                    <option value="12">Last 12 Months</option>
                    <option value="6">Last 6 Months</option>
                    <option value="3">Last 3 Months</option>
                    <option value="1">This Month</option>
                </select>
                <div class="dropdown" style="position: relative;">
                    <button onclick="toggleDropdown('exportDropdown')" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Export Reports
                    </button>
                    <div id="exportDropdown" class="dropdown-menu" style="display: none;">
                        <a href="../actions/export_data.php?type=users&format=csv" class="dropdown-item">
                            <i class="fas fa-users"></i> Patient Data (CSV)
                        </a>
                        <a href="../actions/export_data.php?type=doctors&format=csv" class="dropdown-item">
                            <i class="fas fa-user-md"></i> Doctor Performance (CSV)
                        </a>
                        <a href="../actions/export_data.php?type=appointments&format=csv" class="dropdown-item">
                            <i class="fas fa-calendar"></i> Appointment Analytics (CSV)
                        </a>
                        <a href="#" onclick="generateFullReport()" class="dropdown-item">
                            <i class="fas fa-file-pdf"></i> Complete Report (PDF)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="section-block">
        <div class="section-header">
            <h3>🎯 Key Performance Indicators</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="kpi-card" style="background: linear-gradient(135deg, rgba(244, 114, 182, 0.2) 0%, rgba(196, 167, 255, 0.1) 100%); border: 1px solid rgba(244, 114, 182, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: #C4A7FF; margin-bottom: 8px;"><?php echo number_format($kpis['totalPatients']); ?></div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 4px;">Total Patients</div>
                <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-up"></i> +12.3% vs last month</div>
            </div>
            
            <div class="kpi-card" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.1) 100%); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: #10b981; margin-bottom: 8px;"><?php echo number_format($kpis['activePregnancies']); ?></div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 4px;">Active Pregnancies</div>
                <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-up"></i> +8.7% vs last month</div>
            </div>
            
            <div class="kpi-card" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(37, 99, 235, 0.1) 100%); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: #3b82f6; margin-bottom: 8px;"><?php echo $kpis['patientSatisfaction']; ?>%</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 4px;">Patient Satisfaction</div>
                <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-up"></i> +2.1% vs last month</div>
            </div>
            
            <div class="kpi-card" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.1) 100%); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: #f59e0b; margin-bottom: 8px;"><?php echo $kpis['emergencyResponseTime']; ?>m</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 4px;">Avg Response Time</div>
                <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-down"></i> -1.2m vs last month</div>
            </div>
            
            <div class="kpi-card" style="background: linear-gradient(135deg, rgba(139, 69, 19, 0.2) 0%, rgba(120, 53, 15, 0.1) 100%); border: 1px solid rgba(139, 69, 19, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: #8b4513; margin-bottom: 8px;"><?php echo $kpis['successfulDeliveries']; ?>%</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 4px;">Successful Deliveries</div>
                <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-up"></i> +0.3% vs last month</div>
            </div>
            
            <div class="kpi-card" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.2) 0%, rgba(147, 51, 234, 0.1) 100%); border: 1px solid rgba(168, 85, 247, 0.3); border-radius: 12px; padding: 20px; text-align: center;">
                <div style="font-size: 2.5rem; font-weight: 800; color: #a855f7; margin-bottom: 8px;"><?php echo $kpis['appointmentCompletionRate']; ?>%</div>
                <div style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 4px;">Appointment Completion</div>
                <div style="color: #10b981; font-size: 0.8rem;"><i class="fas fa-arrow-up"></i> +1.8% vs last month</div>
            </div>
        </div>
    </div>

    <!-- Manual Reports Section -->
    <div class="section-block">
        <div class="section-header">
            <h3>📝 Manual Reports Management</h3>
            <div style="display: flex; gap: 12px;">
                <button onclick="openCreateReportModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Report
                </button>
                <button onclick="toggleReportsView()" class="btn btn-secondary" id="toggleViewBtn">
                    <i class="fas fa-list"></i> View All Reports
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i>
            <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
            switch($_GET['success']) {
                case 'report_created': echo 'Report created successfully!'; break;
                case 'report_updated': echo 'Report updated successfully!'; break;
                case 'report_deleted': echo 'Report deleted successfully!'; break;
                case 'status_updated': echo 'Report status updated successfully!'; break;
                default: echo 'Operation completed successfully!';
            }
            ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
            <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
            switch($_GET['error']) {
                case 'missing_fields': echo 'Please fill in all required fields.'; break;
                case 'creation_failed': echo 'Failed to create report. Please try again.'; break;
                case 'operation_failed': echo 'Operation failed. Please try again.'; break;
                default: echo 'An error occurred. Please try again.';
            }
            ?>
        </div>
        <?php endif; ?>

        <!-- Reports List -->
        <div id="reportsListSection" style="display: none;">
            <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 20px;">
                <h4 style="color: #C4A7FF; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-alt"></i> All Manual Reports (<?php echo count($manualReports); ?>)
                </h4>
                
                <?php if (empty($manualReports)): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                    <i class="fas fa-file-plus" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>No manual reports created yet.</p>
                    <button onclick="openCreateReportModal()" class="btn btn-primary" style="margin-top: 16px;">
                        <i class="fas fa-plus"></i> Create Your First Report
                    </button>
                </div>
                <?php else: ?>
                <div class="reports-grid" style="display: grid; gap: 16px;">
                    <?php foreach ($manualReports as $report): ?>
                    <div class="report-card" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; transition: all 0.3s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="flex: 1;">
                                <h5 style="color: var(--text-primary); margin-bottom: 4px; font-size: 16px; font-weight: 600;">
                                    <?php echo htmlspecialchars($report['title']); ?>
                                </h5>
                                <div style="display: flex; gap: 12px; margin-bottom: 8px;">
                                    <span class="badge" style="background: rgba(244, 114, 182, 0.2); color: #C4A7FF; padding: 4px 8px; border-radius: 12px; font-size: 11px;">
                                        <?php echo htmlspecialchars($report['report_type']); ?>
                                    </span>
                                    <span class="badge badge-<?php echo $report['status']; ?>" style="padding: 4px 8px; border-radius: 12px; font-size: 11px;">
                                        <?php echo ucfirst($report['status']); ?>
                                    </span>
                                </div>
                                <?php if ($report['description']): ?>
                                <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">
                                    <?php echo htmlspecialchars(substr($report['description'], 0, 100)) . (strlen($report['description']) > 100 ? '...' : ''); ?>
                                </p>
                                <?php endif; ?>
                                <div style="font-size: 12px; color: var(--text-secondary);">
                                    <?php if ($report['patient_name']): ?>
                                    <span><i class="fas fa-user"></i> Patient: <?php echo htmlspecialchars($report['patient_name']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($report['doctor_name']): ?>
                                    <span style="margin-left: 16px;"><i class="fas fa-user-doctor"></i> Doctor: <?php echo htmlspecialchars($report['doctor_name']); ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <span><i class="fas fa-clock"></i> Created: <?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></span>
                                    <span style="margin-left: 16px;"><i class="fas fa-user-shield"></i> By: <?php echo htmlspecialchars($report['created_by_name'] ?? 'Admin'); ?></span>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; margin-left: 16px;">
                                <button onclick="viewReport(<?php echo $report['id']; ?>)" class="btn-icon" title="View Report">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editReport(<?php echo $report['id']; ?>)" class="btn-icon" title="Edit Report">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="toggleReportStatus(<?php echo $report['id']; ?>)" class="btn-icon" title="Toggle Status">
                                    <i class="fas fa-toggle-<?php echo $report['status'] === 'published' ? 'on' : 'off'; ?>"></i>
                                </button>
                                <button onclick="deleteReport(<?php echo $report['id']; ?>)" class="btn-icon btn-danger" title="Delete Report">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Patient Registration Trends -->
        <div class="section-block">
            <div class="section-header">
                <h3>👥 Patient Registration Trends</h3>
            </div>
            <canvas id="userChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Appointment Analytics -->
        <div class="section-block">
            <div class="section-header">
                <h3>📅 Appointment Analytics</h3>
            </div>
            <canvas id="appointmentChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Health Outcomes & Demographics -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <!-- Patient Demographics -->
        <div class="section-block">
            <div class="section-header">
                <h3>👶 Patient Demographics</h3>
            </div>
            <canvas id="demographicsChart" style="max-height: 300px;"></canvas>
        </div>

        <!-- Health Outcomes -->
        <div class="section-block">
            <div class="section-header">
                <h3>🏥 Delivery Outcomes</h3>
            </div>
            <canvas id="outcomesChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Operational Metrics -->
    <div class="section-block">
        <div class="section-header">
            <h3>⚡ Operational Performance</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 1.8rem; font-weight: 700; color: #C4A7FF; margin-bottom: 8px;"><?php echo $operationalMetrics['avgConsultationTime']; ?>m</div>
                <div style="color: var(--text-secondary); font-size: 0.85rem;">Avg Consultation Time</div>
            </div>
            <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 1.8rem; font-weight: 700; color: #10b981; margin-bottom: 8px;"><?php echo $operationalMetrics['bedOccupancyRate']; ?>%</div>
                <div style="color: var(--text-secondary); font-size: 0.85rem;">Bed Occupancy Rate</div>
            </div>
            <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 1.8rem; font-weight: 700; color: #3b82f6; margin-bottom: 8px;"><?php echo $operationalMetrics['staffEfficiency']; ?>%</div>
                <div style="color: var(--text-secondary); font-size: 0.85rem;">Staff Efficiency</div>
            </div>
            <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">
                <div style="font-size: 1.8rem; font-weight: 700; color: #f59e0b; margin-bottom: 8px;">₹<?php echo number_format($operationalMetrics['costPerPatient']); ?></div>
                <div style="color: var(--text-secondary); font-size: 0.85rem;">Cost Per Patient</div>
            </div>
        </div>
    </div>

    <!-- Doctor Performance -->
    <div class="section-block">
        <div class="section-header">
            <h3>👨‍⚕️ Doctor Performance Analytics</h3>
        </div>
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Doctor Name</th>
                        <th>Specialization</th>
                        <th>Appointments</th>
                        <th>Satisfaction</th>
                        <th>Success Rate</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                    $doctorPerformance = [
                        ['name' => 'Dr. Sarah Johnson', 'spec' => 'Obstetrician', 'appointments' => 89, 'satisfaction' => 96.2, 'success' => 98.9],
                        ['name' => 'Dr. Michael Chen', 'spec' => 'Gynecologist', 'appointments' => 76, 'satisfaction' => 94.8, 'success' => 97.4],
                        ['name' => 'Dr. Priya Sharma', 'spec' => 'Maternal-Fetal Medicine', 'appointments' => 67, 'satisfaction' => 97.1, 'success' => 99.2],
                        ['name' => 'Dr. James Wilson', 'spec' => 'Perinatologist', 'appointments' => 54, 'satisfaction' => 95.3, 'success' => 98.1],
                        ['name' => 'Dr. Emily Brown', 'spec' => 'Midwife Specialist', 'appointments' => 92, 'satisfaction' => 98.7, 'success' => 99.8]
                    ];
                    
                    foreach($doctorPerformance as $doctor): ?>
                    <tr>
                        <td><strong><?php echo $doctor['name']; ?></strong></td>
                        <td><?php echo $doctor['spec']; ?></td>
                        <td><?php echo $doctor['appointments']; ?></td>
                        <td>
                            <span style="color: <?php echo $doctor['satisfaction'] >= 95 ? '#10b981' : ($doctor['satisfaction'] >= 90 ? '#f59e0b' : '#ef4444'); ?>;">
                                <?php echo $doctor['satisfaction']; ?>%
                            </span>
                        </td>
                        <td>
                            <span style="color: <?php echo $doctor['success'] >= 98 ? '#10b981' : ($doctor['success'] >= 95 ? '#f59e0b' : '#ef4444'); ?>;">
                                <?php echo $doctor['success']; ?>%
                            </span>
                        </td>
                        <td><span class="badge badge-success">Active</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart.js default configuration
Chart.defaults.color = 'var(--text-secondary)';
Chart.defaults.borderColor = 'rgba(255,255,255,0.1)';

// Patient Registration Chart
const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dummyMetrics['months']); ?>,
        datasets: [{
            label: 'New Patients',
            data: <?php echo json_encode($dummyMetrics['userRegistrations']); ?>,
            borderColor: '#C4A7FF',
            backgroundColor: 'rgba(244, 114, 182, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { color: 'var(--text-secondary)' } }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.1)' },
                ticks: { color: 'var(--text-secondary)' }
            },
            x: { 
                grid: { color: 'rgba(255,255,255,0.1)' },
                ticks: { color: 'var(--text-secondary)' }
            }
        }
    }
});

// Appointment Analytics Chart
const aptCtx = document.getElementById('appointmentChart').getContext('2d');
new Chart(aptCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($dummyMetrics['months']); ?>,
        datasets: [
            {
                label: 'Appointments',
                data: <?php echo json_encode($dummyMetrics['appointments']); ?>,
                backgroundColor: '#C4A7FF',
                borderRadius: 4
            },
            {
                label: 'Prenatal Visits',
                data: <?php echo json_encode($dummyMetrics['prenatalVisits']); ?>,
                backgroundColor: '#10b981',
                borderRadius: 4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { labels: { color: 'var(--text-secondary)' } }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(255,255,255,0.1)' },
                ticks: { color: 'var(--text-secondary)' }
            },
            x: { 
                grid: { color: 'rgba(255,255,255,0.1)' },
                ticks: { color: 'var(--text-secondary)' }
            }
        }
    }
});

// Demographics Pie Chart
const demoCtx = document.getElementById('demographicsChart').getContext('2d');
new Chart(demoCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($demographics['ageGroups'])); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($demographics['ageGroups'])); ?>,
            backgroundColor: ['#C4A7FF', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
            borderWidth: 2,
            borderColor: '#E9D5FF'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { color: 'var(--text-secondary)', padding: 20 }
            }
        }
    }
});

// Health Outcomes Chart
const outcomesCtx = document.getElementById('outcomesChart').getContext('2d');
new Chart(outcomesCtx, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_keys($healthOutcomes['deliveryTypes'])); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($healthOutcomes['deliveryTypes'])); ?>,
            backgroundColor: ['#10b981', '#C4A7FF', '#f59e0b'],
            borderWidth: 2,
            borderColor: '#E9D5FF'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { 
                position: 'bottom',
                labels: { color: 'var(--text-secondary)', padding: 20 }
            }
        }
    }
});

// Utility Functions
function toggleDropdown(id) {
    const dropdown = document.getElementById(id);
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

function generateFullReport() {
    alert('📊 Generating comprehensive PDF report...\n\nThis feature would generate a complete maternal health analytics report including:\n\n• Executive Summary\n• Patient Demographics\n• Health Outcomes Analysis\n• Doctor Performance Metrics\n• Operational Efficiency\n• Risk Assessment\n• Recommendations\n\nReport will be available for download shortly.');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});

// Report period filter
document.getElementById('reportPeriod').addEventListener('change', function() {
    const period = this.value;
    console.log('Filtering reports for period:', period + ' months');
});
</script>

<!-- Manual Reports Modal -->
<div id="reportModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeReportModal()"></div>
    <div class="modal-content" style="max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 id="modalTitle">Create New Report</h3>
            <button class="modal-close" onclick="closeReportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="reportForm" method="POST" action="../actions/create_report.php">
                <input type="hidden" id="reportId" name="report_id" value="">
                <input type="hidden" id="formAction" name="action" value="create">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="reportTitle" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                            Report Title <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="reportTitle" name="title" required 
                               style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary);">
                    </div>
                    
                    <div class="form-group">
                        <label for="reportType" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                            Report Type <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="reportType" name="report_type" required 
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary);">
                            <option value="">Select Type</option>
                            <option value="patient_assessment">Patient Assessment</option>
                            <option value="medical_examination">Medical Examination</option>
                            <option value="lab_results">Lab Results</option>
                            <option value="ultrasound_report">Ultrasound Report</option>
                            <option value="prenatal_checkup">Prenatal Checkup</option>
                            <option value="delivery_report">Delivery Report</option>
                            <option value="postpartum_care">Postpartum Care</option>
                            <option value="emergency_report">Emergency Report</option>
                            <option value="consultation_notes">Consultation Notes</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label for="patientSelect" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                            Patient (Optional)
                        </label>
                        <select id="patientSelect" name="patient_id" 
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary);">
                            <option value="">Select Patient</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['email']) . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="doctorSelect" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                            Doctor (Optional)
                        </label>
                        <select id="doctorSelect" name="doctor_id" 
                                style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary);">
                            <option value="">Select Doctor</option>
                            <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo $doctor['id']; ?>">
                                <?php echo htmlspecialchars($doctor['name']) . ' - ' . htmlspecialchars($doctor['specialization']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="reportDescription" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                        Description
                    </label>
                    <textarea id="reportDescription" name="description" rows="3" 
                              style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary); resize: vertical;"
                              placeholder="Brief description of the report..."></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="reportData" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                        Report Content <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="reportData" name="report_data" rows="8" required 
                              style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary); resize: vertical; font-family: monospace;"
                              placeholder="Enter the detailed report content here...&#10;&#10;You can include:&#10;- Patient vitals&#10;- Examination findings&#10;- Test results&#10;- Recommendations&#10;- Follow-up instructions"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label for="reportStatus" style="color: #C4A7FF; font-weight: 600; margin-bottom: 8px; display: block;">
                        Status
                    </label>
                    <select id="reportStatus" name="status" 
                            style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; color: var(--text-primary);">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" onclick="closeReportModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span id="submitBtnText">Create Report</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div id="viewReportModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeViewReportModal()"></div>
    <div class="modal-content" style="max-width: 900px; width: 95%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 id="viewModalTitle">Report Details</h3>
            <button class="modal-close" onclick="closeViewReportModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="viewReportContent">
            <!-- Report content will be loaded here -->
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
}

.modal-content {
    position: relative;
    background: linear-gradient(135deg, rgba(45, 27, 61, 0.98), rgba(26, 10, 31, 0.98));
    border: 1px solid rgba(196, 167, 255, 0.3);
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(196, 167, 255, 0.3);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid var(--glass-border);
    background: linear-gradient(135deg, #C4A7FF, #C4A7FF);
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-size: 20px;
    font-weight: 700;
}

.modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    color: var(--text-primary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 24px;
    color: var(--bg-dark);
}

.btn-icon {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    width: 32px;
    height: 32px;
    border-radius: 6px;
    color: var(--text-secondary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.btn-icon:hover {
    background: rgba(244, 114, 182, 0.2);
    border-color: #C4A7FF;
    color: #C4A7FF;
}

.btn-icon.btn-danger:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: #ef4444;
    color: #ef4444;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}

.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

.badge-draft {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
}

.badge-published {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.badge-archived {
    background: rgba(107, 114, 128, 0.2);
    color: #9ca3af;
}

.report-card:hover {
    background: rgba(255,255,255,0.05) !important;
    border-color: rgba(244, 114, 182, 0.3) !important;
    transform: translateY(-2px);
}
</style>

<script>
// Manual Reports JavaScript Functions
let currentReportId = null;

function toggleReportsView() {
    const section = document.getElementById('reportsListSection');
    const btn = document.getElementById('toggleViewBtn');
    
    if (section.style.display === 'none') {
        section.style.display = 'block';
        btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Reports';
    } else {
        section.style.display = 'none';
        btn.innerHTML = '<i class="fas fa-list"></i> View All Reports';
    }
}

function openCreateReportModal() {
    document.getElementById('modalTitle').textContent = 'Create New Report';
    document.getElementById('submitBtnText').textContent = 'Create Report';
    document.getElementById('reportForm').action = '../actions/create_report.php';
    document.getElementById('formAction').value = 'create';
    document.getElementById('reportId').value = '';
    
    // Reset form
    document.getElementById('reportForm').reset();
    
    document.getElementById('reportModal').style.display = 'flex';
}

function editReport(reportId) {
    // Get report data from PHP array
    const reports = <?php echo json_encode($manualReports); ?>;
    const reportData = reports.find(r => r.id == reportId);
    
    if (reportData) {
        document.getElementById('modalTitle').textContent = 'Edit Report';
        document.getElementById('submitBtnText').textContent = 'Update Report';
        document.getElementById('reportForm').action = '../actions/manage_report.php';
        document.getElementById('formAction').value = 'update';
        document.getElementById('reportId').value = reportId;
        
        // Populate form fields
        document.getElementById('reportTitle').value = reportData.title || '';
        document.getElementById('reportType').value = reportData.report_type || '';
        document.getElementById('patientSelect').value = reportData.patient_id || '';
        document.getElementById('doctorSelect').value = reportData.doctor_id || '';
        document.getElementById('reportDescription').value = reportData.description || '';
        document.getElementById('reportData').value = reportData.report_data || '';
        document.getElementById('reportStatus').value = reportData.status || 'draft';
        
        currentReportId = reportId;
        document.getElementById('reportModal').style.display = 'flex';
    } else {
        alert('Report not found!');
    }
}

function viewReport(reportId) {
    // Find the report data from the existing reports
    const reportCards = document.querySelectorAll('.report-card');
    let reportData = null;
    
    // Get report data from PHP (we'll add this data to the page)
    const reports = <?php echo json_encode($manualReports); ?>;
    reportData = reports.find(r => r.id == reportId);
    
    if (reportData) {
        const statusBadge = getStatusBadge(reportData.status);
        const typeBadge = `<span class="badge" style="background: rgba(244, 114, 182, 0.2); color: #C4A7FF; padding: 4px 8px; border-radius: 12px; font-size: 11px;">${reportData.report_type}</span>`;
        
        document.getElementById('viewModalTitle').textContent = reportData.title;
        document.getElementById('viewReportContent').innerHTML = `
            <div style="margin-bottom: 24px;">
                <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                    ${typeBadge}
                    ${statusBadge}
                </div>
                
                ${reportData.description ? `
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #C4A7FF; margin-bottom: 8px; font-size: 14px; font-weight: 600;">DESCRIPTION</h4>
                    <p style="color: #e2e8f0; line-height: 1.6;">${reportData.description}</p>
                </div>
                ` : ''}
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    ${reportData.patient_name ? `
                    <div>
                        <h4 style="color: #C4A7FF; margin-bottom: 8px; font-size: 14px; font-weight: 600;">PATIENT</h4>
                        <p style="color: #e2e8f0;"><i class="fas fa-user"></i> ${reportData.patient_name}</p>
                    </div>
                    ` : ''}
                    
                    ${reportData.doctor_name ? `
                    <div>
                        <h4 style="color: #C4A7FF; margin-bottom: 8px; font-size: 14px; font-weight: 600;">DOCTOR</h4>
                        <p style="color: #e2e8f0;"><i class="fas fa-user-doctor"></i> ${reportData.doctor_name}</p>
                    </div>
                    ` : ''}
                </div>
                
                <div style="margin-bottom: 20px;">
                    <h4 style="color: #C4A7FF; margin-bottom: 8px; font-size: 14px; font-weight: 600;">REPORT CONTENT</h4>
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 16px; font-family: monospace; white-space: pre-wrap; color: #e2e8f0; line-height: 1.6;">${reportData.report_data || 'No content available'}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; font-size: 12px; color: var(--text-secondary); border-top: 1px solid rgba(255,255,255,0.1); padding-top: 16px;">
                    <div>
                        <strong>Created:</strong> ${new Date(reportData.created_at).toLocaleString()}
                    </div>
                    <div>
                        <strong>Created By:</strong> ${reportData.created_by_name || 'Admin'}
                    </div>
                </div>
                
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <button onclick="printReport(${reportId})" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button onclick="editReport(${reportId}); closeViewReportModal();" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Report
                    </button>
                </div>
            </div>
        `;
    } else {
        document.getElementById('viewReportContent').innerHTML = `
            <div style="text-align: center; padding: 40px; color: var(--text-secondary);">
                <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 16px; color: #f59e0b;"></i>
                <p>Report not found</p>
            </div>
        `;
    }
    
    document.getElementById('viewReportModal').style.display = 'flex';
}

function getStatusBadge(status) {
    const badges = {
        'draft': '<span class="badge badge-draft" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; padding: 4px 8px; border-radius: 12px; font-size: 11px;">Draft</span>',
        'published': '<span class="badge badge-published" style="background: rgba(34, 197, 94, 0.2); color: #22c55e; padding: 4px 8px; border-radius: 12px; font-size: 11px;">Published</span>',
        'archived': '<span class="badge badge-archived" style="background: rgba(107, 114, 128, 0.2); color: #9ca3af; padding: 4px 8px; border-radius: 12px; font-size: 11px;">Archived</span>'
    };
    return badges[status] || badges['draft'];
}

function toggleReportStatus(reportId) {
    if (confirm('Are you sure you want to toggle the status of this report?')) {
        window.location.href = `../actions/manage_report.php?action=toggle_status&report_id=${reportId}`;
    }
}

function deleteReport(reportId) {
    if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
        window.location.href = `../actions/manage_report.php?action=delete&report_id=${reportId}`;
    }
}

function printReport(reportId) {
    // Get report data
    const reports = <?php echo json_encode($manualReports); ?>;
    const reportData = reports.find(r => r.id == reportId);
    
    if (reportData) {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Medical Report - ${reportData.title}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; color: var(--text-primary); }
                    .header { text-align: center; border-bottom: 2px solid #C4A7FF; padding-bottom: 20px; margin-bottom: 30px; }
                    .logo { font-size: 24px; font-weight: bold; color: #C4A7FF; margin-bottom: 10px; }
                    .clinic-info { font-size: 14px; color: #666; }
                    .report-title { font-size: 20px; font-weight: bold; margin: 20px 0; }
                    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 20px 0; }
                    .info-item { margin-bottom: 10px; }
                    .label { font-weight: bold; color: #555; }
                    .content { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; white-space: pre-wrap; }
                    .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--glass-border); font-size: 12px; color: #666; }
                    @media print { body { margin: 20px; } }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="logo">🤱 Aarunya Maternal Care</div>
                    <div class="clinic-info">
                        Comprehensive Maternal Healthcare System<br>
                        Phone: +91-XXXX-XXXX | Email: info@aarunya.com
                    </div>
                </div>
                
                <div class="report-title">${reportData.title}</div>
                
                <div class="info-grid">
                    <div>
                        <div class="info-item">
                            <span class="label">Report Type:</span> ${reportData.report_type}
                        </div>
                        ${reportData.patient_name ? `
                        <div class="info-item">
                            <span class="label">Patient:</span> ${reportData.patient_name}
                        </div>
                        ` : ''}
                        <div class="info-item">
                            <span class="label">Status:</span> ${reportData.status.charAt(0).toUpperCase() + reportData.status.slice(1)}
                        </div>
                    </div>
                    <div>
                        ${reportData.doctor_name ? `
                        <div class="info-item">
                            <span class="label">Doctor:</span> ${reportData.doctor_name}
                        </div>
                        ` : ''}
                        <div class="info-item">
                            <span class="label">Created:</span> ${new Date(reportData.created_at).toLocaleDateString()}
                        </div>
                        <div class="info-item">
                            <span class="label">Report ID:</span> #${reportData.id}
                        </div>
                    </div>
                </div>
                
                ${reportData.description ? `
                <div>
                    <div class="label">Description:</div>
                    <div style="margin: 10px 0; padding: 10px; background: var(--bg-dark); border-radius: 5px;">${reportData.description}</div>
                </div>
                ` : ''}
                
                <div>
                    <div class="label">Report Content:</div>
                    <div class="content">${reportData.report_data || 'No content available'}</div>
                </div>
                
                <div class="footer">
                    <div>Generated on: ${new Date().toLocaleString()}</div>
                    <div>This is a computer-generated report from Aarunya Maternal Care System</div>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }
}

function closeReportModal() {
    document.getElementById('reportModal').style.display = 'none';
    currentReportId = null;
}

function closeViewReportModal() {
    document.getElementById('viewReportModal').style.display = 'none';
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        closeReportModal();
        closeViewReportModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReportModal();
        closeViewReportModal();
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Add form validation
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            const title = document.getElementById('reportTitle').value.trim();
            const reportType = document.getElementById('reportType').value;
            const reportData = document.getElementById('reportData').value.trim();
            
            if (!title) {
                e.preventDefault();
                alert('Please enter a report title.');
                document.getElementById('reportTitle').focus();
                return false;
            }
            
            if (!reportType) {
                e.preventDefault();
                alert('Please select a report type.');
                document.getElementById('reportType').focus();
                return false;
            }
            
            if (!reportData) {
                e.preventDefault();
                alert('Please enter the report content.');
                document.getElementById('reportData').focus();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.querySelector('#reportForm button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            submitBtn.disabled = true;
            
            // Re-enable button after 5 seconds (in case of slow response)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    }
});

// Add search functionality for reports
function searchReports() {
    const searchTerm = document.getElementById('reportSearch').value.toLowerCase();
    const reportCards = document.querySelectorAll('.report-card');
    
    reportCards.forEach(card => {
        const title = card.querySelector('h5').textContent.toLowerCase();
        const description = card.querySelector('p') ? card.querySelector('p').textContent.toLowerCase() : '';
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Add keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + N to create new report
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        openCreateReportModal();
    }
    
    // Ctrl/Cmd + F to focus search (if search exists)
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        const searchInput = document.getElementById('reportSearch');
        if (searchInput) {
            e.preventDefault();
            searchInput.focus();
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>

