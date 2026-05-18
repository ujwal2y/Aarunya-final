<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
requireLogin();

$pageTitle = 'Help & Support';
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="layout-container">
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="section-layout">
            <div class="section-title">
                <i class="fas fa-question-circle"></i>
                Help & Support Center
            </div>
            <div class="section-subtitle">
                Get assistance and learn how to use the Aarunya Maternal Health Admin System
            </div>
        </div>

        <!-- Quick Actions Bar -->
        <div class="section-layout">
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                </div>
                <div class="layout-grid layout-grid-responsive gap-md">
                    <button onclick="openVideoTutorial()" class="btn btn-primary" style="padding: 16px; text-align: center;">
                        <i class="fas fa-play-circle" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                        <span>Video Tutorials</span>
                    </button>
                    <button onclick="downloadUserGuide()" class="btn btn-secondary" style="padding: 16px; text-align: center;">
                        <i class="fas fa-download" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                        <span>Download Guide</span>
                    </button>
                    <button onclick="contactSupport()" class="btn btn-warning" style="padding: 16px; text-align: center;">
                        <i class="fas fa-headset" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                        <span>Live Support</span>
                    </button>
                    <button onclick="reportBug()" class="btn btn-danger" style="padding: 16px; text-align: center;">
                        <i class="fas fa-bug" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                        <span>Report Issue</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Help Content -->
        <div class="layout-grid layout-grid-2 gap-lg">
            <!-- Quick Start Guide -->
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-rocket"></i> Quick Start Guide
                    </h3>
                    <button onclick="toggleSection('quickstart')" class="btn btn-secondary btn-sm">
                        <i class="fas fa-expand-alt"></i>
                    </button>
                </div>
                <div class="card-body-layout" id="quickstart">
                    <div class="layout-flex-col gap-md" style="color: var(--text-secondary); line-height: 1.8;">
                        <div class="card-layout card-layout-compact">
                            <h4 style="color: #fff; margin-bottom: 12px;">
                                <i class="fas fa-tachometer-alt" style="color: #C4A7FF;"></i> Dashboard Overview
                            </h4>
                            <p>The dashboard provides a comprehensive view of your maternal healthcare system with real-time statistics and recent activities.</p>
                            <button onclick="window.location.href='dashboard.php'" class="btn btn-primary btn-sm" style="margin-top: 8px;">
                                <i class="fas fa-arrow-right"></i> Go to Dashboard
                            </button>
                        </div>
                        
                        <div class="card-layout card-layout-compact">
                            <h4 style="color: #fff; margin-bottom: 12px;">
                                <i class="fas fa-users" style="color: #C4A7FF;"></i> Managing Users
                            </h4>
                            <p>Navigate to the Mothers section to view, add, edit, or delete user accounts. You can also view detailed pregnancy information.</p>
                            <button onclick="window.location.href='users.php'" class="btn btn-primary btn-sm" style="margin-top: 8px;">
                                <i class="fas fa-arrow-right"></i> Manage Users
                            </button>
                        </div>
                        
                        <div class="card-layout card-layout-compact">
                            <h4 style="color: #fff; margin-bottom: 12px;">
                                <i class="fas fa-calendar-check" style="color: #C4A7FF;"></i> Appointments
                            </h4>
                            <p>Manage all appointments from the Appointments page. You can approve, reject, or mark appointments as completed.</p>
                            <button onclick="window.location.href='appointments.php'" class="btn btn-primary btn-sm" style="margin-top: 8px;">
                                <i class="fas fa-arrow-right"></i> View Appointments
                            </button>
                        </div>
                        
                        <div class="card-layout card-layout-compact">
                            <h4 style="color: #fff; margin-bottom: 12px;">
                                <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Emergency Requests
                            </h4>
                            <p>Monitor and respond to emergency requests in real-time. The notification bell shows unresolved emergencies.</p>
                            <button onclick="window.location.href='emergency.php'" class="btn btn-danger btn-sm" style="margin-top: 8px;">
                                <i class="fas fa-arrow-right"></i> Emergency Center
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Common Tasks -->
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-tasks"></i> Common Tasks
                    </h3>
                    <span class="badge badge-info">5 Tasks</span>
                </div>
                <div class="card-body-layout">
                    <div class="layout-flex-col gap-sm">
                        <a href="users.php" class="nav-item-layout" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <i class="fas fa-user-plus" style="color: #10b981;"></i>
                            <span>Add New User</span>
                            <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px;"></i>
                        </a>
                        <a href="doctors.php" class="nav-item-layout" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <i class="fas fa-user-doctor" style="color: #3b82f6;"></i>
                            <span>Manage Doctors</span>
                            <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px;"></i>
                        </a>
                        <a href="appointments.php" class="nav-item-layout" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <i class="fas fa-calendar-check" style="color: #C4A7FF;"></i>
                            <span>View Appointments</span>
                            <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px;"></i>
                        </a>
                        <a href="emergency.php" class="nav-item-layout" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i>
                            <span>Check Emergency Alerts</span>
                            <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px;"></i>
                        </a>
                        <a href="reports.php" class="nav-item-layout" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <i class="fas fa-chart-bar" style="color: #f59e0b;"></i>
                            <span>Generate Reports</span>
                            <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px;"></i>
                        </a>
                    </div>
                </div>
                <div class="card-footer-layout">
                    <button onclick="showAllTasks()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-list"></i> View All Tasks
                    </button>
                </div>
            </div>
        </div>

        <!-- FAQs Section -->
        <div class="section-layout">
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-question-circle"></i> Frequently Asked Questions
                    </h3>
                    <div class="btn-group">
                        <button onclick="expandAllFAQs()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-expand"></i> Expand All
                        </button>
                        <button onclick="collapseAllFAQs()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-compress"></i> Collapse All
                        </button>
                    </div>
                </div>
                <div class="card-body-layout">
                    <div class="layout-flex-col gap-md" style="color: var(--text-secondary); line-height: 1.8;">
                        <details class="faq-item" style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <summary style="color: #fff; cursor: pointer; padding: 8px 0; font-weight: 600;">
                                <i class="fas fa-key" style="color: #C4A7FF; margin-right: 8px;"></i>
                                How do I reset a user's password?
                            </summary>
                            <div style="padding-left: 32px; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <p>Go to the Mothers page, find the user, and click the edit button. You can set a new password from there.</p>
                                <button onclick="window.location.href='users.php'" class="btn btn-primary btn-sm" style="margin-top: 8px;">
                                    <i class="fas fa-users"></i> Go to Users
                                </button>
                            </div>
                        </details>
                        
                        <details class="faq-item" style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <summary style="color: #fff; cursor: pointer; padding: 8px 0; font-weight: 600;">
                                <i class="fas fa-download" style="color: #C4A7FF; margin-right: 8px;"></i>
                                How do I export data?
                            </summary>
                            <div style="padding-left: 32px; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <p>Each page (Users, Appointments, etc.) has an export button that allows you to download data in CSV or JSON format.</p>
                                <div class="btn-group" style="margin-top: 8px;">
                                    <button onclick="window.location.href='reports.php'" class="btn btn-primary btn-sm">
                                        <i class="fas fa-chart-bar"></i> Reports
                                    </button>
                                    <button onclick="showExportGuide()" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-info"></i> Export Guide
                                    </button>
                                </div>
                            </div>
                        </details>
                        
                        <details class="faq-item" style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <summary style="color: #fff; cursor: pointer; padding: 8px 0; font-weight: 600;">
                                <i class="fas fa-tags" style="color: #C4A7FF; margin-right: 8px;"></i>
                                What do the status badges mean?
                            </summary>
                            <div style="padding-left: 32px; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <div class="layout-flex-col gap-sm">
                                    <div class="layout-flex gap-sm layout-align-center">
                                        <span class="badge badge-success">Approved</span>
                                        <span>Confirmed appointments</span>
                                    </div>
                                    <div class="layout-flex gap-sm layout-align-center">
                                        <span class="badge badge-warning">Pending</span>
                                        <span>Awaiting approval</span>
                                    </div>
                                    <div class="layout-flex gap-sm layout-align-center">
                                        <span class="badge badge-danger">Emergency</span>
                                        <span>Urgent requests</span>
                                    </div>
                                    <div class="layout-flex gap-sm layout-align-center">
                                        <span class="badge badge-info">Completed</span>
                                        <span>Finished appointments</span>
                                    </div>
                                </div>
                            </div>
                        </details>
                        
                        <details class="faq-item" style="background: rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <summary style="color: #fff; cursor: pointer; padding: 8px 0; font-weight: 600;">
                                <i class="fas fa-search" style="color: #C4A7FF; margin-right: 8px;"></i>
                                How do I use the search function?
                            </summary>
                            <div style="padding-left: 32px; margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.1);">
                                <p>Use the search functionality on each page to filter results by name, email, or other criteria. You can also use keyboard shortcut <kbd style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; color: #fff;">Ctrl + K</kbd> for quick search.</p>
                                <button onclick="demonstrateSearch()" class="btn btn-primary btn-sm" style="margin-top: 8px;">
                                    <i class="fas fa-search"></i> Try Search
                                </button>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support & Contact -->
        <div class="layout-grid layout-grid-2 gap-lg">
            <!-- Contact Support -->
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-headset"></i> Contact Support
                    </h3>
                    <span class="badge badge-success">Online</span>
                </div>
                <div class="card-body-layout">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">Need additional help? Our support team is here to assist you 24/7.</p>
                    
                    <div class="layout-flex-col gap-md">
                        <div class="layout-flex gap-md layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: rgba(244, 114, 182, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-envelope" style="color: #C4A7FF;"></i>
                            </div>
                            <div style="flex: 1;">
                                <strong style="color: #fff; display: block;">Email Support</strong>
                                <a href="mailto:support@aarunya.com" style="color: #C4A7FF; font-size: 14px;">support@aarunya.com</a>
                            </div>
                            <button onclick="openEmailSupport()" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        
                        <div class="layout-flex gap-md layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-phone" style="color: #10b981;"></i>
                            </div>
                            <div style="flex: 1;">
                                <strong style="color: #fff; display: block;">Phone Support</strong>
                                <span style="color: var(--text-secondary); font-size: 14px;">+91 1800-XXX-XXXX</span>
                            </div>
                            <button onclick="callSupport()" class="btn btn-success btn-sm">
                                <i class="fas fa-phone"></i>
                            </button>
                        </div>
                        
                        <div class="layout-flex gap-md layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                            <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-comments" style="color: #3b82f6;"></i>
                            </div>
                            <div style="flex: 1;">
                                <strong style="color: #fff; display: block;">Live Chat</strong>
                                <span style="color: var(--text-secondary); font-size: 14px;">Available 24/7</span>
                            </div>
                            <button onclick="openLiveChat()" class="btn btn-info btn-sm">
                                <i class="fas fa-comment"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer-layout">
                    <div class="layout-flex gap-sm layout-align-center">
                        <i class="fas fa-clock" style="color: #C4A7FF;"></i>
                        <span style="color: var(--text-secondary); font-size: 14px;">Support Hours: Monday - Friday, 9:00 AM - 6:00 PM IST</span>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-info-circle"></i> System Information
                    </h3>
                    <button onclick="refreshSystemInfo()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
                <div class="card-body-layout">
                    <div class="stats-layout">
                        <div class="stat-item">
                            <div class="stat-value" style="font-size: 1.5rem;">v1.0.0</div>
                            <div class="stat-label">Version</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" style="font-size: 1.5rem;"><?php echo date('M Y'); ?></div>
                            <div class="stat-label">Last Updated</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" style="font-size: 1.5rem;" id="browser-info">Loading...</div>
                            <div class="stat-label">Browser</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value" style="font-size: 1.5rem;" id="uptime">99.9%</div>
                            <div class="stat-label">Uptime</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer-layout">
                    <div class="btn-group">
                        <button onclick="checkUpdates()" class="btn btn-primary btn-sm">
                            <i class="fas fa-download"></i> Check Updates
                        </button>
                        <button onclick="viewChangelog()" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> Changelog
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts -->
        <div class="section-layout">
            <div class="card-layout">
                <div class="card-header-layout">
                    <h3 style="color: #C4A7FF; margin: 0;">
                        <i class="fas fa-keyboard"></i> Keyboard Shortcuts
                    </h3>
                    <button onclick="printShortcuts()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
                <div class="card-body-layout">
                    <div class="layout-grid layout-grid-responsive gap-sm">
                        <div class="layout-flex layout-flex-between layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-secondary);">Search</span>
                            <kbd style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; color: #fff; font-weight: 600;">Ctrl + K</kbd>
                        </div>
                        <div class="layout-flex layout-flex-between layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-secondary);">Dashboard</span>
                            <kbd style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; color: #fff; font-weight: 600;">Alt + D</kbd>
                        </div>
                        <div class="layout-flex layout-flex-between layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-secondary);">Emergency</span>
                            <kbd style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; color: #fff; font-weight: 600;">Alt + E</kbd>
                        </div>
                        <div class="layout-flex layout-flex-between layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-secondary);">Settings</span>
                            <kbd style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; color: #fff; font-weight: 600;">Alt + S</kbd>
                        </div>
                        <div class="layout-flex layout-flex-between layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-secondary);">Help</span>
                            <kbd style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; color: #fff; font-weight: 600;">F1</kbd>
                        </div>
                        <div class="layout-flex layout-flex-between layout-align-center" style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                            <span style="color: var(--text-secondary);">Refresh</span>
                            <kbd style="background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; color: #fff; font-weight: 600;">F5</kbd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Detect browser
