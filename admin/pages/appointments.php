<?php
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireLogin();

$pageTitle = 'Appointments Management';
$message = '';
$messageType = 'success';

// Handle view action
$viewAppointmentId = intval($_GET['view'] ?? 0);
$viewAppointment = null;
if ($viewAppointmentId) {
    $stmt = $pdo->prepare("SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
                          u.age, u.pregnancy_week, d.name as doctor_name, d.specialization, d.contact as doctor_contact
                          FROM appointments a
                          JOIN users u ON a.user_id = u.id
                          JOIN doctors d ON a.doctor_id = d.id
                          WHERE a.id = ?");
    $stmt->execute([$viewAppointmentId]);
    $viewAppointment = $stmt->fetch();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id     = intval($_POST['appointment_id']);
    $status = $_POST['status'];
    $allowed = ['pending','approved','completed','cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $message = 'Appointment status updated successfully.';
        } else {
            $message = 'Failed to update appointment status.';
            $messageType = 'error';
        }
    }
    header("Location: appointments.php" . ($viewAppointmentId ? "?view=$id" : ""));
    exit;
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$dateFilter   = $_GET['date']   ?? '';
$searchQuery  = trim($_GET['search'] ?? '');

// Build query
$query  = "SELECT a.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
           d.name as doctor_name, d.specialization
           FROM appointments a
           JOIN users u ON a.user_id = u.id
           JOIN doctors d ON a.doctor_id = d.id
           WHERE 1=1";
$params = [];

if ($statusFilter) {
    $query   .= " AND a.status = ?";
    $params[] = $statusFilter;
}
if ($dateFilter === 'today') {
    $query .= " AND DATE(a.appointment_date) = CURDATE()";
} elseif ($dateFilter === 'upcoming') {
    $query .= " AND a.appointment_date >= CURDATE()";
} elseif ($dateFilter === 'past') {
    $query .= " AND a.appointment_date < CURDATE()";
}
if ($searchQuery) {
    $query   .= " AND (u.name LIKE ? OR d.name LIKE ? OR u.email LIKE ?)";
    $sp       = "%$searchQuery%";
    $params[] = $sp; $params[] = $sp; $params[] = $sp;
}
$query .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Stats
$totalAppointments     = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$todayAppointments     = $pdo->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetchColumn();
$upcomingAppointments  = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date >= CURDATE() AND status != 'cancelled'")->fetchColumn();
$completedAppointments = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'")->fetchColumn();
$statusCounts          = $pdo->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<style>
/* ── Page-level overrides ─────────────────────────────────────── */
.page-wrap        { padding: 2rem; }
.page-header      { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
.page-header-left h1 { font-size:1.75rem; font-weight:800; color:#fff; margin:0 0 .25rem; }
.page-header-left p  { color:var(--text-muted); font-size:.875rem; margin:0; }

/* Stats row */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:1.25rem; margin-bottom:2rem; }
@media(max-width:1100px){ .stats-row{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px) { .stats-row{ grid-template-columns:1fr; } }

.stat-tile {
    background:var(--glass-bg);
    border:1px solid var(--glass-border);
    border-radius:var(--radius-xl);
    padding:1.25rem 1.5rem;
    display:flex; align-items:center; gap:1rem;
    transition:all var(--transition-base);
}
.stat-tile:hover { transform:translateY(-3px); box-shadow:var(--shadow-glow); border-color:var(--border-glow); }
.stat-tile-icon {
    width:52px; height:52px; border-radius:var(--radius-lg);
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; flex-shrink:0;
}
.stat-tile-body .val { font-size:1.75rem; font-weight:800; color:#fff; line-height:1; }
.stat-tile-body .lbl { font-size:.8rem; color:var(--text-muted); margin-top:.25rem; }

/* Toolbar */
.toolbar {
    display:flex; flex-wrap:wrap; gap:.75rem;
    align-items:center; justify-content:space-between;
    margin-bottom:1.5rem;
}
.toolbar-left  { display:flex; flex-wrap:wrap; gap:.75rem; align-items:center; flex:1; }
.toolbar-right { display:flex; gap:.5rem; }

.search-wrap {
    position:relative; flex:1; min-width:220px; max-width:340px;
}
.search-wrap i { position:absolute; left:.9rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:.875rem; }
.search-wrap input {
    width:100%; padding:.6rem .9rem .6rem 2.4rem;
    background:var(--glass-bg); border:1px solid var(--glass-border);
    border-radius:var(--radius-full); color:#fff; font-size:.875rem;
    transition:all var(--transition-base);
}
.search-wrap input:focus { outline:none; border-color:var(--primary-purple); box-shadow:0 0 0 3px rgba(196,167,255,.12); }
.search-wrap input::placeholder { color:var(--text-muted); }

.filter-sel {
    padding:.6rem 1rem; background:var(--glass-bg);
    border:1px solid var(--glass-border); border-radius:var(--radius-full);
    color:#fff; font-size:.875rem; cursor:pointer;
    transition:all var(--transition-base);
}
.filter-sel:focus { outline:none; border-color:var(--primary-purple); }
.filter-sel option { background:#1e1b4b; color:#fff; }
</style>

<style>
/* Table */
.apt-table-wrap { overflow-x:auto; border-radius:var(--radius-xl); }
.apt-table { width:100%; border-collapse:collapse; }
.apt-table thead tr { background:rgba(196,167,255,.08); }
.apt-table th {
    padding:.85rem 1rem; text-align:left;
    font-size:.75rem; font-weight:600; color:var(--text-muted);
    text-transform:uppercase; letter-spacing:.06em;
    border-bottom:1px solid var(--divider); white-space:nowrap;
}
.apt-table td {
    padding:.9rem 1rem; font-size:.875rem; color:var(--text-primary);
    border-bottom:1px solid var(--divider); vertical-align:middle;
}
.apt-table tbody tr { transition:background var(--transition-fast); }
.apt-table tbody tr:hover { background:rgba(196,167,255,.05); }
.apt-table tbody tr:last-child td { border-bottom:none; }

/* Avatar */
.av { width:38px; height:38px; border-radius:50%; background:var(--gradient-button);
      display:flex; align-items:center; justify-content:center;
      font-weight:700; font-size:.8rem; color:#fff; flex-shrink:0; }

/* Badges */
.badge {
    display:inline-flex; align-items:center; gap:.3rem;
    padding:.3rem .75rem; border-radius:var(--radius-full);
    font-size:.72rem; font-weight:600; white-space:nowrap;
}
.badge-pending  { background:rgba(250,204,21,.15);  color:#FACC15; border:1px solid rgba(250,204,21,.3); }
.badge-approved { background:rgba(0,209,255,.15);   color:#00D1FF; border:1px solid rgba(0,209,255,.3); }
.badge-completed{ background:rgba(34,197,94,.15);   color:#22C55E; border:1px solid rgba(34,197,94,.3); }
.badge-cancelled{ background:rgba(239,68,68,.15);   color:#EF4444; border:1px solid rgba(239,68,68,.3); }

/* Status select in table */
.status-sel {
    padding:.35rem .7rem; font-size:.78rem;
    background:var(--glass-bg); border:1px solid var(--glass-border);
    border-radius:var(--radius-full); color:#fff; cursor:pointer;
}
.status-sel:focus { outline:none; border-color:var(--primary-purple); }
.status-sel option { background:#1e1b4b; }

/* Action buttons */
.act-btn {
    width:32px; height:32px; border-radius:var(--radius-md);
    border:none; display:inline-flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all var(--transition-fast); font-size:.8rem; text-decoration:none;
}
.act-view { background:rgba(59,130,246,.15); color:#3B82F6; }
.act-view:hover { background:rgba(59,130,246,.3); color:#3B82F6; }

/* Empty state */
.empty-box { text-align:center; padding:4rem 2rem; }
.empty-box .empty-ico { font-size:3.5rem; color:var(--text-muted); margin-bottom:1rem; }
.empty-box h3 { font-size:1.25rem; color:var(--text-primary); margin-bottom:.5rem; }
.empty-box p  { color:var(--text-muted); font-size:.875rem; margin-bottom:1.5rem; }

/* Alert */
.alert {
    display:flex; align-items:center; gap:.75rem;
    padding:1rem 1.25rem; border-radius:var(--radius-lg);
    margin-bottom:1.5rem; font-size:.875rem; font-weight:500;
}
.alert-success { background:rgba(34,197,94,.12); border:1px solid rgba(34,197,94,.3); color:#22C55E; }
.alert-error   { background:rgba(239,68,68,.12); border:1px solid rgba(239,68,68,.3); color:#EF4444; }

/* Detail view */
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
@media(max-width:768px){ .detail-grid{ grid-template-columns:1fr; } }
.detail-card { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:var(--radius-xl); padding:1.5rem; }
.detail-card h4 { font-size:1rem; font-weight:700; color:var(--primary-purple); margin-bottom:1.25rem; display:flex; align-items:center; gap:.5rem; }
.detail-row { display:flex; flex-direction:column; gap:.2rem; margin-bottom:.9rem; }
.detail-row:last-child { margin-bottom:0; }
.detail-lbl { font-size:.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; }
.detail-val { font-size:.9rem; color:var(--text-primary); font-weight:500; }
</style>

<div class="page-wrap animate-fade-in">

<?php if (session_status() === PHP_SESSION_NONE) session_start();
      $flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); ?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- ── Page Header ──────────────────────────────────────────── -->
<div class="page-header">
    <div class="page-header-left">
        <?php if ($viewAppointment): ?>
            <h1><i class="fas fa-calendar-alt" style="color:var(--primary-purple);margin-right:.5rem;"></i>Appointment #<?php echo $viewAppointment['id']; ?></h1>
            <p>Full appointment details and status management</p>
        <?php else: ?>
            <h1><i class="fas fa-calendar-check" style="color:var(--primary-purple);margin-right:.5rem;"></i>Appointments</h1>
            <p><?php echo number_format($totalAppointments); ?> total appointments in the system</p>
        <?php endif; ?>
    </div>
    <?php if ($viewAppointment): ?>
    <a href="appointments.php" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
    <?php else: ?>
    <div style="display:flex;gap:.5rem;">
        <a href="../actions/export_data.php?type=appointments&format=csv" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        <a href="../actions/export_data.php?type=appointments&format=json" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-code"></i> JSON
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($viewAppointment): ?>
<!-- ══════════════════════════════════════════════════════════════
     DETAIL VIEW
     ══════════════════════════════════════════════════════════════ -->
<div class="detail-grid">

    <!-- Patient card -->
    <div class="detail-card">
        <h4><i class="fas fa-user-circle"></i> Patient Information</h4>
        <div class="detail-row">
            <span class="detail-lbl">Full Name</span>
            <span class="detail-val"><?php echo htmlspecialchars($viewAppointment['user_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Email</span>
            <span class="detail-val"><?php echo htmlspecialchars($viewAppointment['user_email']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Phone</span>
            <span class="detail-val"><?php echo htmlspecialchars($viewAppointment['user_phone'] ?? '—'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Age</span>
            <span class="detail-val"><?php echo $viewAppointment['age'] ?? '—'; ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Pregnancy Week</span>
            <span class="detail-val"><?php echo $viewAppointment['pregnancy_week'] ? 'Week '.$viewAppointment['pregnancy_week'] : '—'; ?></span>
        </div>
    </div>

    <!-- Appointment card -->
    <div class="detail-card">
        <h4><i class="fas fa-stethoscope"></i> Appointment Details</h4>
        <div class="detail-row">
            <span class="detail-lbl">Doctor</span>
            <span class="detail-val"><?php echo htmlspecialchars($viewAppointment['doctor_name']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Specialization</span>
            <span class="detail-val"><?php echo htmlspecialchars($viewAppointment['specialization']); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Date</span>
            <span class="detail-val"><?php echo date('l, F j, Y', strtotime($viewAppointment['appointment_date'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Time</span>
            <span class="detail-val"><?php echo date('h:i A', strtotime($viewAppointment['appointment_time'])); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Current Status</span>
            <span class="detail-val">
                <?php
                $s = $viewAppointment['status'];
                $cls = ['pending'=>'badge-pending','approved'=>'badge-approved','completed'=>'badge-completed','cancelled'=>'badge-cancelled'][$s] ?? 'badge-pending';
                ?>
                <span class="badge <?php echo $cls; ?>"><?php echo ucfirst($s); ?></span>
            </span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Notes</span>
            <span class="detail-val"><?php echo htmlspecialchars($viewAppointment['notes'] ?? '—'); ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-lbl">Booked On</span>
            <span class="detail-val"><?php echo date('M d, Y h:i A', strtotime($viewAppointment['created_at'])); ?></span>
        </div>

        <!-- Status update form -->
        <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--divider);">
            <form method="POST" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                <input type="hidden" name="appointment_id" value="<?php echo $viewAppointment['id']; ?>">
                <input type="hidden" name="action" value="update">
                <label style="font-size:.8rem;color:var(--text-muted);">Update Status:</label>
                <select name="status" class="status-sel">
                    <?php foreach(['pending','approved','completed','cancelled'] as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php echo $viewAppointment['status']===$opt?'selected':''; ?>>
                        <?php echo ucfirst($opt); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Save
                </button>
            </form>
        </div>
    </div>

</div><!-- /detail-grid -->

<?php else: ?>

<!-- ══════════════════════════════════════════════════════════════
     LIST VIEW — Stats
     ══════════════════════════════════════════════════════════════ -->
<div class="stats-row">
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(168,85,247,.2);color:#A855F7;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($totalAppointments); ?></div>
            <div class="lbl">Total Appointments</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(59,130,246,.2);color:#3B82F6;">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($todayAppointments); ?></div>
            <div class="lbl">Today</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(245,158,11,.2);color:#F59E0B;">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($upcomingAppointments); ?></div>
            <div class="lbl">Upcoming</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(16,185,129,.2);color:#10B981;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($completedAppointments); ?></div>
            <div class="lbl">Completed</div>
        </div>
    </div>
</div>

<!-- ── Main Card ─────────────────────────────────────────────── -->
<div class="glass-card" style="padding:1.5rem;">

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <form method="GET" id="filterForm" style="display:contents;">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search patient, doctor, email…"
                           value="<?php echo htmlspecialchars($searchQuery); ?>"
                           onchange="document.getElementById('filterForm').submit()">
                </div>

                <select name="status" class="filter-sel" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Status (<?php echo $totalAppointments; ?>)</option>
                    <?php foreach(['pending','approved','completed','cancelled'] as $opt): ?>
                    <option value="<?php echo $opt; ?>" <?php echo $statusFilter===$opt?'selected':''; ?>>
                        <?php echo ucfirst($opt); ?> (<?php echo $statusCounts[$opt] ?? 0; ?>)
                    </option>
                    <?php endforeach; ?>
                </select>

                <select name="date" class="filter-sel" onchange="document.getElementById('filterForm').submit()">
                    <option value="">All Dates</option>
                    <option value="today"    <?php echo $dateFilter==='today'   ?'selected':''; ?>>Today</option>
                    <option value="upcoming" <?php echo $dateFilter==='upcoming'?'selected':''; ?>>Upcoming</option>
                    <option value="past"     <?php echo $dateFilter==='past'    ?'selected':''; ?>>Past</option>
                </select>

                <?php if ($searchQuery || $statusFilter || $dateFilter): ?>
                <a href="appointments.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-times"></i> Clear
                </a>
                <?php endif; ?>
            </form>
        </div>
        <div class="toolbar-right">
            <span style="font-size:.8rem;color:var(--text-muted);align-self:center;">
                <?php echo count($appointments); ?> result<?php echo count($appointments)!==1?'s':''; ?>
            </span>
        </div>
    </div>

    <!-- Table -->
    <?php if (count($appointments) > 0): ?>
    <div class="apt-table-wrap">
        <table class="apt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date &amp; Time</th>
                    <th>Status</th>
                    <th>Update</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $apt):
                $s   = $apt['status'];
                $cls = ['pending'=>'badge-pending','approved'=>'badge-approved','completed'=>'badge-completed','cancelled'=>'badge-cancelled'][$s] ?? 'badge-pending';
                $isToday    = date('Y-m-d') === $apt['appointment_date'];
                $isUpcoming = $apt['appointment_date'] > date('Y-m-d');
            ?>
            <tr>
                <td style="color:var(--text-muted);font-size:.8rem;">#<?php echo $apt['id']; ?></td>

                <td>
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div class="av"><?php echo strtoupper(substr($apt['user_name'],0,2)); ?></div>
                        <div>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($apt['user_name']); ?></div>
                            <div style="font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($apt['user_email']); ?></div>
                        </div>
                    </div>
                </td>

                <td>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($apt['doctor_name']); ?></div>
                    <div style="font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($apt['specialization']); ?></div>
                </td>

                <td>
                    <div style="display:flex;align-items:center;gap:.4rem;font-weight:600;">
                        <?php if ($isToday): ?>
                            <span class="badge badge-approved" style="font-size:.68rem;padding:.2rem .5rem;">Today</span>
                        <?php elseif ($isUpcoming): ?>
                            <span class="badge badge-pending" style="font-size:.68rem;padding:.2rem .5rem;">Upcoming</span>
                        <?php endif; ?>
                        <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?>
                    </div>
                    <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;">
                        <i class="fas fa-clock" style="margin-right:.3rem;"></i><?php echo date('h:i A', strtotime($apt['appointment_time'])); ?>
                    </div>
                </td>

                <td><span class="badge <?php echo $cls; ?>"><?php echo ucfirst($s); ?></span></td>

                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="appointment_id" value="<?php echo $apt['id']; ?>">
                        <input type="hidden" name="action" value="update">
                        <select name="status" class="status-sel"
                                onchange="if(confirm('Update status to '+this.options[this.selectedIndex].text+'?')) this.form.submit(); else this.value='<?php echo $s; ?>';">
                            <?php foreach(['pending','approved','completed','cancelled'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php echo $s===$opt?'selected':''; ?>>
                                <?php echo ucfirst($opt); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>

                <td>
                    <a href="?view=<?php echo $apt['id']; ?>" class="act-btn act-view" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="empty-box">
        <div class="empty-ico"><i class="fas fa-calendar-times"></i></div>
        <h3>No Appointments Found</h3>
        <p>No appointments match your current filters.</p>
        <?php if ($searchQuery || $statusFilter || $dateFilter): ?>
        <a href="appointments.php" class="btn btn-primary btn-sm">
            <i class="fas fa-redo"></i> Clear Filters
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /glass-card -->

<?php endif; ?>
</div><!-- /page-wrap -->

<?php include '../includes/footer.php'; ?>
