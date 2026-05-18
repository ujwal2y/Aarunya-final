<?php
// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireDoctorLogin();

$pageTitle = 'My Schedule';
$doctor = getCurrentDoctor();

// Ensure doctor data is available
if (!$doctor || !isset($doctor['id'])) {
    header('Location: ../../client/login.php');
    exit();
}

$db = getDoctorDB();

// Check if doctor_schedule table exists
try {
    $stmt = $db->query("SHOW TABLES LIKE 'doctor_schedule'");
    $tableExists = $stmt->rowCount() > 0;
} catch (Exception $e) {
    $tableExists = false;
}

$schedules = [];
if ($tableExists) {
    try {
        $stmt = $db->prepare("
            SELECT * FROM doctor_schedule 
            WHERE doctor_id = ? 
            ORDER BY day_of_week ASC, start_time ASC
        ");
        $stmt->execute([$doctor['id']]);
        $schedules = $stmt->fetchAll();
    } catch (Exception $e) {
        $schedules = [];
    }
}

// Organize by day
$schedule_by_day = [];
foreach ($schedules as $schedule) {
    if (isset($schedule['day_of_week'])) {
        $schedule_by_day[$schedule['day_of_week']][] = $schedule;
    }
}

$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

include '../includes/header.php';
?>

<div style="padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h1 style="font-size: 32px; font-weight: 800; color: #ffffff; margin: 0;">
            <i class="fas fa-calendar-alt"></i> My Schedule
        </h1>
        <button class="btn btn-primary" onclick="alert('Bulk schedule feature coming soon!')">
            <i class="fas fa-calendar-plus"></i> Set Weekly Schedule
        </button>
    </div>

    <div class="glass-card" style="margin-bottom: 24px;">
        <h3 style="color: #C4A7FF; margin-bottom: 8px; font-size: 16px;">
            <i class="fas fa-info-circle"></i> Schedule Management
        </h3>
        <p style="color: #546e7a; font-size: 14px; line-height: 1.6; margin: 0;">
            Set your availability for each day of the week. Patients can book appointments during your available time slots.
            You can add multiple time slots per day and set different durations for each slot.
        </p>
    </div>

    <div style="display: grid; gap: 20px;">
        <?php foreach ($days as $day_index => $day_name): ?>
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <h2 style="font-size: 20px; font-weight: 700; color: #C4A7FF; margin: 0;">
                    <?php echo $day_name; ?>
                </h2>
                <?php if (isset($schedule_by_day[$day_index]) && count($schedule_by_day[$day_index]) > 0): ?>
                    <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(244, 114, 182, 0.2); color: #C4A7FF; border: 1px solid rgba(244, 114, 182, 0.3);">
                        <i class="fas fa-check-circle"></i> Available
                    </span>
                <?php else: ?>
                    <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(100, 116, 139, 0.2); color: #78909c; border: 1px solid rgba(100, 116, 139, 0.3);">
                        <i class="fas fa-times-circle"></i> Not Set
                    </span>
                <?php endif; ?>
            </div>

            <div style="display: grid; gap: 12px;">
                <?php if (isset($schedule_by_day[$day_index]) && count($schedule_by_day[$day_index]) > 0): ?>
                    <?php foreach ($schedule_by_day[$day_index] as $slot): ?>
                    <div style="background: rgba(244, 114, 182, 0.1); border: 1px solid rgba(244, 114, 182, 0.2); border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: rgba(244, 114, 182, 0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #C4A7FF;">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <div style="font-size: 15px; font-weight: 600;">
                                    <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - 
                                    <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
                                </div>
                                <div style="font-size: 13px; color: #546e7a; margin-top: 2px;">
                                    <?php echo $slot['slot_duration']; ?> min slots
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <button class="action-btn edit" onclick="alert('Edit feature coming soon!')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete" onclick="deleteSlot(<?php echo $slot['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 32px; color: #546e7a;">
                        <i class="fas fa-calendar-times" style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;"></i>
                        <p>No schedule set for this day</p>
                    </div>
                <?php endif; ?>
            </div>

            <button class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 12px;" onclick="addTimeSlot(<?php echo $day_index; ?>, '<?php echo $day_name; ?>')">
                <i class="fas fa-plus"></i> Add Time Slot
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function addTimeSlot(dayIndex, dayName) {
    alert(`Add time slot feature for ${dayName} coming soon!`);
}

function deleteSlot(slotId) {
    if (confirm('Are you sure you want to delete this time slot?')) {
        alert('Delete feature coming soon!');
    }
}
</script>

        </div><!-- End main-content -->
    </div><!-- End flex container -->
</body>
</html>
