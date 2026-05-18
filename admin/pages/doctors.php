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
        $name           = trim($_POST['name']);
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization']);
        $experience     = intval($_POST['experience']);
        $qualification  = trim($_POST['qualification'] ?? '');
        $availability   = trim($_POST['availability']);

        if ($name && $specialization && $availability) {
            try {
                $stmt = $pdo->prepare("INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $phone, $specialization, $experience, $qualification, $availability])) {
                    $message = 'Doctor added successfully';
                    $showAddForm = false;
                } else {
                    $message = 'Failed to add doctor';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Name, specialization, and availability are required';
            $messageType = 'error';
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['doctor_id']);
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
            $stmt->execute([$id]);
            $appointmentCount = $stmt->fetchColumn();

            if ($appointmentCount > 0) {
                $message = 'Cannot delete doctor with existing appointments';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'Doctor deleted successfully';
                } else {
                    $message = 'Failed to delete doctor';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Get statistics
$totalDoctors      = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$activeDoctors     = $pdo->query("SELECT COUNT(*) FROM doctors WHERE is_active = 1")->fetchColumn();
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$specializations   = $pdo->query("SELECT COUNT(DISTINCT specialization) FROM doctors")->fetchColumn();

// Get all doctors with appointment counts
$doctors = $pdo->query("SELECT d.*, COUNT(a.id) as appointment_count
                        FROM doctors d
                        LEFT JOIN appointments a ON d.id = a.doctor_id
                        GROUP BY d.id
                        ORDER BY d.created_at DESC")->fetchAll();

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
.act-btn { width:32px; height:32px; border-radius:var(--radius-md); border:none; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; transition:all var(--transition-fast); font-size:.8rem; text-decoration:none; }
.act-view { background:rgba(59,130,246,.15); color:#3B82F6; }
.act-view:hover { background:rgba(59,130,246,.3); color:#3B82F6; }
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

/* Add Doctor form */
.form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
@media(max-width:768px){ .form-grid-2{ grid-template-columns:1fr; } }
.form-group { display:flex; flex-direction:column; gap:.4rem; }
.form-label { font-size:.8rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.05em; }
.form-input, .form-select {
    padding:.65rem 1rem; background:var(--glass-bg); border:1px solid var(--glass-border);
    border-radius:var(--radius-lg); color:#fff; font-size:.875rem;
    transition:all var(--transition-base);
}
.form-input:focus, .form-select:focus { outline:none; border-color:var(--primary-purple); box-shadow:0 0 0 3px rgba(196,167,255,.12); }
.form-input::placeholder { color:var(--text-muted); }
.form-select option { background:#1e1b4b; color:#fff; }
</style>

<div class="page-wrap animate-fade-in">

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'error'; ?>">
    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
    <div class="page-header-left">
        <h1><i class="fas fa-user-doctor" style="color:var(--primary-purple);margin-right:.5rem;"></i>Doctors</h1>
        <p><?php echo number_format($totalDoctors); ?> healthcare providers in the system</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="../actions/export_data.php?type=doctors&format=csv" class="btn btn-secondary btn-sm">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        <?php if (!$showAddForm): ?>
        <a href="?action=add" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Doctor
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(168,85,247,.2);color:#A855F7;">
            <i class="fas fa-user-doctor"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($totalDoctors); ?></div>
            <div class="lbl">Total Doctors</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(34,197,94,.2);color:#22C55E;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($activeDoctors); ?></div>
            <div class="lbl">Active Doctors</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(59,130,246,.2);color:#3B82F6;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($totalAppointments); ?></div>
            <div class="lbl">Total Appointments</div>
        </div>
    </div>
    <div class="stat-tile">
        <div class="stat-tile-icon" style="background:rgba(245,158,11,.2);color:#F59E0B;">
            <i class="fas fa-stethoscope"></i>
        </div>
        <div class="stat-tile-body">
            <div class="val"><?php echo number_format($specializations); ?></div>
            <div class="lbl">Specializations</div>
        </div>
    </div>
</div>

<?php if ($showAddForm): ?>
<!-- Add Doctor Form -->
<div class="glass-card" style="padding:1.75rem;margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid var(--divider);">
        <h2 style="font-size:1.1rem;font-weight:700;color:#fff;margin:0;display:flex;align-items:center;gap:.5rem;">
            <i class="fas fa-user-plus" style="color:var(--primary-purple);"></i> Add New Doctor
        </h2>
        <a href="doctors.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Cancel</a>
    </div>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-grid-2" style="margin-bottom:1.25rem;">
            <div class="form-group">
                <label class="form-label"><i class="fas fa-user"></i> Full Name *</label>
                <input type="text" name="name" class="form-input" placeholder="Dr. Jane Smith" required>
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="doctor@aarunya.com">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-phone"></i> Phone Number</label>
                <input type="text" name="phone" class="form-input" placeholder="+91 98765 43210">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-briefcase"></i> Experience (Years)</label>
                <input type="number" name="experience" class="form-input" placeholder="10" min="0" max="50">
            </div>
            <div class="form-group">
                <label class="form-label"><i class="fas fa-stethoscope"></i> Specialization *</label>
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
                <label class="form-label"><i class="fas fa-graduation-cap"></i> Qualification</label>
                <input type="text" name="qualification" class="form-input" placeholder="MBBS, MD (OB/GYN)">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:1.5rem;">
            <label class="form-label"><i class="fas fa-clock"></i> Availability *</label>
            <input type="text" name="availability" class="form-input" placeholder="Mon-Fri 9AM-5PM" required>
        </div>
        <div style="display:flex;gap:.75rem;justify-content:flex-end;">
            <a href="doctors.php" class="btn btn-secondary btn-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add Doctor
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Doctors Table Card -->
<div class="glass-card" style="padding:1.5rem;">
    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Search doctors…">
            </div>
        </div>
        <span style="font-size:.8rem;color:var(--text-muted);align-self:center;">
            <?php echo count($doctors); ?> doctor<?php echo count($doctors) !== 1 ? 's' : ''; ?>
        </span>
    </div>

    <?php if (count($doctors) > 0): ?>
    <div class="table-wrap">
        <table class="data-table" id="doctorsTable">
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Experience</th>
                    <th>Contact</th>
                    <th>Appointments</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($doctors as $doctor): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div class="av"><?php echo strtoupper(substr($doctor['name'], 0, 2)); ?></div>
                        <div>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($doctor['name']); ?></div>
                            <div style="font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($doctor['qualification'] ?? '—'); ?></div>
                        </div>
                    </div>
                </td>
                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                <td><?php echo intval($doctor['experience'] ?? 0); ?> yrs</td>
                <td>
                    <div style="font-size:.8rem;">
                        <?php if ($doctor['email']): ?>
                        <div style="margin-bottom:.2rem;"><i class="fas fa-envelope" style="width:14px;color:var(--text-muted);"></i> <?php echo htmlspecialchars($doctor['email']); ?></div>
                        <?php endif; ?>
                        <?php if ($doctor['phone']): ?>
                        <div style="color:var(--text-muted);"><i class="fas fa-phone" style="width:14px;"></i> <?php echo htmlspecialchars($doctor['phone']); ?></div>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <span class="badge badge-info"><?php echo intval($doctor['appointment_count']); ?> appts</span>
                </td>
                <td>
                    <span class="badge <?php echo ($doctor['is_active'] ?? 1) ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo ($doctor['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                    </span>
                </td>
                <td>
                    <div style="display:flex;gap:.4rem;align-items:center;">
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('<?php echo intval($doctor['appointment_count']) > 0 ? 'This doctor has ' . intval($doctor['appointment_count']) . ' appointments. Delete anyway?' : 'Delete this doctor?'; ?>')">
                            <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="act-btn act-del" title="Delete Doctor">
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
    <div class="empty-box">
        <div class="empty-ico"><i class="fas fa-user-doctor"></i></div>
        <h3>No Doctors Found</h3>
        <p>Get started by adding your first doctor to the system.</p>
        <a href="?action=add" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add First Doctor
        </a>
    </div>
    <?php endif; ?>
</div><!-- /glass-card -->

</div><!-- /page-wrap -->

<script>
// Client-side search
document.getElementById('searchInput')?.addEventListener('input', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#doctorsTable tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
