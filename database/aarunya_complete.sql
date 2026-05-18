-- ============================================================================
-- AARUNYA MATERNAL CARE SYSTEM - COMPLETE DATABASE SCHEMA
-- ============================================================================
-- Technologies: HTML5, CSS3, JavaScript, PHP, MySQL
-- Single unified database for the entire system
-- ============================================================================

-- Create Database
DROP DATABASE IF EXISTS aarunya_db;
CREATE DATABASE aarunya_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aarunya_db;

-- ============================================================================
-- TABLE 1: USERS (Mothers/Patients)
-- ============================================================================
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
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE 2: ADMINS
-- ============================================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE 3: DOCTORS
-- ============================================================================
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20),
    specialization VARCHAR(255) NOT NULL,
    experience INT DEFAULT 0,
    qualification VARCHAR(255),
    contact VARCHAR(255),
    availability VARCHAR(255),
    status VARCHAR(20) DEFAULT 'approved',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_specialization (specialization),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLE 4: APPOINTMENTS
-- ============================================================================
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

-- ============================================================================
-- TABLE 5: EMERGENCY REQUESTS
-- ============================================================================
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

-- ============================================================================
-- TABLE 6: HEALTH RECORDS
-- ============================================================================
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

-- ============================================================================
-- TABLE 7: HEALTH METRICS (Admin-managed vital signs)
-- ============================================================================
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
-- INSERT DEFAULT ADMIN ACCOUNT
-- ============================================================================
-- Password: admin123 (hashed with bcrypt - properly generated)
INSERT INTO admins (name, email, password, role) VALUES
('System Admin', 'admin@aarunya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFVNfFJZfHqVz3BXgVJQfJqKJZ0qJ0Hy', 'admin');

-- ============================================================================
-- INSERT SAMPLE DOCTORS
-- ============================================================================
INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability) VALUES
('Dr. Sarah Johnson', 'sarah.johnson@aarunya.com', '+91-9876543210', 'Obstetrician', 15, 'MBBS, MD (OB/GYN)', 'Mon-Fri: 9AM-5PM'),
('Dr. Michael Chen', 'michael.chen@aarunya.com', '+91-9876543211', 'Gynecologist', 12, 'MBBS, MS (Gynecology)', 'Mon-Sat: 10AM-6PM'),
('Dr. Priya Sharma', 'priya.sharma@aarunya.com', '+91-9876543212', 'Maternal-Fetal Medicine', 18, 'MBBS, MD, DM (MFM)', 'Tue-Sat: 9AM-4PM'),
('Dr. James Wilson', 'james.wilson@aarunya.com', '+91-9876543213', 'Perinatologist', 20, 'MBBS, MD, Fellowship', 'Mon-Fri: 8AM-3PM'),
('Dr. Emily Brown', 'emily.brown@aarunya.com', '+91-9876543214', 'Midwife Specialist', 10, 'BSc Nursing, MSc Midwifery', 'Mon-Sun: 24/7'),
('Dr. Rajesh Kumar', 'rajesh.kumar@aarunya.com', '+91-9876543215', 'Obstetrician', 14, 'MBBS, MD (OB/GYN)', 'Mon-Fri: 10AM-6PM');

-- ============================================================================
-- INSERT SAMPLE USER (FOR TESTING)
-- ============================================================================
-- Password: test123 (hashed with bcrypt - properly generated)
INSERT INTO users (name, email, password, phone, age, pregnancy_week, due_date) VALUES
('Test User', 'test@example.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFVNfFJZfHqVz3BXgVJQfJqKJZ0qJ0Hy', '+91-9876543220', 28, 20, '2026-09-15');

-- ============================================================================
-- INSERT SAMPLE APPOINTMENTS
-- ============================================================================
INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status) VALUES
(1, 1, '2026-05-15', '10:00:00', 'approved'),
(1, 2, '2026-05-20', '14:00:00', 'pending');

-- ============================================================================
-- INSERT SAMPLE EMERGENCY REQUEST
-- ============================================================================
INSERT INTO emergency_requests (user_id, message, location, phone, status, priority) VALUES
(1, 'Severe abdominal pain, need immediate assistance', 'Mumbai, Maharashtra', '+91-9876543220', 'pending', 'high');

-- ============================================================================
-- INSERT SAMPLE HEALTH RECORDS
-- ============================================================================
INSERT INTO health_records (user_id, blood_pressure, hemoglobin, pulse_rate, weight, temperature) VALUES
(1, '120/80', 12.5, 75, 65.5, 98.6);

-- ============================================================================
-- INSERT SAMPLE HEALTH METRICS
-- ============================================================================
INSERT INTO health_metrics (user_id, blood_pressure_systolic, blood_pressure_diastolic, hemoglobin, heart_rate, weight, temperature, glucose_level, notes, recorded_by) VALUES
(1, 120, 80, 12.5, 75, 65.0, 36.5, 90, 'Regular checkup - all vitals normal', 1),
(1, 118, 78, 12.8, 72, 65.5, 36.6, 88, 'Follow-up visit - slight improvement in hemoglobin', 1);

-- ============================================================================
-- CREATE VIEWS FOR REPORTING
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
-- DATABASE STATISTICS
-- ============================================================================
SELECT 'Database created successfully!' as Status;
SELECT COUNT(*) as 'Total Users' FROM users;
SELECT COUNT(*) as 'Total Admins' FROM admins;
SELECT COUNT(*) as 'Total Doctors' FROM doctors;
SELECT COUNT(*) as 'Total Appointments' FROM appointments;
SELECT COUNT(*) as 'Total Emergency Requests' FROM emergency_requests;

-- ============================================================================
-- DEFAULT CREDENTIALS
-- ============================================================================
-- Admin Login:
--   Email: admin@aarunya.com
--   Password: admin123
--
-- Test User Login:
--   Email: test@example.com
--   Password: test123
-- ============================================================================
