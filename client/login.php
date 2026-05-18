<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aarunya Healthcare</title>
    <link rel="stylesheet" href="styles/premium-design-system.css">
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
            overflow: hidden;
        }

        /* Animated Background Blobs */
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
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #C4A7FF 0%, transparent 70%);
            top: -150px;
            right: -150px;
        }

        .blob-2 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #00D1FF 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-30px, 30px) scale(0.9); }
        }

        /* Login Container */
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: var(--glass-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--glass-border);
            border-radius: var(--radius-2xl);
            overflow: hidden;
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

        /* Left Section - Visual */
        .login-visual {
            background: linear-gradient(145deg, rgba(196,167,255,0.15), rgba(0,209,255,0.08));
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .login-visual::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(196, 167, 255, 0.1), transparent);
            animation: rotate 15s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .visual-content {
            position: relative;
            z-index: 1;
        }

        .visual-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .visual-logo-icon {
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

        .visual-logo-text {
            font-size: 1.75rem;
            font-weight: 800;
            background: var(--gradient-hero);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .visual-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #FFFFFF 0%, #C4A7FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .visual-description {
            font-size: 1.125rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .visual-features {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .visual-feature {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            transition: all var(--transition-base);
        }

        .visual-feature:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary-purple);
            transform: translateX(5px);
        }

        .visual-feature-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-button);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            color: white;
            flex-shrink: 0;
        }

        .visual-feature-text h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .visual-feature-text p {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Right Section - Form */
        .login-form-section {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            font-size: 1rem;
            color: var(--text-secondary);
        }

        /* Role Toggle */
        .role-toggle {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 0.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 2rem;
        }

        .role-toggle input {
            display: none;
        }

        .role-option {
            padding: 0.75rem 1rem;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            background: transparent;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .role-toggle input:checked + .role-option {
            background: var(--gradient-button);
            color: white;
            border-color: var(--primary-purple);
            box-shadow: 0 4px 12px rgba(127, 90, 240, 0.3);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
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

        /* Form Styles */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
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

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -0.5rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-purple);
            cursor: pointer;
        }

        .checkbox-wrapper label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            cursor: pointer;
            margin: 0;
        }

        .forgot-link {
            font-size: 0.875rem;
            color: var(--primary-purple);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--transition-fast);
        }

        .forgot-link:hover {
            color: var(--accent-cyan);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--divider);
        }

        .divider span {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
        }

        .register-link {
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .register-link a {
            color: var(--primary-purple);
            text-decoration: none;
            font-weight: 700;
            transition: color var(--transition-fast);
        }

        .register-link a:hover {
            color: var(--accent-cyan);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .login-container {
                grid-template-columns: 1fr;
                max-width: 500px;
            }

            .login-visual {
                display: none;
            }

            .login-form-section {
                padding: 2rem;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .login-form-section {
                padding: 1.5rem;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }

            .role-toggle {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-gradient-animated">
        <div class="gradient-blob blob-1"></div>
        <div class="gradient-blob blob-2"></div>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Left Section - Visual -->
        <div class="login-visual">
            <div class="visual-content">
                <div class="visual-logo">
                    <div class="visual-logo-icon">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <span class="visual-logo-text">Aarunya</span>
                </div>

                <h1 class="visual-title">
                    Welcome Back to<br>
                    Your Care Journey
                </h1>
                <p class="visual-description">
                    Access your personalized maternal healthcare dashboard with AI-powered insights and expert support.
                </p>

                <div class="visual-features">
                    <div class="visual-feature">
                        <div class="visual-feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="visual-feature-text">
                            <h4>AI Health Assistant</h4>
                            <p>24/7 intelligent monitoring</p>
                        </div>
                    </div>

                    <div class="visual-feature">
                        <div class="visual-feature-icon">
                            <i class="fas fa-user-doctor"></i>
                        </div>
                        <div class="visual-feature-text">
                            <h4>Expert Consultations</h4>
                            <p>Connect with specialists</p>
                        </div>
                    </div>

                    <div class="visual-feature">
                        <div class="visual-feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="visual-feature-text">
                            <h4>Health Tracking</h4>
                            <p>Monitor your progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section - Form -->
        <div class="login-form-section">
            <div class="form-header">
                <h2>Sign In</h2>
                <p>Enter your credentials to continue</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>
                    <?php
                    $error = $_GET['error'];
                    switch($error) {
                        case 'invalid_credentials':
                            echo 'Invalid email or password. Please try again.';
                            break;
                        case 'account_not_approved':
                            echo 'Your doctor account is pending approval.';
                            break;
                        case 'account_inactive':
                            echo 'Your account is inactive. Contact support.';
                            break;
                        case 'empty_fields':
                            echo 'Please fill in all required fields.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>You have been successfully logged out.</span>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>Registration successful! Please login.</span>
            </div>
            <?php endif; ?>

            <!-- Role Toggle -->
            <div class="role-toggle">
                <input type="radio" name="role_selection" id="user" value="user" checked>
                <label for="user" class="role-option">
                    <i class="fas fa-user"></i> Patient
                </label>

                <input type="radio" name="role_selection" id="doctor" value="doctor">
                <label for="doctor" class="role-option">
                    <i class="fas fa-user-md"></i> Doctor
                </label>

                <input type="radio" name="role_selection" id="admin" value="admin">
                <label for="admin" class="role-option">
                    <i class="fas fa-user-shield"></i> Admin
                </label>
            </div>

            <!-- Login Form -->
            <form method="POST" action="../server/handlers/login_handler.php" class="login-form">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="role" id="selected-role" value="user">

                <div class="input-group">
                    <label class="input-label">Email or Phone Number</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="text" 
                            name="email" 
                            class="input" 
                            placeholder="example@gmail.com or 9876543210"
                            required
                        >
                    </div>
                </div>

                <div class="input-group">
                    <label class="input-label">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input" 
                            placeholder="Enter your password"
                            required
                        >
                        <i class="fas fa-eye" id="togglePassword"></i>
                    </div>
                </div>

                <div class="form-options">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <div class="divider">
                <span>New to Aarunya?</span>
            </div>

            <div class="register-link">
                Don't have an account? <a href="register.php">Create Account</a>
            </div>
        </div>
    </div>

    <script>
        // Role toggle functionality
        const roleInputs = document.querySelectorAll('input[name="role_selection"]');
        const selectedRoleInput = document.getElementById('selected-role');

        roleInputs.forEach(input => {
            input.addEventListener('change', function() {
                selectedRoleInput.value = this.value;
            });
        });

        // Auto-select role from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const roleParam = urlParams.get('role');
        if (roleParam) {
            const roleInput = document.getElementById(roleParam);
            if (roleInput) {
                roleInput.checked = true;
                selectedRoleInput.value = roleParam;
            }
        }

        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

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
    </script>
</body>
</html>
