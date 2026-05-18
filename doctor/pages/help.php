<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Help & Support';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #263238; margin-bottom: 8px;">
            <i class="fas fa-question-circle"></i> Help & Support
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Get help with using the doctor portal and find answers to common questions
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <!-- FAQ Section -->
        <div class="glass-card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fas fa-question"></i>
                    Frequently Asked Questions
                </h2>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <!-- FAQ Item 1 -->
                <div style="border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; overflow: hidden;">
                    <div style="padding: 16px; background: rgba(255, 255, 255, 0.02); cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(1)">
                        <h4 style="font-size: 16px; font-weight: 600; color: #263238; margin: 0;">How do I manage my appointment schedule?</h4>
                        <i class="fas fa-chevron-down" id="faq-icon-1" style="color: #C4A7FF; transition: transform 0.2s;"></i>
                    </div>
                    <div id="faq-content-1" style="padding: 0 16px; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding: 16px 0; color: #546e7a; line-height: 1.6;">
                            You can manage your schedule by going to the "My Schedule" page. There you can set your available time slots for each day of the week, specify consultation durations, and manage your availability. Patients can only book appointments during your available slots.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div style="border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; overflow: hidden;">
                    <div style="padding: 16px; background: rgba(255, 255, 255, 0.02); cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(2)">
                        <h4 style="font-size: 16px; font-weight: 600; color: #263238; margin: 0;">How do I create and manage prescriptions?</h4>
                        <i class="fas fa-chevron-down" id="faq-icon-2" style="color: #C4A7FF; transition: transform 0.2s;"></i>
                    </div>
                    <div id="faq-content-2" style="padding: 0 16px; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding: 16px 0; color: #546e7a; line-height: 1.6;">
                            Navigate to the "Prescriptions" page and click "New Prescription". Select the patient, add diagnosis, medications with dosages, and any special instructions. The system will generate a digital prescription that can be downloaded as PDF and shared with the patient.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div style="border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; overflow: hidden;">
                    <div style="padding: 16px; background: rgba(255, 255, 255, 0.02); cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(3)">
                        <h4 style="font-size: 16px; font-weight: 600; color: #263238; margin: 0;">How do I handle emergency cases?</h4>
                        <i class="fas fa-chevron-down" id="faq-icon-3" style="color: #C4A7FF; transition: transform 0.2s;"></i>
                    </div>
                    <div id="faq-content-3" style="padding: 0 16px; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding: 16px 0; color: #546e7a; line-height: 1.6;">
                            Emergency cases appear in the "Emergency" section with priority levels. Critical cases require immediate attention within 5 minutes. You can call patients directly, dispatch ambulances, or add medical notes. For life-threatening situations, always contact emergency services (911) first.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div style="border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; overflow: hidden;">
                    <div style="padding: 16px; background: rgba(255, 255, 255, 0.02); cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(4)">
                        <h4 style="font-size: 16px; font-weight: 600; color: #263238; margin: 0;">How do I access patient medical records?</h4>
                        <i class="fas fa-chevron-down" id="faq-icon-4" style="color: #C4A7FF; transition: transform 0.2s;"></i>
                    </div>
                    <div id="faq-content-4" style="padding: 0 16px; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding: 16px 0; color: #546e7a; line-height: 1.6;">
                            Patient records are available in the "My Patients" section for patients you've treated, and "All Patients" for the complete directory. Click on any patient card to view detailed medical history, pregnancy progress, previous appointments, and health metrics.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div style="border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; overflow: hidden;">
                    <div style="padding: 16px; background: rgba(255, 255, 255, 0.02); cursor: pointer; display: flex; justify-content: space-between; align-items: center;" onclick="toggleFAQ(5)">
                        <h4 style="font-size: 16px; font-weight: 600; color: #263238; margin: 0;">How do I generate health reports?</h4>
                        <i class="fas fa-chevron-down" id="faq-icon-5" style="color: #C4A7FF; transition: transform 0.2s;"></i>
                    </div>
                    <div id="faq-content-5" style="padding: 0 16px; max-height: 0; overflow: hidden; transition: all 0.3s ease;">
                        <div style="padding: 16px 0; color: #546e7a; line-height: 1.6;">
                            Go to the "Reports" section and choose from available templates like Prenatal Checkup, Ultrasound Report, Lab Results, or Medical Certificates. Fill in the required information and the system will generate a professional report that can be downloaded and shared with patients.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support Options -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Contact Support -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-headset"></i>
                        Contact Support
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <button class="btn btn-primary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-phone"></i> Call Support
                    </button>
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-envelope"></i> Email Support
                    </button>
                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;">
                        <i class="fas fa-comments"></i> Live Chat
                    </button>
                </div>

                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <div style="font-size: 12px; color: #546e7a; text-align: center;">
                        <div>Support Hours:</div>
                        <div>Mon-Fri: 8AM - 8PM</div>
                        <div>Sat-Sun: 9AM - 5PM</div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-external-link-alt"></i>
                        Quick Links
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="#" style="display: flex; align-items: center; gap: 8px; padding: 8px; color: #546e7a; text-decoration: none; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(244,114,182,0.1)'; this.style.color='#C4A7FF'" onmouseout="this.style.background='transparent'; this.style.color='#546e7a'">
                        <i class="fas fa-book" style="width: 16px;"></i>
                        <span>User Manual</span>
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 8px; padding: 8px; color: #546e7a; text-decoration: none; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(244,114,182,0.1)'; this.style.color='#C4A7FF'" onmouseout="this.style.background='transparent'; this.style.color='#546e7a'">
                        <i class="fas fa-video" style="width: 16px;"></i>
                        <span>Video Tutorials</span>
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 8px; padding: 8px; color: #546e7a; text-decoration: none; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(244,114,182,0.1)'; this.style.color='#C4A7FF'" onmouseout="this.style.background='transparent'; this.style.color='#546e7a'">
                        <i class="fas fa-download" style="width: 16px;"></i>
                        <span>Mobile App</span>
                    </a>
                    <a href="#" style="display: flex; align-items: center; gap: 8px; padding: 8px; color: #546e7a; text-decoration: none; border-radius: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(244,114,182,0.1)'; this.style.color='#C4A7FF'" onmouseout="this.style.background='transparent'; this.style.color='#546e7a'">
                        <i class="fas fa-shield-alt" style="width: 16px;"></i>
                        <span>Privacy Policy</span>
                    </a>
                </div>
            </div>

            <!-- System Status -->
            <div class="glass-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-server"></i>
                        System Status
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #546e7a; font-size: 14px;">Portal Status</span>
                        <span style="color: #22c55e; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-circle" style="font-size: 8px;"></i> Online
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #546e7a; font-size: 14px;">Database</span>
                        <span style="color: #22c55e; font-size: 12px; display: flex; align-items: center; gap: 4px;">
                            <i class="fas fa-circle" style="font-size: 8px;"></i> Connected
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #546e7a; font-size: 14px;">Last Update</span>
                        <span style="color: #546e7a; font-size: 12px;">2 hours ago</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFAQ(id) {
    const content = document.getElementById(`faq-content-${id}`);
    const icon = document.getElementById(`faq-icon-${id}`);
    
    if (content.style.maxHeight === '0px' || content.style.maxHeight === '') {
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.style.maxHeight = '0px';
        icon.style.transform = 'rotate(0deg)';
    }
}
</script>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>

