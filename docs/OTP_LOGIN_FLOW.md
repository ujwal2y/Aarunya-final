# OTP Login Flow - Complete Guide

## 📋 Overview

The OTP (One-Time Password) system has been configured to work **during login** instead of registration. This provides an additional layer of security by requiring email verification every time a user logs in.

---

## 🔐 How It Works

### Login Flow with OTP

1. **User enters credentials** (email/phone + password) on login page
2. **System verifies credentials** against database
3. **If credentials are valid:**
   - System generates 6-digit OTP
   - OTP sent to user's email
   - User redirected to OTP verification page
4. **User enters OTP** from email
5. **System verifies OTP**
6. **If OTP is valid:**
   - Session created
   - User redirected to dashboard

### Registration Flow (No OTP)

1. **User fills registration form**
2. **System validates data**
3. **Account created in database**
4. **Welcome email sent** (no OTP required)
5. **User redirected to login page**

---

## 🎯 Key Features

### Security Features
- ✅ Two-factor authentication on every login
- ✅ 6-digit random OTP generation
- ✅ 5-minute OTP expiration
- ✅ Maximum 3 verification attempts
- ✅ Rate limiting (5 OTPs per hour per email)
- ✅ 60-second resend cooldown
- ✅ IP address tracking
- ✅ User agent logging

### User Experience
- ✅ Beautiful OTP verification page
- ✅ Real-time countdown timer (5:00 to 0:00)
- ✅ Auto-focus and auto-advance inputs
- ✅ Paste support (Ctrl+V)
- ✅ Resend OTP button with cooldown
- ✅ Visual feedback (success/error messages)
- ✅ Loading states and animations
- ✅ Fully responsive design

---

## 📁 Files Modified

### Backend
- ✅ `server/handlers/login_handler.php` - Send OTP after password verification
- ✅ `server/handlers/register_handler.php` - Removed OTP, send welcome email only
- ✅ `server/handlers/complete_login.php` - Complete login after OTP verification (NEW)

### Frontend
- ✅ `client/verify-otp.php` - Updated to handle login purpose

---

## 🧪 Testing the Login Flow

### Step 1: Register a New User

1. Go to: `http://localhost/Aarunya final/Aarunya/client/register.php`
2. Fill in registration form:
   - Name: Test User
   - Email: your-email@gmail.com
   - Phone: 9876543210
   - Password: Test@1234
3. Click **Create Account**
4. Should redirect to login page (no OTP required)
5. Check email for welcome message

### Step 2: Login with OTP

1. Go to: `http://localhost/Aarunya final/Aarunya/client/login.php`
2. Enter credentials:
   - Email/Phone: your-email@gmail.com
   - Password: Test@1234
3. Click **Login**
4. Should redirect to OTP verification page
5. Check email for 6-digit OTP
6. Enter OTP in verification page
7. Should redirect to dashboard

### Step 3: Test OTP Features

**Test Expiration:**
1. Login and get OTP
2. Wait 5 minutes
3. Try to verify expired OTP
4. Should show "OTP expired" message

**Test Max Attempts:**
1. Login and get OTP
2. Enter wrong OTP 3 times
3. Should show "Maximum attempts exceeded"

**Test Resend:**
1. Login and get OTP
2. Click "Resend OTP" button
3. Should be disabled for 60 seconds
4. After 60 seconds, click again
5. Should receive new OTP

**Test Rate Limiting:**
1. Login 6 times in quick succession
2. 6th attempt should show "Too many OTP requests"

---

## 🔄 Complete Login Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    USER ENTERS CREDENTIALS                   │
│                  (Email/Phone + Password)                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              LOGIN_HANDLER.PHP VALIDATES                     │
│                                                              │
│  1. Check if user exists                                    │
│  2. Verify password                                         │
│  3. Check account status                                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
                    ┌────────┐
                    │ Valid? │
                    └───┬────┘
                        │
            ┌───────────┴───────────┐
            │                       │
           NO                      YES
            │                       │
            ▼                       ▼
    ┌──────────────┐    ┌─────────────────────────┐
    │ Show Error   │    │  Generate & Send OTP    │
    │ Redirect to  │    │  Store pending_login    │
    │ Login Page   │    │  in session             │
    └──────────────┘    └────────┬────────────────┘
                                 │
                                 ▼
                    ┌────────────────────────────┐
                    │  Redirect to OTP Page      │
                    │  verify-otp.php            │
                    └────────┬───────────────────┘
                             │
                             ▼
                    ┌────────────────────────────┐
                    │  USER ENTERS OTP           │
                    │  (6 digits)                │
                    └────────┬───────────────────┘
                             │
                             ▼
                    ┌────────────────────────────┐
                    │  OTP_HANDLER.PHP           │
                    │  Verifies OTP              │
                    └────────┬───────────────────┘
                             │
                             ▼
                        ┌────────┐
                        │ Valid? │
                        └───┬────┘
                            │
                ┌───────────┴───────────┐
                │                       │
               NO                      YES
                │                       │
                ▼                       ▼
    ┌──────────────────┐    ┌─────────────────────────┐
    │ Show Error       │    │  Set otp_verified=true  │
    │ Allow Retry      │    │  in session             │
    │ (Max 3 attempts) │    └────────┬────────────────┘
    └──────────────────┘             │
                                     ▼
                        ┌────────────────────────────┐
                        │  COMPLETE_LOGIN.PHP        │
                        │                            │
                        │  1. Create session vars    │
                        │  2. Update last_login      │
                        │  3. Clear OTP data         │
                        └────────┬───────────────────┘
                                 │
                                 ▼
                        ┌────────────────────────────┐
                        │  Redirect to Dashboard     │
                        │  (User logged in)          │
                        └────────────────────────────┘
