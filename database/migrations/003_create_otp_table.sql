-- ============================================================================
-- OTP System Migration
-- Creates table for OTP verification system
-- ============================================================================

-- Create OTP codes table
CREATE TABLE IF NOT EXISTS otp_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    purpose ENUM('registration', 'password_reset', '2fa', 'login') NOT NULL DEFAULT 'registration',
    expires_at DATETIME NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    INDEX idx_email (user_email),
    INDEX idx_expires (expires_at),
    INDEX idx_verified (verified),
    INDEX idx_purpose (purpose),
    INDEX idx_email_purpose (user_email, purpose)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create OTP attempts tracking table (for rate limiting)
CREATE TABLE IF NOT EXISTS otp_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_type ENUM('send', 'verify') NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email_ip (email, ip_address),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create cleanup event to remove expired OTPs (runs every hour)
DROP EVENT IF EXISTS cleanup_expired_otps;

CREATE EVENT cleanup_expired_otps
ON SCHEDULE EVERY 1 HOUR
DO
    DELETE FROM otp_codes 
    WHERE expires_at < NOW() 
    OR (verified = TRUE AND verified_at < DATE_SUB(NOW(), INTERVAL 24 HOUR));

-- Create cleanup event for old OTP attempts (runs daily)
DROP EVENT IF EXISTS cleanup_old_otp_attempts;

CREATE EVENT cleanup_old_otp_attempts
ON SCHEDULE EVERY 1 DAY
DO
    DELETE FROM otp_attempts 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Enable event scheduler if not already enabled
SET GLOBAL event_scheduler = ON;

-- ============================================================================
-- Migration Complete
-- ============================================================================
