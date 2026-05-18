# 🔐 Aarunya Healthcare - Default Login Credentials

## 📋 All User Accounts

---

## 👨‍💼 ADMIN ACCOUNT

### System Administrator
- **Email:** `admin@aarunya.com`
- **Password:** `Admin@123`
- **Role:** Admin
- **Access:** Full system access, manage doctors, users, appointments, emergencies

**Login URL:** `http://localhost/Aarunya final/Aarunya/client/login.php`

---

## 👨‍⚕️ DOCTOR ACCOUNTS

**All doctors use the same password:** `Doctor@123`

### 1. Dr. Firuza Parikh
- **Email:** `dr.firuza@aarunya.com`
- **Password:** `Doctor@123`
- **Specialization:** IVF & Reproductive Medicine
- **Hospital:** Jaslok Hospital, Mumbai
- **Experience:** 25 years
- **Consultation Fee:** ₹2,000
- **Status:** ✅ Verified & Active

### 2. Dr. Nandita Palshetkar
- **Email:** `dr.nandita@aarunya.com`
- **Password:** `Doctor@123`
- **Specialization:** IVF & Gynecology
- **Hospital:** Bloom IVF Centre, Mumbai
- **Experience:** 22 years
- **Consultation Fee:** ₹1,800
- **Status:** ✅ Verified & Active

### 3. Dr. Hrishikesh Pai
- **Email:** `dr.hrishikesh@aarunya.com`
- **Password:** `Doctor@123`
- **Specialization:** IVF & Infertility
- **Hospital:** Lilavati Hospital, Mumbai
- **Experience:** 28 years
- **Consultation Fee:** ₹2,200
- **Status:** ✅ Verified & Active

### 4. Dr. Rishma Dhillon Pai
- **Email:** `dr.rishma@aarunya.com`
- **Password:** `Doctor@123`
- **Specialization:** High-Risk Pregnancy
- **Hospital:** Jaslok Hospital, Mumbai
- **Experience:** 20 years
- **Consultation Fee:** ₹1,900
- **Status:** ✅ Verified & Active

### 5. Dr. Anita Soni
- **Email:** `dr.anita@aarunya.com`
- **Password:** `Doctor@123`
- **Specialization:** Fetal Medicine
- **Hospital:** Fortis Hospital, Delhi
- **Experience:** 18 years
- **Consultation Fee:** ₹1,700
- **Status:** ✅ Verified & Active

### 6. Dr. Duru Shah
- **Email:** `dr.duru@aarunya.com`
- **Password:** `Doctor@123`
- **Specialization:** Gynecology & Obstetrics
- **Hospital:** Breach Candy Hospital, Mumbai
- **Experience:** 30 years
- **Consultation Fee:** ₹2,500
- **Status:** ✅ Verified & Active

**Doctor Login URL:** `http://localhost/Aarunya final/Aarunya/client/login.php?role=doctor`

---

## 👩‍🦰 PATIENT ACCOUNT

### Test Patient
- **Email:** `test@gmail.com`
- **Phone:** `9876543210` (can login with phone too)
- **Password:** `Test@123`
- **Age:** 28 years
- **Pregnancy Week:** 24 weeks
- **Due Date:** September 15, 2026
- **Status:** ✅ Active

**Patient Login URL:** `http://localhost/Aarunya final/Aarunya/client/login.php`

---

## 🔑 Quick Login Reference

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@aarunya.com | Admin@123 |
| **Doctor** | dr.firuza@aarunya.com | Doctor@123 |
| **Doctor** | dr.nandita@aarunya.com | Doctor@123 |
| **Doctor** | dr.hrishikesh@aarunya.com | Doctor@123 |
| **Doctor** | dr.rishma@aarunya.com | Doctor@123 |
| **Doctor** | dr.anita@aarunya.com | Doctor@123 |
| **Doctor** | dr.duru@aarunya.com | Doctor@123 |
| **Patient** | test@gmail.com | Test@123 |

---

## 📱 Login Options

### For Patients:
- Login with **Email** OR **Phone Number**
- Example: `test@gmail.com` or `9876543210`

### For Doctors:
- Login with **Email** only
- Must select "Doctor" role on login page

### For Admin:
- Login with **Email** only
- Automatic admin detection

---

## 🔐 Password Requirements

