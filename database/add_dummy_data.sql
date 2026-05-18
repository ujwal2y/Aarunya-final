-- ============================================================================
-- AARUNYA HEALTHCARE - DUMMY DATA
-- Add 20 Doctors and 50 Patients with Realistic Data
-- ============================================================================

USE aarunya_db;

-- ============================================================================
-- INSERT 20 DOCTORS
-- ============================================================================

INSERT INTO doctors (name, email, phone, specialization, experience, qualification, availability, is_active, status, created_at) VALUES
('Dr. Priya Sharma', 'priya.sharma@aarunya.com', '+91 98765 43210', 'Obstetrician', 15, 'MBBS, MD (OB/GYN)', 'Mon-Fri 9AM-5PM', 1, 'approved', NOW()),
('Dr. Rajesh Kumar', 'rajesh.kumar@aarunya.com', '+91 98765 43211', 'Gynecologist', 12, 'MBBS, MS (Gynecology)', 'Mon-Sat 10AM-6PM', 1, 'approved', NOW()),
('Dr. Anita Desai', 'anita.desai@aarunya.com', '+91 98765 43212', 'Maternal-Fetal Medicine', 18, 'MBBS, MD, DM (MFM)', 'Mon-Fri 8AM-4PM', 1, 'approved', NOW()),
('Dr. Vikram Singh', 'vikram.singh@aarunya.com', '+91 98765 43213', 'Perinatologist', 20, 'MBBS, MD, Fellowship', 'Tue-Sat 9AM-5PM', 1, 'approved', NOW()),
('Dr. Meera Patel', 'meera.patel@aarunya.com', '+91 98765 43214', 'Obstetrician', 10, 'MBBS, MD (OB/GYN)', 'Mon-Fri 10AM-6PM', 1, 'approved', NOW()),
('Dr. Arjun Reddy', 'arjun.reddy@aarunya.com', '+91 98765 43215', 'Gynecologist', 14, 'MBBS, MS, DNB', 'Mon-Sat 9AM-5PM', 1, 'approved', NOW()),
('Dr. Kavita Nair', 'kavita.nair@aarunya.com', '+91 98765 43216', 'Midwife Specialist', 8, 'MBBS, Diploma (Midwifery)', 'Mon-Fri 8AM-4PM', 1, 'approved', NOW()),
('Dr. Sanjay Gupta', 'sanjay.gupta@aarunya.com', '+91 98765 43217', 'Reproductive Endocrinologist', 16, 'MBBS, MD, DM (Endo)', 'Tue-Sat 10AM-6PM', 1, 'approved', NOW()),
('Dr. Deepa Iyer', 'deepa.iyer@aarunya.com', '+91 98765 43218', 'Neonatologist', 11, 'MBBS, MD (Pediatrics), DM', 'Mon-Fri 9AM-5PM', 1, 'approved', NOW()),
('Dr. Rahul Mehta', 'rahul.mehta@aarunya.com', '+91 98765 43219', 'Obstetrician', 13, 'MBBS, MD (OB/GYN)', 'Mon-Sat 8AM-4PM', 1, 'approved', NOW()),
('Dr. Sunita Rao', 'sunita.rao@aarunya.com', '+91 98765 43220', 'Gynecologist', 9, 'MBBS, MS (Gynecology)', 'Mon-Fri 10AM-6PM', 1, 'approved', NOW()),
('Dr. Anil Kapoor', 'anil.kapoor@aarunya.com', '+91 98765 43221', 'Maternal-Fetal Medicine', 17, 'MBBS, MD, Fellowship (MFM)', 'Tue-Sat 9AM-5PM', 1, 'approved', NOW()),
('Dr. Pooja Verma', 'pooja.verma@aarunya.com', '+91 98765 43222', 'Obstetrician', 7, 'MBBS, MD (OB/GYN)', 'Mon-Fri 9AM-5PM', 1, 'approved', NOW()),
('Dr. Karthik Krishnan', 'karthik.krishnan@aarunya.com', '+91 98765 43223', 'Perinatologist', 19, 'MBBS, MD, DM, Fellowship', 'Mon-Sat 8AM-4PM', 1, 'approved', NOW()),
('Dr. Lakshmi Menon', 'lakshmi.menon@aarunya.com', '+91 98765 43224', 'Gynecologist', 12, 'MBBS, MS, DNB', 'Mon-Fri 10AM-6PM', 1, 'approved', NOW()),
('Dr. Amit Joshi', 'amit.joshi@aarunya.com', '+91 98765 43225', 'Reproductive Endocrinologist', 15, 'MBBS, MD, DM (RE)', 'Tue-Sat 9AM-5PM', 1, 'approved', NOW()),
('Dr. Nisha Agarwal', 'nisha.agarwal@aarunya.com', '+91 98765 43226', 'Obstetrician', 10, 'MBBS, MD (OB/GYN)', 'Mon-Fri 8AM-4PM', 1, 'approved', NOW()),
('Dr. Suresh Pillai', 'suresh.pillai@aarunya.com', '+91 98765 43227', 'Neonatologist', 14, 'MBBS, MD (Peds), DM (Neo)', 'Mon-Sat 10AM-6PM', 1, 'approved', NOW()),
('Dr. Ritu Malhotra', 'ritu.malhotra@aarunya.com', '+91 98765 43228', 'Midwife Specialist', 6, 'MBBS, Diploma (Midwifery)', 'Mon-Fri 9AM-5PM', 1, 'approved', NOW()),
('Dr. Naveen Chandra', 'naveen.chandra@aarunya.com', '+91 98765 43229', 'Gynecologist', 11, 'MBBS, MS (Gynecology)', 'Tue-Sat 8AM-4PM', 1, 'approved', NOW());

