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
    $id     = intval($_POST['request_id']);
    $status = $_POST['status'];

    $query = "UPDATE emergency_requests SET status = ?";
    if ($status === 'resolved') {
        $query .= ", resolved_at = NOW()";
    }
    $query .= " WHERE id = ?";

    $stmt = $pdo->prepare($query);
    if ($stmt->execute([$status, $id])) {
        $message = 'Emergency request updated successfully';
    } else {
        $message = 'Failed to update emergency request';
        $messageType = 'error';
    }
}

// Get statistics
$totalRequests    = $pdo->query("SELECT COUNT(*) FROM emergency_requests")->fetchColumn();
$criticalRequests = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE priority = 'high' AND status != 'resolved'")->fetchColumn();
$resolvedRequests = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE status = 'resolved'")->fetchColumn();
$pendingRequests  = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE status = 'pending'")->fetchColumn();
$inProgressCount  = $pdo->query("SELECT COUNT(*) FROM emergency_requests WHERE status = 'in_progress'")->fetchColumn();

// Get filters
$statusFilter   = $_GET['status']   ?? '';
$priorityFilter = $_GET['priority'] ?? '';

$query = "SELECT e.*, u.name as user_name, u.email, u.age, u.pregnancy_week, u.phone as user_phone
          FROM emergency_requests e
          JOIN users u ON e.user_id = u.id";

$conditions = [];
$params     = [];