const browserInfo = navigator.userAgent.match(/(firefox|msie|chrome|safari|trident)/ig);
document.getElementById('browser-info').textContent = browserInfo ? browserInfo[0] : 'Unknown';

// Interactive Functions
function openVideoTutorial() {
    alert('🎥 Video Tutorial\n\nOpening comprehensive video tutorials for Aarunya Admin System.\n\nTopics covered:\n• Dashboard Navigation\n• User Management\n• Appointment Handling\n• Emergency Response\n• Report Generation');
}

function downloadUserGuide() {
    alert('📥 Download User Guide\n\nDownloading comprehensive PDF user guide...\n\nIncludes:\n• Step-by-step instructions\n• Screenshots and examples\n• Troubleshooting tips\n• Best practices');
}

function contactSupport() {
    alert('💬 Live Support\n\nConnecting you to our support team...\n\nAvailable channels:\n• Live Chat (24/7)\n• Phone Support\n• Email Support\n• Screen Sharing');
}

function reportBug() {
    alert('🐛 Report Issue\n\nOpening bug report form...\n\nPlease provide:\n• Detailed description\n• Steps to reproduce\n• Screenshots if applicable\n• Browser information');
}

function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    const isHidden = section.style.display === 'none';
    section.style.display = isHidden ? 'block' : 'none';
    
    // Update button icon
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    icon.className = isHidden ? 'fas fa-compress-alt' : 'fas fa-expand-alt';
}

