# 🚀 Quick Start Guide - OTP Registration System

**For:** Developers & Testers  
**Time Required:** 5 minutes  
**Difficulty:** Easy

---

## 📋 Prerequisites

- ✅ XAMPP/WAMP running
- ✅ MySQL running on port 3307
- ✅ PHP 7.4 or higher
- ✅ Email account for testing

---

## ⚡ Quick Setup (3 Steps)

### Step 1: Create Database Tables

Open MySQL command line or phpMyAdmin and run:

```bash
mysql -u root -P 3307 aarunya_db < "c:\xampp\htdocs\Aarunya final\Aarunya\database\migrations\003_create_otp_table.sql"
```

**Or** use phpMyAdmin:
1. Open http://localhost/phpmyadmin
2. Select `aarunya_db` database
3. Go to "Import" tab
4. Choose file: `database/migrations/003_create_otp_table.sql`
5. Click "Go"

**Verify:** Check that `otp_codes` and `otp_attempts` tables exist.

---

### Step 2: Verify Email Configuration

Open `.env` file and confirm these settings:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=bcavjay@gmail.com
MAIL_PASSWORD=xght zfnz jkvn mhan
MAIL_FROM_ADDRESS=bcavjay@gmail.com
MAIL_FROM_NAME="Aarunya Healthcare"
```

✅ **Already configured!** No changes needed.

---

### Step 3: Test the System

1. Open browser: `http://localhost/Aarunya final/Aarunya/client/register.php`
2. Enter your name and email
3. Click "Send Verification Code"
4. Check your email for OTP
5. Enter OTP and click "Verify Code"
6. Complete registration form
7. Submit!

---

## 🎯 What's New?

### Before vs After

| Feature | Before | After |
|---------|--------|-------|
| Email Verification | ❌ None | ✅ Required |
| OTP System | ❌ No | ✅ Yes |
| Spam Prevention | ❌ Weak | ✅ Strong |
| Security | ⚠️ Basic | ✅ Enhanced |
| Doctors Availability | ❌ Shows "Unavailable" | ✅ Shows "AVAILABLE" |

---

## 🔄 Registration Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    NEW REGISTRATION FLOW                     │
└─────────────────────────────────────────────────────────────┘

Step 1: Email Verification
┌──────────────────────┐
│  Enter Name & Email  │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  Click "Send OTP"    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  Receive OTP Email   │  ← 6-digit code sent
└──────────┬───────────┘
           │
           ▼
Step 2: OTP Verification
┌──────────────────────┐
│   Enter OTP Code     │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  Click "Verify"      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  ✓ Email Verified    │
└──────────┬───────────┘
           │
           ▼
Step 3: Complete Registration
┌──────────────────────┐
│  Fill Form Details   │  ← Email pre-filled & locked
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  Submit Registration │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│  ✓ Account Created   │
└──────────────────────┘
```

---

## 🧪 Quick Test Cases

### Test 1: Happy Path ✅
```
1. Enter: name="Test User", email="your@email.com"
2. Click "Send Verification Code"
3. Check email inbox
4. Enter OTP code
5. Click "Verify Code"
6. Complete form
7. Submit

Expected: ✅ Account created successfully
```

### Test 2: Wrong OTP ❌
```
1. Request OTP
2. Enter wrong code: "000000"
3. Click "Verify Code"

Expected: ❌ "Invalid OTP code. 2 attempts remaining."
```

### Test 3: Expired OTP ⏰
```
1. Request OTP
2. Wait 6 minutes
3. Enter OTP code
4. Click "Verify Code"

Expected: ❌ "Invalid or expired OTP. Please request a new one."
```

### Test 4: Duplicate Email 🔄
```
1. Try to register with existing email
2. Click "Send Verification Code"

