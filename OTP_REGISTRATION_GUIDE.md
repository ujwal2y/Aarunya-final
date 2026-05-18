# 🔐 OTP-Based Registration System - Implementation Guide

**Version:** 2.0  
**Last Updated:** May 14, 2026  
**Status:** ✅ Implemented

---

## 📋 Overview

The Aarunya Healthcare registration system now requires **email verification via OTP (One-Time Password)** before account creation. This ensures that only verified email addresses can register, improving security and reducing spam accounts.

---

## 🎯 Key Features

### ✅ Security Features
- **Email Verification Required**: Users must verify their email before registration
- **6-Digit OTP**: Secure random OTP code generation
- **Time-Limited**: OTP expires after 5 minutes
- **Attempt Limiting**: Maximum 3 verification attempts per OTP
- **Rate Limiting**: Maximum 5 OTP requests per email per hour
- **Resend Cooldown**: 60-second cooldown between OTP resends
- **Session Validation**: OTP verification valid for 10 minutes

### 📧 Email Integration
- OTP sent to user's email address
- Professional email template with Aarunya branding
- Clear expiry information
- Security warnings included

### 🎨 User Experience
- **3-Step Process**:
  1. Email Verification (OTP)
  2. Account Information
  3. Registration Complete
- Real-time countdown timer
- Clear error messages
- Resend OTP option
- Responsive design

---

## 🔄 Registration Flow

### Step 1: Email Verification

1. User enters **Name** and **Email**
2. Clicks "Send Verification Code"
3. System:
   - Validates email format
   - Checks if email already exists
   - Checks rate limits
   - Generates 6-digit OTP
   - Sends OTP to email
   - Stores OTP in database with expiry

### Step 2: OTP Verification

1. User receives OTP email
2. Enters 6-digit code
3. Clicks "Verify Code"
4. System:
   - Validates OTP code
   - Checks expiry time
   - Checks attempt count
   - Marks OTP as verified
   - Stores verification in session

### Step 3: Complete Registration

1. Registration form appears (email pre-filled and locked)
2. User completes remaining fields
3. Submits registration
4. System:
   - Verifies OTP session is valid
   - Creates user account
   - Sends welcome email
   - Redirects to login

---

## 🗄️ Database Structure

### `otp_codes` Table

```sql
CREATE TABLE otp_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    purpose ENUM('registration', 'password_reset', '2fa', 'login') NOT NULL,
    expires_at DATETIME NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    INDEX idx_email (user_email),
    INDEX idx_expires (expires_at),
    INDEX idx_verified (verified)
);
```

### `otp_attempts` Table

```sql
CREATE TABLE otp_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempt_type ENUM('send', 'verify') NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email_ip (email, ip_address)
);
```

---

## 📁 File Structure

```
Aarunya/
├── client/
│   └── register.php                    # Updated with OTP verification UI
├── server/
│   ├── api/
│   │   └── otp_handler.php            # NEW: OTP API endpoint
│   ├── handlers/
│   │   └── register_handler.php       # Updated with OTP validation
│   └── includes/
│       ├── OTPService.php             # OTP generation & verification
│       └── MailService.php            # Email sending service
└── database/
    └── migrations/
        └── 003_create_otp_table.sql   # OTP tables migration
```

---

## 🔧 Configuration

### Environment Variables (.env)

```env
# OTP Configuration
OTP_LENGTH=6
OTP_EXPIRY_MINUTES=5
OTP_MAX_ATTEMPTS=3
OTP_RESEND_COOLDOWN_SECONDS=60

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=bcavjay@gmail.com
MAIL_PASSWORD=xght zfnz jkvn mhan
MAIL_FROM_ADDRESS=bcavjay@gmail.com
MAIL_FROM_NAME="Aarunya Healthcare"
```

---

## 🚀 API Endpoints

### 1. Send OTP

**Endpoint:** `POST /server/api/otp_handler.php`

**Request:**
```json
{
    "action": "send_otp",
    "email": "user@example.com",
    "name": "John Doe"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "OTP sent successfully to your email",
    "expires_in": 300,
    "can_resend_after": 60
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "This email is already registered. Please login instead."
}
```

---

### 2. Verify OTP

**Endpoint:** `POST /server/api/otp_handler.php`

**Request:**
```json
{
    "action": "verify_otp",
    "email": "user@example.com",
    "otp_code": "123456"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "OTP verified successfully"
}
```

**Response (Error):**
```json
{
    "success": false,
    "message": "Invalid OTP code. 2 attempts remaining.",
    "remaining_attempts": 2
}
```

---

### 3. Resend OTP

**Endpoint:** `POST /server/api/otp_handler.php`

**Request:**
```json
{
    "action": "resend_otp",
    "email": "user@example.com",
    "name": "John Doe"
}
```

**Response (Success):**
```json
{
    "success": true,
    "message": "OTP sent successfully to your email",
    "expires_in": 300,
    "can_resend_after": 60
}
```

**Response (Cooldown):**
```json
{
    "success": false,
    "message": "Please wait 45 seconds before requesting a new OTP.",
    "cooldown_remaining": 45
}
```

