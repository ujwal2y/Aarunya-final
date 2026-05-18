-- ============================================================================
-- Database Optimization Migration
-- ============================================================================
-- This migration optimizes the database for better performance and data integrity
-- ============================================================================

USE aarunya_db;

-- ============================================================================
-- SECTION 1: ADD MISSING FOREIGN KEYS
-- ============================================================================

-- Add foreign key for status_updated_by in doctors table
ALTER TABLE doctors
ADD CONSTRAINT fk_doctors_status_updated_by 
FOREIGN KEY (status_updated_by) REFERENCES admins(id) ON DELETE SET NULL;

-- ============================================================================
-- SECTION 2: ADD COMPOSITE INDEXES FOR COMMON QUERIES
-- ============================================================================

-- Appointments: Common query - get appointments by user and date
ALTER TABLE appointments
ADD INDEX idx_user_date (user_id, appointment_date);

-- Appointments: Common query - get appointments by doctor and status
ALTER TABLE appointments
ADD INDEX idx_doctor_status (doctor_id, status);

-- Appointments: Common query - get upcoming appointments
ALTER TABLE appointments
ADD INDEX idx_date_status (appointment_date, status);

-- Doctors: Common query - get active verified doctors
ALTER TABLE doctors
ADD INDEX idx_active_verified (is_active, is_verified, status);

-- Medical Documents: Common query - get patient documents by type
ALTER TABLE medical_documents
ADD INDEX idx_patient_type (patient_id, document_type);

-- Health Records: Common query - get recent records for user
ALTER TABLE health_records
ADD INDEX idx_user_date_desc (user_id, recorded_at DESC);

-- Health Metrics: Common query - get recent metrics for user
ALTER TABLE health_metrics
ADD INDEX idx_user_recorded (user_id, recorded_at DESC);

-- ============================================================================
-- SECTION 3: OPTIMIZE DATA TYPES
-- ============================================================================

-- Optimize phone number storage (VARCHAR(10) is sufficient for 10-digit numbers)
ALTER TABLE users MODIFY phone VARCHAR(10);
ALTER TABLE doctors MODIFY phone VARCHAR(10);
ALTER TABLE emergency_requests MODIFY phone VARCHAR(10);

-- Optimize status fields (use ENUM for better performance)
ALTER TABLE users MODIFY status ENUM('active', 'inactive', 'suspended') DEFAULT 'active';
ALTER TABLE admins MODIFY status ENUM('active', 'inactive') DEFAULT 'active';
ALTER TABLE doctors MODIFY status ENUM('pending', 'approved', 'rejected', 'suspended') DEFAULT 'approved';
ALTER TABLE appointments MODIFY status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending';
ALTER TABLE emergency_requests MODIFY status ENUM('pending', 'in_progress', 'resolved', 'cancelled') DEFAULT 'pending';
ALTER TABLE emergency_requests MODIFY priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'high';

-- ============================================================================
-- SECTION 4: ADD SOFT DELETE SUPPORT
-- ============================================================================

-- Add deleted_at column for soft deletes (better than hard deletes)
ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE doctors ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE appointments ADD COLUMN deleted_at TIMESTAMP NULL;

-- Add indexes for soft delete queries
ALTER TABLE users ADD INDEX idx_deleted_at (deleted_at);
ALTER TABLE doctors ADD INDEX idx_deleted_at (deleted_at);
ALTER TABLE appointments ADD INDEX idx_deleted_at (deleted_at);

-- ============================================================================
-- SECTION 5: ADD AUDIT TRAIL COLUMNS
-- ============================================================================

-- Add created_by and updated_by for better audit trail
ALTER TABLE appointments ADD COLUMN created_by INT NULL COMMENT 'User who created the appointment';
ALTER TABLE appointments ADD COLUMN updated_by INT NULL COMMENT 'User who last updated the appointment';

ALTER TABLE health_records ADD COLUMN created_by INT NULL COMMENT 'Admin/Doctor who created the record';
ALTER TABLE health_records ADD COLUMN updated_by INT NULL COMMENT 'Admin/Doctor who last updated the record';

-- ============================================================================
-- SECTION 6: ADD PERFORMANCE OPTIMIZATION COLUMNS
-- ============================================================================

-- Add full-text search index for doctor search
ALTER TABLE doctors ADD FULLTEXT INDEX ft_doctor_search (name, specialization, bio, qualification);

-- Add full-text search index for medical documents
ALTER TABLE medical_documents ADD FULLTEXT INDEX ft_document_search (document_name, description, notes);

-- ============================================================================
-- SECTION 7: ADD DATA VALIDATION CONSTRAINTS
-- ============================================================================

