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
    $userId = intval($_POST['user_id'] ?? 0);

    if ($action === 'delete' && $userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ?");
        $stmt->execute([$userId]);
        $appointmentCount = $stmt->fetchColumn();

        if ($appointmentCount > 0) {
            $message = 'Cannot delete patient with existing appointments';
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                $message = 'Patient deleted successfully';
            } else {
                $message = 'Failed to delete patient';
                $messageType = 'error';
            }
        }
    } elseif ($action === 'toggle_status' && $userId) {
        $stmt = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'blocked', 'active') WHERE id = ?");
        if ($stmt->execute([$userId])) {
            $message = 'Patient status updated successfully';
        } else {
            $message = 'Failed to update patient status';
            $messageType = 'error';
        }
    }
}

// ── View single patient ──────────────────────────────────────────────────────
$viewUserId = intval($_GET['view'] ?? 0);
$viewUser   = null;
$healthMetrics = [];
$userAppointments = [];

if ($viewUserId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$viewUserId]);
    $viewUser = $stmt->fetch();

    if ($viewUser) {
        // Health metrics history
        $stmt = $pdo->prepare("SELECT hm.*, a.name as recorded_by_name
                               FROM health_metrics hm
                               LEFT JOIN admins a ON hm.recorded_by = a.id
                               WHERE hm.user_id = ?
                               ORDER BY hm.recorded_at DESC");
        $stmt->execute([$viewUserId]);
        $healthMetrics = $stmt->fetchAll();

        // Recent appointments
        $stmt = $pdo->prepare("SELECT a.*, d.name as doctor_name, d.specialization
                               FROM appointments a
                               JOIN doctors d ON a.doctor_id = d.id
                               WHERE a.user_id = ?
                               ORDER BY a.appointment_date DESC LIMIT 5");
        $stmt->execute([$viewUserId]);
        $userAppointments = $stmt->fetchAll();
    }
}

// Flash messages from redirect
$flashSuccess = $_GET['success'] ?? '';
$flashError   = $_GET['error']   ?? '';

// Get statistics
$totalPatients      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeMothers      = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND pregnancy_week IS NOT NULL")->fetchColumn();
$weeklyAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$highRiskCases      = $pdo->query("SELECT COUNT(*) FROM users WHERE pregnancy_week > 35")->fetchColumn();

// Get all users with additional info
$search       = trim($_GET['search'] ?? '');
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
    $query   .= " AND u.status = ?";
    $params[] = $statusFilter;
}

