<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
session_start();

require_once '../server/config/database.php';
require_once '../server/includes/validation.php';

$errors = $_SESSION['registration_errors'] ?? [];
$oldData = $_SESSION['registration_data'] ?? [];
$success = isset($_GET['success']) ? 'Registration successful! Please login.' : '';

// Clear session data after retrieving
unset($_SESSION['registration_errors']);
unset($_SESSION['registration_data']);

// Also clear any old OTP verification data if starting fresh
if (!isset($_POST['action'])) {
    unset($_SESSION['otp_verified_email']);
    unset($_SESSION['otp_verified_at']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aarunya Healthcare</title>
    <link rel="stylesheet" href="styles/premium-design-system.css">
    <?php include 'includes/theme_loader.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 50%, #0F172A 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-gradient-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .gradient-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            animation: float 20s ease-in-out infinite;
        }

        .blob-1 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #C4A7FF 0%, transparent 70%);
            top: -200px;
            right: -200px;
        }

        .blob-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #00D1FF 0%, transparent 70%);
            bottom: -150px;
            left: -150px;
            animation-delay: 5s;
        }

        .blob-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #7F5AF0 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }

        /* Register Container */
        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 900px;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            padding: 3rem;
            box-shadow: var(--shadow-xl);
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .register-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient-button);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .logo-text {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-hero);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .register-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .register-header p {
            font-size: 1rem;
            color: var(--text-secondary);
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2.5rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: all var(--transition-base);
        }

        .step.active .step-number {
            background: var(--gradient-button);
            border-color: var(--primary-purple);
            color: white;
            box-shadow: 0 0 20px rgba(196, 167, 255, 0.5);
        }

        .step-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .step.active .step-label {
            color: var(--text-primary);
        }

        .step-divider {
            width: 40px;
            height: 2px;
            background: rgba(255, 255, 255, 0.1);
            margin: 0 0.5rem;
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FCA5A5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86EFAC;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        /* Helper Text */
        .helper-text {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        /* Fix input icon positioning */
        .input-wrapper {
            position: relative;
            display: block;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.875rem;
            z-index: 2;
            pointer-events: none;
        }

        .input-wrapper .input {
            padding-left: 2.75rem;
            padding-right: 1rem;
        }

        .input-wrapper:has(#togglePassword) .input {
            padding-right: 2.75rem;
        }

        #togglePassword {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            font-size: 0.875rem;
            z-index: 3;
            transition: color 0.2s;
        }

        #togglePassword:hover {
            color: var(--primary-purple);
        }

        /* Required Indicator */
        .required {
            color: var(--danger);
            margin-left: 0.25rem;
        }

        /* Select Dropdown */
        select.input {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2394A3B8' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        select.input option {
            background: #1E293B;
            color: var(--text-primary);
            padding: 0.75rem;
        }

        /* Calculated Info Card */
        .calculated-info {
            grid-column: 1 / -1;
            padding: 1.5rem;
            background: linear-gradient(145deg, rgba(196,167,255,0.1), rgba(0,209,255,0.05));
            border: 1px solid rgba(196, 167, 255, 0.2);
            border-radius: var(--radius-lg);
            display: none;
        }

        .calculated-info.show {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .calculated-info h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--primary-purple);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .calculated-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .calculated-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .calculated-icon {
            width: 36px;
            height: 36px;
            background: var(--gradient-button);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            color: white;
        }

        .calculated-text strong {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
        }

        .calculated-text span {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-submit .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-submit.loading .spinner {
            display: block;
        }

        .btn-submit.loading .btn-text {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer Links */
        .register-footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--divider);
            text-align: center;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .footer-links a {
            color: var(--primary-purple);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }

        .footer-links a:hover {
            color: var(--accent-cyan);
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .register-container {
                padding: 2rem 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }

            .progress-steps {
                flex-direction: column;
                align-items: center;
            }

            .step-divider {
                width: 2px;
                height: 20px;
            }

            .calculated-grid {
                grid-template-columns: 1fr;
            }

            .register-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-gradient-animated">
        <div class="gradient-blob blob-1"></div>
        <div class="gradient-blob blob-2"></div>
        <div class="gradient-blob blob-3"></div>
    </div>

    <!-- Register Container -->
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-heart-pulse"></i>
                </div>
                <span class="logo-text">Aarunya</span>
            </div>
            <h2>Create Your Account</h2>
            <p>Join thousands of mothers on their pregnancy journey</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active" id="step1">
                <div class="step-number">1</div>
                <span class="step-label">Email Verification</span>
            </div>
            <div class="step-divider"></div>
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <span class="step-label">Account Info</span>
            </div>
            <div class="step-divider"></div>
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <span class="step-label">Complete</span>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php 
                if (isset($errors['general'])) {
                    // Don't show HTML in general errors on OTP page
                    $generalError = strip_tags($errors['general']);
                    echo '<div>' . htmlspecialchars($generalError) . '</div>';
                } else {
                    foreach ($errors as $error): 
                        // Don't show HTML in errors on OTP page
                        $cleanError = strip_tags($error);
                ?>
                    <div><?php echo htmlspecialchars($cleanError); ?></div>
                <?php 
                    endforeach;
                }
                ?>
            </div>
        </div>
        <?php 
        // Clear errors after displaying
        unset($_SESSION['registration_errors']);
        endif; 
        ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span>
                <?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
                $error = $_GET['error'];
                switch($error) {
                    case 'validation':
                        echo 'Please check your input and try again.';
                        break;
                    case 'database':
                        echo 'Database error. Please try again or contact support.';
                        break;
                    case 'system':
                        echo 'System error. Please try again later.';
                        break;
                    default:
                        echo 'An error occurred. Please try again.';
                }
                ?>
            </span>
        </div>
        <?php endif; ?>

        <!-- OTP Verification Section (Step 1) -->
        <div id="otpVerificationSection">
            <div class="form-grid">
                <div class="input-group form-group-full">
                    <label class="input-label">
                        Full Name<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            type="text" 
                            id="otp_name" 
                            class="input" 
                            placeholder="Enter your full name" 
                            required
                        >
                    </div>
                </div>

                <div class="input-group form-group-full">
                    <label class="input-label">
                        Email Address<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            id="otp_email" 
                            class="input" 
                            placeholder="example@gmail.com" 
                            required
                        >
                    </div>
                    <span class="helper-text">We'll send a verification code to this email</span>
                </div>

                <div class="input-group form-group-full" id="otpInputGroup" style="display: none;">
                    <label class="input-label">
                        Enter OTP Code<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input 
                            type="text" 
                            id="otp_code" 
                            class="input" 
                            placeholder="Enter 6-digit OTP" 
                            maxlength="6"
                            pattern="[0-9]{6}"
                        >
                    </div>
                    <span class="helper-text" id="otpTimer"></span>
                </div>
            </div>

            <div id="otpButtons">
                <button type="button" class="btn btn-primary btn-lg" id="sendOtpBtn" onclick="sendOTP()">
                    <i class="fas fa-paper-plane"></i>
                    <span>Send Verification Code</span>
                </button>

                <button type="button" class="btn btn-primary btn-lg" id="verifyOtpBtn" onclick="verifyOTP()" style="display: none;">
                    <i class="fas fa-check-circle"></i>
                    <span>Verify Code</span>
                </button>

                <button type="button" class="btn btn-secondary btn-lg" id="resendOtpBtn" onclick="resendOTP()" style="display: none; margin-top: 1rem;">
                    <i class="fas fa-redo"></i>
                    <span>Resend Code</span>
                </button>
            </div>

            <div id="otpMessage" style="margin-top: 1rem;"></div>
        </div>

        <!-- Registration Form (Step 2) - Hidden initially -->
        <form method="POST" action="../server/handlers/register_handler.php" id="registerForm" style="display: none;">
            <div class="form-grid">
                <!-- Full Name -->
                <div class="input-group">
                    <label class="input-label">
                        Full Name<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input 
                            type="text" 
                            name="name" 
                            class="input" 
                            placeholder="Enter your full name" 
                            value="<?php echo htmlspecialchars($oldData['name'] ?? ''); ?>" 
                            required
                        >
                    </div>
                </div>

                <!-- Email -->
                <div class="input-group">
                    <label class="input-label">
                        Email Address<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            class="input" 
                            placeholder="example@gmail.com" 
                            value="<?php echo htmlspecialchars($oldData['email'] ?? ''); ?>" 
                            required
                        >
                    </div>
                </div>

                <!-- Phone Number -->
                <div class="input-group">
                    <label class="input-label">
                        Phone Number<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone input-icon"></i>
                        <input 
                            type="tel" 
                            name="phone" 
                            class="input" 
                            placeholder="9876543210" 
                            value="<?php echo htmlspecialchars($oldData['phone'] ?? ''); ?>" 
                            required 
                            maxlength="10"
                        >
                    </div>
                    <span class="helper-text">10-digit mobile number</span>
                </div>

                <!-- Password -->
                <div class="input-group">
                    <label class="input-label">
                        Password<span class="required">*</span>
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input" 
                            placeholder="Create a strong password" 
                            required
                        >
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>
                    <span class="helper-text">Min 8 characters with uppercase, lowercase, number & special character</span>
                </div>

                <!-- Age -->
                <div class="input-group">
                    <label class="input-label">Age</label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar input-icon"></i>
                        <input 
                            type="number" 
                            name="age" 
                            class="input" 
                            placeholder="25" 
                            min="18" 
                            max="60"
                        >
                    </div>
                </div>

                <!-- Blood Group -->
                <div class="input-group">
                    <label class="input-label">Blood Group</label>
                    <div class="input-wrapper">
                        <i class="fas fa-droplet input-icon"></i>
                        <select name="blood_group" class="input">
                            <option value="">Select Blood Group</option>
                            <option value="A+">A+ (A Positive)</option>
                            <option value="A-">A- (A Negative)</option>
                            <option value="B+">B+ (B Positive)</option>
                            <option value="B-">B- (B Negative)</option>
                            <option value="AB+">AB+ (AB Positive)</option>
                            <option value="AB-">AB- (AB Negative)</option>
                            <option value="O+">O+ (O Positive)</option>
                            <option value="O-">O- (O Negative)</option>
                        </select>
                    </div>
                </div>

                <!-- Last Menstrual Period -->
                <div class="input-group form-group-full">
                    <label class="input-label">Last Menstrual Period (LMP)</label>
                    <div class="input-wrapper">
                        <i class="fas fa-calendar-day input-icon"></i>
                        <input 
                            type="date" 
                            name="lmp_date" 
                            id="lmp_date" 
                            class="input" 
                            max="<?php echo date('Y-m-d'); ?>"
                        >
                    </div>
                    <span class="helper-text">First day of your last period (optional)</span>
                </div>

                <!-- Calculated Information -->
                <div class="calculated-info" id="calculatedInfo">
                    <h4>
                        <i class="fas fa-calculator"></i>
                        Calculated Information
                    </h4>
                    <div class="calculated-grid">
                        <div class="calculated-item">
                            <div class="calculated-icon">
                                <i class="fas fa-baby"></i>
                            </div>
                            <div class="calculated-text">
                                <strong>Pregnancy Week</strong>
                                <span id="calcWeek">-</span>
                            </div>
                        </div>
                        <div class="calculated-item">
                            <div class="calculated-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="calculated-text">
                                <strong>Due Date</strong>
                                <span id="calcDueDate">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary btn-lg btn-submit" id="submitBtn">
                <div class="spinner"></div>
                <span class="btn-text">
                    <i class="fas fa-rocket"></i>
                    <span>Create Account</span>
                </span>
            </button>
        </form>

        <!-- Footer -->
        <div class="register-footer">
            <div class="footer-links">
                <div>
                    Already have an account? <a href="login.php">Login here</a>
                </div>
                <div>
                    Are you a doctor? <a href="../doctor/register.php" style="color: var(--accent-cyan);">Register as Doctor</a>
                </div>
                <div>
                    <a href="../index.html">Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // OTP Verification Variables
        let otpTimer = null;
        let otpExpiryTime = null;
        let canResendAfter = null;
        let verifiedEmail = null;

        // Send OTP
        async function sendOTP() {
            const name = document.getElementById('otp_name').value.trim();
            const email = document.getElementById('otp_email').value.trim();
            const sendBtn = document.getElementById('sendOtpBtn');
            const messageDiv = document.getElementById('otpMessage');

            // Validate inputs
            if (!name) {
                showMessage('Please enter your name', 'error');
                return;
            }

            if (!email || !isValidEmail(email)) {
                showMessage('Please enter a valid email address', 'error');
                return;
            }

            // Disable button and show loading
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Sending...</span>';

            try {
                const response = await fetch('../server/api/otp_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'send_otp',
                        email: email,
                        name: name
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message, 'success');
                    
                    // Show OTP input and verify button
                    document.getElementById('otpInputGroup').style.display = 'block';
                    document.getElementById('verifyOtpBtn').style.display = 'block';
                    document.getElementById('sendOtpBtn').style.display = 'none';
                    
                    // Disable email and name fields
                    document.getElementById('otp_email').disabled = true;
                    document.getElementById('otp_name').disabled = true;
                    
                    // Start timer
                    if (result.expires_in) {
                        startOTPTimer(result.expires_in);
                    }
                    
                    if (result.can_resend_after) {
                        canResendAfter = result.can_resend_after;
                        setTimeout(() => {
                            document.getElementById('resendOtpBtn').style.display = 'block';
                        }, canResendAfter * 1000);
                    }
                } else {
                    showMessage(result.message, 'error');
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Send Verification Code</span>';
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to send OTP. Please try again.', 'error');
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i> <span>Send Verification Code</span>';
            }
        }

        // Verify OTP
        async function verifyOTP() {
            const email = document.getElementById('otp_email').value.trim();
            const otpCode = document.getElementById('otp_code').value.trim();
            const verifyBtn = document.getElementById('verifyOtpBtn');

            // Validate OTP code
            if (!otpCode || otpCode.length !== 6) {
                showMessage('Please enter a valid 6-digit OTP code', 'error');
                return;
            }

            // Disable button and show loading
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Verifying...</span>';

            try {
                const response = await fetch('../server/api/otp_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'verify_otp',
                        email: email,
                        otp_code: otpCode
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('✓ Email verified successfully! Please complete your registration.', 'success');
                    verifiedEmail = email;
                    
                    // Clear timer
                    if (otpTimer) {
                        clearInterval(otpTimer);
                    }
                    
                    // Hide OTP section and show registration form
                    setTimeout(() => {
                        document.getElementById('otpVerificationSection').style.display = 'none';
                        document.getElementById('registerForm').style.display = 'block';
                        
                        // Pre-fill email and name in registration form
                        document.querySelector('input[name="email"]').value = email;
                        document.querySelector('input[name="email"]').readOnly = true;
                        document.querySelector('input[name="name"]').value = document.getElementById('otp_name').value;
                        
                        // Update progress steps
                        document.getElementById('step1').classList.remove('active');
                        document.getElementById('step2').classList.add('active');
                        
                        // Scroll to top
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }, 1500);
                } else {
                    showMessage(result.message, 'error');
                    verifyBtn.disabled = false;
                    verifyBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Verify Code</span>';
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Verification failed. Please try again.', 'error');
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = '<i class="fas fa-check-circle"></i> <span>Verify Code</span>';
            }
        }

        // Resend OTP
        async function resendOTP() {
            const name = document.getElementById('otp_name').value.trim();
            const email = document.getElementById('otp_email').value.trim();
            const resendBtn = document.getElementById('resendOtpBtn');

            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Resending...</span>';

            try {
                const response = await fetch('../server/api/otp_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'resend_otp',
                        email: email,
                        name: name
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('OTP resent successfully!', 'success');
                    
                    // Hide resend button temporarily
                    resendBtn.style.display = 'none';
                    
                    // Restart timer
                    if (result.expires_in) {
                        startOTPTimer(result.expires_in);
                    }
                    
                    if (result.can_resend_after) {
                        setTimeout(() => {
                            resendBtn.style.display = 'block';
                            resendBtn.disabled = false;
                            resendBtn.innerHTML = '<i class="fas fa-redo"></i> <span>Resend Code</span>';
                        }, result.can_resend_after * 1000);
                    }
                } else {
                    showMessage(result.message, 'error');
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = '<i class="fas fa-redo"></i> <span>Resend Code</span>';
                }
            } catch (error) {
                console.error('Error:', error);
                showMessage('Failed to resend OTP. Please try again.', 'error');
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i class="fas fa-redo"></i> <span>Resend Code</span>';
            }
        }

        // Start OTP expiry timer
        function startOTPTimer(seconds) {
            const timerSpan = document.getElementById('otpTimer');
            otpExpiryTime = Date.now() + (seconds * 1000);
            
            if (otpTimer) {
                clearInterval(otpTimer);
            }
            
            otpTimer = setInterval(() => {
                const remaining = Math.max(0, Math.floor((otpExpiryTime - Date.now()) / 1000));
                
                if (remaining > 0) {
                    const minutes = Math.floor(remaining / 60);
                    const secs = remaining % 60;
                    timerSpan.textContent = `Code expires in ${minutes}:${secs.toString().padStart(2, '0')}`;
                    timerSpan.style.color = remaining < 60 ? '#ef4444' : '#86EFAC';
                } else {
                    timerSpan.textContent = 'Code expired. Please request a new one.';
                    timerSpan.style.color = '#ef4444';
                    clearInterval(otpTimer);
                }
            }, 1000);
        }

        // Show message
        function showMessage(message, type) {
            const messageDiv = document.getElementById('otpMessage');
            messageDiv.innerHTML = `
                <div class="alert alert-${type === 'success' ? 'success' : 'error'}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Auto-hide success messages after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    messageDiv.innerHTML = '';
                }, 5000);
            }
        }

        // Validate email format
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Allow Enter key to trigger OTP actions
        document.getElementById('otp_email').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (document.getElementById('sendOtpBtn').style.display !== 'none') {
                    sendOTP();
                }
            }
        });

        document.getElementById('otp_code').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyOTP();
            }
        });

        // Original registration form scripts
        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });

            togglePassword.addEventListener('mouseenter', function() {
                this.style.color = 'var(--primary-purple)';
            });

            togglePassword.addEventListener('mouseleave', function() {
                this.style.color = 'var(--text-muted)';
            });
        }

        // Calculate pregnancy week and due date from LMP
        const lmpInput = document.getElementById('lmp_date');
        const calculatedInfo = document.getElementById('calculatedInfo');
        const calcWeek = document.getElementById('calcWeek');
        const calcDueDate = document.getElementById('calcDueDate');

        if (lmpInput) {
            lmpInput.addEventListener('change', function() {
                if (this.value) {
                    const lmpDate = new Date(this.value);
                    const today = new Date();

                    // Calculate days since LMP
                    const timeDiff = today - lmpDate;
                    const daysSinceLMP = Math.floor(timeDiff / (1000 * 60 * 60 * 24));

                    // Calculate pregnancy week
                    const pregnancyWeek = Math.floor(daysSinceLMP / 7);

                    // Calculate due date (LMP + 280 days)
                    const dueDate = new Date(lmpDate);
                    dueDate.setDate(dueDate.getDate() + 280);

                    // Format due date
                    const dueDateStr = dueDate.toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    // Display calculated info
                    if (pregnancyWeek >= 0 && pregnancyWeek <= 42) {
                        calcWeek.textContent = pregnancyWeek + ' weeks';
                        calcDueDate.textContent = dueDateStr;
                        calculatedInfo.classList.add('show');
                    } else {
                        calculatedInfo.classList.remove('show');
                    }
                } else {
                    calculatedInfo.classList.remove('show');
                }
            });
        }

        // Form submission loading state
        const registerForm = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');

        if (registerForm && submitBtn) {
            registerForm.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>
