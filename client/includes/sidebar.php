<!-- Left Sidebar -->
<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <i class="fas fa-heart-pulse"></i>
        <span>Aarunya</span>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="doctors.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-doctor"></i>
            <span>Doctors</span>
        </a>
        <a href="appointments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Appointments</span>
        </a>
        <a href="health.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'health.php' ? 'active' : ''; ?>">
            <i class="fas fa-heartbeat"></i>
            <span>Health Tracking</span>
        </a>
        <a href="medical_documents.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'medical_documents.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-medical"></i>
            <span>Medical Documents</span>
        </a>
        <a href="ai_wellness_plan.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'ai_wellness_plan.php' ? 'active' : ''; ?>">
            <i class="fas fa-magic"></i>
            <span>AI Wellness Plan</span>
        </a>
        <a href="emergency.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'emergency.php' ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Emergency</span>
        </a>
        <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
        <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-item logout-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
