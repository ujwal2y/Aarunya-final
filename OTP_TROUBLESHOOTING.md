# 🔧 OTP System Troubleshooting Guide

**Issue:** "Invalid or expired OTP" even with correct code

---

## 🚀 Quick Fix Steps

### Step 1: Check Database Tables

Open: `http://localhost/Aarunya final/Aarunya/test_otp_system.php`

**What to check:**
- ✅ Both tables exist (`otp_codes` and `otp_attempts`)
- ✅ Recent OTPs are being stored
- ✅ Server time is correct

**If tables don't exist:**
1. Open: `http://localhost/Aarunya final/Aarunya/setup_otp_system.php`
2. Click "Run Setup"
3. Verify success message

---

### Step 2: Test OTP Generation

1. Open: `http://localhost/Aarunya final/Aarunya/test_otp_system.php`
2. Scroll to "Test OTP System" section
3. Enter your email
4. Click "Send Test OTP"
5. Refresh the page
6. Check the "Recent OTP Codes" table
7. You should see your OTP code there

**What to verify:**
- ✅ OTP code appears in table
- ✅ Status shows "Valid" (not "Expired")
- ✅ Verified shows "No"
- ✅ Expires At is in the future

---

### Step 3: Check PHP Error Logs

**Location:** `C:\xampp\php\logs\php_error_log`

**Look for these log entries:**
```
OTP Generated - Email: test@example.com, Code: 123456, Expires: 2026-05-14 13:00:00
OTP Debug - Email: test@example.com, Code in DB: 123456, Status: valid
OTP Verification Attempt - Submitted Code: 123456, DB Code: 123456, Match: YES
```

**If you see "Match: NO":**
- The code you're entering doesn't match the database
- Check for extra spaces or characters
- Make sure you're using the latest OTP (not an old one)

---

## 🐛 Common Issues & Solutions

### Issue 1: Tables Don't Exist

**Symptoms:**
- Error: "Table 'otp_codes' doesn't exist"
- No OTPs in test page

**Solution:**
```bash
# Run setup script
Open: http://localhost/Aarunya final/Aarunya/setup_otp_system.php
Click: "Run Setup"
```

---

### Issue 2: OTP Immediately Expires

**Symptoms:**
- OTP shows as "Expired" right after generation
- Status in table shows "Expired"

**Cause:** Server time mismatch

**Solution:**
1. Check server times in test page
2. If MySQL and PHP times differ by more than 1 minute:
   ```sql
   -- Check MySQL timezone
   SELECT @@global.time_zone, @@session.time_zone;
   
   -- Set to system time
   SET GLOBAL time_zone = 'SYSTEM';
   ```

---

### Issue 3: OTP Not Found in Database

**Symptoms:**
- "No OTP found for email" in logs
- Table is empty after sending OTP

**Possible Causes:**
1. Email sending failed
2. Database insert failed
3. Wrong database selected

**Solution:**
1. Check PHP error log for database errors
2. Verify database connection in `.env`:
   ```env
   DB_HOST=localhost
   DB_PORT=3307
   DB_NAME=aarunya_db
   DB_USER=root
   DB_PASS=
   ```
3. Test database connection:
   ```php
   <?php
   require_once 'server/config/database.php';
   $pdo = getDB();
   echo "Connected!";
   ?>
   ```

---

### Issue 4: Wrong OTP Code in Email vs Database

**Symptoms:**
- Email shows one code
- Database shows different code

**Cause:** Multiple OTPs generated quickly

**Solution:**
1. Wait 60 seconds between OTP requests (cooldown period)
2. Always use the LATEST OTP code
3. Check "Recent OTP Codes" table for the newest entry

---

### Issue 5: "Already Verified" Error

**Symptoms:**
- OTP was verified once
- Can't use it again

**Cause:** OTP can only be used once

**Solution:**
- Request a new OTP
- Each OTP is single-use for security

---

## 🔍 Debug Checklist

Use this checklist to diagnose issues:

### Database
- [ ] Tables `otp_codes` and `otp_attempts` exist
- [ ] Can insert records into tables
- [ ] Can query records from tables
- [ ] Server time is correct

### OTP Generation
- [ ] OTP appears in database after sending
- [ ] OTP code is 6 digits
- [ ] Expires_at is 5 minutes in future
- [ ] Email is sent successfully

### OTP Verification
- [ ] OTP exists in database
- [ ] OTP is not expired (expires_at > NOW())
- [ ] OTP is not verified (verified = FALSE)
- [ ] Attempts < 3
- [ ] Code matches exactly