-- Ensure positive values for numeric fields
ALTER TABLE doctors ADD CONSTRAINT chk_experience CHECK (experience >= 0);
ALTER TABLE doctors ADD CONSTRAINT chk_consultation_fee CHECK (consultation_fee >= 0);
ALTER TABLE users ADD CONSTRAINT chk_age CHECK (age >= 0 AND age <= 150);
ALTER TABLE users ADD CONSTRAINT chk_pregnancy_week CHECK (pregnancy_week >= 0 AND pregnancy_week <= 42);

-- Ensure valid blood pressure values
ALTER TABLE health_metrics ADD CONSTRAINT chk_bp_systolic CHECK (blood_pressure_systolic >= 0 AND blood_pressure_systolic <= 300);
ALTER TABLE health_metrics ADD CONSTRAINT chk_bp_diastolic CHECK (blood_pressure_diastolic >= 0 AND blood_pressure_diastolic <= 200);

-- Ensure valid hemoglobin values
ALTER TABLE health_metrics ADD CONSTRAINT chk_hemoglobin CHECK (hemoglobin >= 0 AND hemoglobin <= 25);

-- Ensure valid heart rate
ALTER TABLE health_metrics ADD CONSTRAINT chk_heart_rate CHECK (heart_rate >= 0 AND heart_rate <= 300);

-- Ensure valid temperature
ALTER TABLE health_metrics ADD CONSTRAINT chk_temperature CHECK (temperature >= 30 AND temperature <= 45);

-- ============================================================================
-- SECTION 8: CREATE OPTIMIZED VIEWS
-- ============================================================================

-- Drop existing views if they exist
DROP VIEW IF EXISTS active_appointments;
DROP VIEW IF EXISTS pending_emergencies;

-- Recreate active_appointments view with better performance
CREATE VIEW active_appointments AS
SELECT 
    a.id,
    a.appointment_date,
    a.appointment_time,
    a.status,
    a.notes,
    u.id as user_id,
    u.name as user_name,
    u.email as user_email,
    u.phone as user_phone,
    u.pregnancy_week,
    d.id as doctor_id,
    d.name as doctor_name,
    d.specialization,
    d.hospital_affiliation,
    d.consultation_fee,
    a.created_at,
    a.updated_at
FROM appointments a
INNER JOIN users u ON a.user_id = u.id AND u.deleted_at IS NULL
INNER JOIN doctors d ON a.doctor_id = d.id AND d.deleted_at IS NULL
WHERE a.status != 'cancelled' 
  AND a.deleted_at IS NULL
ORDER BY a.appointment_date DESC, a.appointment_time DESC;

-- Recreate pending_emergencies view with better performance
CREATE VIEW pending_emergencies AS
SELECT 
    e.id,
    e.message,
    e.location,
    e.phone,
    e.priority,
    e.status,
    u.id as user_id,
    u.name as user_name,
    u.email as user_email,
    u.phone as user_phone,
    u.pregnancy_week,
    u.due_date,
    e.created_at,
    TIMESTAMPDIFF(MINUTE, e.created_at, NOW()) as minutes_pending
FROM emergency_requests e
INNER JOIN users u ON e.user_id = u.id AND u.deleted_at IS NULL
WHERE e.status = 'pending'
ORDER BY 
    CASE e.priority
        WHEN 'critical' THEN 1
        WHEN 'high' THEN 2
        WHEN 'medium' THEN 3
        WHEN 'low' THEN 4
    END,
    e.created_at ASC;

-- Create view for doctor statistics
CREATE VIEW doctor_statistics AS
SELECT 
    d.id,
    d.name,
    d.email,
    d.specialization,
    d.hospital_affiliation,
    d.is_active,
    d.is_verified,
    COUNT(DISTINCT a.id) as total_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END) as completed_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'pending' THEN a.id END) as pending_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'confirmed' THEN a.id END) as confirmed_appointments,
    COUNT(DISTINCT md.id) as total_documents_uploaded,
    COUNT(DISTINCT pr.id) as total_reports_generated,
    d.consultation_fee,
    d.created_at,
    d.updated_at
FROM doctors d
LEFT JOIN appointments a ON d.id = a.doctor_id AND a.deleted_at IS NULL
LEFT JOIN medical_documents md ON d.id = md.doctor_id
LEFT JOIN patient_reports pr ON d.id = pr.doctor_id
WHERE d.deleted_at IS NULL
GROUP BY d.id;