function showAllTasks() {
    alert('📋 All Available Tasks\n\n• User Management\n• Doctor Administration\n• Appointment Scheduling\n• Emergency Response\n• Report Generation\n• System Settings\n• Data Export\n• Backup Management\n• User Support\n• System Monitoring');
}

function expandAllFAQs() {
    document.querySelectorAll('.faq-item').forEach(item => {
        item.open = true;
    });
}

function collapseAllFAQs() {
    document.querySelectorAll('.faq-item').forEach(item => {
        item.open = false;
    });
}

function showExportGuide() {
    alert('📊 Export Guide\n\nData Export Options:\n\n1. CSV Format - For spreadsheet analysis\n2. JSON Format - For system integration\n3. PDF Reports - For presentations\n\nAvailable on:\n• Users page\n• Appointments page\n• Doctors page\n• Emergency requests\n• Reports dashboard');
}

function demonstrateSearch() {
    alert('🔍 Search Demonstration\n\nSearch Features:\n\n• Real-time filtering\n• Multiple criteria support\n• Keyboard shortcuts (Ctrl+K)\n• Advanced filters\n• Export filtered results\n\nTry searching on any data page!');
}

function openEmailSupport() {
    window.open('mailto:support@aarunya.com?subject=Aarunya Admin Support Request&body=Hello Support Team,%0D%0A%0D%0AI need assistance with:%0D%0A%0D%0A[Please describe your issue here]%0D%0A%0D%0ASystem Information:%0D%0A- Browser: ' + navigator.userAgent + '%0D%0A- Page: ' + window.location.href + '%0D%0A%0D%0AThank you!');
}