### Configuration
- [ ] `.env` file has correct settings
- [ ] SMTP settings are correct
- [ ] Database connection works
- [ ] PHP error logging enabled

---

## 🧪 Manual Testing Steps

### Test 1: Complete Flow

1. **Generate OTP:**
   ```
   Open: http://localhost/Aarunya final/Aarunya/test_otp_system.php
   Enter email: your@email.com
   Click: "Send Test OTP"
   ```

2. **Check Database:**
   ```
   Refresh page
   Look at "Recent OTP Codes" table
   Note the OTP code (e.g., 123456)
   Verify Status = "Valid"
   ```

3. **Test Verification:**
   ```
   Open browser console (F12)
   Run this JavaScript:
   
   fetch('../server/api/otp_handler.php', {
       method: 'POST',
       headers: {'Content-Type': 'application/json'},
       body: JSON.stringify({
           action: 'verify_otp',
           email: 'your@email.com',
           otp_code: '123456'  // Use code from database
       })
   })
   .then(r => r.json())
   .then(d => console.log(d));
   ```

4. **Check Result:**
   ```
   Should see: {success: true, message: "OTP verified successfully"}
   ```

---

## 📊 SQL Queries for Debugging

### Check Recent OTPs
```sql
SELECT 
    user_email,
    otp_code,
    purpose,
    expires_at,
    verified,
    attempts,
    created_at,
    CASE WHEN expires_at > NOW() THEN 'Valid' ELSE 'Expired' END as status
FROM otp_codes
ORDER BY created_at DESC
LIMIT 10;
```

### Check Specific Email
```sql
SELECT * FROM otp_codes
WHERE user_email = 'your@email.com'
ORDER BY created_at DESC;
```

### Check Server Time
```sql
SELECT NOW() as mysql_time;
```

### Clear All OTPs (for testing)
```sql
DELETE FROM otp_codes;
DELETE FROM otp_attempts;
```

---

## 🔧 Advanced Debugging

### Enable Detailed Logging

Add to `server/includes/OTPService.php`:

```php
// At the start of verifyOTP method
error_log("=== OTP VERIFICATION START ===");
error_log("Email: $email");
error_log("Submitted Code: $otpCode");
error_log("Purpose: $purpose");

// After database query
error_log("OTP Record Found: " . ($otpRecord ? 'YES' : 'NO'));
if ($otpRecord) {
    error_log("DB Code: " . $otpRecord['otp_code']);
    error_log("Expires: " . $otpRecord['expires_at']);
    error_log("Verified: " . $otpRecord['verified']);
    error_log("Attempts: " . $otpRecord['attempts']);
}
error_log("=== OTP VERIFICATION END ===");
```

### Check Email Sending

Test email manually:

```php
<?php
require_once 'server/includes/MailService.php';
$mail = new MailService();
$result = $mail->send(
    'your@email.com',
    'Test Email',
    'This is a test email from Aarunya Healthcare'
);
echo $result ? 'Email sent!' : 'Email failed!';
?>
```

---

## 📞 Still Having Issues?

### Collect This Information:

1. **PHP Error Log:**
   - Location: `C:\xampp\php\logs\php_error_log`
   - Last 50 lines related to OTP

2. **Database State:**
   - Screenshot of "Recent OTP Codes" table
   - Server time from test page

3. **Browser Console:**
   - Any JavaScript errors
   - Network tab showing API responses

4. **Steps to Reproduce:**
   - Exact steps you followed
   - What you expected
   - What actually happened

### Contact Support:

- **Email:** killekarakash468@gmail.com
- **Include:** All information from above

---

## ✅ Success Indicators

Your OTP system is working correctly when:

- ✅ OTP appears in database immediately after sending
- ✅ OTP status shows "Valid" for 5 minutes
- ✅ Correct OTP code verifies successfully
- ✅ Verified OTP shows "Yes" in database
- ✅ Registration completes after OTP verification
- ✅ No errors in PHP error log

---

## 🎯 Quick Reference

| Problem | Solution |
|---------|----------|
| Tables missing | Run `setup_otp_system.php` |
| OTP expired immediately | Check server time |
| OTP not in database | Check PHP error log |
| Wrong code in email | Use latest OTP from database |
| Already verified | Request new OTP |
| Can't verify | Check debug logs |

---

**End of Troubleshooting Guide**

*For more help, see `test_otp_system.php` for live debugging.*
