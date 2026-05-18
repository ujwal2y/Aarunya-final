# 🔐 Authentication Flow Diagram

## Overview

This document visualizes the complete authentication and validation flow in the Aarunya Healthcare system.

## 📊 Registration Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                     USER REGISTRATION FLOW                       │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│   User Opens │
│ register.php │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────┐
│              FRONTEND VALIDATION (JavaScript)                 │
│                                                               │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │    Name     │  │    Email    │  │    Phone    │         │
│  │ Validation  │  │ Validation  │  │ Validation  │         │
│  │             │  │             │  │             │         │
│  │ • Letters   │  │ • Format    │  │ • 10 digits │         │
│  │ • Spaces    │  │ • Regex     │  │ • Numeric   │         │
│  │ • Auto-cap  │  │ • Real-time │  │ • Real-time │         │
│  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                               │
│  ┌─────────────────────────────────────────────────┐         │
│  │         Password Validation                      │         │
│  │                                                  │         │
│  │  • Min 8 characters                             │         │
│  │  • 1 Uppercase (A-Z)                            │         │
│  │  • 1 Lowercase (a-z)                            │         │
│  │  • 1 Number (0-9)                               │         │
│  │  • 1 Special (@$!%*?&#)                         │         │
│  │  • Strength Indicator (5 levels)                │         │
│  └─────────────────────────────────────────────────┘         │
│                                                               │
│  ┌─────────────────────────────────────────────────┐         │
│  │         Visual Feedback                          │         │
│  │                                                  │         │
│  │  ✅ Valid:   Green border + success icon        │         │
│  │  ❌ Invalid: Red border + error message         │         │
│  │  🔵 Focus:   Blue glow + animated icon          │         │
│  └─────────────────────────────────────────────────┘         │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ All Valid?    │
                    └───┬───────┬───┘
                        │       │
                    NO  │       │  YES
                        │       │
                        ▼       ▼
                ┌───────────┐   ┌────────────────┐
                │  Show     │   │ Submit Form to │
                │  Errors   │   │ register_      │
                │  & Block  │   │ handler.php    │
                └───────────┘   └────────┬───────┘
                                         │
                                         ▼
┌────────────────────────────────────────────────────────────────┐
│              BACKEND VALIDATION (PHP)                          │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  1. Sanitize All Inputs (XSS Prevention)         │         │
│  │     • htmlspecialchars()                         │         │
│  │     • trim()                                     │         │
│  │     • stripslashes()                             │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  2. Validate All Fields                          │         │
│  │     • Name: Letters + spaces only                │         │
│  │     • Email: Regex pattern match                 │         │
│  │     • Phone: Exactly 10 digits                   │         │
│  │     • Password: Complexity requirements          │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  3. Check Database Uniqueness                    │         │
│  │     • Email already exists?                      │         │
│  │     • Phone already exists?                      │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  4. Process Data                                 │         │
│  │     • Capitalize name                            │         │
│  │     • Clean phone number                         │         │
│  │     • Hash password (bcrypt, cost 12)            │         │
│  │     • Calculate pregnancy data (if patient)      │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  5. Insert into Database                         │         │
│  │     • Prepared statement (SQL injection safe)    │         │
│  │     • Set status = 'active'                      │         │
│  │     • Set created_at = NOW()                     │         │
│  └──────────────────────────────────────────────────┘         │
└────────────────────────────┬───────────────────────────────────┘
                             │
                             ▼
                     ┌───────────────┐
                     │  Success?     │
                     └───┬───────┬───┘
                         │       │
                     NO  │       │  YES
                         │       │
                         ▼       ▼
                 ┌───────────┐   ┌────────────────┐
                 │  Return   │   │  Redirect to   │
                 │  Error    │   │  login.php     │
                 │  Message  │   │  with success  │
                 └───────────┘   └────────────────┘
```

## 🔑 Login Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                       USER LOGIN FLOW                            │
└─────────────────────────────────────────────────────────────────┘

┌──────────────┐
│  User Opens  │
│  login.php   │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────────────────────────────────┐
│              FRONTEND VALIDATION (JavaScript)                 │
│                                                               │
│  ┌─────────────────────────────────────────────────┐         │
│  │     Identifier Field (Email OR Phone)           │         │
│  │                                                  │         │
│  │  User enters: "test@gmail.com" OR "9876543210"  │         │
│  │                                                  │         │
│  │  Validation:                                     │         │
│  │  • If contains @: Validate as email             │         │
│  │  • If all digits: Validate as phone             │         │
│  │  • Real-time feedback                           │         │
│  └─────────────────────────────────────────────────┘         │
│                                                               │
│  ┌─────────────────────────────────────────────────┐         │
│  │     Password Field                               │         │
│  │                                                  │         │
│  │  • Required field                                │         │
│  │  • Show/hide toggle                             │         │
│  │  • Real-time validation                         │         │
│  └─────────────────────────────────────────────────┘         │
│                                                               │
│  ┌─────────────────────────────────────────────────┐         │
│  │     Role Selection                               │         │
│  │                                                  │         │
│  │  ○ Patient   ○ Doctor   ○ Admin                │         │
│  └─────────────────────────────────────────────────┘         │
└───────────────────────────┬───────────────────────────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ All Valid?    │
                    └───┬───────┬───┘
                        │       │
                    NO  │       │  YES
                        │       │
                        ▼       ▼
                ┌───────────┐   ┌────────────────┐
                │  Show     │   │ Submit Form to │
                │  Errors   │   │ login_         │
                │  & Block  │   │ handler.php    │
                └───────────┘   └────────┬───────┘
                                         │
                                         ▼
┌────────────────────────────────────────────────────────────────┐
│              BACKEND AUTHENTICATION (PHP)                      │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  1. Sanitize Inputs                              │         │
│  │     • Clean identifier (email/phone)             │         │
│  │     • Get password (not sanitized)               │         │
│  │     • Get role                                   │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  2. Determine Identifier Type                    │         │
│  │     • Is email? (contains @)                     │         │
│  │     • Is phone? (10 digits)                      │         │
│  │     • Try both if unclear                        │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  3. Select Correct Table                         │         │
│  │     • Patient → users table                      │         │
│  │     • Doctor → doctors table                     │         │
│  │     • Admin → admins table                       │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  4. Query Database                               │         │
│  │     • Prepared statement                         │         │
│  │     • WHERE email = ? OR phone = ?               │         │
│  │     • Fetch user record                          │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  5. Verify Credentials                           │         │
│  │     • User exists?                               │         │
│  │     • Password correct? (password_verify)        │         │
│  │     • Account active?                            │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  6. Create Session                               │         │
│  │     • Set user_id                                │         │
│  │     • Set user_email                             │         │
│  │     • Set user_name                              │         │
│  │     • Set user_role                              │         │
│  │     • Set login_time                             │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  7. Handle "Remember Me"                         │         │
│  │     • Generate secure token                      │         │
│  │     • Store in database                          │         │
│  │     • Set cookie (30 days)                       │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  8. Update Last Login                            │         │
│  │     • UPDATE users SET last_login = NOW()        │         │
│  └──────────────────────────────────────────────────┘         │
│                                                                │
│  ┌──────────────────────────────────────────────────┐         │
│  │  9. Log Login Attempt                            │         │
│  │     • INSERT INTO login_attempts                 │         │
│  │     • Store IP, user agent, success status       │         │
│  └──────────────────────────────────────────────────┘         │
└────────────────────────────┬───────────────────────────────────┘
                             │
                             ▼
                     ┌───────────────┐
                     │  Success?     │
                     └───┬───────┬───┘
                         │       │
                     NO  │       │  YES
                         │       │
                         ▼       ▼
                 ┌───────────┐   ┌────────────────┐
                 │  Return   │   │  Redirect to   │
                 │  Error    │   │  Dashboard     │
                 │  Message  │   │  (role-based)  │
                 └───────────┘   └────────┬───────┘
                                          │
                                          ▼
                                  ┌───────────────┐
                                  │  Patient →    │
                                  │  client/      │
                                  │  dashboard    │
                                  ├───────────────┤
                                  │  Doctor →     │
                                  │  doctor/      │
                                  │  dashboard    │
                                  ├───────────────┤
                                  │  Admin →      │
                                  │  admin/pages/ │
                                  │  dashboard    │
                                  └───────────────┘
```

## 🔄 Validation Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    VALIDATION PROCESS FLOW                       │
└─────────────────────────────────────────────────────────────────┘

User Types in Input Field
         │
         ▼
┌────────────────────┐
│  'input' Event     │  ← Real-time validation
│  Triggered         │
└────────┬───────────┘
         │
         ▼
┌────────────────────────────────────────────────────────┐
│  AarunyaValidator.validateField(input)                 │
│                                                         │
│  1. Determine field type:                              │
│     • type="email" → validateEmail()                   │
│     • type="tel" → validatePhone()                     │
│     • type="password" → validatePassword()             │
│     • name="name" → validateName()                     │
│                                                         │
│  2. Run validation:                                    │
│     • Check pattern match                              │
│     • Check length requirements                        │
│     • Check complexity (for password)                  │
│                                                         │
│  3. Return result:                                     │
│     { valid: true/false, message: "..." }              │
└────────┬───────────────────────────────────────────────┘
         │
         ▼
    ┌────────┐
    │ Valid? │
    └───┬─┬──┘
        │ │
    NO  │ │  YES
        │ │
        ▼ ▼
┌───────────┐  ┌────────────┐
│ showError │  │ showSuccess│
│           │  │            │
│ • Red     │  │ • Green    │
│   border  │  │   border   │
│ • Error   │  │ • Success  │
│   icon    │  │   icon     │
│ • Error   │  │ • No error │
│   message │  │   message  │
└───────────┘  └────────────┘
```

## 🔐 Password Strength Calculation

```
┌─────────────────────────────────────────────────────────────────┐
│                  PASSWORD STRENGTH INDICATOR                     │
└─────────────────────────────────────────────────────────────────┘

User Types Password
         │
         ▼
┌────────────────────────────────────────────────────────┐
│  Calculate Strength (0-5)                              │
│                                                         │
│  Base Requirements:                                    │
│  ✓ Length >= 8        → +3 points (minimum)           │
│  ✓ Has lowercase      → included in base              │
│  ✓ Has uppercase      → included in base              │
│  ✓ Has number         → included in base              │
│  ✓ Has special char   → included in base              │
│                                                         │
│  Bonus Points:                                         │
│  ✓ Length >= 12       → +1 point                      │
│  ✓ Multiple uppercase → +1 point                      │
│  ✓ Multiple numbers   → +1 point                      │
│  ✓ Multiple special   → +1 point                      │
│                                                         │
│  Maximum: 5 points                                     │
└────────┬───────────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────────────────────┐
│  Display Strength Bars                                 │
│                                                         │
│  Strength 1: ▓░░░░ Weak (Red)                         │
│  Strength 2: ▓▓░░░ Fair (Orange)                      │
│  Strength 3: ▓▓▓░░ Good (Blue)                        │
│  Strength 4: ▓▓▓▓░ Strong (Green)                     │
│  Strength 5: ▓▓▓▓▓ Very Strong (Dark Green)           │
└────────────────────────────────────────────────────────┘
```

## 🛡️ Security Layers

```
┌─────────────────────────────────────────────────────────────────┐
│                      SECURITY LAYERS                             │
└─────────────────────────────────────────────────────────────────┘

Layer 1: Frontend Validation
┌────────────────────────────────────────────────────────┐
│  • Real-time input validation                          │
│  • Pattern matching                                    │
│  • Length checks                                       │
│  • Format verification                                 │
│  • User feedback                                       │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Layer 2: Input Sanitization
┌────────────────────────────────────────────────────────┐
│  • XSS prevention (htmlspecialchars)                   │
│  • Trim whitespace                                     │
│  • Remove slashes                                      │
│  • Clean special characters                            │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Layer 3: Backend Validation
┌────────────────────────────────────────────────────────┐
│  • Re-validate all inputs                              │
│  • Check data types                                    │
│  • Verify constraints                                  │
│  • Business logic validation                           │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Layer 4: Database Security
┌────────────────────────────────────────────────────────┐
│  • Prepared statements (SQL injection prevention)      │
│  • Parameter binding                                   │
│  • Unique constraints                                  │
│  • Foreign key constraints                             │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Layer 5: Password Security
┌────────────────────────────────────────────────────────┐
│  • Bcrypt hashing (cost 12)                            │
│  • Salt generation                                     │
│  • Secure comparison                                   │
│  • No plain text storage                               │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Layer 6: Session Security
┌────────────────────────────────────────────────────────┐
│  • Secure session configuration                        │
│  • Session regeneration on login                       │
│  • Timeout tracking                                    │
│  • Secure cookie flags                                 │
└────────────────────────────────────────────────────────┘
```

## 📱 Responsive Validation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                   RESPONSIVE VALIDATION FLOW                     │
└─────────────────────────────────────────────────────────────────┘

Desktop/Tablet                          Mobile
┌──────────────────┐                   ┌──────────────────┐
│  User Types      │                   │  User Types      │
│  in Field        │                   │  in Field        │
└────────┬─────────┘                   └────────┬─────────┘
         │                                      │
         ▼                                      ▼
┌──────────────────┐                   ┌──────────────────┐
│  Real-time       │                   │  Real-time       │
│  Validation      │                   │  Validation      │
│  (on input)      │                   │  (on input)      │
└────────┬─────────┘                   └────────┬─────────┘
         │                                      │
         ▼                                      ▼
┌──────────────────┐                   ┌──────────────────┐
│  Inline Error    │                   │  Inline Error    │
│  Below Field     │                   │  Below Field     │
│                  │                   │  (Stacked)       │
│  ❌ Error msg    │                   │                  │
│     here         │                   │  ❌ Error msg    │
│                  │                   │     here         │
└──────────────────┘                   └──────────────────┘
         │                                      │
         ▼                                      ▼
┌──────────────────┐                   ┌──────────────────┐
│  Icon Color      │                   │  Icon Color      │
│  Changes         │                   │  Changes         │
│                  │                   │                  │
│  🔴 Red (error)  │                   │  🔴 Red (error)  │
│  🟢 Green (ok)   │                   │  🟢 Green (ok)   │
└──────────────────┘                   └──────────────────┘
```

## 🎯 Complete System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    SYSTEM ARCHITECTURE                           │
└─────────────────────────────────────────────────────────────────┘

Frontend Layer
┌────────────────────────────────────────────────────────┐
│  validation.js                                         │
│  • AarunyaValidator class                              │
│  • Real-time validation                                │
│  • Visual feedback                                     │
│  • Password strength                                   │
│  • Auto-initialization                                 │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Backend Layer
┌────────────────────────────────────────────────────────┐
│  validation.php                                        │
│  • AarunyaValidator class                              │
│  • Server-side validation                              │
│  • Input sanitization                                  │
│  • Password hashing                                    │
│  • Database checks                                     │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Handler Layer
┌────────────────────────────────────────────────────────┐
│  login_handler.php    │  register_handler.php          │
│  • Process login      │  • Process registration        │
│  • Verify credentials │  • Validate data               │
│  • Create session     │  • Insert into DB              │
│  • Track login        │  • Handle errors               │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Database Layer
┌────────────────────────────────────────────────────────┐
│  database.php                                          │
│  • PDO connection                                      │
│  • Prepared statements                                 │
│  • Error handling                                      │
│  • Connection pooling                                  │
└────────────────────────────────────────────────────────┘
                         │
                         ▼
Data Layer
┌────────────────────────────────────────────────────────┐
│  MySQL Database                                        │
│  • users table                                         │
│  • doctors table                                       │
│  • admins table                                        │
│  • login_attempts table                                │
│  • password_resets table                               │
└────────────────────────────────────────────────────────┘
```

---

**Document Version:** 1.0.0
**Last Updated:** 2024
**Status:** ✅ Complete
