<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'Patient Details';
$doctor = getCurrentDoctor();

// Get patient ID from URL
$patient_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$patient_id) {
    header('Location: patients.php');
    exit();
}

// Dummy patient data (in real app, this would come from database)
$patients = [
    1 => [
        'id' => 1,
        'name' => 'Sarah Parker',
        'email' => 'sarah.parker@email.com',
        'phone' => '+1 (555) 123-4567',
        'age' => 28,
        'pregnancy_week' => 24,
        'due_date' => '2026-09-15',
        'blood_type' => 'O+',
        'emergency_contact' => 'John Parker - +1 (555) 123-4568',
        'address' => '123 Main St, City, State 12345',
        'last_visit' => '2026-05-05',
        'next_appointment' => '2026-05-12',
        'status' => 'Active',
        'notes' => 'Normal pregnancy progression. Patient is healthy and following all recommendations.',
        'allergies' => 'None known',
        'medications' => 'Prenatal vitamins, Folic acid',
        'medical_history' => 'No significant medical history. First pregnancy.',
        'weight_gain' => '12 lbs',
        'bp_last' => '120/80',
        'heart_rate' => '72 bpm'
    ],
    2 => [
        'id' => 2,
        'name' => 'Maria Johnson',
        'email' => 'maria.johnson@email.com',
        'phone' => '+1 (555) 234-5678',
        'age' => 32,
        'pregnancy_week' => 16,
        'due_date' => '2026-11-20',
        'blood_type' => 'A+',
        'emergency_contact' => 'Carlos Johnson - +1 (555) 234-5679',
        'address' => '456 Oak Ave, City, State 12345',
        'last_visit' => '2026-04-28',
        'next_appointment' => '2026-05-14',
        'status' => 'Active',
        'notes' => 'Second pregnancy. All parameters normal. Scheduled for anatomy scan.',
        'allergies' => 'Penicillin',
        'medications' => 'Prenatal vitamins, Iron supplement',
        'medical_history' => 'Previous C-section delivery in 2023',
        'weight_gain' => '8 lbs',
        'bp_last' => '118/75',
        'heart_rate' => '68 bpm'
    ],
    3 => [
        'id' => 3,
        'name' => 'Emily Wilson',
        'email' => 'emily.wilson@email.com',
        'phone' => '+1 (555) 345-6789',
        'age' => 25,
        'pregnancy_week' => 32,
        'due_date' => '2026-07-10',
        'blood_type' => 'B+',
        'emergency_contact' => 'David Wilson - +1 (555) 345-6790',
        'address' => '789 Pine St, City, State 12345',
        'last_visit' => '2026-05-03',
        'next_appointment' => '2026-05-10',
        'status' => 'Active',
        'notes' => 'Third trimester. Baby position is good. Preparing for delivery.',
        'allergies' => 'Shellfish',
        'medications' => 'Prenatal vitamins, Calcium supplement',
        'medical_history' => 'Gestational diabetes in previous pregnancy (controlled)',
        'weight_gain' => '22 lbs',
        'bp_last' => '125/82',
        'heart_rate' => '76 bpm'
    ],
    4 => [
        'id' => 4,
        'name' => 'Lisa Brown',
        'email' => 'lisa.brown@email.com',
        'phone' => '+1 (555) 456-7890',
        'age' => 29,
        'pregnancy_week' => 28,
        'due_date' => '2026-08-22',
        'blood_type' => 'AB+',
        'emergency_contact' => 'Michael Brown - +1 (555) 456-7891',
        'address' => '321 Elm St, City, State 12345',
        'last_visit' => '2026-05-07',
        'next_appointment' => '2026-05-15',
        'status' => 'Active',
        'notes' => 'Third pregnancy. Experienced with pregnancy care. All vitals normal.',
        'allergies' => 'None known',
        'medications' => 'Prenatal vitamins, DHA supplement',
        'medical_history' => 'Two previous normal deliveries',
        'weight_gain' => '18 lbs',
        'bp_last' => '122/78',
        'heart_rate' => '74 bpm'
    ],
    5 => [
        'id' => 5,
        'name' => 'Jennifer Davis',
        'email' => 'jennifer.davis@email.com',
        'phone' => '+1 (555) 567-8901',
        'age' => 26,
        'pregnancy_week' => 20,
        'due_date' => '2026-10-05',
        'blood_type' => 'O-',
        'emergency_contact' => 'Robert Davis - +1 (555) 567-8902',
        'address' => '654 Maple Ave, City, State 12345',
        'last_visit' => '2026-04-30',
        'next_appointment' => '2026-05-13',
        'status' => 'Active',
        'notes' => 'Halfway through pregnancy. Anatomy scan scheduled. RH negative monitoring.',
        'allergies' => 'Latex',
        'medications' => 'Prenatal vitamins, RhoGAM injection scheduled',
        'medical_history' => 'First pregnancy, RH negative blood type',
        'weight_gain' => '10 lbs',
        'bp_last' => '115/72',
        'heart_rate' => '70 bpm'
    ],
    6 => [
        'id' => 6,
        'name' => 'Amanda Wilson',
        'email' => 'amanda.wilson@email.com',
        'phone' => '+1 (555) 678-9012',
        'age' => 24,
        'pregnancy_week' => 12,
        'due_date' => '2026-12-10',
        'blood_type' => 'A-',
        'emergency_contact' => 'James Wilson - +1 (555) 678-9013',
        'address' => '987 Cedar St, City, State 12345',
        'last_visit' => '2026-04-25',
        'next_appointment' => '2026-05-11',
        'status' => 'Active',
        'notes' => 'First trimester completed. Morning sickness improving. First pregnancy.',
        'allergies' => 'None known',
        'medications' => 'Prenatal vitamins, Folic acid',
        'medical_history' => 'No significant medical history',
        'weight_gain' => '3 lbs',
        'bp_last' => '110/70',
        'heart_rate' => '65 bpm'
    ],
    7 => [
        'id' => 7,
        'name' => 'Rachel Green',
        'email' => 'rachel.green@email.com',
        'phone' => '+1 (555) 789-0123',
        'age' => 30,
        'pregnancy_week' => 8,
        'due_date' => '2027-01-15',
        'blood_type' => 'B-',
        'emergency_contact' => 'Ross Green - +1 (555) 789-0124',
        'address' => '147 Birch Lane, City, State 12345',
        'last_visit' => '2026-04-20',
        'next_appointment' => '2026-05-09',
        'status' => 'Active',
        'notes' => 'Early pregnancy. First prenatal visit completed. Establishing care.',
        'allergies' => 'Aspirin',
        'medications' => 'Prenatal vitamins',
        'medical_history' => 'Previous miscarriage in 2024, monitoring closely',
        'weight_gain' => '1 lb',
        'bp_last' => '118/76',
        'heart_rate' => '68 bpm'
    ],
    8 => [
        'id' => 8,
        'name' => 'Jessica Taylor',
        'email' => 'jessica.taylor@email.com',
        'phone' => '+1 (555) 890-1234',
        'age' => 35,
        'pregnancy_week' => 36,
        'due_date' => '2026-06-20',
        'blood_type' => 'AB-',
        'emergency_contact' => 'Mark Taylor - +1 (555) 890-1235',
        'address' => '258 Spruce Dr, City, State 12345',
        'last_visit' => '2026-05-06',
        'next_appointment' => '2026-05-08',
        'status' => 'Active',
        'notes' => 'Advanced maternal age. Close monitoring. Baby in good position for delivery.',
        'allergies' => 'Codeine',
        'medications' => 'Prenatal vitamins, Baby aspirin',
        'medical_history' => 'Advanced maternal age, gestational hypertension (controlled)',
        'weight_gain' => '28 lbs',
        'bp_last' => '130/85',
        'heart_rate' => '78 bpm'
    ]
];