When creating new accounts, passwords must have:
- ✅ Minimum 8 characters
- ✅ At least 1 uppercase letter
- ✅ At least 1 lowercase letter
- ✅ At least 1 number
- ✅ At least 1 special character (@, #, $, !, etc.)

**Valid Examples:**
- `Admin@123`
- `Doctor@123`
- `Test@123`
- `MyPass@2026`

**Invalid Examples:**
- `admin123` (no uppercase, no special char)
- `ADMIN123` (no lowercase, no special char)
- `Admin@12` (too short)

---

## 🧪 Testing Accounts

### Create New Test Patient
1. Go to registration page
2. Use these details:
   - **Name:** Your Name
   - **Email:** youremail@gmail.com (must be @gmail.com)
   - **Phone:** 9876543210 (exactly 10 digits)
   - **Password:** Test@1234
3. Submit and login

### Create New Test Doctor
1. Go to doctor registration page
2. Fill in details
3. Wait for admin approval
4. Login after approval

---

## 🚨 Important Notes

### OTP Verification
- ✅ **Registration:** No OTP required (fast registration)
- ✅ **Login:** OTP required (sent to email)
- ⏱️ **OTP Expiry:** 5 minutes
- 🔄 **Resend Cooldown:** 60 seconds
- 🔢 **Max Attempts:** 3 per OTP

### Email Requirements
- **Patients:** Must use @gmail.com email
- **Doctors/Admin:** Any professional email domain allowed

### Phone Requirements
- **Format:** Exactly 10 digits
- **Valid:** 9876543210
- **Invalid:** 98765432101 (too long), 987654321 (too short)

---

## 🔄 Password Reset

If you forget your password:
1. Click "Forgot Password" on login page
2. Enter your email
3. Receive OTP via email
4. Verify OTP
5. Set new password

---

## 🎯 Quick Access URLs

### Main Pages
- **Home:** `http://localhost/Aarunya final/Aarunya/client/index.html`
- **Login:** `http://localhost/Aarunya final/Aarunya/client/login.php`
- **Register:** `http://localhost/Aarunya final/Aarunya/client/register.php`

### Admin Panel
- **Dashboard:** `http://localhost/Aarunya final/Aarunya/admin/pages/dashboard.php`
- **Doctors:** `http://localhost/Aarunya final/Aarunya/admin/pages/doctors.php`
- **Users:** `http://localhost/Aarunya final/Aarunya/admin/pages/users.php`
- **Appointments:** `http://localhost/Aarunya final/Aarunya/admin/pages/appointments.php`

### Doctor Portal
- **Dashboard:** `http://localhost/Aarunya final/Aarunya/doctor/dashboard.php`
- **Appointments:** `http://localhost/Aarunya final/Aarunya/doctor/appointments.php`
- **Patients:** `http://localhost/Aarunya final/Aarunya/doctor/patients.php`

### Patient Portal
- **Dashboard:** `http://localhost/Aarunya final/Aarunya/client/dashboard.php`
- **Doctors:** `http://localhost/Aarunya final/Aarunya/client/doctors.php`
- **Appointments:** `http://localhost/Aarunya final/Aarunya/client/appointments.php`
- **Health:** `http://localhost/Aarunya final/Aarunya/client/health.php`

---

## 📊 Database Information

- **Database Name:** `aarunya_db`
- **Host:** `localhost`
- **Port:** `3306`
- **Username:** `root`
- **Password:** (empty)

---

## ✅ Verification Checklist

After setting up, verify you can:

- [ ] Login as Admin
- [ ] Login as any Doctor
- [ ] Login as Test Patient
- [ ] View admin dashboard
- [ ] View doctor dashboard
- [ ] View patient dashboard
- [ ] Toggle doctor status (admin)
- [ ] Book appointment (patient)
- [ ] View appointments (all roles)

---

## 🔒 Security Notes

### Default Passwords
- ⚠️ **Change default passwords in production**
- ⚠️ **These are for testing only**
- ⚠️ **Never use default credentials in live environment**

### Password Hashing
- All passwords are hashed using **bcrypt**
- Hash algorithm: `$2y$10$` (cost factor: 10)
- Secure against rainbow table attacks

### Session Security
- Sessions expire after inactivity
- Secure session management
- CSRF protection enabled

---

## 📞 Support

If you have issues logging in:
1. Verify XAMPP is running (Apache + MySQL)
2. Check database exists: `aarunya_db`
3. Verify user exists in database
4. Clear browser cache and cookies
5. Try different browser

---

**Last Updated:** May 11, 2026  
**Version:** 2.1  
**Status:** ✅ Ready for Testing

---

## 🎉 Quick Start

**To test the system right now:**

1. Start XAMPP (Apache + MySQL)
2. Go to: `http://localhost/Aarunya final/Aarunya/client/login.php`
3. Login as:
   - **Admin:** admin@aarunya.com / Admin@123
   - **Doctor:** dr.firuza@aarunya.com / Doctor@123
   - **Patient:** test@gmail.com / Test@123
4. Enter OTP from email (for login)
5. Explore the dashboard!

**That's it! You're ready to go! 🚀**
