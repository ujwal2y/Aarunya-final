<?php
require_once 'server/config/database.php';

echo "=== GENERATING DUMMY DATA ===\n\n";

try {
    $db = getDB();
    $db->beginTransaction();
    
    // Password hash for all dummy accounts (password: test123)
    $passwordHash = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHeFVNfFJZfHqVz3BXgVJQfJqKJZ0qJ0Hy';
    
    // Indian female names
    $firstNames = ['Priya', 'Ananya', 'Sneha', 'Kavya', 'Riya', 'Aisha', 'Diya', 'Meera', 'Sana', 'Tara',
                   'Neha', 'Pooja', 'Simran', 'Anjali', 'Divya', 'Shreya', 'Nisha', 'Rani', 'Swati', 'Vidya',
                   'Aarti', 'Deepa', 'Geeta', 'Hema', 'Isha', 'Jaya', 'Kiran', 'Lata', 'Maya', 'Naina'];
    
    $lastNames = ['Sharma', 'Patel', 'Kumar', 'Singh', 'Reddy', 'Gupta', 'Verma', 'Joshi', 'Mehta', 'Nair',
                  'Iyer', 'Rao', 'Desai', 'Kulkarni', 'Bhat', 'Menon', 'Pillai', 'Agarwal', 'Banerjee', 'Das'];
    
    // Cities
    $cities = ['Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Kolkata', 'Pune', 'Ahmedabad', 'Jaipur', 'Lucknow'];
    
    echo "1. Inserting 50 Users...\n";
    $userIds = [];
    for ($i = 1; $i <= 50; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $name = $firstName . ' ' . $lastName;
        $email = strtolower($firstName . '.' . $lastName . $i . '@example.com');
        $phone = '+91-' . rand(7000000000, 9999999999);
        $age = rand(22, 40);
        $pregnancyWeek = rand(4, 40);
        $dueDate = date('Y-m-d', strtotime('+' . (40 - $pregnancyWeek) . ' weeks'));
        
        $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, age, pregnancy_week, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))");
        $stmt->execute([$name, $email, $passwordHash, $phone, $age, $pregnancyWeek, $dueDate, rand(1, 180)]);
        $userIds[] = $db->lastInsertId();
    }
    echo "✅ 50 users created\n\n";
    
    echo "2. Inserting 20 Additional Doctors...\n";
    $doctorNames = [
        ['Dr. Amit Patel', 'Obstetrician', 15, 'MBBS, MD'],
        ['Dr. Sunita Reddy', 'Gynecologist', 12, 'MBBS, MS'],
        ['Dr. Vikram Singh', 'Perinatologist', 18, 'MBBS, MD, DM'],
        ['Dr. Lakshmi Iyer', 'Maternal-Fetal Medicine', 20, 'MBBS, MD, Fellowship'],
        ['Dr. Rahul Gupta', 'Obstetrician', 10, 'MBBS, MD'],
        ['Dr. Kavita Desai', 'Gynecologist', 14, 'MBBS, MS'],
        ['Dr. Arjun Nair', 'Perinatologist', 16, 'MBBS, MD, DM'],
        ['Dr. Meena Kulkarni', 'Midwife Specialist', 8, 'BSc Nursing, MSc'],
        ['Dr. Sanjay Mehta', 'Obstetrician', 13, 'MBBS, MD'],
        ['Dr. Anjali Bhat', 'Gynecologist', 11, 'MBBS, MS'],
        ['Dr. Karthik Menon', 'Maternal-Fetal Medicine', 17, 'MBBS, MD, DM'],
        ['Dr. Deepa Agarwal', 'Obstetrician', 9, 'MBBS, MD'],
        ['Dr. Ravi Banerjee', 'Perinatologist', 19, 'MBBS, MD, Fellowship'],
        ['Dr. Swati Das', 'Gynecologist', 12, 'MBBS, MS'],
        ['Dr. Nikhil Joshi', 'Obstetrician', 14, 'MBBS, MD'],
        ['Dr. Pooja Verma', 'Midwife Specialist', 7, 'BSc Nursing, MSc'],
        ['Dr. Manoj Kumar', 'Perinatologist', 16, 'MBBS, MD, DM'],
        ['Dr. Rekha Sharma', 'Gynecologist', 13, 'MBBS, MS'],
        ['Dr. Anil Rao', 'Obstetrician', 15, 'MBBS, MD'],
        ['Dr. Nandini Pillai', 'Maternal-Fetal Medicine', 18, 'MBBS, MD, DM']
    ];
    
    $doctorIds = [1, 2, 3, 4, 5, 6]; // Existing doctors
    foreach ($doctorNames as $index => $doctor) {
        $email = strtolower(str_replace([' ', '.'], ['', ''], $doctor[0])) . '@aarunya.com';
        $phone = '+91-' . rand(7000000000, 9999999999);
        $availability = ['Mon-Fri: 9AM-5PM', 'Mon-Sat: 10AM-6PM', 'Tue-Sat: 9AM-4PM', 'Mon-Sun: 24/7'][rand(0, 3)];
        
        $stmt = $db->prepare("INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))");
        $stmt->execute([$doctor[0], $email, $phone, $doctor[1], $doctor[2], $doctor[3], $availability, rand(1, 365)]);
        $doctorIds[] = $db->lastInsertId();
    }
    echo "✅ 20 additional doctors created\n\n";
    
    echo "3. Inserting 100 Appointments...\n";
    $statuses = ['pending', 'approved', 'completed', 'cancelled'];
    $statusWeights = [20, 40, 30, 10]; // Percentage distribution
    
    for ($i = 0; $i < 100; $i++) {
        $userId = $userIds[array_rand($userIds)];
        $doctorId = $doctorIds[array_rand($doctorIds)];
        $daysOffset = rand(-30, 60);
        $appointmentDate = date('Y-m-d', strtotime("+$daysOffset days"));
        $hour = rand(9, 17);
        $minute = [0, 15, 30, 45][rand(0, 3)];
        $appointmentTime = sprintf('%02d:%02d:00', $hour, $minute);
        
        $rand = rand(1, 100);
        if ($rand <= 20) $status = 'pending';
        elseif ($rand <= 60) $status = 'approved';
        elseif ($rand <= 90) $status = 'completed';
        else $status = 'cancelled';
        
        $notes = ['Regular checkup', 'Follow-up visit', 'Ultrasound scan', 'Blood test', 'Consultation', 'Emergency visit'][rand(0, 5)];
        
        $stmt = $db->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, status, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))");
        $stmt->execute([$userId, $doctorId, $appointmentDate, $appointmentTime, $status, $notes, rand(1, 90)]);
    }
    echo "✅ 100 appointments created\n\n";
    
    echo "4. Inserting 30 Emergency Requests...\n";
    $emergencyMessages = [
        'Severe abdominal pain, need immediate assistance',
        'Heavy bleeding, please help urgently',
        'High fever and dizziness',
        'Sudden contractions, might be early labor',
        'Severe headache and blurred vision',
        'Chest pain and difficulty breathing',
        'Reduced fetal movement',
        'Water broke unexpectedly',
        'Severe back pain',
        'Extreme nausea and vomiting'
    ];
    
    $priorities = ['high', 'medium', 'low'];
    $emergencyStatuses = ['pending', 'in_progress', 'resolved'];
    
    for ($i = 0; $i < 30; $i++) {
        $userId = $userIds[array_rand($userIds)];
        $message = $emergencyMessages[array_rand($emergencyMessages)];
        $location = $cities[array_rand($cities)] . ', India';
        $phone = '+91-' . rand(7000000000, 9999999999);
        $priority = $priorities[array_rand($priorities)];
        
        $rand = rand(1, 100);
        if ($rand <= 30) $status = 'pending';
        elseif ($rand <= 60) $status = 'in_progress';
        else $status = 'resolved';
        
        $resolvedAt = $status === 'resolved' ? date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days')) : null;
        
        $stmt = $db->prepare("INSERT INTO emergency_requests (user_id, message, location, phone, status, priority, resolved_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))");
        $stmt->execute([$userId, $message, $location, $phone, $status, $priority, $resolvedAt, rand(1, 60)]);
    }
    echo "✅ 30 emergency requests created\n\n";
    
    echo "5. Inserting 150 Health Records...\n";
    foreach ($userIds as $userId) {
        // Create 3 health records per user
        for ($j = 0; $j < 3; $j++) {
            $bloodPressure = rand(110, 140) . '/' . rand(70, 90);
            $hemoglobin = rand(100, 140) / 10;
            $pulseRate = rand(60, 100);
            $weight = rand(550, 850) / 10;
            $temperature = rand(970, 990) / 10;
            $notes = ['Normal checkup', 'Slight anemia detected', 'All vitals normal', 'Blood pressure slightly elevated', 'Good progress'][rand(0, 4)];
            
            $stmt = $db->prepare("INSERT INTO health_records (user_id, blood_pressure, hemoglobin, pulse_rate, weight, temperature, notes, recorded_at) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_SUB(NOW(), INTERVAL ? DAY))");
            $stmt->execute([$userId, $bloodPressure, $hemoglobin, $pulseRate, $weight, $temperature, $notes, rand(1, 90)]);
        }
    }
    echo "✅ 150 health records created\n\n";
    
    $db->commit();
    
    echo "\n=== SUMMARY ===\n";
    echo "✅ 50 Users\n";
    echo "✅ 20 Additional Doctors (26 total)\n";
    echo "✅ 100 Appointments\n";
    echo "✅ 30 Emergency Requests\n";
    echo "✅ 150 Health Records\n";
    echo "\n=== DUMMY DATA GENERATION COMPLETE ===\n";
    echo "\nAll dummy accounts use password: test123\n";
    echo "Admin account: admin@aarunya.com / admin123\n";
    
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
