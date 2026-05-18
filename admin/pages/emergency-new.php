<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$pageTitle = 'Emergency Requests';
$message = '';
$messageType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = intval($_POST['request_id']);
    $status = $_POST['status'];
    
    $updateData = [$status, $id];
    $query = "UPDATE emergency_requests SET status = ?";
    
    if ($status === 'resolved') {
        $query .= ", resolved_at = NOW()";
    }
    
    $query .= " WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    if ($stmt->execute($updateData)) {
        $message = 'Emergency request updated successfully';
    } else {
        $message = 'Failed to update emergency request';
        $messageType = 'danger';
    }
}

// Get statistics
$totalRequests = $pdo->query("SELECT COUNT(*) FROM emergency_requests")->fetchColumn();
$criticalRequests = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE priority = 'high' AND status != 'resolved'")->fetchColumn();
$resolvedRequests = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE status = 'resolved'")->fetchColumn();
$pendingRequests = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE status = 'pending'")->fetchColumn();

// Get filters
$statusFilter = $_GET['status'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';

$query = "SELECT e.*, u.name as user_name, u.email, u.age, u.pregnancy_week, u.phone as user_phone
          FROM emergency_requests e 
          JOIN users u ON e.user_id = u.id";

$conditions = [];
$params = [];

if ($statusFilter) {
    $conditions[] = "e.status = ?";
    $params[] = $statusFilter;
}

if ($priorityFilter) {
    $conditions[] = "e.priority = ?";
    $params[] = $priorityFilter;
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY e.priority DESC, e.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();
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
    <style>
        .emergency-header {
            background: linear-gradient(135deg, #DC2626, #EF4444);
            border: 2px solid #EF4444;
            border-radius: var(--radius-xl);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            position: relative;
            overflow: hidden;
        }
        
        .emergency-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .emergency-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }
        
        .emergency-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
            animation: pulse 2s ease-in-out infinite;
        }
        
        .emergency-text h2 {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }
        
        .emergency-text p {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
        }
        
        .priority-critical {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1px solid var(--danger);
            animation: blink 2s ease-in-out infinite;
        }
        
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .priority-medium {
            background: var(--warning-bg);
            color: var(--warning);
            border: 1px solid var(--warning);
        }
        
        .priority-low {
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid var(--success);
        }
    </style>
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
                <a href="doctors.php" class="nav-item">
                    <i class="fas fa-user-md"></i>
                    <span>Doctors</span>
                </a>
                <a href="appointments.php" class="nav-item">
                    <i class="fas fa-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <a href="emergency.php" class="nav-item active">
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
            <!-- Emergency Header -->
            <div class="emergency-header">
                <div class="emergency-header-content">
                    <div class="emergency-icon">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <div class="emergency-text">
                        <h2>Emergency Requests Management</h2>
                        <p>Monitor and respond to emergency maternal care requests - <?php echo $totalRequests; ?> total requests</p>
                    </div>
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
                            <div class="stat-value"><?php echo $totalRequests; ?></div>
                            <div class="stat-label">Total Requests</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span style="color: var(--text-secondary);">All time</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" style="color: var(--danger);"><?php echo $criticalRequests; ?></div>
                            <div class="stat-label">Critical Cases</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #DC2626, #EF4444);">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="stat-trend negative">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Requires immediate attention</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" style="color: var(--success);"><?php echo $resolvedRequests; ?></div>
                            <div class="stat-label">Resolved</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-trend positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>Successfully handled</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value" style="color: var(--warning);"><?php echo $pendingRequests; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                        <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-trend">
                        <span style="color: var(--text-secondary);">Awaiting response</span>
                    </div>
                </div>
            </div>

            <!-- Emergency Requests Table -->
            <div class="content-card">
                <div class="card-header">
                    <h2 class="card-title">All Emergency Requests</h2>
                    <div class="card-actions">
                        <select id="priorityFilter" class="form-select" style="width: auto; height: var(--input-height);" onchange="filterRequests()">
                            <option value="">All Priority</option>
                            <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>Critical</option>
                            <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                        <select id="statusFilter" class="form-select" style="width: auto; height: var(--input-height);" onchange="filterRequests()">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $statusFilter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                        <button onclick="exportData('csv')" class="btn btn-secondary btn-sm">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                    </div>
                </div>

                <?php if (count($requests) > 0): ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Contact</th>
                                <th>Message</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($requests as $req): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--text-secondary);">#<?php echo $req['id']; ?></td>
                                <td>
                                    <div>
                                        <div style="font-weight: 600; color: var(--text-primary);">
                                            <?php echo htmlspecialchars($req['user_name']); ?>
                                        </div>
                                        <div style="font-size: 13px; color: var(--text-secondary);">
                                            <?php echo $req['age'] ?? 'N/A'; ?> years
                                            <?php if ($req['pregnancy_week']): ?>
                                                • Week <?php echo $req['pregnancy_week']; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 13px;">
                                        <div style="color: var(--text-primary); margin-bottom: 4px;">
                                            <i class="fas fa-phone" style="width: 16px;"></i>
                                            <?php echo htmlspecialchars($req['user_phone'] ?? $req['contact'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-secondary); font-size: 13px;">
                                        <?php echo htmlspecialchars($req['message'] ?? 'No message'); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: var(--text-secondary); font-size: 13px;">
                                        <?php echo htmlspecialchars($req['location'] ?? 'Not specified'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge priority-<?php echo $req['priority'] === 'high' ? 'critical' : $req['priority']; ?>">
                                        <?php 
                                        $priorityText = $req['priority'] === 'high' ? 'CRITICAL' : strtoupper($req['priority']);
                                        echo $priorityText;
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="color: var(--text-secondary); font-size: 13px;">
                                        <?php echo date('M d, Y', strtotime($req['created_at'])); ?>
                                        <br>
                                        <?php echo date('h:i A', strtotime($req['created_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $req['status'] === 'resolved' ? 'success' : 
                                            ($req['status'] === 'in_progress' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $req['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button onclick="viewRequest(<?php echo $req['id']; ?>)" class="action-btn view" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                            <input type="hidden" name="action" value="update">
                                            <select name="status" onchange="this.form.submit()" class="action-btn edit" style="width: auto; padding: 8px; font-size: 11px; cursor: pointer;" title="Update Status">
                                                <option value="pending" <?php echo $req['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="in_progress" <?php echo $req['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                <option value="resolved" <?php echo $req['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            </select>
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
                    <div class="empty-icon" style="background: linear-gradient(135deg, #10B981, #059669);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="empty-title">No Emergency Requests</h3>
                    <p class="empty-subtitle">Great! There are no emergency requests at the moment. All patients are safe.</p>
                    <button onclick="window.location.href='emergency.php'" class="btn btn-primary">
                        <i class="fas fa-refresh"></i>
                        Refresh
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function filterRequests() {
            const priority = document.getElementById('priorityFilter').value;
            const status = document.getElementById('statusFilter').value;
            let url = 'emergency.php?';
            if (priority) url += `priority=${priority}&`;
            if (status) url += `status=${status}`;
            window.location.href = url;
        }

        function viewRequest(id) {
            alert('View emergency request details - ID: ' + id);
        }

        function exportData(format) {
            window.location.href = `../actions/export_data.php?type=emergency&format=${format}`;
        }
    </script>
</body>
</html>
