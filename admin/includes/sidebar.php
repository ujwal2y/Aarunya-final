<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
?>

<!-- Left Sidebar -->
<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <i class="fas fa-shield-alt"></i>
        <span>Aarunya Admin</span>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i>
            <span>Dashboard</span>
        </a>
        <a href="users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Patients</span>
        </a>
        <a href="doctors.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-doctor"></i>
            <span>Doctors</span>
        </a>
        <a href="appointments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Appointments</span>
        </a>
        <a href="emergency.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'emergency.php' ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Emergency</span>
        </a>
        <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="help.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'help.php' ? 'active' : ''; ?>">
            <i class="fas fa-question-circle"></i>
            <span>Help</span>
        </a>
        <a href="../logout.php" class="nav-item logout-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
