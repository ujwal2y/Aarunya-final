-- ============================================================================
-- Migration: Add Doctor Active/Inactive Status
-- ============================================================================
-- This migration adds proper active/inactive status tracking for doctors
-- ============================================================================

USE aarunya_db;

-- Add is_active column if it doesn't exist
ALTER TABLE doctors 
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE COMMENT 'Doctor active status for patient visibility';

-- Add index for performance
ALTER TABLE doctors 
ADD INDEX IF NOT EXISTS idx_is_active (is_active);

-- Update existing doctors to be active by default
UPDATE doctors 
SET is_active = TRUE 
WHERE is_active IS NULL;

-- Add status_updated_at for tracking
ALTER TABLE doctors 
ADD COLUMN IF NOT EXISTS status_updated_at TIMESTAMP NULL COMMENT 'Last status change timestamp';

-- Add status_updated_by for audit trail
ALTER TABLE doctors 
ADD COLUMN IF NOT EXISTS status_updated_by INT NULL COMMENT 'Admin ID who changed status';

-- ============================================================================
-- Verification Query
-- ============================================================================
SELECT 
    id,
    name,
    email,
    status,
    is_active,
    is_verified,
    status_updated_at
FROM doctors
ORDER BY id;

-- ============================================================================
-- Notes:
-- - is_active: Controls visibility in patient module
-- - status: Controls overall account status (approved/pending/rejected)
-- - is_verified: Shows verification badge
-- ============================================================================
