<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'All Patients';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

$db = getDoctorDB();

// For demo purposes, use dummy patient data since we're using hardcoded doctor credentials
$patients = [
    [
        'id' => 1,
        'name' => 'Sarah Parker',
        'email' => 'sarah.parker@email.com',
        'phone' => '+1 (555) 123-4567',
        'pregnancy_week' => 24,
        'appointment_count' => 8,
        'last_appointment' => '2026-05-05',
        'created_at' => '2026-01-15'
    ],
    [
        'id' => 2,
        'name' => 'Maria Johnson',
        'email' => 'maria.johnson@email.com',
        'phone' => '+1 (555) 234-5678',
        'pregnancy_week' => 16,
        'appointment_count' => 5,
        'last_appointment' => '2026-04-28',
        'created_at' => '2026-02-10'
    ],
    [
        'id' => 3,
        'name' => 'Emily Wilson',
        'email' => 'emily.wilson@email.com',
        'phone' => '+1 (555) 345-6789',
        'pregnancy_week' => 32,
        'appointment_count' => 12,
        'last_appointment' => '2026-05-03',
        'created_at' => '2026-01-20'
    ],
    [
        'id' => 4,
        'name' => 'Lisa Brown',
        'email' => 'lisa.brown@email.com',
        'phone' => '+1 (555) 456-7890',
        'pregnancy_week' => 28,
        'appointment_count' => 9,
        'last_appointment' => '2026-05-07',
        'created_at' => '2026-02-05'
    ],
    [
        'id' => 5,
        'name' => 'Jennifer Davis',
        'email' => 'jennifer.davis@email.com',
        'phone' => '+1 (555) 567-8901',
        'pregnancy_week' => 20,
        'appointment_count' => 6,
        'last_appointment' => '2026-04-30',
        'created_at' => '2026-02-15'
    ],
    [
        'id' => 6,
        'name' => 'Amanda Wilson',
        'email' => 'amanda.wilson@email.com',
        'phone' => '+1 (555) 678-9012',
        'pregnancy_week' => 12,
        'appointment_count' => 3,
        'last_appointment' => '2026-04-25',
        'created_at' => '2026-03-01'
    ],
    [
        'id' => 7,
        'name' => 'Rachel Green',
        'email' => 'rachel.green@email.com',
        'phone' => '+1 (555) 789-0123',
        'pregnancy_week' => 8,
        'appointment_count' => 2,
        'last_appointment' => '2026-04-20',
        'created_at' => '2026-03-10'
    ],
    [
        'id' => 8,
        'name' => 'Jessica Taylor',
        'email' => 'jessica.taylor@email.com',
        'phone' => '+1 (555) 890-1234',
        'pregnancy_week' => 36,
        'appointment_count' => 15,
        'last_appointment' => '2026-05-06',
        'created_at' => '2026-01-05'
    ]
];

// In a real application, this would fetch from database:
/*
$stmt = $db->prepare("
    SELECT u.*, 
           COUNT(DISTINCT a.id) as appointment_count,
           MAX(a.appointment_date) as last_appointment
    FROM users u 
    LEFT JOIN appointments a ON u.id = a.user_id AND a.doctor_id = ?
    GROUP BY u.id 
    ORDER BY u.created_at DESC
");
$stmt->execute([$doctor['id']]);
$patients = $stmt->fetchAll();
*/

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #263238; margin-bottom: 8px;">
            <i class="fas fa-user-friends"></i> All Patients
        </h1>
        <p style="color: #546e7a; font-size: 16px;">
            Complete patient directory - <?php echo count($patients); ?> total patients
        </p>
    </div>

    <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 16px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-search" style="color: #C4A7FF;"></i>
        <input type="text" placeholder="Search patients by name, email, or phone..." id="searchInput" style="flex: 1; background: transparent; border: none; color: #263238; font-size: 16px; outline: none;">
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php foreach ($patients as $patient): ?>
        <div class="glass-card patient-card" style="cursor: pointer;" onclick="viewPatient(<?php echo $patient['id']; ?>)">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div class="user-avatar" style="width: 60px; height: 60px; font-size: 24px;">
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
                <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 12px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: #C4A7FF; margin-bottom: 4px;">
                        <?php echo $patient['pregnancy_week'] ?? 'N/A'; ?>
                    </div>
                    <div style="font-size: 12px; color: #546e7a;">Pregnancy Week</div>
                </div>
                <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 12px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: #C4A7FF; margin-bottom: 4px;">
                        <?php echo $patient['appointment_count']; ?>
                    </div>
                    <div style="font-size: 12px; color: #546e7a;">Appointments</div>
                </div>
            </div>

            <div style="padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.1); font-size: 14px; color: #546e7a;">
                <div style="margin-bottom: 4px;">
                    <i class="fas fa-phone" style="color: #C4A7FF; width: 20px;"></i>
                    <?php echo htmlspecialchars($patient['phone'] ?? 'Not provided'); ?>
                </div>
                <div>
                    <i class="fas fa-calendar" style="color: #C4A7FF; width: 20px;"></i>
                    Last Visit: <?php echo $patient['last_appointment'] ? date('M d, Y', strtotime($patient['last_appointment'])) : 'Never'; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function viewPatient(id) {
    window.location.href = 'patient_detail.php?id=' + id;
}

document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.patient-card');
    
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(searchTerm) ? 'block' : 'none';
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

.patient-card::after {
    content: 'Click to view details';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #C4A7FF 0%, #7F5AF0 100%);
    color: white;
    padding: 8px 16px;
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.patient-card:hover::after {
    transform: translateY(0);
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
    
    .patient-card::after {
        font-size: 11px;
        padding: 6px 12px;
    }
}
</style>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>

