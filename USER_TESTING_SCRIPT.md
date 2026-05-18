# 🧪 Aarunya Healthcare - User Testing Script

**Version:** 1.0  
**Last Updated:** May 12, 2026  
**Purpose:** Comprehensive testing guide for all user roles and features

---

## 📋 Table of Contents

1. [Pre-Testing Setup](#pre-testing-setup)
2. [Patient User Testing](#patient-user-testing)
3. [Doctor User Testing](#doctor-user-testing)
4. [Admin User Testing](#admin-user-testing)
5. [Login Alert Email Testing](#login-alert-email-testing)
6. [Cross-Browser Testing](#cross-browser-testing)
7. [Mobile Responsiveness Testing](#mobile-responsiveness-testing)
8. [Security Testing](#security-testing)
9. [Performance Testing](#performance-testing)
10. [Bug Reporting Template](#bug-reporting-template)

---

## 🔧 Pre-Testing Setup

### Environment Requirements

- [ ] XAMPP/WAMP server running
- [ ] MySQL database running on port 3307
- [ ] PHP 7.4 or higher
- [ ] Modern web browser (Chrome, Firefox, Safari, Edge)
- [ ] Email client access to check login alerts

### Database Setup

```bash
# Navigate to project directory
cd "c:\xampp\htdocs\Aarunya final\Aarunya"

# Import database
mysql -u root -P 3307 < database/COMPLETE_DATABASE_SETUP.sql
```

### Test Credentials

#### Patient Account
- **Email:** `patient@test.com` or `9876543210`
- **Password:** `password123`

#### Doctor Account
- **Email:** `doctor@test.com`
- **Password:** `password123`

#### Admin Account
- **Email:** `admin@aarunya.com`
- **Password:** `admin123`

### Email Monitoring

- **Monitoring Email:** `killekarakash468@gmail.com`
- Check this inbox for all login alerts

---

## 👤 Patient User Testing

### Test Case 1: Patient Registration

**Objective:** Verify new patient can register successfully

**Steps:**
1. Navigate to `http://localhost/Aarunya final/Aarunya/client/register.php`
2. Fill in registration form:
   - Full Name: `Test Patient`
   - Email: `testpatient@example.com`
   - Phone: `9876543210`
   - Password: `Test@123`
   - Confirm Password: `Test@123`
3. Click "Create Account"

**Expected Results:**
- [ ] Form validates all fields
- [ ] Password strength indicator works
- [ ] Success message appears
- [ ] Redirected to login page
- [ ] Welcome email sent (if configured)

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 2: Patient Login (Email)

**Objective:** Verify patient can login with email

**Steps:**
1. Navigate to `http://localhost/Aarunya final/Aarunya/client/login.php`
2. Select "Patient" role
3. Enter email: `patient@test.com`
4. Enter password: `password123`
5. Click "Sign In"

**Expected Results:**
- [ ] Login successful
- [ ] Redirected to patient dashboard
- [ ] Session created
- [ ] Login alert email sent to user
- [ ] Login alert email sent to `killekarakash468@gmail.com`
- [ ] Email contains correct login details (time, IP, browser, role)

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 3: Patient Login (Phone)

**Objective:** Verify patient can login with phone number

**Steps:**
1. Navigate to login page
2. Select "Patient" role
3. Enter phone: `9876543210`
4. Enter password: `password123`
5. Click "Sign In"

**Expected Results:**
- [ ] Login successful
- [ ] Redirected to dashboard
- [ ] Login alert emails sent

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 4: Patient Dashboard Access

**Objective:** Verify patient dashboard loads correctly

**Steps:**
1. After successful login, observe dashboard
2. Check all dashboard sections

**Expected Results:**
- [ ] Dashboard loads without errors
- [ ] User name displayed correctly
- [ ] Navigation menu visible
- [ ] Health metrics section visible
- [ ] Upcoming appointments section visible
- [ ] Quick actions available

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 5: Book Appointment

**Objective:** Verify patient can book an appointment

**Steps:**
1. From dashboard, click "Book Appointment"
2. Select a doctor from the list
3. Choose available date and time
4. Enter reason for visit
5. Click "Book Appointment"

**Expected Results:**
- [ ] Doctor list loads correctly
- [ ] Calendar shows available slots
- [ ] Booking confirmation appears
- [ ] Appointment appears in "My Appointments"
- [ ] Confirmation email sent (if configured)

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 6: View Doctors

**Objective:** Verify patient can view doctor profiles

**Steps:**
1. Navigate to "Doctors" section
2. Browse doctor list
3. Click on a doctor profile
4. View doctor details

**Expected Results:**
- [ ] Doctor list displays with photos
- [ ] Specializations shown
- [ ] Ratings visible
- [ ] "Book Appointment" button available
- [ ] Doctor profile modal opens with full details

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 7: Health Metrics

**Objective:** Verify patient can view and update health metrics

**Steps:**
1. Navigate to "Health" section
2. View current health metrics
3. Click "Update Metrics"
4. Enter new values (BP, weight, glucose, etc.)
5. Save changes

**Expected Results:**
- [ ] Current metrics displayed
- [ ] Charts/graphs render correctly
- [ ] Update form validates input
- [ ] Success message on save
- [ ] Updated values reflected immediately

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 8: AI Wellness Plan

**Objective:** Verify AI wellness plan feature works

**Steps:**
1. Navigate to "AI Wellness Plan"
2. View personalized recommendations
3. Interact with chatbot (if available)

**Expected Results:**
- [ ] Wellness plan loads
- [ ] Recommendations are relevant
- [ ] Chatbot responds appropriately
- [ ] UI is user-friendly

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 9: Medical Documents

**Objective:** Verify patient can upload and view medical documents

**Steps:**
1. Navigate to "Medical Documents"
2. Click "Upload Document"
3. Select a PDF file
4. Add document details
5. Upload document
6. View uploaded document

**Expected Results:**
- [ ] Upload form accepts PDF files
- [ ] File size validation works
- [ ] Document appears in list
- [ ] Document can be downloaded
- [ ] Document can be deleted

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 10: Profile Management

**Objective:** Verify patient can update profile

**Steps:**
1. Navigate to "Profile" or "Settings"
2. Update profile information
3. Upload profile photo
4. Save changes

**Expected Results:**
- [ ] Current profile data pre-filled
- [ ] Photo upload works
- [ ] Changes save successfully
- [ ] Updated info reflected across app

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 11: Emergency Feature

**Objective:** Verify emergency contact feature

**Steps:**
1. Navigate to "Emergency" section
2. View emergency contacts
3. Test emergency call button (if applicable)

**Expected Results:**
- [ ] Emergency contacts displayed
- [ ] Emergency button prominent
- [ ] Quick access to emergency services

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 12: Patient Logout

**Objective:** Verify patient can logout successfully

**Steps:**
1. Click logout button
2. Confirm logout

**Expected Results:**
- [ ] Session destroyed
- [ ] Redirected to login page
- [ ] Cannot access dashboard without login
- [ ] Success message displayed

**Actual Results:**
```
[Record your observations here]
```

---

## 👨‍⚕️ Doctor User Testing

### Test Case 13: Doctor Login

**Objective:** Verify doctor can login successfully

**Steps:**
1. Navigate to login page
2. Select "Doctor" role
3. Enter email: `doctor@test.com`
4. Enter password: `password123`
5. Click "Sign In"

**Expected Results:**
- [ ] Login successful
- [ ] Redirected to doctor dashboard
- [ ] Login alert emails sent to doctor and monitoring email
- [ ] Email shows role as "Doctor"

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 14: Doctor Dashboard

**Objective:** Verify doctor dashboard functionality

**Steps:**
1. After login, observe dashboard
2. Check all sections

**Expected Results:**
- [ ] Dashboard loads correctly
- [ ] Today's appointments visible
- [ ] Patient statistics shown
- [ ] Quick actions available
- [ ] Navigation menu appropriate for doctor role

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 15: View Appointments

**Objective:** Verify doctor can view appointments

**Steps:**
1. Navigate to "Appointments" section
2. View appointment list
3. Filter by date/status
4. Click on an appointment

**Expected Results:**
- [ ] Appointment list displays
- [ ] Filters work correctly
- [ ] Appointment details show patient info
- [ ] Can update appointment status

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 16: Patient Records

**Objective:** Verify doctor can access patient records

**Steps:**
1. Navigate to "Patients" section
2. Search for a patient
3. View patient profile
4. Check medical history

**Expected Results:**
- [ ] Patient search works
- [ ] Patient list displays
- [ ] Can view patient details
- [ ] Medical history accessible
- [ ] Health metrics visible

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 17: Doctor Profile Management

**Objective:** Verify doctor can update their profile

**Steps:**
1. Navigate to profile settings
2. Update specialization, bio, etc.
3. Update availability schedule
4. Save changes

**Expected Results:**
- [ ] Profile form pre-filled
- [ ] All fields editable
- [ ] Changes save successfully
- [ ] Updated info visible to patients

**Actual Results:**
```
[Record your observations here]
```

---

## 🛡️ Admin User Testing

### Test Case 18: Admin Login

**Objective:** Verify admin can login successfully

**Steps:**
1. Navigate to login page
2. Select "Admin" role
3. Enter email: `admin@aarunya.com`
4. Enter password: `admin123`
5. Click "Sign In"

**Expected Results:**
- [ ] Login successful
- [ ] Redirected to admin dashboard
- [ ] Login alert emails sent
- [ ] Email shows role as "Admin"

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 19: Admin Dashboard

**Objective:** Verify admin dashboard displays system overview

**Steps:**
1. After login, observe dashboard
2. Check all statistics and metrics

**Expected Results:**
- [ ] Dashboard loads correctly
- [ ] User statistics displayed
- [ ] Appointment statistics shown
- [ ] System health metrics visible
- [ ] Charts and graphs render

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 20: User Management

**Objective:** Verify admin can manage users

**Steps:**
1. Navigate to "Users" section
2. View user list
3. Search for a user
4. Edit user details
5. Deactivate/activate user

**Expected Results:**
- [ ] User list displays all users
- [ ] Search functionality works
- [ ] Can edit user information
- [ ] Can change user status
- [ ] Changes reflect immediately

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 21: Doctor Management

**Objective:** Verify admin can manage doctors

**Steps:**
1. Navigate to "Doctors" section
2. View pending doctor registrations
3. Approve a doctor
4. Toggle doctor active status
5. View doctor details

**Expected Results:**
- [ ] Doctor list displays
- [ ] Can approve/reject doctors
- [ ] Can activate/deactivate doctors
- [ ] Status changes work correctly
- [ ] Doctor receives notification (if configured)

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 22: Appointment Management

**Objective:** Verify admin can view and manage all appointments

**Steps:**
1. Navigate to "Appointments" section
2. View all appointments
3. Filter by status/date
4. Update appointment status
5. Cancel an appointment

**Expected Results:**
- [ ] All appointments visible
- [ ] Filters work correctly
- [ ] Can modify appointments
- [ ] Status updates save
- [ ] Notifications sent (if configured)

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 23: Reports Generation

**Objective:** Verify admin can generate reports

**Steps:**
1. Navigate to "Reports" section
2. Select report type
3. Choose date range
4. Generate report
5. Export report (PDF/Excel)

**Expected Results:**
- [ ] Report options available
- [ ] Date picker works
- [ ] Report generates successfully
- [ ] Export functionality works
- [ ] Report contains accurate data

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 24: System Settings

**Objective:** Verify admin can configure system settings

**Steps:**
1. Navigate to "Settings" section
2. View current settings
3. Update configuration
4. Save changes

**Expected Results:**
- [ ] Settings page loads
- [ ] All settings editable
- [ ] Validation works
- [ ] Changes save successfully
- [ ] System reflects new settings

**Actual Results:**
```
[Record your observations here]
```

---

## 📧 Login Alert Email Testing

### Test Case 25: Patient Login Alert Email

**Objective:** Verify login alert email is sent for patient login

**Steps:**
1. Login as patient (email: `patient@test.com`)
2. Check email inbox for `patient@test.com`
3. Check monitoring inbox for `killekarakash468@gmail.com`

**Expected Results:**
- [ ] Email received in patient inbox
- [ ] Email received in monitoring inbox
- [ ] Email subject: "Login Alert - Aarunya Healthcare"
- [ ] Email contains:
  - [ ] User name
  - [ ] User email
  - [ ] Login timestamp
  - [ ] IP address
  - [ ] Browser information
  - [ ] Role: "Patient"
- [ ] Email design matches Aarunya branding
- [ ] Security warning included

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 26: Doctor Login Alert Email

**Objective:** Verify login alert email is sent for doctor login

**Steps:**
1. Login as doctor (email: `doctor@test.com`)
2. Check both email inboxes

**Expected Results:**
- [ ] Email received in doctor inbox
- [ ] Email received in monitoring inbox
- [ ] Role shows as "Doctor"
- [ ] All login details present

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 27: Admin Login Alert Email

**Objective:** Verify login alert email is sent for admin login

**Steps:**
1. Login as admin (email: `admin@aarunya.com`)
2. Check both email inboxes

**Expected Results:**
- [ ] Email received in admin inbox
- [ ] Email received in monitoring inbox
- [ ] Role shows as "Admin"
- [ ] All login details present

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 28: Multiple Login Alerts

**Objective:** Verify multiple logins generate separate alerts

**Steps:**
1. Login as patient
2. Logout
3. Login as doctor
4. Logout
5. Login as admin
6. Check monitoring email inbox

**Expected Results:**
- [ ] Three separate emails received
- [ ] Each email has correct user details
- [ ] Each email has correct role
- [ ] Timestamps are accurate
- [ ] No emails are missing

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 29: Email Delivery Time

**Objective:** Measure email delivery time

**Steps:**
1. Note current time
2. Login to system
3. Note time when email arrives

**Expected Results:**
- [ ] Email arrives within 1-2 minutes
- [ ] Login is not delayed by email sending
- [ ] System remains responsive

**Actual Results:**
```
Login Time: [Record time]
Email Arrival Time: [Record time]
Delay: [Calculate difference]
```

---

### Test Case 30: Email Content Accuracy

**Objective:** Verify all email details are accurate

**Steps:**
1. Login from different browsers (Chrome, Firefox, Edge)
2. Check if browser is correctly identified in email
3. Verify IP address matches
4. Verify timestamp is correct

**Expected Results:**
- [ ] Browser correctly identified
- [ ] IP address accurate
- [ ] Timestamp in correct timezone (Asia/Kolkata)
- [ ] All user details correct

**Actual Results:**
```
[Record your observations here]
```

---

## 🌐 Cross-Browser Testing

### Test Case 31: Chrome Browser

**Objective:** Verify application works in Chrome

**Steps:**
1. Open application in Google Chrome
2. Test login functionality
3. Navigate through all pages
4. Test key features

**Expected Results:**
- [ ] All pages load correctly
- [ ] CSS styles render properly
- [ ] JavaScript functions work
- [ ] No console errors
- [ ] Responsive design works

**Actual Results:**
```
Chrome Version: [Record version]
[Record your observations here]
```

---

### Test Case 32: Firefox Browser

**Objective:** Verify application works in Firefox

**Steps:**
1. Open application in Mozilla Firefox
2. Repeat all key tests

**Expected Results:**
- [ ] All functionality works
- [ ] No browser-specific issues
- [ ] Performance is acceptable

**Actual Results:**
```
Firefox Version: [Record version]
[Record your observations here]
```

---

### Test Case 33: Edge Browser

**Objective:** Verify application works in Edge

**Steps:**
1. Open application in Microsoft Edge
2. Repeat all key tests

**Expected Results:**
- [ ] All functionality works
- [ ] No browser-specific issues

**Actual Results:**
```
Edge Version: [Record version]
[Record your observations here]
```

---

### Test Case 34: Safari Browser (Mac)

**Objective:** Verify application works in Safari

**Steps:**
1. Open application in Safari
2. Repeat all key tests

**Expected Results:**
- [ ] All functionality works
- [ ] No browser-specific issues

**Actual Results:**
```
Safari Version: [Record version]
[Record your observations here]
```

---

## 📱 Mobile Responsiveness Testing

### Test Case 35: Mobile Login

**Objective:** Verify login works on mobile devices

**Steps:**
1. Open application on mobile device or use browser dev tools
2. Test login for all roles
3. Check responsive design

**Expected Results:**
- [ ] Login form displays correctly
- [ ] Touch interactions work
- [ ] Keyboard doesn't obscure inputs
- [ ] Role toggle works on mobile

**Actual Results:**
```
Device/Screen Size: [Record details]
[Record your observations here]
```

---

### Test Case 36: Mobile Navigation

**Objective:** Verify navigation works on mobile

**Steps:**
1. Login on mobile
2. Test hamburger menu
3. Navigate between pages

**Expected Results:**
- [ ] Menu opens/closes smoothly
- [ ] All menu items accessible
- [ ] Navigation is intuitive

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 37: Mobile Dashboard

**Objective:** Verify dashboard is mobile-friendly

**Steps:**
1. View dashboard on mobile
2. Check all sections
3. Test interactions

**Expected Results:**
- [ ] Dashboard adapts to screen size
- [ ] Cards stack vertically
- [ ] Charts are readable
- [ ] Buttons are touch-friendly

**Actual Results:**
```
[Record your observations here]
```

---

## 🔒 Security Testing

### Test Case 38: Invalid Login Attempts

**Objective:** Verify system handles invalid login attempts

**Steps:**
1. Attempt login with wrong password
2. Attempt login with non-existent email
3. Attempt SQL injection in login form

**Expected Results:**
- [ ] Error message displayed
- [ ] No sensitive information leaked
- [ ] Account not locked after few attempts
- [ ] SQL injection prevented

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 39: Session Management

**Objective:** Verify session security

**Steps:**
1. Login successfully
2. Copy session cookie
3. Logout
4. Try to use old session cookie

**Expected Results:**
- [ ] Session destroyed on logout
- [ ] Old session cannot be reused
- [ ] Session timeout works

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 40: Password Security

**Objective:** Verify password requirements

**Steps:**
1. Try to register with weak password
2. Try to register with common password
3. Verify password is hashed in database

**Expected Results:**
- [ ] Weak passwords rejected
- [ ] Password strength indicator works
- [ ] Passwords stored as hashes
- [ ] No plain text passwords

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 41: Role-Based Access Control

**Objective:** Verify users can only access authorized pages

**Steps:**
1. Login as patient
2. Try to access admin dashboard directly
3. Try to access doctor pages

**Expected Results:**
- [ ] Unauthorized access blocked
- [ ] Redirected to appropriate page
- [ ] Error message displayed

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 42: XSS Prevention

**Objective:** Verify XSS attacks are prevented

**Steps:**
1. Try to inject JavaScript in form fields
2. Try to inject HTML in text areas
3. Check if input is sanitized

**Expected Results:**
- [ ] Scripts not executed
- [ ] HTML tags escaped
- [ ] Input properly sanitized

**Actual Results:**
```
[Record your observations here]
```

---

## ⚡ Performance Testing

### Test Case 43: Page Load Time

**Objective:** Measure page load times

**Steps:**
1. Clear browser cache
2. Load login page
3. Measure load time
4. Repeat for dashboard and other pages

**Expected Results:**
- [ ] Login page loads in < 2 seconds
- [ ] Dashboard loads in < 3 seconds
- [ ] No excessive resource loading

**Actual Results:**
```
Login Page: [Record time]
Dashboard: [Record time]
Other Pages: [Record times]
```

---

### Test Case 44: Database Query Performance

**Objective:** Verify database queries are optimized

**Steps:**
1. Enable MySQL slow query log
2. Perform various operations
3. Check for slow queries

**Expected Results:**
- [ ] No queries taking > 1 second
- [ ] Proper indexing used
- [ ] No N+1 query problems

**Actual Results:**
```
[Record your observations here]
```

---

### Test Case 45: Concurrent Users

**Objective:** Test system with multiple users

**Steps:**
1. Have multiple users login simultaneously
2. Perform operations concurrently
3. Monitor system performance

**Expected Results:**
- [ ] System remains responsive
- [ ] No conflicts or errors
- [ ] Data integrity maintained

**Actual Results:**
```
Number of Concurrent Users: [Record number]
[Record your observations here]
```

---

## 🐛 Bug Reporting Template

When you find a bug, use this template:

### Bug Report #[Number]

**Title:** [Brief description]

**Severity:** 
- [ ] Critical (System crash, data loss)
- [ ] High (Major feature broken)
- [ ] Medium (Feature partially working)
- [ ] Low (Minor UI issue)

**Priority:**
- [ ] Urgent (Fix immediately)
- [ ] High (Fix soon)
- [ ] Medium (Fix in next release)
- [ ] Low (Fix when possible)

**Environment:**
- Browser: [Browser name and version]
- OS: [Operating system]
- Screen Size: [Desktop/Mobile/Tablet]
- User Role: [Patient/Doctor/Admin]

**Steps to Reproduce:**
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected Behavior:**
[What should happen]

**Actual Behavior:**
[What actually happens]

**Screenshots:**
[Attach screenshots if applicable]

**Console Errors:**
```
[Paste any console errors here]
```

**Additional Notes:**
[Any other relevant information]

---

## ✅ Testing Checklist Summary

### Patient Features
- [ ] Registration
- [ ] Login (Email & Phone)
- [ ] Dashboard
- [ ] Book Appointment
- [ ] View Doctors
- [ ] Health Metrics
- [ ] AI Wellness Plan
- [ ] Medical Documents
- [ ] Profile Management
- [ ] Emergency Feature
- [ ] Logout

### Doctor Features
- [ ] Login
- [ ] Dashboard
- [ ] View Appointments
- [ ] Patient Records
- [ ] Profile Management

### Admin Features
- [ ] Login
- [ ] Dashboard
- [ ] User Management
- [ ] Doctor Management
- [ ] Appointment Management
- [ ] Reports Generation
- [ ] System Settings

### Login Alert Emails
- [ ] Patient login alert
- [ ] Doctor login alert
- [ ] Admin login alert
- [ ] Email to user
- [ ] Email to monitoring address
- [ ] Email content accuracy
- [ ] Email delivery time

### Cross-Platform
- [ ] Chrome
- [ ] Firefox
- [ ] Edge
- [ ] Safari
- [ ] Mobile Responsive

### Security
- [ ] Invalid login attempts
- [ ] Session management
- [ ] Password security
- [ ] Role-based access
- [ ] XSS prevention

### Performance
- [ ] Page load times
- [ ] Database performance
- [ ] Concurrent users

---

## 📊 Test Results Summary

**Testing Date:** [Date]  
**Tester Name:** [Name]  
**Total Test Cases:** 45  
**Passed:** [Number]  
**Failed:** [Number]  
**Blocked:** [Number]  
**Not Tested:** [Number]

**Overall Status:** [Pass/Fail]

**Critical Issues Found:** [Number]

**Recommendations:**
```
[List your recommendations here]
```

---

## 📞 Support Contact

If you encounter any issues during testing:

- **Email:** support@aarunya.com
- **Monitoring Email:** killekarakash468@gmail.com
- **Developer:** [Contact information]

---

**End of Testing Script**

*Thank you for testing Aarunya Healthcare! Your feedback helps us improve the system.*
