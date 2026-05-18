<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireDoctorLogin();

$pageTitle = 'Dashboard';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../client/login.php');
    exit();
}

// Handle AJAX availability toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_availability'])) {
    header('Content-Type: application/json');
    
    // Use intval() — isset() is always true even when value is "0"
    $is_available = intval($_POST['is_available'] ?? 0) ? 1 : 0;
    $availability_note = trim($_POST['availability_note'] ?? '');
    
    // Demo doctor (id=999) has no real DB row — treat as success
    if ($doctor['id'] == 999) {
        echo json_encode(['success' => true]);
        exit;
    }
    
    try {
        $db = getDB();
        // Auto-add columns if they don't exist (safe migration)
        $chk = $db->query("SHOW COLUMNS FROM doctors LIKE 'is_available'");
        if ($chk->rowCount() === 0) {
            $db->exec("ALTER TABLE doctors ADD COLUMN is_available TINYINT(1) DEFAULT 1");
        }
        $chk2 = $db->query("SHOW COLUMNS FROM doctors LIKE 'availability_note'");
        if ($chk2->rowCount() === 0) {
            $db->exec("ALTER TABLE doctors ADD COLUMN availability_note TEXT NULL");
        }
        $stmt = $db->prepare("UPDATE doctors SET is_available = ?, availability_note = ? WHERE id = ?");
        $success = $stmt->execute([$is_available, $availability_note, $doctor['id']]);
        echo json_encode(['success' => true, 'rows' => $stmt->rowCount()]);
    } catch (Exception $e) {
        error_log('Availability toggle error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Get current availability status
$db = getDB();
$checkColumns = $db->query("SHOW COLUMNS FROM doctors LIKE 'is_available'");
$hasAvailability = $checkColumns->rowCount() > 0;

if ($hasAvailability) {
    $stmt = $db->prepare("SELECT is_available, availability_note FROM doctors WHERE id = ?");
    $stmt->execute([$doctor['id']]);
    $availabilityData = $stmt->fetch();
    $is_available = $availabilityData['is_available'] ?? 1;
    $availability_note = $availabilityData['availability_note'] ?? '';
} else {
    $is_available = 1;
    $availability_note = '';
}

// Dummy statistics
$stats = [
    'total_patients' => 247,
    'appointments_today' => 12,
    'pending_appointments' => 8,
    'completed_today' => 4
];

include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div style="padding: 24px;">
    <!-- Availability Toggle Card -->
    <div class="glass-card" style="margin-bottom: 24px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.1) 100%); border: 1px solid rgba(16, 185, 129, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1; min-width: 250px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <i class="fas fa-toggle-on" style="font-size: 24px; color: #10b981;"></i>
                    <h3 style="font-size: 20px; font-weight: 700; color: #263238; margin: 0;">Availability Status</h3>
                </div>
                <p style="color: #546e7a; font-size: 14px; margin: 0;">
                    Control when patients can book appointments with you
                </p>
            </div>
            
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="text-align: right;">
                    <div id="statusText" style="font-size: 18px; font-weight: 700; margin-bottom: 4px; color: <?php echo $is_available ? '#10b981' : '#ef4444'; ?>;">
                        <?php echo $is_available ? 'Available' : 'Unavailable'; ?>
                    </div>
                    <div id="statusSubText" style="font-size: 12px; color: #78909c;">
                        <?php echo $is_available ? 'Patients can book appointments' : 'Booking disabled'; ?>
                    </div>
                </div>
                
                <label class="toggle-switch" style="position: relative; display: inline-block; width: 60px; height: 34px;">
                    <input type="checkbox" id="availabilityToggle" <?php echo $is_available ? 'checked' : ''; ?> 
                           onchange="toggleAvailability()" style="opacity: 0; width: 0; height: 0;">
                    <span class="slider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?php echo $is_available ? '#10b981' : '#78909c'; ?>; transition: .4s; border-radius: 34px;">
                        <span style="position: absolute; content: ''; height: 26px; width: 26px; left: <?php echo $is_available ? '30px' : '4px'; ?>; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; display: block;"></span>
                    </span>
                </label>
                
                <button onclick="showAvailabilityModal()" class="btn btn-primary" style="padding: 10px 20px; font-size: 14px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <i class="fas fa-edit"></i> Edit Note
                </button>
            </div>
        </div>
        
        <?php if (!$is_available && !empty($availability_note)): ?>
        <div id="availabilityNote" style="margin-top: 16px; padding: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-info-circle" style="color: #ef4444;"></i>
                <strong style="color: #ef4444; font-size: 14px;">Unavailability Note:</strong>
            </div>
            <p style="color: #fca5a5; font-size: 14px; margin: 8px 0 0 0;">
                <?php echo htmlspecialchars($availability_note); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #263238; margin-bottom: 8px;">
            Doctor Dashboard
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Welcome back, Dr. <?php echo htmlspecialchars($doctor['name']); ?>
        </p>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="glass-card stat-card clickable-card" onclick="showDetailedReport('patients')">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['total_patients']; ?></h3>
                    <p class="stat-label">Total Patients</p>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12.5% from last month</span>
                    </div>
                </div>
                <div class="stat-icon pink">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="card-hover-indicator">
                <i class="fas fa-chart-line"></i>
                <span>View Details</span>
            </div>
        </div>

        <div class="glass-card stat-card clickable-card" onclick="showDetailedReport('appointments')">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['appointments_today']; ?></h3>
                    <p class="stat-label">Appointments Today</p>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+3 from yesterday</span>
                    </div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
            </div>
            <div class="card-hover-indicator">
                <i class="fas fa-calendar-alt"></i>
                <span>View Schedule</span>
            </div>
        </div>

        <div class="glass-card stat-card clickable-card" onclick="showDetailedReport('pending')">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['pending_appointments']; ?></h3>
                    <p class="stat-label">Pending Approvals</p>
                    <div class="stat-trend neutral">
                        <i class="fas fa-minus"></i>
                        <span>No change</span>
                    </div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="card-hover-indicator">
                <i class="fas fa-hourglass-half"></i>
                <span>View Pending</span>
            </div>
        </div>

        <div class="glass-card stat-card clickable-card" onclick="showDetailedReport('completed')">
            <div class="stat-header">
                <div class="stat-content">
                    <h3 class="stat-number"><?php echo $stats['completed_today']; ?></h3>
                    <p class="stat-label">Completed Today</p>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+2 from yesterday</span>
                    </div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="card-hover-indicator">
                <i class="fas fa-check-double"></i>
                <span>View Completed</span>
            </div>
        </div>
    </div>

    <!-- Recent Appointments -->
    <div class="glass-card" style="margin-bottom: 24px;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-calendar-alt"></i>
                Today's Appointments
            </h2>
            <a href="pages/appointments.php" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">SP</div>
                                <div>
                                    <div style="color: #263238; font-weight: 500;">Sarah Parker</div>
                                    <div style="font-size: 12px; color: #78909c;">Week 24 of Pregnancy</div>
                                </div>
                            </div>
                        </td>
                        <td>09:00 AM</td>
                        <td>Regular Checkup</td>
                        <td><span class="status-badge confirmed">Confirmed</span></td>
                        <td>
                            <div class="table-actions">
                                <button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">MJ</div>
                                <div>
                                    <div style="color: #263238; font-weight: 500;">Maria Johnson</div>
                                    <div style="font-size: 12px; color: #78909c;">Week 16 of Pregnancy</div>
                                </div>
                            </div>
                        </td>
                        <td>10:30 AM</td>
                        <td>Ultrasound</td>
                        <td><span class="status-badge pending">Pending</span></td>
                        <td>
                            <div class="table-actions">
                                <button class="action-btn approve" title="Approve"><i class="fas fa-check"></i></button>
                                <button class="action-btn delete" title="Decline"><i class="fas fa-times"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">EW</div>
                                <div>
                                    <div style="color: #263238; font-weight: 500;">Emily Wilson</div>
                                    <div style="font-size: 12px; color: #78909c;">Week 32 of Pregnancy</div>
                                </div>
                            </div>
                        </td>
                        <td>02:00 PM</td>
                        <td>Consultation</td>
                        <td><span class="status-badge confirmed">Confirmed</span></td>
                        <td>
                            <div class="table-actions">
                                <button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Edit"><i class="fas fa-edit"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">LB</div>
                                <div>
                                    <div style="color: #263238; font-weight: 500;">Lisa Brown</div>
                                    <div style="font-size: 12px; color: #78909c;">Week 28 of Pregnancy</div>
                                </div>
                            </div>
                        </td>
                        <td>03:30 PM</td>
                        <td>Follow-up</td>
                        <td><span class="status-badge completed">Completed</span></td>
                        <td>
                            <div class="table-actions">
                                <button class="action-btn view" title="View Details"><i class="fas fa-eye"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions Grid -->
    <div class="grid grid-cols-2 gap-6">
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="pages/appointments.php" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-calendar-plus"></i>
                    New Appointment
                </a>
                <a href="upload_documents.php" class="btn btn-primary" style="width: 100%; justify-content: center; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <i class="fas fa-cloud-upload-alt"></i>
                    Upload Medical Documents
                </a>
                <a href="generate_report.php" class="btn btn-primary" style="width: 100%; justify-content: center; background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-file-medical-alt"></i>
                    Generate Patient Report
                </a>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Emergency Alerts
                </h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="padding: 12px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                        <i class="fas fa-circle" style="font-size: 8px; color: #ef4444;"></i>
                        <span style="font-weight: 600; color: #263238; font-size: 14px;">High Priority</span>
                    </div>
                    <p style="font-size: 12px; color: #546e7a; margin: 0;">2 emergency cases pending</p>
                </div>
                <a href="pages/emergency.php" class="btn btn-danger" style="width: 100%; justify-content: center;">
                    <i class="fas fa-ambulance"></i>
                    View Emergency Cases
                </a>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line"></i>
                    This Week
                </h3>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #546e7a; font-size: 14px;">Appointments</span>
                    <span style="color: #C4A7FF; font-weight: 700; font-size: 18px;">48</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #546e7a; font-size: 14px;">Consultations</span>
                    <span style="color: #3b82f6; font-weight: 700; font-size: 18px;">32</span>
                </div>
            </div>
        </div>
    </div>
</div>

        </div><!-- End main-content -->
    </div><!-- End flex container -->

    <!-- Detailed Report Modals -->
    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Detailed Report</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Availability Note Modal -->
    <div id="availabilityModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Unavailability Note</h2>
                <span class="close" onclick="closeAvailabilityModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p style="color: #546e7a; margin-bottom: 16px; font-size: 14px;">
                    This note will be shown to patients when you're unavailable. Leave blank if not needed.
                </p>
                <textarea id="availabilityNoteInput" 
                          style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(148, 163, 184, 0.3); border-radius: 8px; color: #f1f5f9; font-family: inherit; font-size: 14px; resize: vertical; min-height: 100px;"
                          placeholder="e.g., On vacation until May 20th, Attending conference, Medical leave"><?php echo htmlspecialchars($availability_note); ?></textarea>
                <div style="margin-top: 20px; display: flex; gap: 12px; justify-content: flex-end;">
                    <button onclick="closeAvailabilityModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button onclick="saveAvailabilityNote()" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-save"></i> Save Note
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show detailed report modal
        function showDetailedReport(type) {
            const modal = document.getElementById('reportModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');
            
            // Set title and content based on type
            switch(type) {
                case 'patients':
                    modalTitle.textContent = 'Patient Analytics Report';
                    modalBody.innerHTML = generatePatientsReport();
                    break;
                case 'appointments':
                    modalTitle.textContent = 'Today\'s Appointments Report';
                    modalBody.innerHTML = generateAppointmentsReport();
                    break;
                case 'pending':
                    modalTitle.textContent = 'Pending Approvals Report';
                    modalBody.innerHTML = generatePendingReport();
                    break;
                case 'completed':
                    modalTitle.textContent = 'Completed Appointments Report';
                    modalBody.innerHTML = generateCompletedReport();
                    break;
            }
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Close modal
        function closeModal() {
            const modal = document.getElementById('reportModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reportModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Generate patients report
        function generatePatientsReport() {
            return `
                <div class="report-section">
                    <div class="report-stats-grid">
                        <div class="report-stat-card">
                            <div class="report-stat-number">247</div>
                            <div class="report-stat-label">Total Patients</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">28</div>
                            <div class="report-stat-label">New This Month</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">89%</div>
                            <div class="report-stat-label">Active Patients</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">4.8</div>
                            <div class="report-stat-label">Avg Rating</div>
                        </div>
                    </div>
                    
                    <div class="report-chart-section">
                        <h3>Patient Growth Trend</h3>
                        <div class="chart-placeholder">
                            <div class="chart-bar" style="height: 60%"><span>Jan</span></div>
                            <div class="chart-bar" style="height: 75%"><span>Feb</span></div>
                            <div class="chart-bar" style="height: 85%"><span>Mar</span></div>
                            <div class="chart-bar" style="height: 90%"><span>Apr</span></div>
                            <div class="chart-bar" style="height: 100%"><span>May</span></div>
                        </div>
                    </div>
                    
                    <div class="report-table-section">
                        <h3>Recent Patients</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Age</th>
                                    <th>Pregnancy Week</th>
                                    <th>Last Visit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sarah Parker</td>
                                    <td>28</td>
                                    <td>Week 24</td>
                                    <td>2 days ago</td>
                                    <td><span class="status-badge active">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Maria Johnson</td>
                                    <td>32</td>
                                    <td>Week 16</td>
                                    <td>1 week ago</td>
                                    <td><span class="status-badge active">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Emily Wilson</td>
                                    <td>25</td>
                                    <td>Week 32</td>
                                    <td>3 days ago</td>
                                    <td><span class="status-badge active">Active</span></td>
                                </tr>
                                <tr>
                                    <td>Lisa Brown</td>
                                    <td>29</td>
                                    <td>Week 28</td>
                                    <td>Today</td>
                                    <td><span class="status-badge active">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="window.open('pages/patients.php', '_blank')">
                            <i class="fas fa-users"></i> View All Patients
                        </button>
                        <button class="btn btn-secondary" onclick="exportReport('patients')">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            `;
        }

        // Generate appointments report
        function generateAppointmentsReport() {
            return `
                <div class="report-section">
                    <div class="report-stats-grid">
                        <div class="report-stat-card">
                            <div class="report-stat-number">12</div>
                            <div class="report-stat-label">Total Today</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">4</div>
                            <div class="report-stat-label">Completed</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">6</div>
                            <div class="report-stat-label">Upcoming</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">2</div>
                            <div class="report-stat-label">Cancelled</div>
                        </div>
                    </div>
                    
                    <div class="report-timeline">
                        <h3>Today's Schedule</h3>
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div class="timeline-time">09:00 AM</div>
                                <div class="timeline-content">
                                    <h4>Sarah Parker - Regular Checkup</h4>
                                    <p>Completed • Duration: 30 mins</p>
                                </div>
                            </div>
                            <div class="timeline-item completed">
                                <div class="timeline-time">10:30 AM</div>
                                <div class="timeline-content">
                                    <h4>Maria Johnson - Ultrasound</h4>
                                    <p>Completed • Duration: 45 mins</p>
                                </div>
                            </div>
                            <div class="timeline-item upcoming">
                                <div class="timeline-time">02:00 PM</div>
                                <div class="timeline-content">
                                    <h4>Emily Wilson - Consultation</h4>
                                    <p>Upcoming • Estimated: 30 mins</p>
                                </div>
                            </div>
                            <div class="timeline-item upcoming">
                                <div class="timeline-time">03:30 PM</div>
                                <div class="timeline-content">
                                    <h4>Lisa Brown - Follow-up</h4>
                                    <p>Upcoming • Estimated: 20 mins</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="window.open('pages/appointments.php', '_blank')">
                            <i class="fas fa-calendar-alt"></i> View All Appointments
                        </button>
                        <button class="btn btn-secondary" onclick="exportReport('appointments')">
                            <i class="fas fa-download"></i> Export Schedule
                        </button>
                    </div>
                </div>
            `;
        }

        // Generate pending report
        function generatePendingReport() {
            return `
                <div class="report-section">
                    <div class="report-stats-grid">
                        <div class="report-stat-card">
                            <div class="report-stat-number">8</div>
                            <div class="report-stat-label">Total Pending</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">5</div>
                            <div class="report-stat-label">New Requests</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">3</div>
                            <div class="report-stat-label">Rescheduled</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">2h</div>
                            <div class="report-stat-label">Avg Wait Time</div>
                        </div>
                    </div>
                    
                    <div class="report-table-section">
                        <h3>Pending Appointments</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Requested Date</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Jennifer Davis</td>
                                    <td>May 8, 2026 - 10:00 AM</td>
                                    <td>Emergency Consultation</td>
                                    <td><span class="priority-badge high">High</span></td>
                                    <td>
                                        <button class="btn-small btn-success">Approve</button>
                                        <button class="btn-small btn-danger">Decline</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Amanda Wilson</td>
                                    <td>May 9, 2026 - 02:30 PM</td>
                                    <td>Regular Checkup</td>
                                    <td><span class="priority-badge medium">Medium</span></td>
                                    <td>
                                        <button class="btn-small btn-success">Approve</button>
                                        <button class="btn-small btn-danger">Decline</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Rachel Green</td>
                                    <td>May 10, 2026 - 11:00 AM</td>
                                    <td>Ultrasound</td>
                                    <td><span class="priority-badge low">Low</span></td>
                                    <td>
                                        <button class="btn-small btn-success">Approve</button>
                                        <button class="btn-small btn-danger">Decline</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="approveAllPending()">
                            <i class="fas fa-check-double"></i> Approve All
                        </button>
                        <button class="btn btn-secondary" onclick="exportReport('pending')">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            `;
        }

        // Generate completed report
        function generateCompletedReport() {
            return `
                <div class="report-section">
                    <div class="report-stats-grid">
                        <div class="report-stat-card">
                            <div class="report-stat-number">4</div>
                            <div class="report-stat-label">Completed Today</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">2h 15m</div>
                            <div class="report-stat-label">Total Time</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">34m</div>
                            <div class="report-stat-label">Avg Duration</div>
                        </div>
                        <div class="report-stat-card">
                            <div class="report-stat-number">100%</div>
                            <div class="report-stat-label">Success Rate</div>
                        </div>
                    </div>
                    
                    <div class="report-table-section">
                        <h3>Completed Appointments</h3>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Duration</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Sarah Parker</td>
                                    <td>09:00 - 09:30 AM</td>
                                    <td>Regular Checkup</td>
                                    <td>30 mins</td>
                                    <td>Normal progress, next visit in 2 weeks</td>
                                </tr>
                                <tr>
                                    <td>Maria Johnson</td>
                                    <td>10:30 - 11:15 AM</td>
                                    <td>Ultrasound</td>
                                    <td>45 mins</td>
                                    <td>Healthy development, all parameters normal</td>
                                </tr>
                                <tr>
                                    <td>Emily Wilson</td>
                                    <td>02:00 - 02:30 PM</td>
                                    <td>Consultation</td>
                                    <td>30 mins</td>
                                    <td>Discussed nutrition plan, prescribed vitamins</td>
                                </tr>
                                <tr>
                                    <td>Lisa Brown</td>
                                    <td>03:30 - 03:50 PM</td>
                                    <td>Follow-up</td>
                                    <td>20 mins</td>
                                    <td>Recovery progressing well, cleared for activities</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="generateDailyReport()">
                            <i class="fas fa-file-alt"></i> Generate Daily Report
                        </button>
                        <button class="btn btn-secondary" onclick="exportReport('completed')">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            `;
        }

        // Export report function
        function exportReport(type) {
            alert(`Exporting ${type} report... (This would trigger a download in a real implementation)`);
        }

        // Approve all pending function
        function approveAllPending() {
            if (confirm('Are you sure you want to approve all pending appointments?')) {
                alert('All pending appointments approved! (This would update the database in a real implementation)');
                closeModal();
            }
        }

        // Generate daily report function
        function generateDailyReport() {
            alert('Generating comprehensive daily report... (This would create a PDF in a real implementation)');
        }

        // Availability Toggle Functions
        function toggleAvailability() {
            const toggle = document.getElementById('availabilityToggle');
            const isAvailable = toggle.checked ? 1 : 0;
            
            // Update UI immediately
            updateAvailabilityUI(toggle.checked);
            
            // Send AJAX request
            const formData = new FormData();
            formData.append('toggle_availability', '1');
            formData.append('is_available', isAvailable);
            formData.append('availability_note', '<?php echo addslashes($availability_note); ?>');
            
            fetch('dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(toggle.checked ? 'You are now available for appointments' : 'You are now unavailable for appointments', 'success');
                } else {
                    // Revert on failure
                    toggle.checked = !toggle.checked;
                    updateAvailabilityUI(toggle.checked);
                    showNotification('Failed to update availability', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toggle.checked = !toggle.checked;
                updateAvailabilityUI(toggle.checked);
                showNotification('Failed to update availability', 'error');
            });
        }

        function updateAvailabilityUI(isAvailable) {
            const statusText = document.getElementById('statusText');
            const statusSubText = document.getElementById('statusSubText');
            const slider = document.querySelector('.slider');
            const sliderCircle = slider ? slider.querySelector('span') : null;
            const availabilityNote = document.getElementById('availabilityNote');
            
            if (isAvailable) {
                if (statusText) { statusText.textContent = 'Available'; statusText.style.color = '#10b981'; }
                if (statusSubText) statusSubText.textContent = 'Patients can book appointments';
                if (slider) slider.style.backgroundColor = '#10b981';
                if (sliderCircle) sliderCircle.style.left = '30px';
                if (availabilityNote) availabilityNote.style.display = 'none';
            } else {
                if (statusText) { statusText.textContent = 'Unavailable'; statusText.style.color = '#ef4444'; }
                if (statusSubText) statusSubText.textContent = 'Booking disabled';
                if (slider) slider.style.backgroundColor = '#78909c';
                if (sliderCircle) sliderCircle.style.left = '4px';
            }
        }

        function showAvailabilityModal() {
            const modal = document.getElementById('availabilityModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeAvailabilityModal() {
            const modal = document.getElementById('availabilityModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function saveAvailabilityNote() {
            const note = document.getElementById('availabilityNoteInput').value;
            const toggle = document.getElementById('availabilityToggle');
            
            const formData = new FormData();
            formData.append('toggle_availability', '1');
            formData.append('is_available', toggle.checked ? 1 : 0);
            formData.append('availability_note', note);
            
            fetch('dashboard.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Availability note updated successfully', 'success');
                    closeAvailabilityModal();
                    // Reload to show updated note
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Failed to update note', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to update note', 'error');
            });
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 16px 24px;
                background: ${type === 'success' ? '#10b981' : '#ef4444'};
                color: white;
                border-radius: 8px;
                font-weight: 600;
                z-index: 10000;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                animation: slideIn 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>

    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        /* Notification Animations */
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        .modal-content {
            background: linear-gradient(135deg, #F3E8FF 0%, #E9D5FF 100%);
            margin: 2% auto;
            padding: 0;
            border: 1px solid rgba(244, 114, 182, 0.3);
            border-radius: 16px;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            padding: 24px 32px;
            border-bottom: 1px solid rgba(244, 114, 182, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(244, 114, 182, 0.05);
        }

        .modal-header h2 {
            color: #263238;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .close {
            color: #546e7a;
            font-size: 32px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }

        .close:hover {
            color: #C4A7FF;
            background: rgba(244, 114, 182, 0.1);
        }

        .modal-body {
            padding: 32px;
        }

        /* Clickable Card Styles */
        .clickable-card {
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .clickable-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(244, 114, 182, 0.2);
        }

        .card-hover-indicator {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
            color: white;
            padding: 8px 16px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .clickable-card:hover .card-hover-indicator {
            transform: translateY(0);
        }

        /* Report Styles */
        .report-section {
            color: #ffffff;
        }

        .report-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .report-stat-card {
            background: rgba(244, 114, 182, 0.1);
            border: 1px solid rgba(244, 114, 182, 0.2);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .report-stat-number {
            font-size: 32px;
            font-weight: 800;
            color: #C4A7FF;
            margin-bottom: 8px;
        }

        .report-stat-label {
            font-size: 14px;
            color: #546e7a;
            margin: 0;
        }

        .report-chart-section {
            margin-bottom: 32px;
        }

        .report-chart-section h3 {
            color: #263238;
            margin-bottom: 16px;
            font-size: 18px;
        }

        .chart-placeholder {
            display: flex;
            align-items: end;
            gap: 12px;
            height: 200px;
            padding: 20px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            border: 1px solid rgba(244, 114, 182, 0.2);
        }

        .chart-bar {
            flex: 1;
            background: linear-gradient(to top, #C4A7FF, #00D1FF);
            border-radius: 4px 4px 0 0;
            position: relative;
            min-height: 20px;
            display: flex;
            align-items: end;
            justify-content: center;
        }

        .chart-bar span {
            position: absolute;
            bottom: -25px;
            font-size: 12px;
            color: #546e7a;
        }

        .report-table-section {
            margin-bottom: 32px;
        }

        .report-table-section h3 {
            color: #263238;
            margin-bottom: 16px;
            font-size: 18px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(244, 114, 182, 0.2);
        }

        .report-table th {
            background: rgba(244, 114, 182, 0.2);
            color: #263238;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }

        .report-table td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(244, 114, 182, 0.1);
            color: #546e7a;
            font-size: 14px;
        }

        .report-table tr:last-child td {
            border-bottom: none;
        }

        .report-timeline {
            margin-bottom: 32px;
        }

        .report-timeline h3 {
            color: #263238;
            margin-bottom: 16px;
            font-size: 18px;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: rgba(244, 114, 182, 0.3);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 24px;
            padding: 16px 20px;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
            border: 1px solid rgba(244, 114, 182, 0.2);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -37px;
            top: 20px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #C4A7FF;
        }

        .timeline-item.completed::before {
            background: #22c55e;
        }

        .timeline-time {
            font-size: 12px;
            color: #C4A7FF;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .timeline-content h4 {
            color: #263238;
            margin: 0 0 4px 0;
            font-size: 16px;
        }

        .timeline-content p {
            color: #546e7a;
            margin: 0;
            font-size: 14px;
        }

        .report-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            padding-top: 24px;
            border-top: 1px solid rgba(244, 114, 182, 0.2);
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-success {
            background: #22c55e;
            color: white;
        }

        .btn-success:hover {
            background: #16a34a;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .status-badge.active {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .priority-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .priority-badge.high {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .priority-badge.medium {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }

        .priority-badge.low {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
                margin: 5% auto;
            }

            .modal-header {
                padding: 16px 20px;
            }

            .modal-body {
                padding: 20px;
            }

            .report-stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 12px;
            }

            .report-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>

