<?php
/**
 * Appointment Notification System
 * Uses output buffering — stores HTML in $appointmentNotificationHTML.
 * Each page must echo $appointmentNotificationHTML inside <body>.
 */

// Suppress warnings
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

$appointmentNotificationHTML = '';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Handle dismiss request first (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_notification'])) {
    $appointmentId = intval($_POST['dismiss_notification']);
    $_SESSION['appointment_notification_dismissed_' . $appointmentId] = true;
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    return;
}

require_once __DIR__ . '/../../server/config/database.php';

try {
    $pdo = getDB();
    $userId = $_SESSION['user_id'];

    // Get next upcoming appointment within 7 days
    $sql = "SELECT 
                a.id,
                a.appointment_date,
                a.appointment_time,
                a.status,
                a.notes,
                d.name as doctor_name,
                d.specialization,
                d.hospital_affiliation
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            WHERE a.user_id = ?
            AND a.status IN ('confirmed', 'pending')
            AND a.appointment_date >= CURDATE()
            AND a.appointment_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $nextAppointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($nextAppointment) {
        $appointmentDate = date('l, F j, Y', strtotime($nextAppointment['appointment_date']));
        $appointmentTime = date('g:i A', strtotime($nextAppointment['appointment_time']));
        $daysUntil = floor((strtotime($nextAppointment['appointment_date']) - time()) / (60 * 60 * 24));

        // Determine urgency
        if ($daysUntil == 0)      { $urgency = 'today';    $urgencyText = 'Today'; }
        elseif ($daysUntil == 1)  { $urgency = 'tomorrow'; $urgencyText = 'Tomorrow'; }
        elseif ($daysUntil <= 3)  { $urgency = 'soon';     $urgencyText = "In {$daysUntil} days"; }
        else                      { $urgency = 'normal';   $urgencyText = "In {$daysUntil} days"; }

        $dismissKey = 'appointment_notification_dismissed_' . $nextAppointment['id'];

        if (!isset($_SESSION[$dismissKey])) {
            // Capture HTML into buffer
            ob_start();
            ?>
            <!-- Appointment Notification Popup -->
            <div id="appointmentNotification" class="appointment-notification-overlay" data-urgency="<?php echo $urgency; ?>" style="display:none;">
                <div class="appointment-notification-modal">
                    <button class="notification-close" onclick="dismissNotification(<?php echo $nextAppointment['id']; ?>)">
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="notification-icon <?php echo $urgency; ?>">
                        <i class="fas fa-calendar-check"></i>
                    </div>

                    <h2 class="notification-title">Upcoming Appointment</h2>

                    <div class="notification-urgency <?php echo $urgency; ?>">
                        <i class="fas fa-clock"></i>
                        <span><?php echo $urgencyText; ?></span>
                    </div>

                    <div class="notification-details">
                        <div class="detail-row">
                            <i class="fas fa-user-md"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($nextAppointment['doctor_name']); ?></strong>
                                <p><?php echo htmlspecialchars($nextAppointment['specialization']); ?></p>
                            </div>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <strong><?php echo $appointmentDate; ?></strong>
                                <p><?php echo $appointmentTime; ?></p>
                            </div>
                        </div>
                        <?php if ($nextAppointment['hospital_affiliation']): ?>
                        <div class="detail-row">
                            <i class="fas fa-hospital"></i>
                            <div>
                                <strong><?php echo htmlspecialchars($nextAppointment['hospital_affiliation']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($nextAppointment['notes']): ?>
                        <div class="detail-row">
                            <i class="fas fa-sticky-note"></i>
                            <div>
                                <p><?php echo htmlspecialchars($nextAppointment['notes']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="notification-actions">
                        <a href="appointments.php" class="btn-primary">
                            <i class="fas fa-calendar-alt"></i> View All Appointments
                        </a>
                        <button onclick="dismissNotification(<?php echo $nextAppointment['id']; ?>)" class="btn-secondary">
                            <i class="fas fa-check"></i> Got It
                        </button>
                    </div>
                </div>
            </div>

            <style>
                .appointment-notification-overlay {
                    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
                    background: rgba(0,0,0,0.75);
                    backdrop-filter: blur(10px);
                    display: flex; align-items: center; justify-content: center;
                    z-index: 10000;
                    animation: apptFadeIn 0.3s ease;
                    pointer-events: auto;
                }
                @keyframes apptFadeIn { from { opacity: 0; } to { opacity: 1; } }

                .appointment-notification-modal {
                    background: linear-gradient(145deg, #1a1f3a 0%, #0f1729 100%);
                    border: 1px solid rgba(196,167,255,0.25);
                    border-radius: 20px;
                    padding: 40px;
                    max-width: 500px;
                    width: 90%;
                    position: relative;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.6), 0 0 40px rgba(196,167,255,0.1);
                    animation: apptSlideUp 0.4s ease;
                }
                @keyframes apptSlideUp {
                    from { transform: translateY(50px); opacity: 0; }
                    to   { transform: translateY(0);    opacity: 1; }
                }

                .notification-close {
                    position: absolute; top: 15px; right: 15px;
                    background: rgba(196,167,255,0.08);
                    border: 1px solid rgba(196,167,255,0.2);
                    color: #C4A7FF;
                    width: 35px; height: 35px;
                    border-radius: 50%;
                    display: flex; align-items: center; justify-content: center;
                    cursor: pointer; transition: all 0.2s ease;
                }
                .notification-close:hover { background: rgba(196,167,255,0.18); transform: rotate(90deg); }

                .notification-icon {
                    width: 80px; height: 80px;
                    margin: 0 auto 20px;
                    border-radius: 50%;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 35px;
                    animation: apptPulse 2s infinite;
                }
                @keyframes apptPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }

                .notification-icon.today    { background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 0 30px rgba(239,68,68,.5); }
                .notification-icon.tomorrow { background: linear-gradient(135deg,#f59e0b,#d97706); color:#fff; box-shadow:0 0 30px rgba(245,158,11,.5); }
                .notification-icon.soon     { background: linear-gradient(135deg,#3b82f6,#2563eb); color:#fff; box-shadow:0 0 30px rgba(59,130,246,.5); }
                .notification-icon.normal   { background: linear-gradient(135deg,#C4A7FF,#7F5AF0); color:#fff; box-shadow:0 0 30px rgba(196,167,255,.5); }

                .notification-title { text-align:center; font-size:28px; font-weight:800; color:#fff; margin-bottom:15px; }

                .notification-urgency {
                    text-align:center; padding:10px 20px; border-radius:25px;
                    margin-bottom:25px; display:inline-flex; align-items:center;
                    gap:8px; font-weight:600; font-size:16px;
                    width:100%; justify-content:center;
                }
                .notification-urgency.today    { background:rgba(239,68,68,.15);   border:2px solid rgba(239,68,68,.4);   color:#fca5a5; }
                .notification-urgency.tomorrow { background:rgba(245,158,11,.15);  border:2px solid rgba(245,158,11,.4);  color:#fcd34d; }
                .notification-urgency.soon     { background:rgba(59,130,246,.15);  border:2px solid rgba(59,130,246,.4);  color:#93c5fd; }
                .notification-urgency.normal   { background:rgba(196,167,255,.12); border:2px solid rgba(196,167,255,.3); color:#C4A7FF; }

                .notification-details {
                    background: rgba(255,255,255,0.04);
                    border: 1px solid rgba(196,167,255,0.15);
                    border-radius: 15px; padding: 20px; margin-bottom: 25px;
                }
                .detail-row {
                    display:flex; align-items:flex-start; gap:15px;
                    margin-bottom:15px; padding-bottom:15px;
                    border-bottom:1px solid rgba(196,167,255,0.08);
                }
                .detail-row:last-child { margin-bottom:0; padding-bottom:0; border-bottom:none; }
                .detail-row i      { color:#C4A7FF; font-size:20px; width:25px; flex-shrink:0; margin-top:2px; }
                .detail-row strong { color:#fff; font-size:16px; display:block; margin-bottom:4px; }
                .detail-row p      { color:#94a3b8; font-size:14px; margin:0; }

                .notification-actions { display:flex; gap:12px; flex-direction:column; }
                .notification-actions .btn-primary,
                .notification-actions .btn-secondary {
                    padding:14px 24px; border-radius:10px; font-weight:600; font-size:15px;
                    display:flex; align-items:center; justify-content:center; gap:8px;
                    cursor:pointer; transition:all 0.2s ease; text-decoration:none;
                    border:none; width:100%; z-index:10001; pointer-events:auto;
                }
                .notification-actions .btn-primary {
                    background: linear-gradient(135deg,#7F5AF0,#C4A7FF);
                    color:#fff; box-shadow:0 4px 16px rgba(127,90,240,.35);
                }
                .notification-actions .btn-primary:hover {
                    background: linear-gradient(135deg,#C4A7FF,#00D1FF);
                    box-shadow:0 6px 20px rgba(196,167,255,.45);
                    transform:translateY(-2px);
                }
                .notification-actions .btn-secondary {
                    background:rgba(196,167,255,0.08);
                    border:1px solid rgba(196,167,255,0.2); color:#C4A7FF;
                }
                .notification-actions .btn-secondary:hover {
                    background:rgba(196,167,255,0.15); border-color:rgba(196,167,255,0.4);
                }
                @media(max-width:768px){
                    .appointment-notification-modal { padding:30px 20px; margin:20px; }
                    .notification-title { font-size:24px; }
                    .notification-icon  { width:60px; height:60px; font-size:28px; }
                }
                @keyframes apptFadeOut { from { opacity:1; } to { opacity:0; } }
            </style>

            <script>
                function dismissNotification(appointmentId) {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'dismiss_notification=' + appointmentId
                    }).catch(err => console.log('Dismiss error:', err));

                    const n = document.getElementById('appointmentNotification');
                    if (n) {
                        n.style.animation = 'apptFadeOut 0.3s ease forwards';
                        setTimeout(() => n.style.display = 'none', 300);
                    }
                }

                window.addEventListener('load', function() {
                    setTimeout(() => {
                        const n = document.getElementById('appointmentNotification');
                        if (n) {
                            n.style.display = 'flex';
                            n.addEventListener('click', function(e) {
                                if (e.target === n) {
                                    const id = n.querySelector('[onclick*="dismissNotification"]')
                                        ?.getAttribute('onclick')?.match(/\d+/)?.[0];
                                    if (id) dismissNotification(id);
                                }
                            });
                        }
                    }, 800);
                });
            </script>
            <?php
            $appointmentNotificationHTML = ob_get_clean();
        }
    }

} catch (Exception $e) {
    error_log("Appointment notification error: " . $e->getMessage());
}
?>