$patient = isset($patients[$patient_id]) ? $patients[$patient_id] : null;

if (!$patient) {
    header('Location: patients.php?error=not_found');
    exit();
}

// Dummy appointment history
$appointment_history = [
    [
        'date' => '2026-05-05',
        'time' => '10:00 AM',
        'type' => 'Regular Checkup',
        'notes' => 'Normal fetal heart rate. Blood pressure stable. Weight gain appropriate.',
        'doctor' => 'Dr. Demo Doctor'
    ],
    [
        'date' => '2026-04-21',
        'time' => '02:30 PM',
        'type' => 'Ultrasound',
        'notes' => 'Anatomy scan completed. All organs developing normally. Gender revealed.',
        'doctor' => 'Dr. Demo Doctor'
    ],
    [
        'date' => '2026-04-07',
        'time' => '11:15 AM',
        'type' => 'Blood Work',
        'notes' => 'Glucose tolerance test passed. Iron levels slightly low, supplement prescribed.',
        'doctor' => 'Dr. Demo Doctor'
    ]
];

// Dummy vital signs history
$vitals_history = [
    ['date' => '2026-05-05', 'weight' => '145 lbs', 'bp' => '120/80', 'heart_rate' => '72'],
    ['date' => '2026-04-21', 'weight' => '143 lbs', 'bp' => '118/78', 'heart_rate' => '70'],
    ['date' => '2026-04-07', 'weight' => '141 lbs', 'bp' => '115/75', 'heart_rate' => '68'],
    ['date' => '2026-03-24', 'weight' => '139 lbs', 'bp' => '112/72', 'heart_rate' => '66']
];

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <a href="patients.php" class="btn btn-secondary" style="padding: 8px 12px;">
                    <i class="fas fa-arrow-left"></i> Back to Patients
                </a>
                <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin: 0;">
                    <?php echo htmlspecialchars($patient['name']); ?>
                </h1>
            </div>
            <p style="color: #546e7a; font-size: 16px; margin: 0;">
                Week <?php echo $patient['pregnancy_week']; ?> of Pregnancy • Due: <?php echo date('M j, Y', strtotime($patient['due_date'])); ?>
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <button class="btn btn-primary" onclick="scheduleAppointment()">
                <i class="fas fa-calendar-plus"></i> Schedule Appointment
            </button>
            <button class="btn btn-secondary" onclick="addNotes()">
                <i class="fas fa-notes-medical"></i> Add Notes
            </button>
        </div>
    </div>

    <!-- Patient Overview Cards -->
    <div class="grid grid-cols-4 gap-6" style="margin-bottom: 32px;">
        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-heartbeat"></i>
                    Current Status
                </h3>
            </div>
            <div style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Week:</span>
                        <span style="color: #C4A7FF; font-weight: 600;"><?php echo $patient['pregnancy_week']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Blood Pressure:</span>
                        <span style="color: #ffffff;"><?php echo $patient['bp_last']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Heart Rate:</span>
                        <span style="color: #ffffff;"><?php echo $patient['heart_rate']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Weight Gain:</span>
                        <span style="color: #ffffff;"><?php echo $patient['weight_gain']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i>
                    Personal Info
                </h3>
            </div>
            <div style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Age:</span>
                        <span style="color: #ffffff;"><?php echo $patient['age']; ?> years</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Blood Type:</span>
                        <span style="color: #ffffff;"><?php echo $patient['blood_type']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Phone:</span>
                        <span style="color: #ffffff; font-size: 12px;"><?php echo $patient['phone']; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #546e7a;">Status:</span>
                        <span class="status-badge confirmed"><?php echo $patient['status']; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check"></i>
                    Appointments
                </h3>
            </div>
            <div style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div>
                        <span style="color: #546e7a; font-size: 12px;">Last Visit:</span>
                        <div style="color: #ffffff; font-weight: 500;"><?php echo date('M j, Y', strtotime($patient['last_visit'])); ?></div>
                    </div>
                    <div>
                        <span style="color: #546e7a; font-size: 12px;">Next Appointment:</span>
                        <div style="color: #C4A7FF; font-weight: 500;"><?php echo date('M j, Y', strtotime($patient['next_appointment'])); ?></div>
                    </div>
                    <button class="btn btn-primary" style="width: 100%; margin-top: 8px;" onclick="viewSchedule()">
                        <i class="fas fa-calendar-alt"></i> View Schedule
                    </button>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Alerts
                </h3>
            </div>
            <div style="padding: 16px;">
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php if (!empty($patient['allergies']) && $patient['allergies'] !== 'None known'): ?>
                    <div style="padding: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px;">
                        <div style="font-size: 12px; color: #ef4444; font-weight: 600;">ALLERGY</div>
                        <div style="font-size: 12px; color: #ffffff;"><?php echo $patient['allergies']; ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="padding: 8px; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 6px;">
                        <div style="font-size: 12px; color: #22c55e; font-weight: 600;">STATUS</div>
                        <div style="font-size: 12px; color: #ffffff;">Normal Progress</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="glass-card">
        <div class="card-header">
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('overview')">
                    <i class="fas fa-user"></i> Overview
                </button>
                <button class="tab-btn" onclick="showTab('appointments')">
                    <i class="fas fa-calendar-alt"></i> Appointments
                </button>
                <button class="tab-btn" onclick="showTab('vitals')">
                    <i class="fas fa-heartbeat"></i> Vitals
                </button>
                <button class="tab-btn" onclick="showTab('notes')">
                    <i class="fas fa-notes-medical"></i> Notes
                </button>
            </div>
        </div>

        <!-- Overview Tab -->
        <div id="overview-tab" class="tab-content active">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 style="color: #ffffff; margin-bottom: 16px; font-size: 18px;">
                        <i class="fas fa-info-circle"></i> Patient Information
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="info-row">
                            <label>Full Name:</label>
                            <span><?php echo htmlspecialchars($patient['name']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Email:</label>
                            <span><?php echo htmlspecialchars($patient['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Phone:</label>
                            <span><?php echo htmlspecialchars($patient['phone']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Address:</label>
                            <span><?php echo htmlspecialchars($patient['address']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Emergency Contact:</label>
                            <span><?php echo htmlspecialchars($patient['emergency_contact']); ?></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 style="color: #ffffff; margin-bottom: 16px; font-size: 18px;">
                        <i class="fas fa-notes-medical"></i> Medical Information
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="info-row">
                            <label>Medical History:</label>
                            <span><?php echo htmlspecialchars($patient['medical_history']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Current Medications:</label>
                            <span><?php echo htmlspecialchars($patient['medications']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Known Allergies:</label>
                            <span><?php echo htmlspecialchars($patient['allergies']); ?></span>
                        </div>
                        <div class="info-row">
                            <label>Current Notes:</label>
                            <span><?php echo htmlspecialchars($patient['notes']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Tab -->
        <div id="appointments-tab" class="tab-content">
            <h3 style="color: #ffffff; margin-bottom: 16px; font-size: 18px;">
                <i class="fas fa-history"></i> Appointment History
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Doctor</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointment_history as $appointment): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($appointment['date'])); ?></td>
                            <td><?php echo $appointment['time']; ?></td>
                            <td><?php echo $appointment['type']; ?></td>
                            <td><?php echo $appointment['doctor']; ?></td>
                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($appointment['notes']); ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="action-btn view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit" title="Edit Notes">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vitals Tab -->
        <div id="vitals-tab" class="tab-content">
            <h3 style="color: #ffffff; margin-bottom: 16px; font-size: 18px;">
                <i class="fas fa-chart-line"></i> Vital Signs History
            </h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Weight</th>
                            <th>Blood Pressure</th>
                            <th>Heart Rate</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vitals_history as $vital): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($vital['date'])); ?></td>
                            <td><?php echo $vital['weight']; ?></td>
                            <td><?php echo $vital['bp']; ?></td>
                            <td><?php echo $vital['heart_rate']; ?></td>
                            <td>
                                <div class="table-actions">
                                    <button class="action-btn view" title="View Chart">
                                        <i class="fas fa-chart-area"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notes Tab -->
        <div id="notes-tab" class="tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="color: #ffffff; margin: 0; font-size: 18px;">
                    <i class="fas fa-sticky-note"></i> Clinical Notes
                </h3>
                <button class="btn btn-primary" onclick="addNewNote()">
                    <i class="fas fa-plus"></i> Add Note
                </button>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div class="note-card">
                    <div class="note-header">
                        <div>
                            <strong>May 5, 2026 - Regular Checkup</strong>
                            <span style="color: #546e7a; margin-left: 12px;">Dr. Demo Doctor</span>
                        </div>
                        <button class="action-btn edit" title="Edit Note">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="note-content">
                        Patient reports feeling well. Fetal movements are strong and regular. No concerning symptoms. 
                        Blood pressure remains stable. Weight gain is appropriate for gestational age. 
                        Discussed nutrition and exercise recommendations.
                    </div>
                </div>

                <div class="note-card">
                    <div class="note-header">
                        <div>
                            <strong>April 21, 2026 - Ultrasound</strong>
                            <span style="color: #546e7a; margin-left: 12px;">Dr. Demo Doctor</span>
                        </div>
                        <button class="action-btn edit" title="Edit Note">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="note-content">
                        Anatomy scan completed successfully. All fetal organs appear normal and developing appropriately. 
                        Placental position is normal. Amniotic fluid levels are adequate. 
                        Patient opted to learn baby's gender - it's a girl!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Tab functionality
    function showTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(tab => tab.classList.remove('active'));
        
        // Remove active class from all tab buttons
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => btn.classList.remove('active'));
        
        // Show selected tab content
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Add active class to clicked button
        event.target.classList.add('active');
    }

    // Action functions
    function scheduleAppointment() {
        alert('Opening appointment scheduler... (This would open a scheduling modal in a real implementation)');
    }

    function addNotes() {
        alert('Opening notes editor... (This would open a notes modal in a real implementation)');
    }

    function viewSchedule() {
        window.open('appointments.php', '_blank');
    }

    function addNewNote() {
        alert('Opening new note editor... (This would open a note creation modal in a real implementation)');
    }
</script>

<style>
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 12px 0;
        border-bottom: 1px solid rgba(244, 114, 182, 0.1);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row label {
        color: #546e7a;
        font-weight: 600;
        min-width: 140px;
        font-size: 14px;
    }

    .info-row span {
        color: #ffffff;
        text-align: right;
        flex: 1;
        font-size: 14px;
    }

    .tab-navigation {
        display: flex;
        gap: 4px;
    }

    .tab-btn {
        padding: 12px 20px;
        background: transparent;
        border: none;
        color: #546e7a;
        cursor: pointer;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tab-btn:hover {
        background: rgba(244, 114, 182, 0.1);
        color: #C4A7FF;
    }

    .tab-btn.active {
        background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
        color: #ffffff;
    }

    .tab-content {
        display: none;
        padding: 24px;
    }

    .tab-content.active {
        display: block;
    }

    .note-card {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(244, 114, 182, 0.2);
        border-radius: 12px;
        padding: 20px;
    }

    .note-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        color: #ffffff;
        font-size: 14px;
    }

    .note-content {
        color: #546e7a;
        line-height: 1.6;
        font-size: 14px;
    }

    .grid-cols-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .grid-cols-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .grid-cols-2,
        .grid-cols-4 {
            grid-template-columns: 1fr;
        }

        .tab-navigation {
            flex-wrap: wrap;
        }

        .tab-btn {
            flex: 1;
            min-width: 120px;
        }
    }
</style>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
