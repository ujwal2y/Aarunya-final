<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// Detect if we're in pages subfolder or root
$isInPages = (basename(dirname($_SERVER['PHP_SELF'])) === 'pages');
$dashboardPath = $isInPages ? '../dashboard.php' : 'dashboard.php';
$pagesPrefix = $isInPages ? '' : 'pages/';
?>

<!-- Left Sidebar -->
<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <i class="fas fa-user-md"></i>
        <span>Aarunya Doctor</span>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
        <a href="<?php echo $dashboardPath; ?>" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="<?php echo $pagesPrefix; ?>appointments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            <span>Appointments</span>
        </a>
        <a href="<?php echo $pagesPrefix; ?>users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-friends"></i>
            <span>My Patients</span>
        </a>
        <a href="<?php echo $isInPages ? '../upload_documents.php' : 'upload_documents.php'; ?>" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'upload_documents.php' ? 'active' : ''; ?>">
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Upload Documents</span>
        </a>
        <a href="<?php echo $isInPages ? '../generate_report.php' : 'generate_report.php'; ?>" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'generate_report.php' ? 'active' : ''; ?>">
            <i class="fas fa-file-medical-alt"></i>
            <span>Generate Report</span>
        </a>
        <a href="<?php echo $pagesPrefix; ?>emergency.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'emergency.php' ? 'active' : ''; ?>">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Emergency</span>
        </a>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <a href="<?php echo $pagesPrefix; ?>schedule.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>My Schedule</span>
        </a>
        <a href="<?php echo $pagesPrefix; ?>settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        <a href="<?php echo $isInPages ? '../logout.php' : 'logout.php'; ?>" class="nav-item logout-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
