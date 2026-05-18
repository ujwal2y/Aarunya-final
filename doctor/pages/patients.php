<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'My Patients';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

$db = getDoctorDB();

// For demo purposes, use dummy patient data
$patients = [
    [
        'id' => 1,
        'name' => 'Sarah Parker',
        'email' => 'sarah.parker@email.com',
        'phone' => '+1 (555) 123-4567',
        'pregnancy_week' => 24,
        'blood_group' => 'O+',
        'total_appointments' => 8,
        'last_visit' => '2026-05-05'
    ],
    [
        'id' => 2,
        'name' => 'Maria Johnson',
        'email' => 'maria.johnson@email.com',
        'phone' => '+1 (555) 234-5678',
        'pregnancy_week' => 16,
        'blood_group' => 'A+',
        'total_appointments' => 5,
        'last_visit' => '2026-04-28'
    ],
    [
        'id' => 3,
        'name' => 'Emily Wilson',
        'email' => 'emily.wilson@email.com',
        'phone' => '+1 (555) 345-6789',
        'pregnancy_week' => 32,
        'blood_group' => 'B+',
        'total_appointments' => 12,
        'last_visit' => '2026-05-03'
    ],
    [
        'id' => 4,
        'name' => 'Lisa Brown',
        'email' => 'lisa.brown@email.com',
        'phone' => '+1 (555) 456-7890',
        'pregnancy_week' => 28,
        'blood_group' => 'AB+',
        'total_appointments' => 9,
        'last_visit' => '2026-05-07'
    ],
    [
        'id' => 5,
        'name' => 'Jennifer Davis',
        'email' => 'jennifer.davis@email.com',
        'phone' => '+1 (555) 567-8901',
        'pregnancy_week' => 20,
        'blood_group' => 'O-',
        'total_appointments' => 6,
        'last_visit' => '2026-04-30'
    ],
    [
        'id' => 6,
        'name' => 'Amanda Wilson',
        'email' => 'amanda.wilson@email.com',
        'phone' => '+1 (555) 678-9012',
        'pregnancy_week' => 12,
        'blood_group' => 'A-',
        'total_appointments' => 3,
        'last_visit' => '2026-04-25'
    ]
];

// In a real application, this would fetch from database:
/*
$stmt = $db->prepare("
    SELECT DISTINCT u.*, 
           COUNT(a.id) as total_appointments,
           MAX(a.appointment_date) as last_visit
    FROM users u
    JOIN appointments a ON u.id = a.user_id
    WHERE a.doctor_id = ?
    GROUP BY u.id
    ORDER BY last_visit DESC
");
$stmt->execute([$doctor['id']]);
$patients = $stmt->fetchAll();
*/

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <!-- Page Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #263238; margin-bottom: 8px;">
            <i class="fas fa-users"></i> My Patients
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            View and manage your patient medical records
        </p>
    </div>

    <!-- Search Bar -->
    <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-search" style="color: #C4A7FF; font-size: 18px;"></i>
        <input type="text" placeholder="Search patients by name, email, or phone..." id="searchInput" style="flex: 1; background: transparent; border: none; color: #263238; font-size: 16px; outline: none;">
    </div>

    <?php if (count($patients) > 0): ?>
        <!-- Patients Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
            <?php foreach ($patients as $patient): ?>
            <div class="glass-card patient-card" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                    <div class="user-avatar" style="width: 64px; height: 64px; font-size: 24px;">
                        <?php echo strtoupper(substr($patient['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($patient['name']); ?>
                        </h3>
                        <p style="font-size: 14px; color: #546e7a;">
                            <?php echo htmlspecialchars($patient['email']); ?>
                        </p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 16px;">
                    <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 12px;">
                        <div style="font-size: 20px; font-weight: 700; color: #C4A7FF; margin-bottom: 4px;">
                            <?php echo $patient['pregnancy_week'] ?? 'N/A'; ?>
                        </div>
                        <div style="font-size: 12px; color: #546e7a;">Pregnancy Week</div>
                    </div>
                    <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 12px;">
                        <div style="font-size: 20px; font-weight: 700; color: #C4A7FF; margin-bottom: 4px;">
                            <?php echo $patient['total_appointments']; ?>
                        </div>
                        <div style="font-size: 12px; color: #546e7a;">Appointments</div>
                    </div>
                </div>

                <div style="padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-phone" style="color: #C4A7FF; width: 20px;"></i>
                        <span><?php echo htmlspecialchars($patient['phone'] ?? 'Not provided'); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-calendar" style="color: #C4A7FF; width: 20px;"></i>
                        <span>Last Visit: <?php echo $patient['last_visit'] ? date('M d, Y', strtotime($patient['last_visit'])) : 'Never'; ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #546e7a;">
                        <i class="fas fa-droplet" style="color: #C4A7FF; width: 20px;"></i>
                        <span>Blood Group: <?php echo htmlspecialchars($patient['blood_group'] ?? 'Not specified'); ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 64px; color: #334155; margin-bottom: 16px;">
                <i class="fas fa-user-slash"></i>
            </div>
            <p style="font-size: 18px; color: #78909c;">No patients yet. Patients will appear here after appointments.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function viewPatient(patientId) {
    window.location.href = 'patient_detail.php?id=' + patientId;
}

document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.patient-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<style>
.patient-card {
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.patient-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(244, 114, 182, 0.2);
}

.patient-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(244, 114, 182, 0.1), transparent);
    transition: left 0.6s ease;
}

.patient-card:hover::before {
    left: 100%;
}

.user-avatar {
    background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(244, 114, 182, 0.3);
}

@media (max-width: 768px) {
    .patient-card {
        margin-bottom: 16px;
    }
}
</style>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>

