<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
session_start();

// Check if email is set in session
$email = $_SESSION['otp_email'] ?? $_GET['email'] ?? '';
$purpose = $_SESSION['otp_purpose'] ?? $_GET['purpose'] ?? 'registration';
$name = $_SESSION['registration_data']['name'] ?? 'User';

if (empty($email)) {
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - Aarunya</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 50%, #1a0e2e 100%);
            color: #263238;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: -30%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(244, 114, 182, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        
        .otp-container {
            width: 100%;
            max-width: 480px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(244, 114, 182, 0.15);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
            position: relative;
            z-index: 1;
        }
        
        .otp-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
        }
        
        .logo i {
            font-size: 32px;
            background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .logo span {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #C4A7FF 0%, #00D1FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .otp-header h2 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .otp-header p {
            font-size: 14px;
            color: #546e7a;
            line-height: 1.5;
        }
        
        .email-display {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(244, 114, 182, 0.15);
            border: 1px solid rgba(244, 114, 182, 0.3);
            border-radius: 8px;
            color: #00D1FF;
            font-weight: 600;
            margin-top: 8px;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #FCA5A5;
        }
        
        .message-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: #86EFAC;
        }
        
        .message-info {
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.4);
            color: #93C5FD;
        }
        
        .otp-inputs {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin: 32px 0;
        }
        
        .otp-input {
            width: 56px;
            height: 64px;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            background: rgba(15, 23, 42, 0.5);
            border: 2px solid rgba(244, 114, 182, 0.2);
            border-radius: 12px;
            color: #263238;
            transition: all 0.25s ease;
        }
        
        .otp-input:focus {
            outline: none;
            border-color: #C4A7FF;
            background: rgba(15, 23, 42, 0.7);
            box-shadow: 0 0 0 3px rgba(244, 114, 182, 0.15);
            transform: scale(1.05);
        }
        
        .otp-input.filled {
            border-color: #C4A7FF;
            background: rgba(244, 114, 182, 0.1);
        }
        
        .timer-container {
            text-align: center;
            margin: 20px 0;
            font-size: 14px;
            color: #546e7a;
        }
        
        .timer {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(244, 114, 182, 0.1);
            border: 1px solid rgba(244, 114, 182, 0.2);
            border-radius: 8px;
            font-weight: 600;
            color: #C4A7FF;
        }
        
        .timer.expired {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
            color: #EF4444;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        
        .btn-verify {
            background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
            color: #263238;
            box-shadow: 0 4px 12px rgba(244, 114, 182, 0.3);
        }
        
        .btn-verify:hover:not(:disabled) {
            background: linear-gradient(135deg, #00D1FF 0%, #C4A7FF 100%);
            box-shadow: 0 6px 16px rgba(244, 114, 182, 0.5);
            transform: translateY(-1px);
        }
        
        .btn-verify:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-resend {
            background: rgba(244, 114, 182, 0.1);
            border: 2px solid rgba(244, 114, 182, 0.3);
            color: #C4A7FF;
        }
        
        .btn-resend:hover:not(:disabled) {
            background: rgba(244, 114, 182, 0.2);
            border-color: #C4A7FF;
        }
        
        .btn-resend:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        .spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .btn.loading .spinner {
            display: block;
        }
        
        .btn.loading .btn-text {
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }
        
        .back-link a {
            color: #C4A7FF;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .back-link a:hover {
            color: #00D1FF;
        }
        
        @media (max-width: 480px) {
            .otp-container {
                padding: 32px 24px;
            }
            
            .otp-inputs {
                gap: 8px;
            }
            
            .otp-input {
                width: 48px;
                height: 56px;
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="otp-header">
            <div class="logo">
                <i class="fas fa-heart-pulse"></i>
                <span>Aarunya</span>
            </div>
            <h2><?php echo $purpose === 'login' ? 'Verify Your Login' : 'Verify Your Email'; ?></h2>
            <p><?php echo $purpose === 'login' ? 'Enter the OTP sent to your email to complete login' : "We've sent a 6-digit OTP to"; ?></p>
            <div class="email-display">
                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email); ?>
            </div>
        </div>
        
        <div id="messageContainer"></div>
        
        <form id="otpForm">
            <div class="otp-inputs">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="0">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="1">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="2">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="3">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="4">
                <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" data-index="5">
            </div>
            
            <div class="timer-container">
                <div class="timer" id="timer">
                    <i class="fas fa-clock"></i>
                    <span id="timerText">5:00</span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-verify" id="verifyBtn">
                <div class="spinner"></div>
                <span class="btn-text">
                    <i class="fas fa-check-circle"></i> Verify OTP
                </span>
            </button>
            
            <button type="button" class="btn btn-resend" id="resendBtn" disabled>
                <i class="fas fa-redo"></i> Resend OTP
            </button>
        </form>
        
        <div class="back-link">
            <a href="<?php echo $purpose === 'login' ? 'login.php' : 'register.php'; ?>">
                <i class="fas fa-arrow-left"></i> Back to <?php echo $purpose === 'login' ? 'Login' : 'Registration'; ?>
            </a>
        </div>
    </div>

    <script>
        const email = <?php echo json_encode($email); ?>;
        const purpose = <?php echo json_encode($purpose); ?>;
        const name = <?php echo json_encode($name); ?>;
        
        const otpInputs = document.querySelectorAll('.otp-input');
        const otpForm = document.getElementById('otpForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const resendBtn = document.getElementById('resendBtn');
        const timerElement = document.getElementById('timer');
        const timerText = document.getElementById('timerText');
        const messageContainer = document.getElementById('messageContainer');
        
        let timeRemaining = 300; // 5 minutes in seconds
        let timerInterval;
        let resendCooldown = 60; // 60 seconds cooldown
        let resendCooldownInterval;
        
        // OTP Input Handling
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // Only allow numbers
                if (!/^\d$/.test(value)) {
                    e.target.value = '';
                    return;
                }
                
                // Add filled class
                if (value) {
                    e.target.classList.add('filled');
                    // Move to next input
                    if (index < otpInputs.length - 1) {
                        otpInputs[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }
            });
            
            input.addEventListener('keydown', (e) => {
                // Handle backspace
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                    otpInputs[index - 1].value = '';
                    otpInputs[index - 1].classList.remove('filled');
                }
                
                // Handle paste
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    navigator.clipboard.readText().then(text => {
                        const digits = text.replace(/\D/g, '').slice(0, 6);
                        digits.split('').forEach((digit, i) => {
                            if (otpInputs[i]) {
                                otpInputs[i].value = digit;
                                otpInputs[i].classList.add('filled');
                            }
                        });
                        if (digits.length === 6) {
                            otpInputs[5].focus();
                        }
                    });
                }
            });
        });
        
        // Focus first input on load
        otpInputs[0].focus();
        
        // Timer
        function startTimer() {
            clearInterval(timerInterval);
            timeRemaining = 300;
            timerElement.classList.remove('expired');
            
            timerInterval = setInterval(() => {
                timeRemaining--;
                
                const minutes = Math.floor(timeRemaining / 60);
                const seconds = timeRemaining % 60;
                timerText.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    timerElement.classList.add('expired');
                    timerText.textContent = 'Expired';
                    showMessage('OTP has expired. Please request a new one.', 'error');
                    resendBtn.disabled = false;
                }
            }, 1000);
        }
        
        // Resend Cooldown
        function startResendCooldown() {
            resendBtn.disabled = true;
            let cooldown = resendCooldown;
            
            resendCooldownInterval = setInterval(() => {
                cooldown--;
                resendBtn.innerHTML = `<i class="fas fa-clock"></i> Resend in ${cooldown}s`;
                
                if (cooldown <= 0) {
                    clearInterval(resendCooldownInterval);
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = '<i class="fas fa-redo"></i> Resend OTP';
                }
            }, 1000);
        }
        
        // Show Message
        function showMessage(message, type = 'info') {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                info: 'fa-info-circle'
            };
            
            messageContainer.innerHTML = `
                <div class="message message-${type}">
                    <i class="fas ${icons[type]}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
        }
        
        // Verify OTP
        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Get OTP code
            const otpCode = Array.from(otpInputs).map(input => input.value).join('');
            
            if (otpCode.length !== 6) {
                showMessage('Please enter all 6 digits', 'error');
                return;
            }
            
            // Show loading
            verifyBtn.classList.add('loading');
            verifyBtn.disabled = true;
            
            try {
                const response = await fetch('../server/handlers/otp_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'verify',
                        email: email,
                        otp_code: otpCode,
                        purpose: purpose
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('OTP verified successfully! Redirecting...', 'success');
                    
                    // Redirect based on purpose
                    setTimeout(() => {
                        if (purpose === 'login') {
                            // Complete login by calling login completion handler
                            window.location.href = '../server/handlers/complete_login.php';
                        } else if (purpose === 'registration') {
                            window.location.href = 'login.php?registered=success';
                        } else if (purpose === 'password_reset') {
                            window.location.href = 'reset-password.php?verified=true';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }, 1500);
                } else {
                    showMessage(result.message || 'Verification failed', 'error');
                    
                    // Clear inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    otpInputs[0].focus();
                    
                    verifyBtn.classList.remove('loading');
                    verifyBtn.disabled = false;
                }
            } catch (error) {
                console.error('Verification error:', error);
                showMessage('Network error. Please try again.', 'error');
                verifyBtn.classList.remove('loading');
                verifyBtn.disabled = false;
            }
        });
        
        // Resend OTP
        resendBtn.addEventListener('click', async () => {
            resendBtn.classList.add('loading');
            resendBtn.disabled = true;
            
            try {
                const response = await fetch('../server/handlers/otp_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'resend',
                        email: email,
                        name: name,
                        purpose: purpose
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showMessage('New OTP sent successfully!', 'success');
                    startTimer();
                    startResendCooldown();
                    
                    // Clear inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                        input.classList.remove('filled');
                    });
                    otpInputs[0].focus();
                } else {
                    showMessage(result.message || 'Failed to resend OTP', 'error');
                    resendBtn.classList.remove('loading');
                    resendBtn.disabled = false;
                }
            } catch (error) {
                console.error('Resend error:', error);
                showMessage('Network error. Please try again.', 'error');
                resendBtn.classList.remove('loading');
                resendBtn.disabled = false;
            }
        });
        
        // Start timer on page load
        startTimer();
        startResendCooldown();
    </script>
</body>
</html>

