<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
session_start();

// Auto-setup: Ensure database schema is ready
require_once '../server/config/database.php';
try {
    $db = getDB();
    
    // Check if medical_license_number column exists
    $stmt = $db->query("SHOW COLUMNS FROM doctors LIKE 'medical_license_number'");
    if (!$stmt->fetch()) {
        // Run minimal setup - add essential columns
        $essential_columns = [
            "gender VARCHAR(20) DEFAULT NULL",
            "date_of_birth DATE DEFAULT NULL",
            "mobile VARCHAR(20) DEFAULT NULL",
            "address TEXT DEFAULT NULL",
            "city VARCHAR(100) DEFAULT NULL",
            "state VARCHAR(100) DEFAULT NULL",
            "pin_code VARCHAR(10) DEFAULT NULL",
            "medical_license_number VARCHAR(100) DEFAULT NULL",
            "medical_council_registration VARCHAR(100) DEFAULT NULL",
            "secondary_specialization VARCHAR(255) DEFAULT NULL",
            "hospital_name VARCHAR(255) DEFAULT NULL",
            "workplace_address TEXT DEFAULT NULL",
            "consultation_fee DECIMAL(10,2) DEFAULT NULL",
            "languages_spoken TEXT DEFAULT NULL",
            "degree_name VARCHAR(255) DEFAULT NULL",
            "university VARCHAR(255) DEFAULT NULL",
            "graduation_year INT DEFAULT NULL",
            "fellowship_details TEXT DEFAULT NULL",
            "additional_certifications TEXT DEFAULT NULL",
            "available_days TEXT DEFAULT NULL",
            "time_slots TEXT DEFAULT NULL",
            "online_consultation BOOLEAN DEFAULT FALSE",
            "in_person_consultation BOOLEAN DEFAULT TRUE",
            "emergency_availability BOOLEAN DEFAULT FALSE",
            "username VARCHAR(100) DEFAULT NULL",
            "password VARCHAR(255) DEFAULT NULL",
            "registration_status VARCHAR(50) DEFAULT 'pending'"
        ];
        
        foreach ($essential_columns as $column_def) {
            try {
                $db->exec("ALTER TABLE doctors ADD COLUMN $column_def");
            } catch (PDOException $e) {
                // Column might already exist, continue
            }
        }
        
        // Create drafts table
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS doctor_registration_drafts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                session_id VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                step_number INT DEFAULT 1,
                form_data TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (PDOException $e) {
            // Table might exist
        }
    }
    
    // Create uploads directory
    $upload_dir = '../uploads/doctor_documents/';
    if (!file_exists($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }
    
} catch (Exception $e) {
    // Continue anyway - errors will be caught during submission
}

// Generate session ID for draft saving
if (!isset($_SESSION['doctor_reg_session'])) {
    $_SESSION['doctor_reg_session'] = uniqid('doc_reg_', true);
}

$pageTitle = 'Doctor Registration - Aarunya';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/doctor-registration.css">
</head>
<body>
    <!-- Header -->
    <header class="registration-header">
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-heart-pulse"></i>
                <span>Aarunya</span>
            </div>
            <div class="header-actions">
                <a href="../client/login.php" class="btn-link">Already have an account? Login</a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <div class="registration-container">
        <!-- Progress Indicator -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-circle">
                        <i class="fas fa-user"></i>
                        <span class="step-number">1</span>
                    </div>
                    <span class="step-label">Personal Info</span>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle">
                        <i class="fas fa-briefcase-medical"></i>
                        <span class="step-number">2</span>
                    </div>
                    <span class="step-label">Professional</span>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle">
                        <i class="fas fa-graduation-cap"></i>
                        <span class="step-number">3</span>
                    </div>
                    <span class="step-label">Education</span>
                </div>
                <div class="step" data-step="4">
                    <div class="step-circle">
                        <i class="fas fa-file-upload"></i>
                        <span class="step-number">4</span>
                    </div>
                    <span class="step-label">Documents</span>
                </div>
                <div class="step" data-step="5">
                    <div class="step-circle">
                        <i class="fas fa-calendar-check"></i>
                        <span class="step-number">5</span>
                    </div>
                    <span class="step-label">Availability</span>
                </div>
                <div class="step" data-step="6">
                    <div class="step-circle">
                        <i class="fas fa-lock"></i>
                        <span class="step-number">6</span>
                    </div>
                    <span class="step-label">Account Setup</span>
                </div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <form id="doctorRegistrationForm" enctype="multipart/form-data">
                
                <!-- Step 1: Personal Information -->
                <div class="form-step active" data-step="1">
                    <div class="step-header">
                        <h2><i class="fas fa-user"></i> Personal Information</h2>
                        <p>Let's start with your basic details</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Full Name <span class="required">*</span>
                            </label>
                            <input type="text" name="full_name" class="form-input" placeholder="Dr. John Doe" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-venus-mars"></i> Gender <span class="required">*</span>
                            </label>
                            <select name="gender" class="form-input" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Date of Birth <span class="required">*</span>
                            </label>
                            <input type="date" name="date_of_birth" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone"></i> Mobile Number <span class="required">*</span>
                            </label>
                            <input type="tel" name="mobile" class="form-input" placeholder="+91-9876543210" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email Address <span class="required">*</span>
                            </label>
                            <input type="email" name="email" class="form-input" placeholder="doctor@example.com" required>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Residential Address <span class="required">*</span>
                            </label>
                            <textarea name="address" class="form-input" rows="3" placeholder="Enter your complete address" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-city"></i> City <span class="required">*</span>
                            </label>
                            <input type="text" name="city" class="form-input" placeholder="Mumbai" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-map"></i> State <span class="required">*</span>
                            </label>
                            <input type="text" name="state" class="form-input" placeholder="Maharashtra" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-mail-bulk"></i> PIN Code <span class="required">*</span>
                            </label>
                            <input type="text" name="pin_code" class="form-input" placeholder="400001" required pattern="[0-9]{6}">
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-camera"></i> Profile Photo
                            </label>
                            <div class="file-upload-container">
                                <input type="file" name="profile_photo" id="profilePhoto" accept="image/*" class="file-input">
                                <label for="profilePhoto" class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click to upload or drag and drop</span>
                                    <small>JPG, PNG or GIF (MAX. 2MB)</small>
                                </label>
                                <div class="file-preview" id="profilePreview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Professional Information -->
                <div class="form-step" data-step="2">
                    <div class="step-header">
                        <h2><i class="fas fa-briefcase-medical"></i> Professional Information</h2>
                        <p>Tell us about your medical practice</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card"></i> Medical License Number <span class="required">*</span>
                            </label>
                            <input type="text" name="medical_license_number" class="form-input" placeholder="MH-12345-2020" required>
                            <small class="helper-text">This will be verified</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-certificate"></i> Medical Council Registration <span class="required">*</span>
                            </label>
                            <input type="text" name="medical_council_registration" class="form-input" placeholder="MCI-123456" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-stethoscope"></i> Primary Specialization <span class="required">*</span>
                            </label>
                            <select name="primary_specialization" class="form-input" required>
                                <option value="">Select Specialization</option>
                                <option value="Obstetrician">Obstetrician</option>
                                <option value="Gynecologist">Gynecologist</option>
                                <option value="Maternal-Fetal Medicine">Maternal-Fetal Medicine</option>
                                <option value="Perinatologist">Perinatologist</option>
                                <option value="Midwife Specialist">Midwife Specialist</option>
                                <option value="Reproductive Endocrinologist">Reproductive Endocrinologist</option>
                                <option value="Neonatologist">Neonatologist</option>
                                <option value="General Practitioner">General Practitioner</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-md"></i> Secondary Specialization
                            </label>
                            <input type="text" name="secondary_specialization" class="form-input" placeholder="e.g., High-Risk Pregnancy">
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-clock"></i> Years of Experience <span class="required">*</span>
                            </label>
                            <input type="number" name="years_of_experience" class="form-input" placeholder="10" min="0" max="60" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-hospital"></i> Hospital / Clinic Name <span class="required">*</span>
                            </label>
                            <input type="text" name="hospital_name" class="form-input" placeholder="City Hospital" required>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-map-marked-alt"></i> Workplace Address <span class="required">*</span>
                            </label>
                            <textarea name="workplace_address" class="form-input" rows="3" placeholder="Hospital/Clinic complete address" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-rupee-sign"></i> Consultation Fee <span class="required">*</span>
                            </label>
                            <input type="number" name="consultation_fee" class="form-input" placeholder="500" min="0" step="50" required>
                            <small class="helper-text">Amount in INR</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-language"></i> Languages Spoken <span class="required">*</span>
                            </label>
                            <input type="text" name="languages_spoken" class="form-input" placeholder="English, Hindi, Marathi" required>
                            <small class="helper-text">Comma separated</small>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Education & Certifications -->
                <div class="form-step" data-step="3">
                    <div class="step-header">
                        <h2><i class="fas fa-graduation-cap"></i> Education & Certifications</h2>
                        <p>Your academic qualifications</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-book"></i> Degree Name <span class="required">*</span>
                            </label>
                            <input type="text" name="degree_name" class="form-input" placeholder="MBBS, MD" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-university"></i> University / Medical College <span class="required">*</span>
                            </label>
                            <input type="text" name="university" class="form-input" placeholder="All India Institute of Medical Sciences" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i> Graduation Year <span class="required">*</span>
                            </label>
                            <input type="number" name="graduation_year" class="form-input" placeholder="2015" min="1950" max="2026" required>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-award"></i> Fellowship Details
                            </label>
                            <textarea name="fellowship_details" class="form-input" rows="3" placeholder="Fellowship in Maternal-Fetal Medicine, XYZ Institute, 2018"></textarea>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-certificate"></i> Additional Certifications
                            </label>
                            <textarea name="additional_certifications" class="form-input" rows="3" placeholder="List any additional certifications, courses, or training"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Verification Documents -->
                <div class="form-step" data-step="4">
                    <div class="step-header">
                        <h2><i class="fas fa-file-upload"></i> Verification Documents</h2>
                        <p>Upload required documents for verification</p>
                    </div>

                    <div class="documents-grid">
                        <!-- Medical License Certificate -->
                        <div class="document-upload-card">
                            <div class="document-header">
                                <i class="fas fa-file-medical"></i>
                                <h3>Medical License Certificate <span class="required">*</span></h3>
                            </div>
                            <div class="file-drop-zone" id="licenseDrop">
                                <input type="file" name="license_certificate" id="licenseCert" accept=".pdf,.jpg,.jpeg,.png" required class="file-input">
                                <label for="licenseCert" class="drop-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Drag & drop or click to upload</span>
                                    <small>PDF, JPG, PNG (MAX. 5MB)</small>
                                </label>
                                <div class="file-preview-doc" id="licensePreview"></div>
                            </div>
                        </div>

                        <!-- Degree Certificates -->
                        <div class="document-upload-card">
                            <div class="document-header">
                                <i class="fas fa-scroll"></i>
                                <h3>Degree Certificates <span class="required">*</span></h3>
                            </div>
                            <div class="file-drop-zone" id="degreeDrop">
                                <input type="file" name="degree_certificate" id="degreeCert" accept=".pdf,.jpg,.jpeg,.png" required class="file-input">
                                <label for="degreeCert" class="drop-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Drag & drop or click to upload</span>
                                    <small>PDF, JPG, PNG (MAX. 5MB)</small>
                                </label>
                                <div class="file-preview-doc" id="degreePreview"></div>
                            </div>
                        </div>

                        <!-- Government ID Proof -->
                        <div class="document-upload-card">
                            <div class="document-header">
                                <i class="fas fa-id-card"></i>
                                <h3>Government ID Proof <span class="required">*</span></h3>
                            </div>
                            <div class="file-drop-zone" id="govIdDrop">
                                <input type="file" name="government_id" id="govId" accept=".pdf,.jpg,.jpeg,.png" required class="file-input">
                                <label for="govId" class="drop-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Drag & drop or click to upload</span>
                                    <small>Aadhaar, PAN, Passport (MAX. 5MB)</small>
                                </label>
                                <div class="file-preview-doc" id="govIdPreview"></div>
                            </div>
                        </div>

                        <!-- Experience Certificates -->
                        <div class="document-upload-card">
                            <div class="document-header">
                                <i class="fas fa-briefcase"></i>
                                <h3>Experience Certificates</h3>
                            </div>
                            <div class="file-drop-zone" id="expDrop">
                                <input type="file" name="experience_certificate" id="expCert" accept=".pdf,.jpg,.jpeg,.png" class="file-input">
                                <label for="expCert" class="drop-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Drag & drop or click to upload</span>
                                    <small>PDF, JPG, PNG (MAX. 5MB)</small>
                                </label>
                                <div class="file-preview-doc" id="expPreview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Availability & Consultation -->
                <div class="form-step" data-step="5">
                    <div class="step-header">
                        <h2><i class="fas fa-calendar-check"></i> Availability & Consultation</h2>
                        <p>Set your consultation preferences</p>
                    </div>

                    <div class="availability-container">
                        <!-- Available Days -->
                        <div class="form-section">
                            <h3><i class="fas fa-calendar-week"></i> Available Days <span class="required">*</span></h3>
                            <div class="days-selector">
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Monday">
                                    <span class="day-label">Mon</span>
                                </label>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Tuesday">
                                    <span class="day-label">Tue</span>
                                </label>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Wednesday">
                                    <span class="day-label">Wed</span>
                                </label>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Thursday">
                                    <span class="day-label">Thu</span>
                                </label>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Friday">
                                    <span class="day-label">Fri</span>
                                </label>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Saturday">
                                    <span class="day-label">Sat</span>
                                </label>
                                <label class="day-checkbox">
                                    <input type="checkbox" name="available_days[]" value="Sunday">
                                    <span class="day-label">Sun</span>
                                </label>
                            </div>
                        </div>

                        <!-- Time Slots -->
                        <div class="form-section">
                            <h3><i class="fas fa-clock"></i> Time Slots <span class="required">*</span></h3>
                            <div class="time-slots-grid">
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" name="start_time" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Time</label>
                                    <input type="time" name="end_time" class="form-input" required>
                                </div>
                            </div>
                        </div>

                        <!-- Consultation Types -->
                        <div class="form-section">
                            <h3><i class="fas fa-laptop-medical"></i> Consultation Types</h3>
                            <div class="toggle-options">
                                <label class="toggle-card">
                                    <input type="checkbox" name="online_consultation" value="1">
                                    <div class="toggle-content">
                                        <i class="fas fa-video"></i>
                                        <span>Online Consultation</span>
                                        <small>Video/Audio calls</small>
                                    </div>
                                </label>
                                <label class="toggle-card">
                                    <input type="checkbox" name="in_person_consultation" value="1" checked>
                                    <div class="toggle-content">
                                        <i class="fas fa-hospital-user"></i>
                                        <span>In-Person Consultation</span>
                                        <small>Clinic visits</small>
                                    </div>
                                </label>
                                <label class="toggle-card">
                                    <input type="checkbox" name="emergency_availability" value="1">
                                    <div class="toggle-content">
                                        <i class="fas fa-ambulance"></i>
                                        <span>Emergency Availability</span>
                                        <small>24/7 emergency cases</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Account Setup -->
                <div class="form-step" data-step="6">
                    <div class="step-header">
                        <h2><i class="fas fa-lock"></i> Account Setup</h2>
                        <p>Create your login credentials</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">
                                <i class="fas fa-user-circle"></i> Username <span class="required">*</span>
                            </label>
                            <input type="text" name="username" class="form-input" placeholder="dr.johndoe" required>
                            <small class="helper-text">This will be used for login</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-key"></i> Password <span class="required">*</span>
                            </label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" class="form-input" placeholder="Create strong password" required>
                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            </div>
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar"></div>
                                <span class="strength-text"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-check-circle"></i> Confirm Password <span class="required">*</span>
                            </label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-input" placeholder="Re-enter password" required>
                                <i class="fas fa-eye password-toggle" id="toggleConfirmPassword"></i>
                            </div>
                            <small class="password-match" id="passwordMatch"></small>
                        </div>

                        <div class="form-group full-width">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms_accepted" required>
                                <span>I agree to the <a href="#" class="link">Terms & Conditions</a> and <a href="#" class="link">Privacy Policy</a> <span class="required">*</span></span>
                            </label>
                        </div>

                        <div class="form-group full-width">
                            <label class="checkbox-label">
                                <input type="checkbox" name="data_consent" required>
                                <span>I consent to the collection and processing of my personal and professional data for verification purposes <span class="required">*</span></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Navigation -->
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn btn-outline" id="saveDraftBtn">
                        <i class="fas fa-save"></i> Save Draft
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="fas fa-check-circle"></i> Submit Registration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal" id="successModal" style="display: none;">
        <div class="modal-overlay"></div>
        <div class="modal-content success-modal">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Registration Successful!</h2>
            <p>Thank you for registering with Aarunya. Your application has been submitted for verification.</p>
            <p class="info-text">You will receive an email notification once your account is verified by our admin team. This usually takes 24-48 hours.</p>
            <div class="modal-actions">
                <a href="../client/index.html" class="btn btn-primary">Go to Home</a>
            </div>
        </div>
    </div>

    <script src="scripts/doctor-registration.js"></script>
</body>
</html>