if ($statusFilter) {
    $conditions[] = "e.status = ?";
    $params[]     = $statusFilter;
}
if ($priorityFilter) {
    $conditions[] = "e.priority = ?";
    $params[]     = $priorityFilter;
}
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY e.priority DESC, e.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$requests = $stmt->fetchAll();

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
.badge { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .75rem; border-radius:var(--radius-full); font-size:.72rem; font-weight:600; white-space:nowrap; }
.badge-success { background:rgba(34,197,94,.15); color:#22C55E; border:1px solid rgba(34,197,94,.3); }
.badge-warning { background:rgba(250,204,21,.15); color:#FACC15; border:1px solid rgba(250,204,21,.3); }
.badge-danger  { background:rgba(239,68,68,.15);  color:#EF4444; border:1px solid rgba(239,68,68,.3); }
.badge-info    { background:rgba(0,209,255,.15);   color:#00D1FF; border:1px solid rgba(0,209,255,.3); }
/* Priority badges */
.badge-priority-high   { background:rgba(239,68,68,.2); color:#EF4444; border:1px solid rgba(239,68,68,.4); animation:blink-badge 1.8s ease-in-out infinite; }
.badge-priority-medium { background:rgba(250,204,21,.15); color:#FACC15; border:1px solid rgba(250,204,21,.3); }
.badge-priority-low    { background:rgba(34,197,94,.15); color:#22C55E; border:1px solid rgba(34,197,94,.3); }
@keyframes blink-badge { 0%,100%{opacity:1;} 50%{opacity:.55;} }
/* Status select */
.status-sel { padding:.35rem .7rem; font-size:.78rem; background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-full); color:#fff; cursor:pointer; }
.status-sel:focus { outline:none; border-color:var(--primary-purple); }
.status-sel option { background:#1e1b4b; }
.empty-box { text-align:center; padding:4rem 2rem; }
.empty-box .empty-ico { font-size:3.5rem; color:var(--text-muted); margin-bottom:1rem; }
.empty-box h3 { font-size:1.25rem; color:var(--text-primary); margin-bottom:.5rem; }
.empty-box p { color:var(--text-muted); font-size:.875rem; margin-bottom:1.5rem; }
.alert { display:flex; align-items:center; gap:.75rem; padding:1rem 1.25rem; border-radius:var(--radius-lg); margin-bottom:1.5rem; font-size:.875rem; font-weight:500; }
.alert-success { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#22C55E; }
.alert-error   { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#EF4444; }
.table-wrap { overflow-x:auto; border-radius:var(--radius-xl); }
/* Critical alert banner */
.critical-banner {
    display:flex; align-items:center; gap:1rem;
    background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.35);
    border-radius:var(--radius-xl); padding:1rem 1.5rem; margin-bottom:1.5rem;
}
.critical-banner .pulse-icon {
    width:44px; height:44px; border-radius:50%;
    background:rgba(239,68,68,.2); color:#EF4444;
    display:flex; align-items:center; justify-content:center;
    font-size:1.2rem; flex-shrink:0;
    animation:pulse-ring 2s ease-in-out infinite;
}
@keyframes pulse-ring {
    0%,100%{ box-shadow:0 0 0 0 rgba(239,68,68,.5); }
    50%{ box-shadow:0 0 0 10px rgba(239,68,68,0); }
}
.critical-banner .banner-text strong { color:#EF4444; font-size:1rem; }
.critical-banner .banner-text p { color:var(--text-muted); font-size:.8rem; margin:.15rem 0 0; }
</style>

<div class="page-wrap animate-fade-in">

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($criticalRequests > 0): ?>
<!-- Critical Alert Banner -->
<div class="critical-banner">
    <div class="pulse-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <div class="banner-text">
        <strong><?php echo number_format($criticalRequests); ?> Critical &amp; Pending Emergency Request<?php echo $criticalRequests !== 1 ? 's' : ''; ?></strong>
        <p>Immediate attention required — high-priority cases are awaiting response.</p>
    </div>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-exclamation-triangle" style="color:#EF4444;margin-right:.5rem;"></i>Emergency Requests</h1>
        <p>Monitor and respond to emergency maternal care requests — <?php echo number_format($totalRequests); ?> total</p>
    </div>
    <a href="../actions/export_data.php?type=emergency&format=csv" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-csv"></i> Export
    </a>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(59,130,246,.2);color:#3B82F6;">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($totalRequests); ?></div>
            <div class="lbl">Total Requests</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(239,68,68,.2);color:#EF4444;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val" style="color:#EF4444;"><?php echo number_format($criticalRequests); ?></div>
            <div class="lbl">Critical / Pending</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(245,158,11,.2);color:#F59E0B;">
            <i class="fas fa-spinner"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val" style="color:#F59E0B;"><?php echo number_format($inProgressCount); ?></div>
            <div class="lbl">In Progress</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(34,197,94,.2);color:#22C55E;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val" style="color:#22C55E;"><?php echo number_format($resolvedRequests); ?></div>
            <div class="lbl">Resolved</div>
        </div>
    </div>
</div>

<!-- Emergency Table Card -->
<div class="glass-card" style="padding:1.5rem;">
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <form method="GET" id="filterForm" style="display:contents;">
                <select name="priority" class="filter-sel" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Priority</option>
                    <option value="high"   <?php echo $priorityFilter === 'high'   ? 'selected' : ''; ?>>Critical (High)</option>
                    <option value="medium" <?php echo $priorityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="low"    <?php echo $priorityFilter === 'low'    ? 'selected' : ''; ?>>Low</option>
                </select>
                <select name="status" class="filter-sel" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Status</option>
                    <option value="pending"     <?php echo $statusFilter === 'pending'     ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved"    <?php echo $statusFilter === 'resolved'    ? 'selected' : ''; ?>>Resolved</option>
                </select>
                <?php if ($statusFilter || $priorityFilter): ?>
                <a href="emergency.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
                <?php endif; ?>
            </form>
        </div>
        <span style="font-size:.8rem;color:var(--text-muted);align-self:center;">
            <?php echo count($requests); ?> result<?php echo count($requests) !== 1 ? 's' : ''; ?>
        </span>
    </div>

    <?php if (count($requests) > 0): ?>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Patient</th>
                    <th>Contact</th>
                    <th>Message</th>
                    <th>Location</th>
                    <th>Priority</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($requests as $req): ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.8rem;">#<?php echo $req['id']; ?></td>
                <td>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($req['user_name']); ?></div>
                    <div style="font-size:.78rem;color:var(--text-muted);">
                        <?php echo $req['age'] ? intval($req['age']) . ' yrs' : ''; ?>
                        <?php if ($req['pregnancy_week']): ?>
                            &bull; Week <?php echo intval($req['pregnancy_week']); ?>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="font-size:.8rem;">
                    <i class="fas fa-phone" style="color:var(--text-muted);margin-right:.3rem;"></i>
                    <?php echo htmlspecialchars($req['user_phone'] ?? $req['contact'] ?? '—'); ?>
                </td>
                <td style="max-width:180px;">
                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.8rem;color:var(--text-muted);" title="<?php echo htmlspecialchars($req['message'] ?? ''); ?>">
                        <?php echo htmlspecialchars($req['message'] ?? 'No message'); ?>
                    </div>
                </td>
                <td style="font-size:.8rem;color:var(--text-muted);">
                    <?php echo htmlspecialchars($req['location'] ?? '—'); ?>
                </td>
                <td>
                    <?php
                    $p = $req['priority'] ?? 'low';
                    $pClass = $p === 'high' ? 'badge-priority-high' : ($p === 'medium' ? 'badge-priority-medium' : 'badge-priority-low');
                    $pLabel = $p === 'high' ? 'CRITICAL' : strtoupper($p);
                    ?>
                    <span class="badge <?php echo $pClass; ?>">
                        <?php if ($p === 'high'): ?><i class="fas fa-bolt"></i><?php endif; ?>
                        <?php echo $pLabel; ?>
                    </span>
                </td>
                <td style="font-size:.78rem;color:var(--text-muted);">
                    <?php echo date('M d, Y', strtotime($req['created_at'])); ?><br>
                    <?php echo date('h:i A', strtotime($req['created_at'])); ?>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                        <input type="hidden" name="action" value="update">
                        <select name="status" class="status-sel"
                                onchange="if(confirm('Update status to '+this.options[this.selectedIndex].text+'?')) this.form.submit(); else this.value='<?php echo htmlspecialchars($req['status']); ?>';">
                            <option value="pending"     <?php echo $req['status'] === 'pending'     ? 'selected' : ''; ?>>Pending</option>
                            <option value="in_progress" <?php echo $req['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved"    <?php echo $req['status'] === 'resolved'    ? 'selected' : ''; ?>>Resolved</option>
                        </select>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-box">
        <div class="empty-ico" style="color:#22C55E;"><i class="fas fa-check-circle"></i></div>
        <h3>No Emergency Requests</h3>
        <p>All clear — there are no emergency requests matching your filters right now.</p>
        <?php if ($statusFilter || $priorityFilter): ?>
        <a href="emergency.php" class="btn btn-primary btn-sm"><i class="fas fa-redo"></i> Clear Filters</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div><!-- /glass-card -->

</div><!-- /page-wrap -->

<?php include '../includes/footer.php'; ?>