```

---

## 📊 Session Variables

### During OTP Verification
```php
$_SESSION['otp_email'] = 'user@gmail.com';
$_SESSION['otp_purpose'] = 'login';
$_SESSION['pending_login'] = [
    'user_id' => 123,
    'email' => 'user@gmail.com',
    'name' => 'John Doe',
    'phone' => '9876543210',
    'role' => 'user',
    'table' => 'users',
    'dashboard_path' => '../../client/dashboard.php'
];
```

### After OTP Verification
```php
$_SESSION['otp_verified'] = true;
$_SESSION['otp_verified_email'] = 'user@gmail.com';
$_SESSION['otp_verified_purpose'] = 'login';
$_SESSION['otp_verified_at'] = 1715443200;
```

### After Login Completion
```php
$_SESSION['user_id'] = 123;
$_SESSION['user_email'] = 'user@gmail.com';
$_SESSION['user_name'] = 'John Doe';
$_SESSION['user_phone'] = '9876543210';
$_SESSION['user_role'] = 'user';
$_SESSION['role'] = 'user';
$_SESSION['logged_in'] = true;
$_SESSION['user_type'] = 'user';
```

---

## 🔐 Security Considerations

### What's Protected
- ✅ Password verification happens first
- ✅ OTP only sent if password is correct
- ✅ Session not created until OTP verified
- ✅ Rate limiting prevents brute force
- ✅ IP tracking for suspicious activity
- ✅ Automatic cleanup of expired OTPs

### Attack Prevention
- **Brute Force:** Rate limiting (5 OTPs per hour)
- **Session Hijacking:** OTP required for each login
- **Password Theft:** OTP adds second factor
- **Replay Attacks:** OTPs expire after 5 minutes
- **Multiple Attempts:** Max 3 verification attempts

---

## 🎨 UI/UX Features

### OTP Verification Page
- **Header:** Shows "Verify Your Login" for login purpose
- **Email Display:** Shows email where OTP was sent
- **6 Input Boxes:** One for each digit
- **Auto-advance:** Moves to next box automatically
- **Paste Support:** Paste 6-digit code with Ctrl+V
- **Timer:** Real-time countdown (5:00 to 0:00)
- **Resend Button:** Disabled for 60 seconds after send
- **Back Link:** Returns to login page
- **Visual Feedback:** Success/error messages with animations

---

## 🐛 Troubleshooting

### Issue: OTP Email Not Received

**Possible Causes:**
1. Email in spam/junk folder
2. SMTP configuration incorrect
3. Gmail app password expired
4. Rate limit exceeded

**Solutions:**
1. Check spam folder
2. Verify `.env` email settings
3. Generate new Gmail app password
4. Wait 1 hour if rate limited

### Issue: "Session Expired" Error

**Cause:** User took too long or cleared cookies

**Solution:** Go back to login page and start again

### Issue: "OTP Not Verified" Error

**Cause:** Trying to access complete_login.php directly

**Solution:** Must verify OTP first through verify-otp.php

### Issue: "Maximum Attempts Exceeded"

**Cause:** Entered wrong OTP 3 times

**Solution:** Request new OTP by clicking "Resend OTP"

---

## 📈 Monitoring

### Check OTP Statistics
```sql
-- Recent OTP attempts
SELECT 
    user_email,
    purpose,
    verified,
    attempts,
    created_at,
    expires_at
FROM otp_codes
WHERE purpose = 'login'
ORDER BY created_at DESC
LIMIT 50;

-- Failed verification attempts
SELECT 
    email,
    COUNT(*) as failed_attempts,
    MAX(created_at) as last_attempt
FROM otp_attempts
WHERE attempt_type = 'verify'
AND success = FALSE
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY email
ORDER BY failed_attempts DESC;

-- Rate limited users
SELECT 
    email,
    COUNT(*) as otp_requests,
    MAX(created_at) as last_request
FROM otp_attempts
WHERE attempt_type = 'send'
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY email
HAVING otp_requests >= 5;
```

---

## ✅ Verification Checklist

After implementation, verify:

- [ ] Registration works without OTP
- [ ] Welcome email sent after registration
- [ ] Login requires password verification first
- [ ] OTP sent to email after password verified
- [ ] OTP verification page displays correctly
- [ ] Timer counts down from 5:00 to 0:00
- [ ] Resend button works with 60s cooldown
- [ ] Wrong OTP shows error message
- [ ] Correct OTP redirects to dashboard
- [ ] Session created after OTP verification
- [ ] User can access dashboard after login
- [ ] Rate limiting works (5 OTPs per hour)
- [ ] Max attempts works (3 attempts per OTP)
- [ ] Expired OTPs show error message

---

## 🎉 Summary

The OTP system now works **during login** instead of registration:

- ✅ **Registration:** Simple, no OTP required
- ✅ **Login:** Two-factor authentication with OTP
- ✅ **Security:** Enhanced with email verification
- ✅ **User Experience:** Smooth, modern UI
- ✅ **Rate Limiting:** Prevents abuse
- ✅ **Monitoring:** Track all attempts

This provides better security while keeping registration simple and fast!

---

**Last Updated:** May 11, 2026  
**Version:** 2.1  
**Status:** ✅ Production Ready