-- ============================================================================
-- INSERT 50 PATIENTS (MOTHERS)
-- ============================================================================

INSERT INTO users (name, email, phone, password, age, pregnancy_week, due_date, status, created_at) VALUES
('Anjali Sharma', 'anjali.sharma@gmail.com', '+91 98100 00001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 12, DATE_ADD(NOW(), INTERVAL 28 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 12 WEEK)),
('Priya Patel', 'priya.patel@gmail.com', '+91 98100 00002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 32, 24, DATE_ADD(NOW(), INTERVAL 16 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 24 WEEK)),
('Sneha Reddy', 'sneha.reddy@gmail.com', '+91 98100 00003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 8, DATE_ADD(NOW(), INTERVAL 32 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 8 WEEK)),
('Kavita Singh', 'kavita.singh@gmail.com', '+91 98100 00004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 36, DATE_ADD(NOW(), INTERVAL 4 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 36 WEEK)),
('Deepika Kumar', 'deepika.kumar@gmail.com', '+91 98100 00005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 16, DATE_ADD(NOW(), INTERVAL 24 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 16 WEEK)),
('Ritu Gupta', 'ritu.gupta@gmail.com', '+91 98100 00006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 20, DATE_ADD(NOW(), INTERVAL 20 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 20 WEEK)),
('Meera Nair', 'meera.nair@gmail.com', '+91 98100 00007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 31, 28, DATE_ADD(NOW(), INTERVAL 12 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 28 WEEK)),
('Pooja Desai', 'pooja.desai@gmail.com', '+91 98100 00008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 25, 10, DATE_ADD(NOW(), INTERVAL 30 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 10 WEEK)),
('Sunita Iyer', 'sunita.iyer@gmail.com', '+91 98100 00009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 33, 32, DATE_ADD(NOW(), INTERVAL 8 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 32 WEEK)),
('Lakshmi Rao', 'lakshmi.rao@gmail.com', '+91 98100 00010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 14, DATE_ADD(NOW(), INTERVAL 26 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 14 WEEK)),
('Nisha Verma', 'nisha.verma@gmail.com', '+91 98100 00011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 22, DATE_ADD(NOW(), INTERVAL 18 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 22 WEEK)),
('Divya Menon', 'divya.menon@gmail.com', '+91 98100 00012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 18, DATE_ADD(NOW(), INTERVAL 22 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 18 WEEK)),
('Swati Joshi', 'swati.joshi@gmail.com', '+91 98100 00013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 26, DATE_ADD(NOW(), INTERVAL 14 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 26 WEEK)),
('Aarti Pillai', 'aarti.pillai@gmail.com', '+91 98100 00014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 30, DATE_ADD(NOW(), INTERVAL 10 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 30 WEEK)),
('Rekha Agarwal', 'rekha.agarwal@gmail.com', '+91 98100 00015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 31, 15, DATE_ADD(NOW(), INTERVAL 25 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 15 WEEK)),
('Shalini Kapoor', 'shalini.kapoor@gmail.com', '+91 98100 00016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 11, DATE_ADD(NOW(), INTERVAL 29 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 11 WEEK)),
('Neha Malhotra', 'neha.malhotra@gmail.com', '+91 98100 00017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 34, DATE_ADD(NOW(), INTERVAL 6 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 34 WEEK)),
('Radha Krishnan', 'radha.krishnan@gmail.com', '+91 98100 00018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 32, 19, DATE_ADD(NOW(), INTERVAL 21 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 19 WEEK)),
('Geeta Chandra', 'geeta.chandra@gmail.com', '+91 98100 00019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 23, DATE_ADD(NOW(), INTERVAL 17 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 23 WEEK)),
('Anita Bose', 'anita.bose@gmail.com', '+91 98100 00020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 27, DATE_ADD(NOW(), INTERVAL 13 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 27 WEEK)),
('Shweta Bansal', 'shweta.bansal@gmail.com', '+91 98100 00021', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 13, DATE_ADD(NOW(), INTERVAL 27 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 13 WEEK)),
('Manisha Saxena', 'manisha.saxena@gmail.com', '+91 98100 00022', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 31, DATE_ADD(NOW(), INTERVAL 9 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 31 WEEK)),
('Vandana Mishra', 'vandana.mishra@gmail.com', '+91 98100 00023', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 17, DATE_ADD(NOW(), INTERVAL 23 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 17 WEEK)),
('Kiran Shetty', 'kiran.shetty@gmail.com', '+91 98100 00024', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 31, 21, DATE_ADD(NOW(), INTERVAL 19 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 21 WEEK)),
('Usha Pandey', 'usha.pandey@gmail.com', '+91 98100 00025', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 25, DATE_ADD(NOW(), INTERVAL 15 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 25 WEEK)),
('Archana Dubey', 'archana.dubey@gmail.com', '+91 98100 00026', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 9, DATE_ADD(NOW(), INTERVAL 31 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 9 WEEK)),
('Bharti Tiwari', 'bharti.tiwari@gmail.com', '+91 98100 00027', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 33, DATE_ADD(NOW(), INTERVAL 7 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 33 WEEK)),
('Chitra Yadav', 'chitra.yadav@gmail.com', '+91 98100 00028', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 29, DATE_ADD(NOW(), INTERVAL 11 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 29 WEEK)),
('Dipti Bhatt', 'dipti.bhatt@gmail.com', '+91 98100 00029', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 35, DATE_ADD(NOW(), INTERVAL 5 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 35 WEEK)),
('Ekta Jain', 'ekta.jain@gmail.com', '+91 98100 00030', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 32, 7, DATE_ADD(NOW(), INTERVAL 33 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 7 WEEK)),
('Farida Khan', 'farida.khan@gmail.com', '+91 98100 00031', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 38, DATE_ADD(NOW(), INTERVAL 2 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 38 WEEK)),
('Garima Soni', 'garima.soni@gmail.com', '+91 98100 00032', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 6, DATE_ADD(NOW(), INTERVAL 34 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 6 WEEK)),
('Hema Kulkarni', 'hema.kulkarni@gmail.com', '+91 98100 00033', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 37, DATE_ADD(NOW(), INTERVAL 3 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 37 WEEK)),
('Indira Naik', 'indira.naik@gmail.com', '+91 98100 00034', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 5, DATE_ADD(NOW(), INTERVAL 35 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 5 WEEK)),
('Jaya Hegde', 'jaya.hegde@gmail.com', '+91 98100 00035', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 39, DATE_ADD(NOW(), INTERVAL 1 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 39 WEEK)),
('Kamala Rao', 'kamala.rao@gmail.com', '+91 98100 00036', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 31, 4, DATE_ADD(NOW(), INTERVAL 36 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 4 WEEK)),
('Lata Deshpande', 'lata.deshpande@gmail.com', '+91 98100 00037', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 40, NOW(), 'active', DATE_SUB(NOW(), INTERVAL 40 WEEK)),
('Madhuri Patil', 'madhuri.patil@gmail.com', '+91 98100 00038', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 3, DATE_ADD(NOW(), INTERVAL 37 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 3 WEEK)),
('Nalini Ghosh', 'nalini.ghosh@gmail.com', '+91 98100 00039', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 2, DATE_ADD(NOW(), INTERVAL 38 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 2 WEEK)),
('Omana Nambiar', 'omana.nambiar@gmail.com', '+91 98100 00040', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 1, DATE_ADD(NOW(), INTERVAL 39 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 1 WEEK)),
('Padma Iyer', 'padma.iyer@gmail.com', '+91 98100 00041', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 12, DATE_ADD(NOW(), INTERVAL 28 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 12 WEEK)),
('Qamar Begum', 'qamar.begum@gmail.com', '+91 98100 00042', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 32, 24, DATE_ADD(NOW(), INTERVAL 16 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 24 WEEK)),
('Rama Devi', 'rama.devi@gmail.com', '+91 98100 00043', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 16, DATE_ADD(NOW(), INTERVAL 24 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 16 WEEK)),
('Sarita Bhat', 'sarita.bhat@gmail.com', '+91 98100 00044', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 20, DATE_ADD(NOW(), INTERVAL 20 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 20 WEEK)),
('Tanvi Kamath', 'tanvi.kamath@gmail.com', '+91 98100 00045', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 28, DATE_ADD(NOW(), INTERVAL 12 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 28 WEEK)),
('Uma Shenoy', 'uma.shenoy@gmail.com', '+91 98100 00046', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 27, 10, DATE_ADD(NOW(), INTERVAL 30 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 10 WEEK)),
('Vidya Balan', 'vidya.balan@gmail.com', '+91 98100 00047', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 31, 32, DATE_ADD(NOW(), INTERVAL 8 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 32 WEEK)),
('Yamini Reddy', 'yamini.reddy@gmail.com', '+91 98100 00048', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 28, 14, DATE_ADD(NOW(), INTERVAL 26 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 14 WEEK)),
('Zoya Ahmed', 'zoya.ahmed@gmail.com', '+91 98100 00049', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 29, 22, DATE_ADD(NOW(), INTERVAL 18 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 22 WEEK)),
('Asha Menon', 'asha.menon@gmail.com', '+91 98100 00050', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 26, 18, DATE_ADD(NOW(), INTERVAL 22 WEEK), 'active', DATE_SUB(NOW(), INTERVAL 18 WEEK));

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================

SELECT 'Dummy data inserted successfully!' AS Status,
       (SELECT COUNT(*) FROM doctors) AS Total_Doctors,
       (SELECT COUNT(*) FROM users) AS Total_Patients;