---

## 🧪 Testing Guide

### Test Case 1: Successful Registration

1. Navigate to registration page
2. Enter name: "Test User"
3. Enter email: "test@example.com"
4. Click "Send Verification Code"
5. Check email inbox for OTP
6. Enter OTP code
7. Click "Verify Code"
8. Complete registration form
9. Submit registration

**Expected:** Account created successfully

---

### Test Case 2: Invalid OTP

1. Request OTP
2. Enter wrong code (e.g., "000000")
3. Click "Verify Code"

**Expected:** Error message "Invalid OTP code. X attempts remaining."

---

### Test Case 3: Expired OTP

1. Request OTP
2. Wait 6 minutes
3. Enter OTP code
4. Click "Verify Code"

**Expected:** Error message "Invalid or expired OTP. Please request a new one."

---

### Test Case 4: Rate Limiting

1. Request OTP 5 times in quick succession
2. Try to request 6th OTP

**Expected:** Error message "Too many OTP requests. Please try again after 1 hour."

---

### Test Case 5: Resend Cooldown

1. Request OTP
2. Immediately click "Resend Code"

**Expected:** Error message "Please wait X seconds before requesting a new OTP."

---

### Test Case 6: Duplicate Email

1. Request OTP for existing email
2. Click "Send Verification Code"

**Expected:** Error message "This email is already registered. Please login instead."

---

### Test Case 7: Registration Without OTP

1. Try to access registration form directly
2. Submit form without OTP verification

**Expected:** Error message "Email verification required. Please verify your email with OTP first."

---

## 📧 Email Template

The OTP email includes:

- **Subject:** "Your OTP Code - Aarunya Healthcare"
- **Content:**
  - Aarunya branding
  - 6-digit OTP code (large, centered)
  - Expiry time (5 minutes)
  - Security warnings
  - Professional footer

---

## 🔒 Security Measures

### 1. Rate Limiting
- **Email Level:** Max 5 OTP requests per hour
- **IP Level:** Tracked in `otp_attempts` table
- **Purpose:** Prevent spam and abuse

### 2. Attempt Limiting
- **Max Attempts:** 3 verification attempts per OTP
- **Purpose:** Prevent brute force attacks

### 3. Time Expiry
- **OTP Expiry:** 5 minutes
- **Session Expiry:** 10 minutes after verification
- **Purpose:** Limit attack window

### 4. Resend Cooldown
- **Cooldown:** 60 seconds between resends
- **Purpose:** Prevent email flooding

### 5. Session Validation
- OTP verification stored in session
- Validated before account creation
- Cleared after successful registration

### 6. Database Cleanup
- Automatic cleanup of expired OTPs (hourly)
- Automatic cleanup of old attempts (daily)
- Event scheduler enabled

---

## 🐛 Troubleshooting

### Issue: OTP Email Not Received

**Possible Causes:**
1. Email in spam folder
2. SMTP configuration incorrect
3. Email service blocking

**Solutions:**
1. Check spam/junk folder
2. Verify `.env` SMTP settings
3. Check email service logs
4. Test with different email provider

---

### Issue: "Email verification required" Error

**Cause:** User trying to register without OTP verification

**Solution:** Complete OTP verification first

---

### Issue: "Too many OTP requests" Error

**Cause:** Rate limit exceeded (5 requests per hour)

**Solution:** Wait 1 hour or contact support

---

### Issue: OTP Expired

**Cause:** More than 5 minutes passed since OTP sent

**Solution:** Click "Resend Code" to get new OTP

---

## 📊 Monitoring & Logs

### Log Locations

- **OTP Sending:** `error_log("OTP sent successfully to $email")`
- **OTP Verification:** `error_log("OTP verification check error")`
- **Registration:** `error_log("Registration successful - User ID: $userId")`

### Database Queries for Monitoring

```sql
-- Check recent OTP requests
SELECT * FROM otp_codes 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY created_at DESC;

-- Check verification success rate
SELECT 
    COUNT(*) as total,
    SUM(verified) as verified,
    (SUM(verified) / COUNT(*) * 100) as success_rate
FROM otp_codes
WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Check rate limit violations
SELECT email, COUNT(*) as attempts
FROM otp_attempts
WHERE attempt_type = 'send'
AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY email
HAVING attempts >= 5;
```

---

## ✅ Benefits

1. **Enhanced Security:** Only verified emails can register
2. **Spam Prevention:** Reduces fake account creation
3. **Email Validation:** Ensures email addresses are valid and accessible
4. **User Trust:** Professional verification process
5. **Compliance:** Meets email verification best practices

---

## 🔄 Future Enhancements

- [ ] SMS OTP option
- [ ] Two-factor authentication (2FA)
- [ ] Social login integration
- [ ] Biometric authentication
- [ ] Magic link login (passwordless)

---

## 📞 Support

For issues or questions:
- **Email:** support@aarunya.com
- **Monitoring:** killekarakash468@gmail.com
- **Documentation:** This file

---

**End of OTP Registration Guide**

*Secure registration for a secure healthcare platform.*
