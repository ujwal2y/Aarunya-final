<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$pageTitle = 'Doctors Management';
$message = '';
$messageType = 'success';

// Handle add doctor action
$showAddForm = isset($_GET['action']) && $_GET['action'] === 'add';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization']);
        $experience = intval($_POST['experience']);
        $qualification = trim($_POST['qualification'] ?? '');
        $availability = trim($_POST['availability']);
        
        if ($name && $specialization && $availability) {
            try {
                $stmt = $pdo->prepare("INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $phone, $specialization, $experience, $qualification, $availability])) {
                    $message = 'Doctor added successfully';
                    $showAddForm = false;
                } else {
                    $message = 'Failed to add doctor';
                    $messageType = 'danger';
                }
            } catch(Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } else {
            $message = 'Name, specialization, and availability are required';
            $messageType = 'danger';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['doctor_id']);
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
            $stmt->execute([$id]);
            $appointmentCount = $stmt->fetchColumn();
            
            if ($appointmentCount > 0) {
                $message = 'Cannot delete doctor with existing appointments';
                $messageType = 'danger';
            } else {
                $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'Doctor deleted successfully';
                } else {
                    $message = 'Failed to delete doctor';
                    $messageType = 'danger';
                }
            }
        } catch(Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get statistics
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$activeDoctors = $pdo->query("SELECT COUNT(*) FROM doctors WHERE is_active = 1")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$specializations = $pdo->query("SELECT COUNT(DISTINCT specialization) FROM doctors")->fetchColumn();

// Get all doctors with appointment counts
$doctors = $pdo->query("SELECT d.*, COUNT(a.id) as appointment_count 
                        FROM doctors d 
                        LEFT JOIN appointments a ON d.id = a.doctor_id 
                        GROUP BY d.id 
                        ORDER BY d.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Aarunya Admin</title>
    <link rel="stylesheet" href="../assets/css/healthcare-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>Aarunya Admin</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
                <a href="doctors.php" class="nav-item active">
                    <i class="fas fa-user-md"></i>
                    <span>Doctors</span>
                </a>
                <a href="appointments.php" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <a href="emergency.php" class="nav-item">
                    <i class="fas fa-ambulance"></i>
                    <span>Emergency</span>
                </a>
                <a href="reports.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <button onclick="window.location.href='../logout.php'" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Doctors Management</h1>
                    <p class="page-subtitle">Manage healthcare providers and medical staff</p>
                </div>
            </div>

            <!-- Alert Message -->
            <?php if ($message): ?>
            <div class="content-card" style="background: <?php echo $messageType === 'success' ? 'var(--success-bg)' : 'var(--danger-bg)'; ?>; border-color: <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; margin-bottom: var(--spacing-xl);">
                <div class="flex items-center gap-md">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>" style="font-size: 24px; color: <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>;"></i>
                    <span style="color: <?php echo $messageType === 'success' ? 'var(--success)' : 'var(--danger)'; ?>; font-weight: 500;"><?php echo $message; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $totalDoctors; ?></div>
                            <div class="stat-label">Total Doctors</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>12% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $activeDoctors; ?></div>
                            <div class="stat-label">Active Doctors</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>8% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $totalAppointments; ?></div>
                            <div class="stat-label">Total Appointments</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>15% from last month</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $specializations; ?></div>
                            <div class="stat-label">Specializations</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span style="color: var(--text-secondary);">Active departments</span>
                    </div>
                </div>
            </div>

            <!-- Add Doctor Form -->
            <?php if ($showAddForm): ?>
            <div class="content-card mb-xl">
                <div class="card-header">
                    <h2 class="card-title">Add New Doctor</h2>
                    <button onclick="window.location.href='doctors.php'" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Full Name *
                            </label>
                            <input type="text" name="name" class="form-input" placeholder="Dr. John Doe" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" name="email" class="form-input" placeholder="doctor@aarunya.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="text" name="phone" class="form-input" placeholder="+91 98765 43210">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-briefcase"></i> Experience (Years)
                            </label>
                            <input type="number" name="experience" class="form-input" placeholder="10" min="0" max="50">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-stethoscope"></i> Specialization *
                            </label>
                            <select name="specialization" class="form-select" required>
                                <option value="">Select Specialization</option>
                                <option value="Obstetrician">Obstetrician</option>
                                <option value="Gynecologist">Gynecologist</option>
                                <option value="Maternal-Fetal Medicine">Maternal-Fetal Medicine</option>
                                <option value="Perinatologist">Perinatologist</option>
                                <option value="Midwife Specialist">Midwife Specialist</option>
                                <option value="Reproductive Endocrinologist">Reproductive Endocrinologist</option>
                                <option value="Neonatologist">Neonatologist</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-graduation-cap"></i> Qualification
                            </label>
                            <input type="text" name="qualification" class="form-input" placeholder="MBBS, MD (OB/GYN)">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock"></i> Availability *
                        </label>
                        <input type="text" name="availability" class="form-input" placeholder="Mon-Fri 9AM-5PM" required>
                    </div>
                    
                    <div class="flex gap-md" style="justify-content: flex-end; margin-top: var(--spacing-xl);">
                        <button type="button" onclick="window.location.href='doctors.php'" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Doctor
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- Doctors Table -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">All Doctors (<?php echo count($doctors); ?>)</h2>
                    <div class="card-actions">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" class="search-input" placeholder="Search doctors...">
                        </div>
                        <button onclick="exportData('csv')" class="btn btn-secondary btn-sm">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                        <?php if (!$showAddForm): ?>
                        <button onclick="window.location.href='doctors.php?action=add'" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Doctor
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (count($doctors) > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Specialization</th>
                                <th>Experience</th>
                                <th>Contact</th>
                                <th>Appointments</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="doctorsTable">
                            <?php foreach($doctors as $doctor): ?>
                            <tr>
                                <td>
                                    <div class="flex items-center gap-md">
                                        <div class="avatar">
                                            <?php if (!empty($doctor['profile_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($doctor['profile_image']); ?>" alt="Dr. <?php echo htmlspecialchars($doctor['name']); ?>">
                                            <?php else: ?>
                                                <?php echo strtoupper(substr($doctor['name'], 0, 2)); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($doctor['name']); ?>
                                            </div>
                                            <div style="font-size: 13px; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($doctor['qualification'] ?? 'No qualification'); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                <td><?php echo $doctor['experience'] ?? 0; ?> years</td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <?php if ($doctor['email']): ?>
                                            <div style="color: var(--text-primary); margin-bottom: 4px;">
                                                <i class="fas fa-envelope" style="width: 16px;"></i>
                                                <?php echo htmlspecialchars($doctor['email']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($doctor['phone']): ?>
                                            <div style="color: var(--text-secondary);">
                                                <i class="fas fa-phone" style="width: 16px;"></i>
                                                <?php echo htmlspecialchars($doctor['phone']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo $doctor['appointment_count']; ?> appointments
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo ($doctor['is_active'] ?? 1) ? 'success' : 'danger'; ?>">
                                        <?php echo ($doctor['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button onclick="viewDoctor(<?php echo $doctor['id']; ?>)" class="action-btn view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editDoctor(<?php echo $doctor['id']; ?>)" class="action-btn edit" title="Edit Doctor">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $doctor['appointment_count']; ?>)">
                                            <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="action-btn delete" title="Delete Doctor">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3 class="empty-title">No Doctors Found</h3>
                    <p class="empty-subtitle">Get started by adding your first doctor to the system</p>
                    <button onclick="window.location.href='doctors.php?action=add'" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Add First Doctor
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#doctorsTable tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        function viewDoctor(id) {
            alert('View doctor details - ID: ' + id);
        }

        function editDoctor(id) {
            alert('Edit doctor - ID: ' + id);
        }

        function confirmDelete(appointmentCount) {
            if (appointmentCount > 0) {
                return confirm(`This doctor has ${appointmentCount} appointments. Are you sure you want to delete?`);
            }
            return confirm('Are you sure you want to delete this doctor?');
        }

        function exportData(format) {
            window.location.href = `../actions/export_data.php?type=doctors&format=${format}`;
        }
    </script>
</body>
</html>
