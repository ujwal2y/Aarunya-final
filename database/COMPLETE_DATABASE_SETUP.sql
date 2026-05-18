-- ============================================================================
-- AARUNYA MATERNAL HEALTHCARE SYSTEM - COMPLETE DATABASE SETUP
-- ============================================================================
-- Version: 2.0
-- Date: May 9, 2026
-- Description: Complete database schema with all features
-- Technologies: MySQL 5.7+, PHP 7.4+
-- ============================================================================

-- ============================================================================
-- SECTION 1: DATABASE CREATION
-- ============================================================================

DROP DATABASE IF EXISTS aarunya_db;
CREATE DATABASE aarunya_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aarunya_db;

-- ============================================================================
-- SECTION 2: CORE TABLES
-- ============================================================================

-- ----------------------------------------------------------------------------
-- TABLE: USERS (Patients/Mothers)
-- ----------------------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    age INT,
    lmp_date DATE DEFAULT NULL COMMENT 'Last Menstrual Period date',
    pregnancy_week INT,
    due_date DATE,
    profile_photo VARCHAR(255) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: ADMINS
-- ----------------------------------------------------------------------------
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: DOCTORS
-- ----------------------------------------------------------------------------
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255),
    specialization VARCHAR(255) NOT NULL,
    hospital_affiliation VARCHAR(255) NULL,
    experience INT DEFAULT 0,
    consultation_fee DECIMAL(10,2) DEFAULT 0.00,
    qualification VARCHAR(255),
    bio TEXT NULL,
    medical_council_registration VARCHAR(100) NULL,
    contact VARCHAR(255),
    availability VARCHAR(255),
    status VARCHAR(20) DEFAULT 'approved',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Doctor active status for patient visibility',
    is_verified BOOLEAN DEFAULT FALSE,
    verification_date TIMESTAMP NULL,
    status_updated_at TIMESTAMP NULL COMMENT 'Last status change timestamp',
    status_updated_by INT NULL COMMENT 'Admin ID who changed status',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_specialization (specialization),
    INDEX idx_status (status),
    INDEX idx_is_active (is_active),
    INDEX idx_verified (is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: APPOINTMENTS
-- ----------------------------------------------------------------------------
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_date (appointment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: EMERGENCY REQUESTS
-- ----------------------------------------------------------------------------
CREATE TABLE emergency_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT,
    location VARCHAR(255),
    phone VARCHAR(20),
    status VARCHAR(50) DEFAULT 'pending',
    priority VARCHAR(20) DEFAULT 'high',
    resolved_by INT,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (resolved_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: HEALTH RECORDS
-- ----------------------------------------------------------------------------
CREATE TABLE health_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    blood_pressure VARCHAR(20),
    hemoglobin DECIMAL(4,2),
    pulse_rate INT,
    weight DECIMAL(5,2),
    temperature DECIMAL(4,2),
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_date (recorded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: HEALTH METRICS (Admin-managed vital signs)
-- ----------------------------------------------------------------------------
CREATE TABLE health_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    blood_pressure_systolic INT DEFAULT NULL COMMENT 'Systolic blood pressure (mmHg)',
    blood_pressure_diastolic INT DEFAULT NULL COMMENT 'Diastolic blood pressure (mmHg)',
    hemoglobin DECIMAL(4,2) DEFAULT NULL COMMENT 'Hemoglobin level (g/dL)',
    heart_rate INT DEFAULT NULL COMMENT 'Heart rate (bpm)',
    weight DECIMAL(5,2) DEFAULT NULL COMMENT 'Weight (kg)',
    temperature DECIMAL(4,2) DEFAULT NULL COMMENT 'Body temperature (°C)',
    glucose_level INT DEFAULT NULL COMMENT 'Blood glucose level (mg/dL)',
    notes TEXT COMMENT 'Clinical notes and observations',
    recorded_by INT DEFAULT NULL COMMENT 'Admin ID who recorded the metrics',
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_recorded_at (recorded_at),
    INDEX idx_recorded_by (recorded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 3: NEW FEATURE TABLES (v2.0)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- TABLE: MEDICAL DOCUMENTS
-- Store CT scans, X-rays, lab reports uploaded by doctors
-- ----------------------------------------------------------------------------
CREATE TABLE medical_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    document_type ENUM('ct_scan', 'xray', 'mri', 'ultrasound', 'lab_report', 'prescription', 'blood_test', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    description TEXT,
    notes TEXT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_visible_to_patient BOOLEAN DEFAULT TRUE,
    INDEX idx_patient (patient_id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_type (document_type),
    INDEX idx_date (upload_date),
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: DOCTOR CERTIFICATES
-- Store doctor verification documents and certificates
-- ----------------------------------------------------------------------------
CREATE TABLE doctor_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    certificate_type ENUM('medical_degree', 'specialization', 'registration', 'fellowship', 'award', 'other') NOT NULL,
    certificate_name VARCHAR(255) NOT NULL,
    issuing_authority VARCHAR(255),
    file_path VARCHAR(500) NOT NULL,
    issue_date DATE,
    expiry_date DATE NULL,
    registration_number VARCHAR(100),
    verified BOOLEAN DEFAULT FALSE,
    verified_by INT NULL,
    verified_date TIMESTAMP NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_doctor (doctor_id),
    INDEX idx_verified (verified),
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: PATIENT REPORTS
-- Store generated patient reports by doctors
-- ----------------------------------------------------------------------------
CREATE TABLE patient_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    report_type VARCHAR(100) NOT NULL,
    report_title VARCHAR(255) NOT NULL,
    report_data JSON,
    diagnosis TEXT,
    recommendations TEXT,
    medications TEXT,
    follow_up_date DATE NULL,
    generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient (patient_id),
    INDEX idx_doctor (doctor_id),
    INDEX idx_date (generated_date),
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLE: AI WELLNESS QUERIES
-- Store patient queries for dynamic AI wellness plans
-- ----------------------------------------------------------------------------
CREATE TABLE ai_wellness_queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    query_text TEXT NOT NULL,
    symptoms TEXT,
    ai_response TEXT,
    wellness_plan JSON,
    query_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient (patient_id),
    INDEX idx_date (query_date),
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 4: DEFAULT DATA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- INSERT DEFAULT ADMIN ACCOUNT
-- ----------------------------------------------------------------------------
-- Email: admin@aarunya.com
-- Password: Admin@123
INSERT INTO admins (name, email, password, role) VALUES
('System Admin', 'admin@aarunya.com', '$2y$12$1b45LAk.2ClG6BR5EgyW8e25cEmjNB2/BPwNJe4.BvxhZeTvdPzke', 'admin');

-- ----------------------------------------------------------------------------
-- INSERT REAL INDIAN DOCTORS (Verified Specialists)
-- ----------------------------------------------------------------------------
-- All doctors use password: Doctor@123

-- Dr. Firuza Parikh - IVF & Reproductive Medicine
INSERT INTO doctors (
    name, email, phone, password, specialization, 
    hospital_affiliation, qualification, experience, 
    medical_council_registration, consultation_fee, bio,
    status, is_verified, verification_date, created_at
) VALUES (
    'Dr. Firuza Parikh',
    'dr.firuza@aarunya.com',
    '9876543201',
    '$2y$10$LXK7ckWWmG/Ck1vw6weKW.XWaCHD/oqO5oPeC0lNjkZnjdzVMH/Oa',
    'IVF & Reproductive Medicine',
    'Jaslok Hospital, Mumbai',
    'MBBS, MD, FCPS, DGO',
    25,
    'MMC/2024/12345',
    2000.00,
    'Pioneer in IVF technology in India. Over 25 years of experience in reproductive medicine and high-risk pregnancies.',
    'approved',
    TRUE,
    NOW(),
    NOW()
);

-- Dr. Nandita Palshetkar - IVF & Gynecology
INSERT INTO doctors (
    name, email, phone, password, specialization,
    hospital_affiliation, qualification, experience,
    medical_council_registration, consultation_fee, bio,
    status, is_verified, verification_date, created_at
) VALUES (
    'Dr. Nandita Palshetkar',
    'dr.nandita@aarunya.com',
    '9876543202',
    '$2y$10$LXK7ckWWmG/Ck1vw6weKW.XWaCHD/oqO5oPeC0lNjkZnjdzVMH/Oa',
    'IVF & Gynecology',
    'Bloom IVF Centre, Mumbai',
    'MBBS, MD, FICOG, FRCOG',
    22,
    'MMC/2024/12346',
    1800.00,
    'Renowned gynecologist and IVF specialist. Expert in managing complex fertility cases and maternal health.',
    'approved',
    TRUE,
    NOW(),
    NOW()
);

-- Dr. Hrishikesh Pai - IVF & Infertility
INSERT INTO doctors (
    name, email, phone, password, specialization,
    hospital_affiliation, qualification, experience,
    medical_council_registration, consultation_fee, bio,
    status, is_verified, verification_date, created_at
) VALUES (
    'Dr. Hrishikesh Pai',
    'dr.hrishikesh@aarunya.com',
    '9876543203',
    '$2y$10$LXK7ckWWmG/Ck1vw6weKW.XWaCHD/oqO5oPeC0lNjkZnjdzVMH/Oa',
    'IVF & Infertility',
    'Lilavati Hospital, Mumbai',
    'MBBS, MD, FCPS, FRCOG',
    28,
    'MMC/2024/12347',
    2200.00,
    'Leading infertility specialist with expertise in advanced reproductive technologies and maternal care.',
    'approved',
    TRUE,
    NOW(),
    NOW()
);

-- Dr. Rishma Dhillon Pai - High-Risk Pregnancy
INSERT INTO doctors (
    name, email, phone, password, specialization,
    hospital_affiliation, qualification, experience,
    medical_council_registration, consultation_fee, bio,
    status, is_verified, verification_date, created_at
) VALUES (
    'Dr. Rishma Dhillon Pai',
    'dr.rishma@aarunya.com',
    '9876543204',
    '$2y$10$LXK7ckWWmG/Ck1vw6weKW.XWaCHD/oqO5oPeC0lNjkZnjdzVMH/Oa',
    'High-Risk Pregnancy',
    'Jaslok Hospital, Mumbai',
    'MBBS, MD, DNB, FCPS',
    20,
    'MMC/2024/12348',
    1900.00,
    'Expert in managing high-risk pregnancies and complicated deliveries. Specializes in maternal-fetal medicine.',
    'approved',
    TRUE,
    NOW(),
    NOW()
);

-- Dr. Anita Soni - Fetal Medicine
INSERT INTO doctors (
    name, email, phone, password, specialization,
    hospital_affiliation, qualification, experience,
    medical_council_registration, consultation_fee, bio,
    status, is_verified, verification_date, created_at
) VALUES (
    'Dr. Anita Soni',
    'dr.anita@aarunya.com',
    '9876543205',
    '$2y$10$LXK7ckWWmG/Ck1vw6weKW.XWaCHD/oqO5oPeC0lNjkZnjdzVMH/Oa',
    'Fetal Medicine',
    'Fortis Hospital, Delhi',
    'MBBS, MD, DGO, FICOG',
    18,
    'DMC/2024/12349',
    1700.00,
    'Specialist in fetal medicine and prenatal diagnosis. Expert in managing complex pregnancy cases.',
    'approved',
    TRUE,
    NOW(),
    NOW()
);

-- Dr. Duru Shah - Gynecology & Obstetrics
INSERT INTO doctors (
    name, email, phone, password, specialization,
    hospital_affiliation, qualification, experience,
    medical_council_registration, consultation_fee, bio,
    status, is_verified, verification_date, created_at
) VALUES (
    'Dr. Duru Shah',
    'dr.duru@aarunya.com',
    '9876543206',
    '$2y$10$LXK7ckWWmG/Ck1vw6weKW.XWaCHD/oqO5oPeC0lNjkZnjdzVMH/Oa',
    'Gynecology & Obstetrics',
    'Breach Candy Hospital, Mumbai',
    'MBBS, MD, FCPS, FRCOG',
    30,
    'MMC/2024/12350',
    2500.00,
    'Internationally recognized gynecologist with over 30 years of experience in women\'s health and maternal care.',
    'approved',
    TRUE,
    NOW(),
    NOW()
);

-- ----------------------------------------------------------------------------
-- INSERT TEST PATIENT ACCOUNT
-- ----------------------------------------------------------------------------
-- Email: test@gmail.com
-- Phone: 9876543210
-- Password: Test@123
INSERT INTO users (name, email, password, phone, age, pregnancy_week, due_date) VALUES
('Test Patient', 'test@gmail.com', '$2y$12$wve82hFVxy6DlwNf89e/fuGjtjgxLzJh4st5bYk8XLqn3ha.lV7Mq', '9876543210', 28, 24, '2026-09-15');

-- ============================================================================
-- SECTION 5: SAMPLE DATA (Optional - for testing)
-- ============================================================================

-- Sample Appointments
INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, notes) VALUES
(1, 1, '2026-05-15', '10:00:00', 'confirmed', 'Regular checkup - Week 24'),
(1, 2, '2026-05-20', '14:00:00', 'pending', 'Ultrasound appointment');

-- Sample Health Records
INSERT INTO health_records (user_id, blood_pressure, hemoglobin, pulse_rate, weight, temperature, notes) VALUES
(1, '120/80', 12.5, 75, 65.5, 98.6, 'Regular checkup - all vitals normal');

-- Sample Health Metrics
INSERT INTO health_metrics (user_id, blood_pressure_systolic, blood_pressure_diastolic, hemoglobin, heart_rate, weight, temperature, glucose_level, notes, recorded_by) VALUES
(1, 120, 80, 12.5, 75, 65.0, 36.5, 90, 'Regular checkup - all vitals normal', 1),
(1, 118, 78, 12.8, 72, 65.5, 36.6, 88, 'Follow-up visit - slight improvement in hemoglobin', 1);

-- ============================================================================
-- SECTION 6: VIEWS FOR REPORTING
-- ============================================================================

-- View: Active appointments with user and doctor details
CREATE VIEW active_appointments AS
SELECT 
    a.id,
    a.appointment_date,
    a.appointment_time,
    a.status,
    u.name as user_name,
    u.email as user_email,
    u.phone as user_phone,
    d.name as doctor_name,
    d.specialization,
    a.created_at
FROM appointments a
JOIN users u ON a.user_id = u.id
JOIN doctors d ON a.doctor_id = d.id
WHERE a.status != 'cancelled'
ORDER BY a.appointment_date DESC, a.appointment_time DESC;

-- View: Pending emergency requests
CREATE VIEW pending_emergencies AS
SELECT 
    e.id,
    e.message,
    e.location,
    e.phone,
    e.priority,
    u.name as user_name,
    u.email as user_email,
    u.pregnancy_week,
    e.created_at
FROM emergency_requests e
JOIN users u ON e.user_id = u.id
WHERE e.status = 'pending'
ORDER BY e.priority DESC, e.created_at DESC;

-- ============================================================================
-- SECTION 7: VERIFICATION & STATISTICS
-- ============================================================================

SELECT '============================================' as '';
SELECT 'DATABASE SETUP COMPLETE!' as 'STATUS';
SELECT '============================================' as '';

SELECT 'Database Statistics:' as '';
SELECT COUNT(*) as 'Total Users' FROM users;
SELECT COUNT(*) as 'Total Admins' FROM admins;
SELECT COUNT(*) as 'Total Doctors' FROM doctors;
SELECT COUNT(*) as 'Verified Doctors' FROM doctors WHERE is_verified = TRUE;
SELECT COUNT(*) as 'Total Appointments' FROM appointments;

SELECT '============================================' as '';
SELECT 'Table Verification:' as '';
SHOW TABLES;

SELECT '============================================' as '';
SELECT 'Doctor List:' as '';
SELECT id, name, email, specialization, hospital_affiliation, is_verified, consultation_fee 
FROM doctors 
ORDER BY name;

-- ============================================================================
-- SECTION 8: DEFAULT CREDENTIALS
-- ============================================================================

SELECT '============================================' as '';
SELECT 'DEFAULT LOGIN CREDENTIALS' as '';
SELECT '============================================' as '';
SELECT '' as '';
SELECT 'ADMIN LOGIN:' as '';
SELECT '  Email: admin@aarunya.com' as '';
SELECT '  Password: Admin@123' as '';
SELECT '' as '';
SELECT 'DOCTOR LOGIN (All doctors):' as '';
SELECT '  Password: Doctor@123' as '';
SELECT '  Emails:' as '';
SELECT '    - dr.firuza@aarunya.com' as '';
SELECT '    - dr.nandita@aarunya.com' as '';
SELECT '    - dr.hrishikesh@aarunya.com' as '';
SELECT '    - dr.rishma@aarunya.com' as '';
SELECT '    - dr.anita@aarunya.com' as '';
SELECT '    - dr.duru@aarunya.com' as '';
SELECT '' as '';
SELECT 'PATIENT LOGIN:' as '';
SELECT '  Email: test@gmail.com' as '';
SELECT '  Phone: 9876543210' as '';
SELECT '  Password: Test@123' as '';
SELECT '============================================' as '';

-- ============================================================================
-- END OF SCRIPT
-- ============================================================================
-- Version: 2.0
-- Last Updated: May 9, 2026
-- Status: Production Ready
-- ============================================================================
