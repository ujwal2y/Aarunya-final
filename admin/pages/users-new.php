<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

// Create health_metrics table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `health_metrics` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `blood_pressure_systolic` int(11) DEFAULT NULL,
        `blood_pressure_diastolic` int(11) DEFAULT NULL,
        `hemoglobin` decimal(4,2) DEFAULT NULL,
        `heart_rate` int(11) DEFAULT NULL,
        `weight` decimal(5,2) DEFAULT NULL,
        `temperature` decimal(4,2) DEFAULT NULL,
        `glucose_level` int(11) DEFAULT NULL,
        `notes` text DEFAULT NULL,
        `recorded_by` int(11) DEFAULT NULL,
        `recorded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `recorded_at` (`recorded_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (PDOException $e) {
    // Table might already exist
}

$pageTitle = 'Patients Management';
$message = '';
$messageType = 'success';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? 0;
    
    if ($action === 'delete' && $userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ?");
        $stmt->execute([$userId]);
        $appointmentCount = $stmt->fetchColumn();
        
        if ($appointmentCount > 0) {
            $message = 'Cannot delete patient with existing appointments';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                $message = 'Patient deleted successfully';
            } else {
                $message = 'Failed to delete patient';
                $messageType = 'danger';
            }
        }
    } elseif ($action === 'toggle_status' && $userId) {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'blocked', 'active') WHERE id = ?");
        if ($stmt->execute([$userId])) {
            $message = 'Patient status updated successfully';
        } else {
            $message = 'Failed to update patient status';
            $messageType = 'danger';
        }
    }
}

// Get statistics
$totalPatients = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeMothers = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND pregnancy_week IS NOT NULL")->fetchColumn();
$weeklyAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$highRiskCases = $pdo->query("SELECT COUNT(*) FROM users WHERE pregnancy_week > 35")->fetchColumn();

// Get all users with additional info
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

$query = "SELECT u.*, 
          COUNT(DISTINCT a.id) as appointment_count,
          COUNT(DISTINCT e.id) as emergency_count,
          MAX(a.appointment_date) as last_appointment
          FROM users u 
          LEFT JOIN appointments a ON u.id = a.user_id 
          LEFT JOIN emergency_requests e ON u.id = e.user_id 
          WHERE (u.name LIKE ? OR u.email LIKE ?)";

$params = ["%$search%", "%$search%"];

if ($statusFilter) {
    $query .= " AND u.status = ?";
    $params[] = $statusFilter;
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
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
                <a href="users.php" class="nav-item active">
                    <i class="fas fa-users"></i>
                    <span>Patients</span>
                </a>
                <a href="doctors.php" class="nav-item">
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
                    <h1 class="page-title">Patients Management</h1>
                    <p class="page-subtitle">Manage all registered mothers and maternal care patients</p>
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
                            <div class="stat-value"><?php echo $totalPatients; ?></div>
                            <div class="stat-label">Total Patients</div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
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
                            <div class="stat-value"><?php echo $activeMothers; ?></div>
                            <div class="stat-label">Active Mothers</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #EC4899, #DB2777);">
                            <i class="fas fa-baby"></i>
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
                            <div class="stat-value"><?php echo $weeklyAppointments; ?></div>
                            <div class="stat-label">Weekly Appointments</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>15% from last week</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $highRiskCases; ?></div>
                            <div class="stat-label">High Risk Cases</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span style="color: var(--text-secondary);">Requires attention</span>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">All Patients (<?php echo count($users); ?>)</h2>
                    <div class="card-actions">
                        <div class="search-box">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchInput" class="search-input" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <select id="statusFilter" class="form-select" style="width: auto; height: var(--input-height);" onchange="filterByStatus()">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="blocked" <?php echo $statusFilter === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                        </select>
                        <button onclick="exportData('csv')" class="btn btn-secondary btn-sm">
                            <i class="fas fa-download"></i>
                            CSV
                        </button>
                        <button onclick="exportData('json')" class="btn btn-secondary btn-sm">
                            <i class="fas fa-file-code"></i>
                            JSON
                        </button>
                    </div>
                </div>

                <?php if (count($users) > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Age</th>
                                <th>Pregnancy Week</th>
                                <th>Appointments</th>
                                <th>Last Visit</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="patientsTable">
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--text-secondary);">#<?php echo $user['id']; ?></td>
                                <td>
                                    <div class="flex items-center gap-md">
                                        <div class="avatar avatar-sm">
                                            <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?php echo htmlspecialchars($user['name']); ?>
                                            </div>
                                            <div style="font-size: 13px; color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo $user['age'] ?? 'N/A'; ?> years</td>
                                <td>
                                    <?php if ($user['pregnancy_week']): ?>
                                        <span class="badge badge-info">
                                            Week <?php echo $user['pregnancy_week']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo $user['appointment_count']; ?> appointments
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['last_appointment']): ?>
                                        <span style="color: var(--text-secondary); font-size: 13px;">
                                            <?php echo date('M d, Y', strtotime($user['last_appointment'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 13px;">No visits</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo ($user['status'] ?? 'active') === 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button onclick="viewPatient(<?php echo $user['id']; ?>)" class="action-btn view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="action-btn edit" title="Toggle Status">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $user['appointment_count']; ?>)">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="action-btn delete" title="Delete Patient">
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

                <!-- Pagination -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--spacing-lg); padding-top: var(--spacing-lg); border-top: 1px solid var(--divider-color);">
                    <div style="color: var(--text-secondary); font-size: 14px;">
                        Showing <strong style="color: var(--text-primary);"><?php echo count($users); ?></strong> patients
                    </div>
                    <div class="flex gap-sm">
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-chevron-left"></i>
                            Previous
                        </button>
                        <button class="btn btn-secondary btn-sm" style="background: var(--gradient-primary); color: white; border: none;">
                            1
                        </button>
                        <button class="btn btn-secondary btn-sm" disabled>
                            Next
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="empty-title">No Patients Found</h3>
                    <p class="empty-subtitle">No patients match your search criteria</p>
                    <button onclick="window.location.href='users.php'" class="btn btn-primary">
                        <i class="fas fa-refresh"></i>
                        Clear Filters
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Real-time search
        document.getElementById('searchInput')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#patientsTable tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Search on enter
        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchValue = e.target.value;
                window.location.href = `users.php?search=${encodeURIComponent(searchValue)}`;
            }
        });

        function filterByStatus() {
            const status = document.getElementById('statusFilter').value;
            const currentSearch = new URLSearchParams(window.location.search).get('search') || '';
            let url = 'users.php?';
            if (currentSearch) url += `search=${encodeURIComponent(currentSearch)}&`;
            if (status) url += `status=${status}`;
            window.location.href = url;
        }

        function viewPatient(id) {
            window.location.href = `users.php?view=${id}`;
        }

        function confirmDelete(appointmentCount) {
            if (appointmentCount > 0) {
                return confirm(`This patient has ${appointmentCount} appointments. Are you sure you want to delete?`);
            }
            return confirm('Are you sure you want to delete this patient?');
        }

        function exportData(format) {
            window.location.href = `../actions/export_data.php?type=users&format=${format}`;
        }
    </script>
</body>
</html>