Expected: ❌ "This email is already registered. Please login instead."
```

---

## 🔍 Troubleshooting

### Problem: OTP Email Not Received

**Check:**
1. ✉️ Spam/Junk folder
2. ⚙️ SMTP settings in `.env`
3. 📧 Try different email provider (Gmail, Yahoo, Outlook)

**Test Email Manually:**
```php
<?php
require_once 'server/includes/MailService.php';
$mail = new MailService();
$result = $mail->send('your@email.com', 'Test', 'Test email body');
echo $result ? 'Success!' : 'Failed!';
?>
```

---

### Problem: "Email verification required" Error

**Cause:** Trying to register without OTP verification

**Solution:** Complete OTP verification first (Steps 1-2)

---

### Problem: Database Tables Missing

**Solution:**
```bash
# Run migration again
mysql -u root -P 3307 aarunya_db < database/migrations/003_create_otp_table.sql
```

**Verify:**
```sql
SHOW TABLES LIKE 'otp%';
-- Should show: otp_codes, otp_attempts
```

---

### Problem: Doctors Show "Unavailable"

**Solution:** Already fixed! Refresh the doctors page.

**Verify:**
```
1. Login as patient
2. Go to Doctors page
3. All active doctors should show "AVAILABLE" badge
```

---

## 📧 Email Templates

### OTP Email Preview

```
┌─────────────────────────────────────────┐
│  🌸 Aarunya Healthcare                  │
│  Your OTP Verification Code             │
├─────────────────────────────────────────┤
│                                         │
│  Hello Test User,                       │
│                                         │
│  Your One-Time Password (OTP) is:      │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │         1  2  3  4  5  6        │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Important:                             │
│  • Valid for 5 minutes                  │
│  • Do not share this code               │
│  • If you didn't request this, ignore  │
│                                         │
│  Thank you for choosing Aarunya!        │
│                                         │
├─────────────────────────────────────────┤
│  © 2026 Aarunya Healthcare              │
│  This is an automated email             │
└─────────────────────────────────────────┘
```

---

## 🎨 UI Preview

### Step 1: Email Verification
```
┌────────────────────────────────────────┐
│  🌸 Aarunya                            │
│  Create Your Account                   │
│  Join thousands of mothers...          │
├────────────────────────────────────────┤
│  ① Email Verification                  │
│  ② Account Info                        │
│  ③ Complete                            │
├────────────────────────────────────────┤
│  Full Name *                           │
│  [👤 Enter your full name          ]  │
│                                        │
│  Email Address *                       │
│  [✉️ example@gmail.com             ]  │
│  We'll send a verification code        │
│                                        │
│  [  Send Verification Code  ]          │
└────────────────────────────────────────┘
```

### Step 2: OTP Input
```
┌────────────────────────────────────────┐
│  Enter OTP Code *                      │
│  [🔑 Enter 6-digit OTP             ]  │
│  Code expires in 4:35                  │
│                                        │
│  [     Verify Code      ]              │
│  [     Resend Code      ]              │
└────────────────────────────────────────┘
```

---

## 📊 System Status Check

Run these queries to check system health:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'otp%';

-- Check recent OTP activity
SELECT COUNT(*) as total_otps_today
FROM otp_codes
WHERE DATE(created_at) = CURDATE();

-- Check verification success rate
SELECT 
    COUNT(*) as total,
    SUM(verified) as verified,
    ROUND(SUM(verified) / COUNT(*) * 100, 2) as success_rate
FROM otp_codes
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Check for rate limit violations
SELECT email, COUNT(*) as attempts
FROM otp_attempts
WHERE attempt_type = 'send'
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY email
HAVING attempts >= 5;
```

---

## 🔐 Security Features

| Feature | Status | Description |
|---------|--------|-------------|
| Rate Limiting | ✅ Active | Max 5 OTP/hour per email |
| Attempt Limiting | ✅ Active | Max 3 verification attempts |
| Time Expiry | ✅ Active | OTP expires in 5 minutes |
| Resend Cooldown | ✅ Active | 60 seconds between resends |
| Session Validation | ✅ Active | 10-minute session validity |
| Duplicate Check | ✅ Active | Prevents duplicate emails |

---

## 📱 Mobile Testing

Test on mobile devices:

1. Open Chrome DevTools (F12)
2. Click device toolbar icon
3. Select device (iPhone, iPad, etc.)
4. Test registration flow
5. Verify responsive design

**Expected:**
- ✅ Form fields are touch-friendly
- ✅ Buttons are large enough
- ✅ Text is readable
- ✅ No horizontal scrolling

---

## 🎯 Success Criteria

Your system is working correctly if:

- ✅ OTP emails arrive within 1 minute
- ✅ OTP verification works with correct code
- ✅ Registration completes after OTP verification
- ✅ Doctors page shows "AVAILABLE" badges
- ✅ Login alert emails are sent
- ✅ Welcome emails are sent after registration
- ✅ No console errors in browser
- ✅ No PHP errors in logs

---

## 📚 Additional Resources

- **Full Documentation:** `OTP_REGISTRATION_GUIDE.md`
- **Testing Guide:** `USER_TESTING_SCRIPT.md`
- **Changes Summary:** `CHANGES_SUMMARY.md`
- **This Guide:** `QUICK_START_GUIDE.md`

---

## 🆘 Need Help?

### Quick Fixes

**Clear browser cache:**
```
Ctrl + Shift + Delete (Chrome/Firefox)
```

**Restart MySQL:**
```bash
# In XAMPP Control Panel
Stop MySQL → Start MySQL
```

**Check PHP errors:**
```
Location: C:\xampp\php\logs\php_error_log
```

### Contact

- **Email:** support@aarunya.com
- **Monitoring:** killekarakash468@gmail.com

---

## ✅ Final Checklist

Before considering setup complete:

- [ ] Database tables created
- [ ] SMTP configuration verified
- [ ] Test OTP sent and received
- [ ] Test OTP verification successful
- [ ] Test registration completed
- [ ] Doctors page checked (no "Unavailable")
- [ ] Login alert email received
- [ ] Welcome email received
- [ ] Mobile responsiveness tested
- [ ] All test cases passed

---

**🎉 Congratulations!**

Your OTP-based registration system is now live and ready to use!

---

**End of Quick Start Guide**

*Get started in 5 minutes!*