$query .= " GROUP BY u.id ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<style>
.page-wrap { padding: 2rem; }
.page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
.page-header-left h1 { font-size:1.75rem; font-weight:800; color:#fff; margin:0 0 .25rem; }
.page-header-left p { color:var(--text-muted); font-size:.875rem; margin:0; }
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1.25rem; margin-bottom:2rem; }
@media(max-width:1100px){ .stats-row{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px) { .stats-row{ grid-template-columns:1fr; } }
.stat-tile { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-xl); padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem; transition:all var(--transition-base); }
.stat-tile:hover { transform:translateY(-3px); box-shadow:var(--shadow-glow); border-color:var(--border-glow); }
.stat-tile-icon { width:52px; height:52px; border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center; font-size:1.4rem; flex-shrink:0; }
.stat-tile-body .val { font-size:1.75rem; font-weight:800; color:#fff; line-height:1; }
.stat-tile-body .lbl { font-size:.8rem; color:var(--text-muted); margin-top:.25rem; }
.toolbar { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.toolbar-left { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; flex:1; }
.search-wrap { position:relative; flex:1; min-width:220px; max-width:340px; }
.search-wrap i { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.875rem; }
.search-wrap input { width:100%; padding:.6rem .9rem .6rem 2.4rem; background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-full); color:#fff; font-size:.875rem; transition:all var(--transition-base); }
.search-wrap input:focus { outline:none; border-color:var(--primary-purple); box-shadow:0 0 0 3px rgba(196,167,255,.12); }
.search-wrap input::placeholder { color:var(--text-muted); }
.filter-sel { padding:.6rem 1rem; background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-full); color:#fff; font-size:.875rem; cursor:pointer; transition:all var(--transition-base); }
.filter-sel:focus { outline:none; border-color:var(--primary-purple); }
.filter-sel option { background:#1e1b4b; color:#fff; }
.data-table { width:100%; border-collapse:collapse; }
.data-table thead tr { background:rgba(196,167,255,.08); }
.data-table th { padding:.85rem 1rem; text-align:left; font-size:.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--divider); white-space:nowrap; }
.data-table td { padding:.9rem 1rem; font-size:.875rem; color:var(--text-primary); border-bottom:1px solid var(--divider); vertical-align:middle; }
.data-table tbody tr { transition:background var(--transition-fast); }
.data-table tbody tr:hover { background:rgba(196,167,255,.05); }
.data-table tbody tr:last-child td { border-bottom:none; }
.av { width:38px; height:38px; border-radius:50%; background:var(--gradient-button); display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.8rem; color:#fff; flex-shrink:0; }
.badge { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .75rem; border-radius:var(--radius-full); font-size:.72rem; font-weight:600; white-space:nowrap; }
.badge-success { background:rgba(34,197,94,.15); color:#22C55E; border:1px solid rgba(34,197,94,.3); }
.badge-danger  { background:rgba(239,68,68,.15);  color:#EF4444; border:1px solid rgba(239,68,68,.3); }
.badge-info    { background:rgba(0,209,255,.15);   color:#00D1FF; border:1px solid rgba(0,209,255,.3); }
.badge-warning { background:rgba(250,204,21,.15);  color:#FACC15; border:1px solid rgba(250,204,21,.3); }
.act-btn { width:32px; height:32px; border-radius:var(--radius-md); border:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all var(--transition-fast); font-size:.8rem; text-decoration:none; }
.act-view { background:rgba(59,130,246,.15); color:#3B82F6; }
.act-view:hover { background:rgba(59,130,246,.3); color:#3B82F6; }
.act-toggle { background:rgba(0,209,255,.15); color:#00D1FF; border:none; cursor:pointer; }
.act-toggle:hover { background:rgba(0,209,255,.3); }
.act-del { background:rgba(239,68,68,.15); color:#EF4444; border:none; cursor:pointer; }
.act-del:hover { background:rgba(239,68,68,.3); color:#EF4444; }
.empty-box { text-align:center; padding:4rem 2rem; }
.empty-box .empty-ico { font-size:3.5rem; color:var(--text-muted); margin-bottom:1rem; }
.empty-box h3 { font-size:1.25rem; color:var(--text-primary); margin-bottom:.5rem; }
.empty-box p { color:var(--text-muted); font-size:.875rem; margin-bottom:1.5rem; }
.alert { display:flex; align-items:center; gap:.75rem; padding:1rem 1.25rem; border-radius:var(--radius-lg); margin-bottom:1.5rem; font-size:.875rem; font-weight:500; }
.alert-success { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#22C55E; }
.alert-error   { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#EF4444; }
.table-wrap { overflow-x:auto; border-radius:var(--radius-xl); }
.pagination-bar { display:flex; justify-content:space-between; align-items:center; margin-top:1.25rem; padding-top:1.25rem; border-top:1px solid var(--divider); font-size:.875rem; color:var(--text-muted); }

/* ── Patient Detail View ── */
.detail-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:1.5rem; }
@media(max-width:900px){ .detail-grid-2{ grid-template-columns:1fr; } }
.detail-card { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-xl); padding:1.5rem; }
.detail-card h4 { font-size:.95rem; font-weight:700; color:var(--primary-purple); margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }
.detail-row { display:flex; flex-direction:column; gap:.15rem; margin-bottom:.85rem; }
.detail-row:last-child { margin-bottom:0; }
.detail-lbl { font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; }
.detail-val { font-size:.9rem; color:var(--text-primary); font-weight:500; }

/* Metrics form */
.metrics-form-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
@media(max-width:900px){ .metrics-form-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px){ .metrics-form-grid{ grid-template-columns:1fr; } }
.form-group { display:flex; flex-direction:column; gap:.35rem; }
.form-label { font-size:.75rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; }
.form-input { padding:.6rem .9rem; background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-lg); color:#fff; font-size:.875rem; transition:all var(--transition-base); }
.form-input:focus { outline:none; border-color:var(--primary-purple); box-shadow:0 0 0 3px rgba(196,167,255,.12); }
.form-input::placeholder { color:var(--text-muted); }
.form-textarea { resize:vertical; min-height:70px; }

/* Metrics history table */
.metrics-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.metrics-table th { padding:.65rem .85rem; text-align:left; font-size:.7rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--divider); white-space:nowrap; }
.metrics-table td { padding:.7rem .85rem; color:var(--text-primary); border-bottom:1px solid var(--divider); vertical-align:middle; }
.metrics-table tbody tr:hover { background:rgba(196,167,255,.04); }
.metrics-table tbody tr:last-child td { border-bottom:none; }

/* Metric value chips */
.mv { display:inline-block; padding:.2rem .55rem; border-radius:var(--radius-sm); font-size:.75rem; font-weight:600; }
.mv-normal { background:rgba(34,197,94,.12); color:#22C55E; }
.mv-warn   { background:rgba(250,204,21,.12); color:#FACC15; }
.mv-danger { background:rgba(239,68,68,.12);  color:#EF4444; }
</style>

<div class="page-wrap animate-fade-in">

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php
// Flash from redirect (health metrics actions)
$flashMsgs = [
    'metrics_added'   => ['success', 'Health metrics recorded successfully.'],
    'metrics_updated' => ['success', 'Health metrics updated successfully.'],
    'metrics_deleted' => ['success', 'Health metrics entry deleted.'],
    'add_failed'      => ['error',   'Failed to save health metrics. Please try again.'],
    'update_failed'   => ['error',   'Failed to update health metrics.'],
    'delete_failed'   => ['error',   'Failed to delete health metrics entry.'],
];
if ($flashSuccess && isset($flashMsgs[$flashSuccess])): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo $flashMsgs[$flashSuccess][1]; ?>
</div>
<?php elseif ($flashError && isset($flashMsgs[$flashError])): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i> <?php echo $flashMsgs[$flashError][1]; ?>
</div>
<?php endif; ?>

<?php if ($viewUser): ?>
<!-- ══════════════════════════════════════════════════════════
     PATIENT DETAIL VIEW
     ══════════════════════════════════════════════════════════ -->
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-user-circle" style="color:var(--primary-purple);margin-right:.5rem;"></i>
            <?php echo htmlspecialchars($viewUser['name']); ?>
        </h1>
        <p>Patient ID #<?php echo $viewUser['id']; ?> &bull; Registered <?php echo date('M d, Y', strtotime($viewUser['created_at'])); ?></p>
    </div>
    <a href="users.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Patients</a>
</div>

<!-- Patient Info + Pregnancy -->
<div class="detail-grid-2">
    <div class="detail-card">
        <h4><i class="fas fa-user"></i> Personal Information</h4>
        <div class="detail-row"><span class="detail-lbl">Full Name</span><span class="detail-val"><?php echo htmlspecialchars($viewUser['name']); ?></span></div>
        <div class="detail-row"><span class="detail-lbl">Email</span><span class="detail-val"><?php echo htmlspecialchars($viewUser['email']); ?></span></div>
        <div class="detail-row"><span class="detail-lbl">Phone</span><span class="detail-val"><?php echo htmlspecialchars($viewUser['phone'] ?? '—'); ?></span></div>
        <div class="detail-row"><span class="detail-lbl">Age</span><span class="detail-val"><?php echo $viewUser['age'] ? $viewUser['age'] . ' years' : '—'; ?></span></div>
        <div class="detail-row">
            <span class="detail-lbl">Status</span>
            <span class="detail-val">
                <?php $st = $viewUser['status'] ?? 'active'; ?>
                <span class="badge <?php echo $st === 'active' ? 'badge-success' : 'badge-danger'; ?>"><?php echo ucfirst($st); ?></span>
            </span>
        </div>
    </div>
    <div class="detail-card">
        <h4><i class="fas fa-baby"></i> Pregnancy Information</h4>
        <div class="detail-row"><span class="detail-lbl">Pregnancy Week</span><span class="detail-val"><?php echo $viewUser['pregnancy_week'] ? 'Week ' . $viewUser['pregnancy_week'] : '—'; ?></span></div>
        <div class="detail-row"><span class="detail-lbl">LMP Date</span><span class="detail-val"><?php echo $viewUser['lmp_date'] ? date('M d, Y', strtotime($viewUser['lmp_date'])) : '—'; ?></span></div>
        <div class="detail-row"><span class="detail-lbl">Due Date</span><span class="detail-val"><?php echo $viewUser['due_date'] ? date('M d, Y', strtotime($viewUser['due_date'])) : '—'; ?></span></div>
        <div class="detail-row"><span class="detail-lbl">Total Appointments</span><span class="detail-val"><?php echo count($userAppointments); ?> recent</span></div>
        <div class="detail-row"><span class="detail-lbl">Emergency Requests</span><span class="detail-val">
            <?php
            $emCount = $pdo->prepare("SELECT COUNT(*) FROM emergency_requests WHERE user_id = ?");
            $emCount->execute([$viewUserId]);
            echo $emCount->fetchColumn();
            ?>
        </span></div>
    </div>
</div>

<!-- ── Add Health Metrics Form ─────────────────────────────── -->
<div class="glass-card" style="padding:1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--divider);">
        <h3 style="font-size:1rem;font-weight:700;color:#fff;margin:0;display:flex;align-items:center;gap:.5rem;">
            <i class="fas fa-heartbeat" style="color:#EF4444;"></i> Record Health Metrics
        </h3>
    </div>
    <form method="POST" action="../actions/update_health_metrics.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="user_id" value="<?php echo $viewUser['id']; ?>">
        <div class="metrics-form-grid" style="margin-bottom:1rem;">
            <div class="form-group">
                <label class="form-label">BP Systolic (mmHg)</label>
                <input type="number" name="bp_systolic" class="form-input" placeholder="120" min="60" max="250">
            </div>
            <div class="form-group">
                <label class="form-label">BP Diastolic (mmHg)</label>
                <input type="number" name="bp_diastolic" class="form-input" placeholder="80" min="40" max="150">
            </div>
            <div class="form-group">
                <label class="form-label">Hemoglobin (g/dL)</label>
                <input type="number" name="hemoglobin" class="form-input" placeholder="12.5" step="0.1" min="5" max="20">
            </div>
            <div class="form-group">
                <label class="form-label">Heart Rate (bpm)</label>
                <input type="number" name="heart_rate" class="form-input" placeholder="75" min="40" max="200">
            </div>
            <div class="form-group">
                <label class="form-label">Weight (kg)</label>
                <input type="number" name="weight" class="form-input" placeholder="65.5" step="0.1" min="30" max="200">
            </div>
            <div class="form-group">
                <label class="form-label">Temperature (°C)</label>
                <input type="number" name="temperature" class="form-input" placeholder="36.6" step="0.1" min="35" max="42">
            </div>
            <div class="form-group">
                <label class="form-label">Glucose Level (mg/dL)</label>
                <input type="number" name="glucose" class="form-input" placeholder="90" min="50" max="500">
            </div>
            <div class="form-group" style="grid-column:span 2;">
                <label class="form-label">Clinical Notes</label>
                <textarea name="notes" class="form-input form-textarea" placeholder="Observations, remarks…"></textarea>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end;">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-save"></i> Save Metrics
            </button>
        </div>
    </form>
</div>

<!-- ── Health Metrics History ──────────────────────────────── -->
<div class="glass-card" style="padding:1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;padding-bottom:1rem;border-bottom:1px solid var(--divider);">
        <h3 style="font-size:1rem;font-weight:700;color:#fff;margin:0;display:flex;align-items:center;gap:.5rem;">
            <i class="fas fa-chart-line" style="color:var(--primary-purple);"></i> Health Metrics History
            <span class="badge badge-info" style="margin-left:.5rem;"><?php echo count($healthMetrics); ?> records</span>
        </h3>
    </div>
    <?php if (count($healthMetrics) > 0): ?>
    <div style="overflow-x:auto;">
        <table class="metrics-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Blood Pressure</th>
                    <th>Hemoglobin</th>
                    <th>Heart Rate</th>
                    <th>Weight</th>
                    <th>Temp</th>
                    <th>Glucose</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($healthMetrics as $m): ?>
            <tr>
                <td style="white-space:nowrap;color:var(--text-muted);">
                    <?php echo date('M d, Y', strtotime($m['recorded_at'])); ?><br>
                    <span style="font-size:.7rem;"><?php echo date('h:i A', strtotime($m['recorded_at'])); ?></span>
                </td>
                <td>
                    <?php if ($m['blood_pressure_systolic']): ?>
                    <?php
                    $sys = $m['blood_pressure_systolic'];
                    $dia = $m['blood_pressure_diastolic'];
                    $bpClass = ($sys > 140 || $dia > 90) ? 'mv-danger' : (($sys > 130 || $dia > 85) ? 'mv-warn' : 'mv-normal');
                    ?>
                    <span class="mv <?php echo $bpClass; ?>"><?php echo $sys; ?>/<?php echo $dia; ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <?php if ($m['hemoglobin']): ?>
                    <?php $hbClass = $m['hemoglobin'] < 11 ? 'mv-danger' : ($m['hemoglobin'] < 12 ? 'mv-warn' : 'mv-normal'); ?>
                    <span class="mv <?php echo $hbClass; ?>"><?php echo $m['hemoglobin']; ?></span>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td><?php echo $m['heart_rate'] ? $m['heart_rate'] . ' bpm' : '—'; ?></td>
                <td><?php echo $m['weight'] ? $m['weight'] . ' kg' : '—'; ?></td>
                <td><?php echo $m['temperature'] ? $m['temperature'] . '°C' : '—'; ?></td>
                <td><?php echo $m['glucose_level'] ? $m['glucose_level'] . ' mg/dL' : '—'; ?></td>
                <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-muted);" title="<?php echo htmlspecialchars($m['notes'] ?? ''); ?>">
                    <?php echo htmlspecialchars($m['notes'] ?? '—'); ?>
                </td>
                <td style="font-size:.75rem;color:var(--text-muted);"><?php echo htmlspecialchars($m['recorded_by_name'] ?? 'Admin'); ?></td>
                <td>
                    <form method="POST" action="../actions/update_health_metrics.php" style="display:inline;"
                          onsubmit="return confirm('Delete this metrics entry?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="metric_id" value="<?php echo $m['id']; ?>">
                        <button type="submit" class="act-btn act-del" title="Delete Entry">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:2rem;color:var(--text-muted);">
        <i class="fas fa-heartbeat" style="font-size:2rem;margin-bottom:.75rem;display:block;opacity:.4;"></i>
        No health metrics recorded yet. Use the form above to add the first entry.
    </div>
    <?php endif; ?>
</div>

<!-- ── Recent Appointments ─────────────────────────────────── -->
<?php if (count($userAppointments) > 0): ?>
<div class="glass-card" style="padding:1.5rem;">
    <h3 style="font-size:1rem;font-weight:700;color:#fff;margin:0 0 1.25rem;display:flex;align-items:center;gap:.5rem;">
        <i class="fas fa-calendar-check" style="color:var(--primary-purple);"></i> Recent Appointments
    </h3>
    <div style="overflow-x:auto;">
        <table class="metrics-table">
            <thead>
                <tr><th>Date</th><th>Time</th><th>Doctor</th><th>Specialization</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($userAppointments as $apt): ?>
            <tr>
                <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?></td>
                <td><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?></td>
                <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                <td style="color:var(--text-muted);"><?php echo htmlspecialchars($apt['specialization']); ?></td>
                <td>
                    <?php
                    $sc = ['pending'=>'badge-warning','approved'=>'badge-info','completed'=>'badge-success','cancelled'=>'badge-danger'][$apt['status']] ?? 'badge-warning';
                    ?>
                    <span class="badge <?php echo $sc; ?>"><?php echo ucfirst($apt['status']); ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════
     PATIENTS LIST VIEW
     ══════════════════════════════════════════════════════════ -->
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-users" style="color:var(--primary-purple);margin-right:.5rem;"></i>Patients</h1>
        <p><?php echo number_format($totalPatients); ?> registered mothers and maternal care patients</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="../actions/export_data.php?type=users&format=csv" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        <a href="../actions/export_data.php?type=users&format=json" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-code"></i> JSON
        </a>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(168,85,247,.2);color:#A855F7;">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($totalPatients); ?></div>
            <div class="lbl">Total Patients</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(236,72,153,.2);color:#EC4899;">
            <i class="fas fa-baby"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($activeMothers); ?></div>
            <div class="lbl">Active Mothers</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(59,130,246,.2);color:#3B82F6;">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($weeklyAppointments); ?></div>
            <div class="lbl">Weekly Appointments</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(245,158,11,.2);color:#F59E0B;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($highRiskCases); ?></div>
            <div class="lbl">High Risk Cases</div>
        </div>
    </div>
</div>

<!-- Patients Table Card -->
<div class="glass-card" style="padding:1.5rem;">
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <form method="GET" id="filterForm" style="display:contents;">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search by name or email…"
                           value="<?php echo htmlspecialchars($search); ?>"
                           onkeydown="if(event.key==='Enter'){document.getElementById('filterForm').submit();}">
                </div>
                <select name="status" class="filter-sel" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Status</option>
                    <option value="active"  <?php echo $statusFilter === 'active'  ? 'selected' : ''; ?>>Active</option>
                    <option value="blocked" <?php echo $statusFilter === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                </select>
                <?php if ($search || $statusFilter): ?>
                <a href="users.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <span style="font-size:.8rem;color:var(--text-muted);align-self:center;">
            <?php echo count($users); ?> result<?php echo count($users) !== 1 ? 's' : ''; ?>
        </span>
    </div>

    <?php if (count($users) > 0): ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Patient</th>
                    <th>Age</th>
                    <th>Pregnancy Week</th>
                    <th>Appointments</th>
                    <th>Last Visit</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.8rem;">#<?php echo $user['id']; ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div class="av"><?php echo strtoupper(substr($user['name'], 0, 2)); ?></div>
                        <div>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div style="font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                </td>
                <td><?php echo $user['age'] ? htmlspecialchars($user['age']) . ' yrs' : '—'; ?></td>
                <td>
                    <?php if ($user['pregnancy_week']): ?>
                        <span class="badge badge-info">Week <?php echo intval($user['pregnancy_week']); ?></span>
                    <?php else: ?>
                        <span style="color:var(--text-muted);">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge badge-info"><?php echo intval($user['appointment_count']); ?> appts</span>
                </td>
                <td style="font-size:.8rem;color:var(--text-muted);">
                    <?php echo $user['last_appointment'] ? date('M d, Y', strtotime($user['last_appointment'])) : 'No visits'; ?>
                </td>
                <td>
                    <?php $st = $user['status'] ?? 'active'; ?>
                    <span class="badge <?php echo $st === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo ucfirst($st); ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;align-items:center;">
                        <a href="?view=<?php echo $user['id']; ?>" class="act-btn act-view" title="View Patient">
                            <i class="fas fa-eye"></i>
                        </a>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="toggle_status">
                            <button type="submit" class="act-btn act-toggle" title="Toggle Status">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('<?php echo intval($user['appointment_count']) > 0 ? 'This patient has ' . intval($user['appointment_count']) . ' appointments. Delete anyway?' : 'Delete this patient?'; ?>')">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="act-btn act-del" title="Delete Patient">
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
    <div class="pagination-bar">
        <span>Showing <strong style="color:#fff;"><?php echo count($users); ?></strong> patient<?php echo count($users) !== 1 ? 's' : ''; ?></span>
    </div>
    <?php else: ?>
    <div class="empty-box">
        <div class="empty-ico"><i class="fas fa-users"></i></div>
        <h3>No Patients Found</h3>
        <p>No patients match your current search or filter.</p>
        <?php if ($search || $statusFilter): ?>
        <a href="users.php" class="btn btn-primary btn-sm"><i class="fas fa-redo"></i> Clear Filters</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div><!-- /glass-card -->

<?php endif; // end else (list view) ?>
</div><!-- /page-wrap -->

<?php include '../includes/footer.php'; ?>