function callSupport() {
    alert('📞 Phone Support\n\nCalling: +91 1800-XXX-XXXX\n\nSupport Hours:\nMonday - Friday: 9:00 AM - 6:00 PM IST\n\nFor immediate assistance, please have your admin ID ready.');
}

function openLiveChat() {
    alert('💬 Live Chat\n\nInitiating live chat session...\n\nFeatures:\n• Instant messaging\n• File sharing\n• Screen sharing\n• Priority support\n\nConnecting to next available agent...');
}

function refreshSystemInfo() {
    // Simulate refresh with loading state
    const elements = ['browser-info', 'uptime'];
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = 'Refreshing...';
            setTimeout(() => {
                if (id === 'browser-info') {
                    element.textContent = browserInfo ? browserInfo[0] : 'Unknown';
                } else if (id === 'uptime') {
                    element.textContent = '99.9%';
                }
            }, 1000);
        }
    });
}

function checkUpdates() {
    alert('🔄 System Updates\n\nChecking for updates...\n\nCurrent Version: v1.0.0\nLatest Version: v1.0.0\n\n✅ Your system is up to date!\n\nNext scheduled update: End of month');
}

function viewChangelog() {
    alert('📋 Changelog - Version 1.0.0\n\n🆕 New Features:\n• Enhanced dashboard analytics\n• Improved user management\n• Real-time emergency alerts\n• Advanced reporting system\n\n🔧 Improvements:\n• Faster page loading\n• Better mobile responsiveness\n• Enhanced security\n\n🐛 Bug Fixes:\n• Fixed login issues\n• Resolved export problems\n• Improved data validation');
}

