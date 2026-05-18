<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once 'includes/auth.php';
require_once 'includes/db.php';
requireDoctorLogin();

$pageTitle = 'Upload Medical Documents';
$doctor = getCurrentDoctor();

// Get all patients for dropdown
$db = getDB();
$stmt = $db->query("SELECT id, name, email FROM users ORDER BY name");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file upload
$uploadMessage = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $patientId = $_POST['patient_id'] ?? '';
    $documentType = $_POST['document_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    if (empty($patientId) || empty($documentType)) {
        $uploadError = 'Please select a patient and document type.';
    } else {
        $file = $_FILES['document'];
        
        // Validate file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadError = 'File upload failed. Please try again.';
        } elseif (!in_array($file['type'], $allowedTypes)) {
            $uploadError = 'Invalid file type. Only JPG, PNG, and PDF files are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $uploadError = 'File is too large. Maximum size is 10MB.';
        } else {
            // Create upload directory if it doesn't exist
            $uploadDir = '../uploads/medical_documents/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'doc_' . $patientId . '_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Save to database
                $stmt = $db->prepare("
                    INSERT INTO medical_documents 
                    (patient_id, doctor_id, document_type, document_name, file_path, file_size, description, notes)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $patientId,
                    $doctor['id'],
                    $documentType,
                    $file['name'],
                    $filepath,
                    $file['size'],
                    $description,
                    $notes
                ]);
                
                $uploadMessage = 'Document uploaded successfully!';
            } else {
                $uploadError = 'Failed to save file. Please try again.';
            }
        }
    }
}

// Get recent uploads by this doctor
$stmt = $db->prepare("
    SELECT md.*, u.name as patient_name 
    FROM medical_documents md
    JOIN users u ON md.patient_id = u.id
    WHERE md.doctor_id = ?
    ORDER BY md.upload_date DESC
    LIMIT 10
");
$stmt->execute([$doctor['id']]);
$recentUploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin-bottom: 8px;">
            Upload Medical Documents
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Upload CT scans, X-rays, lab reports, and other medical documents for your patients
        </p>
    </div>

    <?php if ($uploadMessage): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>
            <span style="color: #10b981; font-weight: 600;"><?php echo htmlspecialchars($uploadMessage); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($uploadError): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px;"></i>
            <span style="color: #ef4444; font-weight: 600;"><?php echo htmlspecialchars($uploadError); ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="glass-card" style="margin-bottom: 32px;">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-cloud-upload-alt"></i>
                Upload New Document
            </h2>
        </div>

        <form method="POST" enctype="multipart/form-data" style="padding: 24px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <!-- Patient Selection -->
                <div>
                    <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                        Select Patient <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="patient_id" required style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
                        <option value="">Choose a patient...</option>
                        <?php foreach ($patients as $patient): ?>
                        <option value="<?php echo $patient['id']; ?>">
                            <?php echo htmlspecialchars($patient['name']) . ' (' . htmlspecialchars($patient['email']) . ')'; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Document Type -->
                <div>
                    <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                        Document Type <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="document_type" required style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
                        <option value="">Choose type...</option>
                        <option value="ct_scan">CT Scan</option>
                        <option value="xray">X-Ray</option>
                        <option value="mri">MRI</option>
                        <option value="ultrasound">Ultrasound</option>
                        <option value="lab_report">Lab Report</option>
                        <option value="blood_test">Blood Test</option>
                        <option value="prescription">Prescription</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- File Upload -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Upload File <span style="color: #ef4444;">*</span>
                </label>
                <div style="position: relative;">
                    <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required 
                           style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
                    <p style="color: #78909c; font-size: 12px; margin-top: 8px;">
                        <i class="fas fa-info-circle"></i> Accepted formats: JPG, PNG, PDF (Max 10MB)
                    </p>
                </div>
            </div>

            <!-- Description -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Description
                </label>
                <input type="text" name="description" placeholder="Brief description of the document"
                       style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px;">
            </div>

            <!-- Notes -->
            <div style="margin-bottom: 24px;">
                <label style="display: block; color: #546e7a; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Medical Notes
                </label>
                <textarea name="notes" rows="4" placeholder="Add any relevant medical notes or observations..."
                          style="width: 100%; padding: 12px; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(196, 167, 255, 0.2); border-radius: 8px; color: #ffffff; font-size: 14px; resize: vertical;"></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fas fa-upload"></i>
                Upload Document
            </button>
        </form>
    </div>

    <!-- Recent Uploads -->
    <?php if (count($recentUploads) > 0): ?>
    <div class="glass-card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-history"></i>
                Recent Uploads
            </h2>
        </div>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Document Type</th>
                        <th>File Name</th>
                        <th>Upload Date</th>
                        <th>Size</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUploads as $doc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['patient_name']); ?></td>
                        <td>
                            <span class="status-badge confirmed">
                                <?php echo ucwords(str_replace('_', ' ', $doc['document_type'])); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($doc['upload_date'])); ?></td>
                        <td><?php echo round($doc['file_size'] / 1024, 2); ?> KB</td>
                        <td>
                            <div class="table-actions">
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" 
                                   class="action-btn view" title="View Document">
                                    <i class="fas fa-eye"></i>
                                </a>
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

<?php include 'includes/footer.php'; ?>
