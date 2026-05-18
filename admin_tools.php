<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tools - Aarunya Healthcare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            width: 100%;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 3rem;
            margin-bottom: 10px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .tool-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .tool-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        }
        
        .tool-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: white;
        }
        
        .tool-card h2 {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .tool-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .tool-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-top: 15px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }
            
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-tools"></i> Admin Tools</h1>
            <p>Powerful tools to manage and customize Aarunya Healthcare</p>
        </div>

        <div class="tools-grid">
            <!-- Theme Customizer -->
            <a href="theme_customizer.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h2>Theme Customizer</h2>
                <p>Change the entire UI color scheme with a single click. Choose from 8 preset themes or create your own custom colors.</p>
                <span class="tool-badge">NEW</span>
            </a>

            <!-- OTP System Test -->
            <a href="test_otp_system.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>OTP System Test</h2>
                <p>Test and debug the OTP verification system. View recent OTP codes, check server time, and send test OTPs.</p>
                <span class="tool-badge">TESTING</span>
            </a>

            <!-- Database Setup -->
            <a href="setup_otp_system.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h2>Database Setup</h2>
                <p>One-click database setup for OTP tables. Creates all necessary tables and verifies installation.</p>
                <span class="tool-badge">SETUP</span>
            </a>

            <!-- Admin Dashboard -->
            <a href="admin/pages/dashboard.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h2>Admin Dashboard</h2>
                <p>Access the main admin dashboard to manage users, doctors, appointments, and view analytics.</p>
                <span class="tool-badge">ADMIN</span>
            </a>

            <!-- Documentation -->
            <a href="FINAL_IMPLEMENTATION_SUMMARY.md" class="tool-card" target="_blank">
                <div class="tool-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h2>Documentation</h2>
                <p>Complete documentation including setup guides, testing scripts, and troubleshooting information.</p>
                <span class="tool-badge">DOCS</span>
            </a>

            <!-- Login Page -->
            <a href="client/login.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h2>Login Page</h2>
                <p>Access the main login page for patients, doctors, and administrators.</p>
                <span class="tool-badge">PUBLIC</span>
            </a>
        </div>
    </div>
</body>
</html>
