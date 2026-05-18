<?php
/**
 * OTP Service
 * Handles OTP generation, validation, and management
 */

require_once __DIR__ . '/Environment.php';
require_once __DIR__ . '/MailService.php';

class OTPService {
    
    private $pdo;
    private $mailService;
    private $otpLength;
    private $expiryMinutes;
    private $maxAttempts;
    private $resendCooldown;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->mailService = new MailService();
        $this->otpLength = Environment::getInt('OTP_LENGTH', 6);
        $this->expiryMinutes = Environment::getInt('OTP_EXPIRY_MINUTES', 5);
        $this->maxAttempts = Environment::getInt('OTP_MAX_ATTEMPTS', 3);
        $this->resendCooldown = Environment::getInt('OTP_RESEND_COOLDOWN_SECONDS', 60);
    }
    
    /**
     * Generate random OTP code
     * 
     * @return string OTP code
     */
    private function generateOTPCode() {
        $min = pow(10, $this->otpLength - 1);
        $max = pow(10, $this->otpLength) - 1;
        return str_pad(random_int($min, $max), $this->otpLength, '0', STR_PAD_LEFT);
    }
    
    /**
     * Send OTP to email
     * 
     * @param string $email Recipient email
     * @param string $name Recipient name
     * @param string $purpose Purpose of OTP (registration, password_reset, 2fa, login)
     * @return array Result with success status and message
     */
    public function sendOTP($email, $name = 'User', $purpose = 'registration') {
        try {
            // Check rate limiting
            $rateLimitCheck = $this->checkRateLimit($email);
            if (!$rateLimitCheck['allowed']) {
                return [
                    'success' => false,
                    'message' => $rateLimitCheck['message']
                ];
            }
            
            // Check for recent OTP (resend cooldown)
            $cooldownCheck = $this->checkResendCooldown($email, $purpose);
            if (!$cooldownCheck['allowed']) {
                return [
                    'success' => false,
                    'message' => $cooldownCheck['message'],
                    'cooldown_remaining' => $cooldownCheck['remaining_seconds']
                ];
            }
            
            // Invalidate any existing OTPs for this email and purpose
            $this->invalidateExistingOTPs($email, $purpose);
            
            // Generate new OTP
            $otpCode = $this->generateOTPCode();
            
            // Calculate expiry time using PHP timestamp to avoid timezone issues
            $currentTime = time();
            $expiryTime = $currentTime + ($this->expiryMinutes * 60);
            
            // Store both as datetime strings
            $createdAt = date('Y-m-d H:i:s', $currentTime);
            $expiresAt = date('Y-m-d H:i:s', $expiryTime);
            
            // Get client info
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Store OTP in database with explicit created_at
            $sql = "INSERT INTO otp_codes (user_email, otp_code, purpose, expires_at, created_at, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $otpCode, $purpose, $expiresAt, $createdAt, $ipAddress, $userAgent]);
            
            // Log send attempt
            $this->logAttempt($email, $ipAddress, 'send', true);
            
            // Log OTP details for debugging
            error_log("OTP Generated - Email: $email, Code: $otpCode, Expires: $expiresAt, Purpose: $purpose");
            
            // Send email to user's actual email
            $emailSent = $this->mailService->sendOTP($email, $otpCode, $name);
            
            if (!$emailSent) {
                error_log("Failed to send OTP email to: $email");
                return [
                    'success' => false,
                    'message' => 'Failed to send OTP email. Please try again.'
                ];
            }
            
            error_log("OTP sent successfully to $email - Purpose: $purpose");
            
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your email',
                'expires_in' => $this->expiryMinutes * 60, // seconds
                'can_resend_after' => $this->resendCooldown // seconds
            ];
            
        } catch (Exception $e) {
            error_log("OTP send error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }
    
    /**
     * Verify OTP code
     * 
     * @param string $email User email
     * @param string $otpCode OTP code to verify
     * @param string $purpose Purpose of OTP
     * @return array Result with success status and message
     */
    public function verifyOTP($email, $otpCode, $purpose = 'registration') {
        try {
            // Get client info
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Get current PHP time for comparison
            $currentTime = date('Y-m-d H:i:s');
            
            // First, check if there's ANY OTP for this email (for debugging)
            $debugSql = "SELECT id, otp_code, expires_at, verified, attempts, created_at
                         FROM otp_codes 
                         WHERE user_email = ? 
                         AND purpose = ? 
                         ORDER BY created_at DESC 
                         LIMIT 1";
            $debugStmt = $this->pdo->prepare($debugSql);
            $debugStmt->execute([$email, $purpose]);
            $debugRecord = $debugStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($debugRecord) {
                $expiryTimestamp = strtotime($debugRecord['expires_at']);
                $currentTimestamp = strtotime($currentTime);
                $secondsUntilExpiry = $expiryTimestamp - $currentTimestamp;
                $status = $secondsUntilExpiry > 0 ? 'valid' : 'expired';
                error_log("OTP Debug - Email: $email, Code in DB: {$debugRecord['otp_code']}, Current Time: $currentTime, Expires: {$debugRecord['expires_at']}, Seconds Until Expiry: $secondsUntilExpiry, Status: $status, Verified: {$debugRecord['verified']}, Attempts: {$debugRecord['attempts']}");
            } else {
                error_log("OTP Debug - No OTP found for email: $email");
            }
            
            // Find valid OTP - Compare with PHP time instead of MySQL NOW()
            $sql = "SELECT * 
                    FROM otp_codes 
                    WHERE user_email = ? 
                    AND purpose = ? 
                    AND verified = FALSE 
                    AND expires_at > ?
                    ORDER BY created_at DESC 
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $purpose, $currentTime]);
            $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$otpRecord) {
                $this->logAttempt($email, $ipAddress, 'verify', false);
                
                // Provide more specific error message
                if ($debugRecord) {
                    if ($debugRecord['verified'] == 1) {
                        return [
                            'success' => false,
                            'message' => 'This OTP has already been used. Please request a new one.'
                        ];
                    }
                    
                    // Check expiry using PHP time
                    $expiryTimestamp = strtotime($debugRecord['expires_at']);
                    $currentTimestamp = strtotime($currentTime);
                    
                    if ($expiryTimestamp <= $currentTimestamp) {
                        return [
                            'success' => false,
                            'message' => 'OTP has expired. Please request a new one.'
                        ];
                    }
                    
                    if ($debugRecord['attempts'] >= $this->maxAttempts) {
                        return [
                            'success' => false,
                            'message' => 'Maximum attempts exceeded. Please request a new OTP.'
                        ];
                    }
                }
                
                return [
                    'success' => false,
                    'message' => 'Invalid or expired OTP. Please request a new one.'
                ];
            }
            
            // Check max attempts
            if ($otpRecord['attempts'] >= $this->maxAttempts) {
                $this->logAttempt($email, $ipAddress, 'verify', false);
                return [
                    'success' => false,
                    'message' => 'Maximum verification attempts exceeded. Please request a new OTP.'
                ];
            }
            
            // Increment attempts
            $updateSql = "UPDATE otp_codes SET attempts = attempts + 1 WHERE id = ?";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([$otpRecord['id']]);
            
            // Verify OTP code
            error_log("OTP Verification Attempt - Email: $email, Submitted Code: $otpCode, DB Code: {$otpRecord['otp_code']}, Match: " . ($otpRecord['otp_code'] === $otpCode ? 'YES' : 'NO'));
            
            if ($otpRecord['otp_code'] !== $otpCode) {
                $this->logAttempt($email, $ipAddress, 'verify', false);
                $remainingAttempts = $this->maxAttempts - ($otpRecord['attempts'] + 1);
                return [
                    'success' => false,
                    'message' => "Invalid OTP code. $remainingAttempts attempts remaining.",
                    'remaining_attempts' => $remainingAttempts
                ];
            }
            
            // Mark as verified
            $verifySql = "UPDATE otp_codes SET verified = TRUE, verified_at = NOW() WHERE id = ?";
            $verifyStmt = $this->pdo->prepare($verifySql);
            $verifyStmt->execute([$otpRecord['id']]);
            
            // Log successful verification
            $this->logAttempt($email, $ipAddress, 'verify', true);
            
            return [
                'success' => true,
                'message' => 'OTP verified successfully'
            ];
            
        } catch (Exception $e) {
            error_log("OTP verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Verification failed. Please try again.'
            ];
        }
    }
    
    /**
     * Resend OTP
     * 
     * @param string $email User email
     * @param string $name User name
     * @param string $purpose Purpose of OTP
     * @return array Result with success status and message
     */
    public function resendOTP($email, $name = 'User', $purpose = 'registration') {
        return $this->sendOTP($email, $name, $purpose);
    }
    
    /**
     * Check if OTP is verified
     * 
     * @param string $email User email
     * @param string $purpose Purpose of OTP
     * @return bool True if verified
     */
    public function isVerified($email, $purpose = 'registration') {
        try {
            $sql = "SELECT COUNT(*) FROM otp_codes 
                    WHERE user_email = ? 
                    AND purpose = ? 
                    AND verified = TRUE 
                    AND verified_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $purpose]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("OTP verification check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Invalidate existing OTPs for email and purpose
     * 
     * @param string $email User email
     * @param string $purpose Purpose of OTP
     */
    private function invalidateExistingOTPs($email, $purpose) {
        try {
            $sql = "UPDATE otp_codes 
                    SET verified = TRUE, verified_at = NOW() 
                    WHERE user_email = ? 
                    AND purpose = ? 
                    AND verified = FALSE";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $purpose]);
        } catch (Exception $e) {
            error_log("OTP invalidation error: " . $e->getMessage());
        }
    }
    
    /**
     * Check rate limiting (max 5 OTPs per email per hour)
     * 
     * @param string $email User email
     * @return array Result with allowed status and message
     */
    private function checkRateLimit($email) {
        try {
            $sql = "SELECT COUNT(*) FROM otp_attempts 
                    WHERE email = ? 
                    AND attempt_type = 'send' 
                    AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email]);
            $count = $stmt->fetchColumn();
            
            if ($count >= 5) {
                return [
                    'allowed' => false,
                    'message' => 'Too many OTP requests. Please try again after 1 hour.'
                ];
            }
            
            return ['allowed' => true];
        } catch (Exception $e) {
            error_log("Rate limit check error: " . $e->getMessage());
            return ['allowed' => true]; // Allow on error
        }
    }
    
    /**
     * Check resend cooldown
     * 
     * @param string $email User email
     * @param string $purpose Purpose of OTP
     * @return array Result with allowed status and message
     */
    private function checkResendCooldown($email, $purpose) {
        try {
            $sql = "SELECT created_at FROM otp_codes 
                    WHERE user_email = ? 
                    AND purpose = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $purpose]);
            $lastOTP = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lastOTP) {
                $lastSentTime = strtotime($lastOTP['created_at']);
                $currentTime = time();
                $timeDiff = $currentTime - $lastSentTime;
                
                if ($timeDiff < $this->resendCooldown) {
                    $remainingSeconds = $this->resendCooldown - $timeDiff;
                    return [
                        'allowed' => false,
                        'message' => "Please wait $remainingSeconds seconds before requesting a new OTP.",
                        'remaining_seconds' => $remainingSeconds
                    ];
                }
            }
            
            return ['allowed' => true];
        } catch (Exception $e) {
            error_log("Cooldown check error: " . $e->getMessage());
            return ['allowed' => true]; // Allow on error
        }
    }
    
    /**
     * Log OTP attempt
     * 
     * @param string $email User email
     * @param string $ipAddress IP address
     * @param string $type Attempt type (send/verify)
     * @param bool $success Success status
     */
    private function logAttempt($email, $ipAddress, $type, $success) {
        try {
            $sql = "INSERT INTO otp_attempts (email, ip_address, attempt_type, success) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$email, $ipAddress, $type, $success ? 1 : 0]);
        } catch (Exception $e) {
            error_log("OTP attempt logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Clean up expired OTPs (manual cleanup)
     */
    public function cleanupExpired() {
        try {
            // Delete expired OTPs
            $sql1 = "DELETE FROM otp_codes WHERE expires_at < NOW()";
            $this->pdo->exec($sql1);
            
            // Delete old verified OTPs (older than 24 hours)
            $sql2 = "DELETE FROM otp_codes 
                     WHERE verified = TRUE 
                     AND verified_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
            $this->pdo->exec($sql2);
            
            // Delete old attempts (older than 7 days)
            $sql3 = "DELETE FROM otp_attempts 
                     WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $this->pdo->exec($sql3);
            
            return true;
        } catch (Exception $e) {
            error_log("OTP cleanup error: " . $e->getMessage());
            return false;
        }
    }
}
?>