-- Create view for patient statistics
CREATE VIEW patient_statistics AS
SELECT 
    u.id,
    u.name,
    u.email,
    u.phone,
    u.age,
    u.pregnancy_week,
    u.due_date,
    COUNT(DISTINCT a.id) as total_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'completed' THEN a.id END) as completed_appointments,
    COUNT(DISTINCT CASE WHEN a.status = 'pending' THEN a.id END) as pending_appointments,
    COUNT(DISTINCT hr.id) as total_health_records,
    COUNT(DISTINCT hm.id) as total_health_metrics,
    COUNT(DISTINCT md.id) as total_medical_documents,
    COUNT(DISTINCT pr.id) as total_reports,
    COUNT(DISTINCT aw.id) as total_wellness_queries,
    MAX(hr.recorded_at) as last_health_record_date,
    MAX(a.appointment_date) as last_appointment_date,
    u.created_at,
    u.updated_at
FROM users u
LEFT JOIN appointments a ON u.id = a.user_id AND a.deleted_at IS NULL
LEFT JOIN health_records hr ON u.id = hr.user_id
LEFT JOIN health_metrics hm ON u.id = hm.user_id
LEFT JOIN medical_documents md ON u.id = md.patient_id
LEFT JOIN patient_reports pr ON u.id = pr.patient_id
LEFT JOIN ai_wellness_queries aw ON u.id = aw.patient_id
WHERE u.deleted_at IS NULL
GROUP BY u.id;

-- ============================================================================
-- SECTION 9: ADD TRIGGERS FOR AUTOMATIC UPDATES
-- ============================================================================

-- Trigger to update appointment count when appointment is created
DELIMITER //

CREATE TRIGGER after_appointment_insert
AFTER INSERT ON appointments
FOR EACH ROW
BEGIN
    -- You can add logic here to send notifications
    -- For now, this is a placeholder for future notification system
    INSERT INTO audit_log (table_name, action, record_id, created_at)
    VALUES ('appointments', 'INSERT', NEW.id, NOW())
    ON DUPLICATE KEY UPDATE created_at = NOW();
END//

DELIMITER ;

-- ============================================================================
-- SECTION 10: CREATE AUDIT LOG TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(50) NOT NULL,
    action ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    record_id INT NOT NULL,
    user_id INT NULL,
    user_type ENUM('admin', 'doctor', 'patient') NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user (user_id, user_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECTION 11: ANALYZE AND OPTIMIZE TABLES
-- ============================================================================

ANALYZE TABLE users;
ANALYZE TABLE admins;
ANALYZE TABLE doctors;
ANALYZE TABLE appointments;
ANALYZE TABLE emergency_requests;
ANALYZE TABLE health_records;
ANALYZE TABLE health_metrics;
ANALYZE TABLE medical_documents;
ANALYZE TABLE doctor_certificates;
ANALYZE TABLE patient_reports;
ANALYZE TABLE ai_wellness_queries;

OPTIMIZE TABLE users;
OPTIMIZE TABLE admins;
OPTIMIZE TABLE doctors;
OPTIMIZE TABLE appointments;
OPTIMIZE TABLE emergency_requests;
OPTIMIZE TABLE health_records;
OPTIMIZE TABLE health_metrics;
OPTIMIZE TABLE medical_documents;
OPTIMIZE TABLE doctor_certificates;
OPTIMIZE TABLE patient_reports;
OPTIMIZE TABLE ai_wellness_queries;

-- ============================================================================
-- VERIFICATION
-- ============================================================================

SELECT '============================================' as '';
SELECT 'DATABASE OPTIMIZATION COMPLETE!' as 'STATUS';
SELECT '============================================' as '';

-- Show table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    table_rows AS 'Rows'
FROM information_schema.TABLES
WHERE table_schema = 'aarunya_db'
ORDER BY (data_length + index_length) DESC;

-- Show indexes
SELECT 
    TABLE_NAME as 'Table',
    INDEX_NAME as 'Index',
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as 'Columns',
    INDEX_TYPE as 'Type'
FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = 'aarunya_db'
GROUP BY TABLE_NAME, INDEX_NAME, INDEX_TYPE
ORDER BY TABLE_NAME, INDEX_NAME;

SELECT '============================================' as '';
SELECT 'Optimization Summary:' as '';
SELECT '- Added composite indexes for common queries' as '';
SELECT '- Optimized data types (ENUM for status fields)' as '';
SELECT '- Added soft delete support' as '';
SELECT '- Added audit trail columns' as '';
SELECT '- Added data validation constraints' as '';
SELECT '- Created optimized views for reporting' as '';
SELECT '- Added full-text search indexes' as '';
SELECT '- Created audit log table' as '';
SELECT '- Analyzed and optimized all tables' as '';
SELECT '============================================' as '';

-- ============================================================================
-- END OF OPTIMIZATION SCRIPT
-- ============================================================================
