<?php
/**
 * Generate Enhanced Database with Dummy Data
 * Run this file once to populate the database with sample data
 */

require_once 'admin/includes/db.php';

echo "<h2>Generating Enhanced Database...</h2>";

// Clear existing data (except admin)
$pdo->exec("DELETE FROM health_records");
$pdo->exec("DELETE FROM emergency_requests");
$pdo->exec("DELETE FROM appointments");
$pdo->exec("DELETE FROM users");
$pdo->exec("DELETE FROM doctors");

echo "<p>✓ Cleared existing data</p>";

// Generate 15 Doctors
$doctors = [
    ['Dr. Sarah Johnson', 'sarah.j@aarunya.com', '+91-9876543210', 'Obstetrician', 15, 'MBBS, MD (OB/GYN)', 'Mon-Fri: 9AM-5PM'],
    ['Dr. Michael Chen', 'michael.c@aarunya.com', '+91-9876543211', 'Gynecologist', 12, 'MBBS, MS (Gynecology)', 'Mon-Sat: 10AM-6PM'],
    ['Dr. Priya Sharma', 'priya.s@aarunya.com', '+91-9876543212', 'Maternal-Fetal Medicine', 18, 'MBBS, MD, DM (MFM)', 'Tue-Sat: 9AM-4PM'],
    ['Dr. James Wilson', 'james.w@aarunya.com', '+91-9876543213', 'Perinatologist', 20, 'MBBS, MD, Fellowship', 'Mon-Fri: 8AM-3PM'],
    ['Dr. Emily Brown', 'emily.b@aarunya.com', '+91-9876543214', 'Midwife Specialist', 10, 'BSc Nursing, MSc Midwifery', 'Mon-Sun: 24/7'],
    ['Dr. Rajesh Kumar', 'rajesh.k@aarunya.com', '+91-9876543215', 'Obstetrician', 14, 'MBBS, MD (OB/GYN)', 'Mon-Fri: 10AM-6PM'],
    ['Dr. Lisa Anderson', 'lisa.a@aarunya.com', '+91-9876543216', 'Gynecologist', 11, 'MBBS, MS', 'Tue-Sat: 11AM-7PM'],
    ['Dr. David Lee', 'david.l@aarunya.com', '+91-9876543217', 'Obstetrician', 16, 'MBBS, MD', 'Mon-Fri: 9AM-5PM'],
    ['Dr. Anita Patel', 'anita.p@aarunya.com', '+91-9876543218', 'Maternal Health', 13, 'MBBS, MD', 'Mon-Sat: 10AM-6PM'],
    ['Dr. Robert Taylor', 'robert.t@aarunya.com', '+91-9876543219', 'Perinatologist', 19, 'MBBS, MD, DM', 'Mon-Fri: 8AM-4PM'],
    ['Dr. Meera Reddy', 'meera.r@aarunya.com', '+91-9876543220', 'Gynecologist', 9, 'MBBS, MS', 'Tue-Sat: 10AM-6PM'],
    ['Dr. John Martinez', 'john.m@aarunya.com', '+91-9876543221', 'Obstetrician', 17, 'MBBS, MD', 'Mon-Fri: 9AM-5PM'],
    ['Dr. Kavita Singh', 'kavita.s@aarunya.com', '+91-9876543222', 'Maternal-Fetal Medicine', 15, 'MBBS, MD, DM', 'Mon-Sat: 9AM-5PM'],
    ['Dr. Thomas White', 'thomas.w@aarunya.com', '+91-9876543223', 'Gynecologist', 12, 'MBBS, MS', 'Tue-Sat: 10AM-6PM'],
    ['Dr. Sunita Gupta', 'sunita.g@aarunya.com', '+91-9876543224', 'Obstetrician', 14, 'MBBS, MD', 'Mon-Fri: 9AM-5PM']
];

$stmt = $pdo->prepare("INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($doctors as $doctor) {
    $stmt->execute($doctor);
}
echo "<p>✓ Added 15 doctors</p>";

