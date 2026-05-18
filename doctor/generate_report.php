<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireDoctorLogin();

$pageTitle = 'Generate Patient Report';
$doctor = getCurrentDoctor();

// Get all patients for dropdown
$db = getDB();
$stmt = $db->query("SELECT id, name, email, pregnancy_week, due_date FROM users ORDER BY name");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle report generation
$reportMessage = '';
$reportError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = $_POST['patient_id'] ?? '';
    $reportType = $_POST['report_type'] ?? '';
    $reportTitle = $_POST['report_title'] ?? '';
    $diagnosis = $_POST['diagnosis'] ?? '';
    $recommendations = $_POST['recommendations'] ?? '';
    $medications = $_POST['medications'] ?? '';
    $followUpDate = $_POST['follow_up_date'] ?? null;
    
    if (empty($patientId) || empty($reportType) || empty($reportTitle)) {
        $reportError = 'Please fill in all required fields.';
    } else {
        // Prepare report data as JSON
        $reportData = json_encode([
            'diagnosis' => $diagnosis,
            'recommendations' => $recommendations,
            'medications' => $medications,
            'follow_up_date' => $followUpDate,
            'generated_by' => $doctor['name'],
            'doctor_specialization' => $doctor['specialization']
        ]);
        
        // Insert report
        $stmt = $db->prepare("
            INSERT INTO patient_reports 
            (patient_id, doctor_id, report_type, report_title, report_data, diagnosis, recommendations, medications, follow_up_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        try {
            $stmt->execute([
                $patientId,
                $doctor['id'],
                $reportType,
                $reportTitle,
                $reportData,
                $diagnosis,
                $recommendations,
                $medications,
                $followUpDate ?: null
            ]);
            
            $reportMessage = 'Report generated successfully!';
        } catch (Exception $e) {
            $reportError = 'Failed to generate report. Please try again.';
        }
    }
}

// Get recent reports by this doctor
$stmt = $db->prepare("
    SELECT pr.*, u.name as patient_name 
    FROM patient_reports pr
    JOIN users u ON pr.patient_id = u.id
    WHERE pr.doctor_id = ?
    ORDER BY pr.generated_date DESC
    LIMIT 10
");
$stmt->execute([$doctor['id']]);
$recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">
            Generate Patient Report
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Create comprehensive medical reports for individual patients
        </p>
    </div>

    <?php if ($reportMessage): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>
            <span style="color: #10b981; font-weight: 600;"><?php echo htmlspecialchars($reportMessage); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($reportError): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px;"></i>
            <span style="color: #ef4444; font-weight: 600;"><?php echo htmlspecialchars($reportError); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Report Generation Form -->
    <div class="glass-card" style="margin-bottom: 32px;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-file-medical"></i>
                Create New Report
            </h2>
        </div>

        <form method="POST" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Patient Selection -->
                <div>
                    <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                        Select Patient <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="patient_id" id="patientSelect" required 
                            style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
                        <option value="">Choose a patient...</option>
                        <?php foreach ($patients as $patient): ?>
                        <option value="<?php echo $patient['id']; ?>" 
                                data-week="<?php echo $patient['pregnancy_week'] ?? 'N/A'; ?>"
                                data-due="<?php echo $patient['due_date'] ?? 'N/A'; ?>">
                            <?php echo htmlspecialchars($patient['name']) . ' (' . htmlspecialchars($patient['email']) . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="patientInfo" style="margin-top: 8px; color: #78909c; font-size: 12px;"></div>
                </div>

                <!-- Report Type -->
                <div>
                    <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                        Report Type <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="report_type" required style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
                        <option value="">Choose type...</option>
                        <option value="checkup">Regular Checkup</option>
                        <option value="ultrasound">Ultrasound Report</option>
                        <option value="lab_results">Lab Results</option>
                        <option value="consultation">Consultation Summary</option>
                        <option value="emergency">Emergency Report</option>
                        <option value="follow_up">Follow-up Report</option>
                        <option value="discharge">Discharge Summary</option>
                    </select>
                </div>
            </div>

            <!-- Report Title -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Report Title <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="report_title" required placeholder="e.g., Week 24 Checkup Report"
                       style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
            </div>

            <!-- Diagnosis -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Diagnosis <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="diagnosis" rows="4" required placeholder="Enter diagnosis details..."
                          style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <!-- Recommendations -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Recommendations <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="recommendations" rows="4" required placeholder="Enter recommendations for the patient..."
                          style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <!-- Medications -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Medications
                </label>
                <textarea name="medications" rows="3" placeholder="List prescribed medications (if any)..."
                          style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <!-- Follow-up Date -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Follow-up Date
                </label>
                <input type="date" name="follow_up_date"
                       style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-file-medical-alt"></i>
                Generate Report
            </button>
        </form>
    </div>

    <!-- Recent Reports -->
    <?php if (count($recentReports) > 0): ?>
    <div class="glass-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-history"></i>
                Recent Reports
            </h2>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Report Title</th>
                        <th>Type</th>
                        <th>Generated Date</th>
                        <th>Follow-up</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentReports as $report): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($report['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['report_title']); ?></td>
                        <td>
                            <span class="status-badge confirmed">
                                <?php echo ucwords(str_replace('_', ' ', $report['report_type'])); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($report['generated_date'])); ?></td>
                        <td>
                            <?php if ($report['follow_up_date']): ?>
                                <?php echo date('M d, Y', strtotime($report['follow_up_date'])); ?>
                            <?php else: ?>
                                <span style="color: #78909c;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="action-btn view" title="View Report" 
                                        onclick="viewReport(<?php echo $report['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Show patient info when selected
document.getElementById('patientSelect').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const week = selected.getAttribute('data-week');
    const due = selected.getAttribute('data-due');
    const infoDiv = document.getElementById('patientInfo');
    
    if (this.value) {
        infoDiv.innerHTML = `<i class="fas fa-info-circle"></i> Pregnancy Week: ${week} | Due Date: ${due}`;
    } else {
        infoDiv.innerHTML = '';
    }
});

function viewReport(reportId) {
    // TODO: Implement report viewing modal
    alert('Report viewing feature coming soon!');
}
</script>

<?php include 'includes/footer.php'; ?>