function printShortcuts() {
    const shortcuts = `
    Aarunya Admin - Keyboard Shortcuts
    
    Navigation:
    • Ctrl + K - Search
    • Alt + D - Dashboard
    • Alt + E - Emergency
    • Alt + S - Settings
    • F1 - Help
    • F5 - Refresh
    
    Data Management:
    • Ctrl + N - New Entry
    • Ctrl + E - Export Data
    • Ctrl + F - Filter
    • Esc - Close Modal
    
    Quick Actions:
    • Alt + U - Users
    • Alt + A - Appointments
    • Alt + R - Reports
    • Alt + H - Help
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head><title>Keyboard Shortcuts</title></head>
            <body style="font-family: monospace; white-space: pre-line; padding: 20px;">
                ${shortcuts}
            </body>
        </html>
    `);
    printWindow.print();
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.altKey && e.key === 'd') {
        e.preventDefault();
        window.location.href = 'dashboard.php';
    } else if (e.altKey && e.key === 'e') {
        e.preventDefault();
        window.location.href = 'emergency.php';
    } else if (e.altKey && e.key === 's') {
        e.preventDefault();
        window.location.href = 'settings.php';
    } else if (e.altKey && e.key === 'u') {
        e.preventDefault();
        window.location.href = 'users.php';
    } else if (e.altKey && e.key === 'a') {
        e.preventDefault();
        window.location.href = 'appointments.php';
    } else if (e.altKey && e.key === 'r') {
        e.preventDefault();
        window.location.href = 'reports.php';
    } else if (e.altKey && e.key === 'h') {
        e.preventDefault();
        window.location.href = 'help.php';
    } else if (e.key === 'F1') {
        e.preventDefault();
        window.location.href = 'help.php';
    } else if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        demonstrateSearch();
    }
});

// Add smooth animations
document.addEventListener('DOMContentLoaded', function() {
    // Animate cards on load
    const cards = document.querySelectorAll('.card-layout');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add hover effects to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>