// Generate 30 Users
$firstNames = ['Priya', 'Anjali', 'Sneha', 'Pooja', 'Neha', 'Riya', 'Kavya', 'Divya', 'Shreya', 'Ananya', 
               'Meera', 'Sanya', 'Isha', 'Tanvi', 'Aisha', 'Simran', 'Nisha', 'Ritika', 'Sakshi', 'Aditi',
               'Radhika', 'Swati', 'Preeti', 'Manisha', 'Deepika', 'Komal', 'Pallavi', 'Shweta', 'Megha', 'Nikita'];
$lastNames = ['Sharma', 'Patel', 'Kumar', 'Singh', 'Reddy', 'Gupta', 'Verma', 'Joshi', 'Mehta', 'Nair'];

$password = password_hash('test123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, age, pregnancy_week, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

for ($i = 0; $i < 30; $i++) {
    $name = $firstNames[$i] . ' ' . $lastNames[$i % count($lastNames)];
    $email = "user" . ($i + 1) . "@example.com";
    $phone = "+91-98765" . (43300 + $i);
    $age = rand(22, 38);
    $week = rand(4, 38);
    $daysAhead = (40 - $week) * 7;
    $dueDate = date('Y-m-d', strtotime("+$daysAhead days"));
    $daysAgo = rand(1, 60);
    $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
    
    $stmt->execute([$name, $email, $password, $phone, $age, $week, $dueDate, $createdAt]);
}
echo "<p>✓ Added 30 users</p>";

// Generate 50 Appointments
$statuses = ['pending', 'approved', 'completed', 'cancelled'];
$stmt = $pdo->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");

for ($i = 0; $i < 50; $i++) {
    $userId = rand(1, 30);
    $doctorId = rand(1, 15);
    $days = rand(-30, 30);
    $aptDate = date('Y-m-d', strtotime("$days days"));
    $hour = rand(9, 17);
    $aptTime = sprintf("%02d:00:00", $hour);
    $status = $statuses[array_rand($statuses)];
    $daysAgo = rand(1, 45);
    $createdAt = date('Y-m-d H:i:s', strtotime("-$daysAgo days"));
    
    $stmt->execute([$userId, $doctorId, $aptDate, $aptTime, $status, $createdAt]);
}
echo "<p>✓ Added 50 appointments</p>";

// Generate 5 Emergency Requests
$messages = [
    'Severe abdominal pain, need immediate assistance',
    'Heavy bleeding, please help urgently',
    'High fever and dizziness',
    'Sudden contractions, might be early labor',
    'Severe headache and vision problems'
];
$locations = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Kolkata', 'Pune', 'Hyderabad'];
$stmt = $pdo->prepare("INSERT INTO emergency_requests (user_id, message, location, phone, status, priority) VALUES (?, ?, ?, ?, ?, ?)");

for ($i = 0; $i < 5; $i++) {
    $userId = rand(1, 30);
    $message = $messages[array_rand($messages)];
    $location = $locations[array_rand($locations)] . ', India';
    $phone = "+91-98765" . (43300 + $userId);
    $status = $i < 2 ? 'pending' : 'resolved';
    $priority = $i < 3 ? 'high' : 'medium';
    
    $stmt->execute([$userId, $message, $location, $phone, $status, $priority]);
}
echo "<p>✓ Added 5 emergency requests</p>";

echo "<h3 style='color: green;'>✅ Database populated successfully!</h3>";
echo "<p><strong>Summary:</strong></p>";
echo "<ul>";
echo "<li>1 Admin (admin@aarunya.com / admin123)</li>";
echo "<li>15 Doctors</li>";
echo "<li>30 Users (user1@example.com to user30@example.com / test123)</li>";
echo "<li>50 Appointments</li>";
echo "<li>5 Emergency Requests</li>";
echo "</ul>";
echo "<p><a href='admin/pages/dashboard.php' style='color: #4db6ac; font-weight: bold;'>→ Go to Admin Dashboard</a></p>";
?>
