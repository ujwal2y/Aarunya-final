<?php
/**
 * Mail Service
 * Handles email sending with SMTP support
 * Uses PHPMailer if available, falls back to PHP mail() function
 */

require_once __DIR__ . '/Environment.php';

class MailService {
    
    private $mailer = null;
    private $usePHPMailer = false;
    
    public function __construct() {
        // Always use custom SMTP implementation
        $this->usePHPMailer = false;
    }
    
    /**
     * Initialize PHPMailer
     */
    private function initPHPMailer() {
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = Environment::get('MAIL_HOST', 'smtp.gmail.com');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Environment::get('MAIL_USERNAME');
            $this->mailer->Password = Environment::get('MAIL_PASSWORD');
            $this->mailer->SMTPSecure = Environment::get('MAIL_ENCRYPTION', 'tls');
            $this->mailer->Port = Environment::getInt('MAIL_PORT', 587);
            
            // Default sender
            $this->mailer->setFrom(
                Environment::get('MAIL_FROM_ADDRESS'),
                Environment::get('MAIL_FROM_NAME', 'Aarunya Healthcare')
            );
            
            // Encoding
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);
            
        } catch (Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
            $this->usePHPMailer = false;
        }
    }
    
    /**
     * Send email
     * 
     * @param string|array $to Recipient email(s)
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string $altBody Plain text alternative
     * @return bool Success status
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            if ($this->usePHPMailer) {
                return $this->sendWithPHPMailer($to, $subject, $body, $altBody);
            } else {
                return $this->sendWithPHPMail($to, $subject, $body);
            }
        } catch (Exception $e) {
            error_log("Mail sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $body, $altBody) {
        try {
            // Clear previous recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email) {
                    $this->mailer->addAddress($email);
                }
            } else {
                $this->mailer->addAddress($to);
            }
            
            // Content
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);
            
            // Send
            $result = $this->mailer->send();
            
            if ($result) {
                error_log("Email sent successfully to: " . (is_array($to) ? implode(', ', $to) : $to));
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("PHPMailer send error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using direct SMTP connection
     */
    private function sendWithPHPMail($to, $subject, $body) {
        $host = Environment::get('MAIL_HOST', 'smtp.gmail.com');
        $port = Environment::getInt('MAIL_PORT', 587);
        $username = Environment::get('MAIL_USERNAME');
        $password = Environment::get('MAIL_PASSWORD');
        $from = Environment::get('MAIL_FROM_ADDRESS');
        $fromName = Environment::get('MAIL_FROM_NAME', 'Aarunya Healthcare');
        
        $smtp = null;
        
        try {
            // Create socket connection
            $smtp = fsockopen($host, $port, $errno, $errstr, 30);
            
            if (!$smtp) {
                error_log("SMTP Connection failed: $errstr ($errno)");
                return false;
            }
            
            // Set timeout
            stream_set_timeout($smtp, 30);
            
            // Read initial server response (220)
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '220')) {
                error_log("SMTP Initial response failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Send EHLO
            $serverName = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
            fputs($smtp, "EHLO $serverName\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '250')) {
                error_log("SMTP EHLO failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Start TLS
            fputs($smtp, "STARTTLS\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '220')) {
                error_log("SMTP STARTTLS failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Enable TLS encryption
            $crypto = stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            if (!$crypto) {
                error_log("SMTP TLS encryption failed");
                fclose($smtp);
                return false;
            }
            
            // Send EHLO again after TLS
            fputs($smtp, "EHLO $serverName\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '250')) {
                error_log("SMTP EHLO after TLS failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Authenticate
            fputs($smtp, "AUTH LOGIN\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '334')) {
                error_log("SMTP AUTH LOGIN failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Send username
            fputs($smtp, base64_encode($username) . "\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '334')) {
                error_log("SMTP Username failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Send password
            fputs($smtp, base64_encode($password) . "\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '235')) {
                error_log("SMTP Authentication failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Send MAIL FROM
            fputs($smtp, "MAIL FROM: <$from>\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '250')) {
                error_log("SMTP MAIL FROM failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Send RCPT TO
            $recipients = is_array($to) ? $to : [$to];
            foreach ($recipients as $recipient) {
                fputs($smtp, "RCPT TO: <$recipient>\r\n");
                $response = $this->readSMTPResponse($smtp);
                if (!$this->checkSMTPResponse($response, '250')) {
                    error_log("SMTP RCPT TO failed for $recipient: $response");
                    fclose($smtp);
                    return false;
                }
            }
            
            // Send DATA
            fputs($smtp, "DATA\r\n");
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '354')) {
                error_log("SMTP DATA failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Prepare email headers
            $date = date('r');
            $messageId = '<' . md5(uniqid(time())) . '@' . $serverName . '>';
            
            $headers = "Date: $date\r\n";
            $headers .= "From: $fromName <$from>\r\n";
            $headers .= "To: " . (is_array($to) ? implode(', ', $to) : $to) . "\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "Message-ID: $messageId\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";
            
            // Send headers and body
            fputs($smtp, $headers . "\r\n");
            fputs($smtp, $body . "\r\n");
            fputs($smtp, ".\r\n");
            
            $response = $this->readSMTPResponse($smtp);
            if (!$this->checkSMTPResponse($response, '250')) {
                error_log("SMTP Message send failed: $response");
                fclose($smtp);
                return false;
            }
            
            // Quit
            fputs($smtp, "QUIT\r\n");
            $response = $this->readSMTPResponse($smtp);
            
            fclose($smtp);
            
            error_log("Email sent successfully via SMTP to: " . (is_array($to) ? implode(', ', $to) : $to));
            return true;
            
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            if ($smtp) {
                fclose($smtp);
            }
            return false;
        }
    }
    
    /**
     * Read SMTP server response
     */
    private function readSMTPResponse($smtp) {
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            // Check if this is the last line (code followed by space, not hyphen)
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return trim($response);
    }
    
    /**
     * Check if SMTP response matches expected code
     */
    private function checkSMTPResponse($response, $expectedCode) {
        return strpos($response, $expectedCode) === 0;
    }
    
    /**
     * Send OTP email
     * 
     * @param string $to Recipient email
     * @param string $otp OTP code
     * @param string $name Recipient name
     * @return bool Success status
     */
    public function sendOTP($to, $otp, $name = 'User') {
        $subject = 'Your OTP Code - Aarunya Healthcare';
        $body = $this->getOTPTemplate($otp, $name);
        
        return $this->send($to, $subject, $body);
    }
    
    /**
     * Send welcome email
     * 
     * @param string $to Recipient email
     * @param string $name Recipient name
     * @return bool Success status
     */
    public function sendWelcome($to, $name) {
        $subject = 'Welcome to Aarunya Healthcare';
        $body = $this->getWelcomeTemplate($name);
        
        return $this->send($to, $subject, $body);
    }
    
    /**
     * Send appointment confirmation email
     * 
     * @param string $to Recipient email
     * @param array $appointmentData Appointment details
     * @return bool Success status
     */
    public function sendAppointmentConfirmation($to, $appointmentData) {
        $subject = 'Appointment Confirmation - Aarunya Healthcare';
        $body = $this->getAppointmentTemplate($appointmentData);
        
        return $this->send($to, $subject, $body);
    }
    
    /**
     * Send password reset email
     * 
     * @param string $to Recipient email
     * @param string $resetLink Password reset link
     * @param string $name Recipient name
     * @return bool Success status
     */
    public function sendPasswordReset($to, $resetLink, $name) {
        $subject = 'Password Reset Request - Aarunya Healthcare';
        $body = $this->getPasswordResetTemplate($resetLink, $name);
        
        return $this->send($to, $subject, $body);
    }
    
    /**
     * Get OTP email template
     */
    private function getOTPTemplate($otp, $name) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .otp-box { background: white; border: 2px dashed #C4A7FF; padding: 20px; text-align: center; margin: 20px 0; border-radius: 10px; }
        .otp-code { font-size: 36px; font-weight: bold; color: #C4A7FF; letter-spacing: 8px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; padding: 12px 30px; background: #C4A7FF; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌸 Aarunya Healthcare</h1>
            <p>Your OTP Verification Code</p>
        </div>
        <div class="content">
            <p>Hello <strong>{$name}</strong>,</p>
            <p>Your One-Time Password (OTP) for verification is:</p>
            
            <div class="otp-box">
                <div class="otp-code">{$otp}</div>
            </div>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This OTP is valid for <strong>5 minutes</strong></li>
                <li>Do not share this code with anyone</li>
                <li>If you didn't request this, please ignore this email</li>
            </ul>
            
            <p>Thank you for choosing Aarunya Healthcare!</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Aarunya Healthcare. All rights reserved.</p>
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get welcome email template
     */
    private function getWelcomeTemplate($name) {
        $appUrl = Environment::get('APP_URL');
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; padding: 12px 30px; background: #C4A7FF; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌸 Welcome to Aarunya Healthcare</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{$name}</strong>,</p>
            <p>Welcome to Aarunya Healthcare! We're thrilled to have you join our community.</p>
            <p>Your account has been successfully created. You can now:</p>
            <ul>
                <li>Book appointments with verified doctors</li>
                <li>Track your health metrics</li>
                <li>Access AI-powered wellness plans</li>
                <li>View medical documents</li>
                <li>Get emergency assistance</li>
            </ul>
            <p style="text-align: center;">
                <a href="{$appUrl}/client/login.php" class="button">Login to Your Account</a>
            </p>
            <p>If you have any questions, feel free to reach out to our support team.</p>
            <p>Best regards,<br>The Aarunya Healthcare Team</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Aarunya Healthcare. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get appointment confirmation template
     */
    private function getAppointmentTemplate($data) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .appointment-box { background: white; border-left: 4px solid #C4A7FF; padding: 20px; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Appointment Confirmed</h1>
        </div>
        <div class="content">
            <p>Dear <strong>{$data['patient_name']}</strong>,</p>
            <p>Your appointment has been confirmed!</p>
            
            <div class="appointment-box">
                <p><strong>Doctor:</strong> {$data['doctor_name']}</p>
                <p><strong>Specialization:</strong> {$data['specialization']}</p>
                <p><strong>Date:</strong> {$data['date']}</p>
                <p><strong>Time:</strong> {$data['time']}</p>
                <p><strong>Location:</strong> {$data['location']}</p>
            </div>
            
            <p><strong>Please note:</strong></p>
            <ul>
                <li>Arrive 15 minutes before your appointment</li>
                <li>Bring any relevant medical documents</li>
                <li>If you need to reschedule, please contact us at least 24 hours in advance</li>
            </ul>
            
            <p>We look forward to seeing you!</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Aarunya Healthcare. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get password reset template
     */
    private function getPasswordResetTemplate($resetLink, $name) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .button { display: inline-block; padding: 12px 30px; background: #C4A7FF; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Password Reset Request</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{$name}</strong>,</p>
            <p>We received a request to reset your password. Click the button below to create a new password:</p>
            <p style="text-align: center;">
                <a href="{$resetLink}" class="button">Reset Password</a>
            </p>
            <p><strong>Important:</strong></p>
            <ul>
                <li>This link is valid for <strong>1 hour</strong></li>
                <li>If you didn't request this, please ignore this email</li>
                <li>Your password will not change unless you click the link above</li>
            </ul>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #666;">{$resetLink}</p>
        </div>
        <div class="footer">
            <p>&copy; 2026 Aarunya Healthcare. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
?>
