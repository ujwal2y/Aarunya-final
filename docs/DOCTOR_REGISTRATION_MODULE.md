# Doctor Registration Module - Complete Documentation

## Overview
A fully responsive, multi-step doctor registration system integrated with the Aarunya Healthcare Platform. Features professional onboarding, document verification, and comprehensive data collection.

## Features

### ✅ Multi-Step Registration (6 Steps)
1. **Personal Information** - Basic details and contact information
2. **Professional Information** - Medical credentials and practice details
3. **Education & Certifications** - Academic qualifications
4. **Verification Documents** - Document uploads with drag-and-drop
5. **Availability & Consultation** - Schedule and consultation preferences
6. **Account Setup** - Login credentials and terms acceptance

### ✅ Key Functionality
- **Auto-Save Draft** - Automatically saves progress every 2 seconds
- **Form Validation** - Real-time validation with error messages
- **File Upload** - Drag-and-drop support with preview
- **Password Strength** - Visual password strength indicator
- **Progress Tracking** - Visual progress bar and step indicators
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Document Verification** - Secure document upload and storage

## Installation

### Step 1: Database Setup
Run the database schema:
```bash
mysql -u root -p aarunya_db < database/doctor_registration_schema.sql
```

Or import via phpMyAdmin:
1. Open phpMyAdmin
2. Select `aarunya_db` database
3. Go to SQL tab
4. Import `database/doctor_registration_schema.sql`

### Step 2: Create Upload Directory
```bash
mkdir -p uploads/doctor_documents
chmod 777 uploads/doctor_documents
```

### Step 3: Access Registration Page
Navigate to:
```
http://localhost/Aarunya/doctor/register.php
```

## File Structure

```
doctor/
├── register.php                    # Main registration page
├── styles/
│   └── doctor-registration.css     # Complete styling
├── scripts/
│   └── doctor-registration.js      # Form functionality
└── actions/
    ├── submit_registration.php     # Handle form submission
    ├── save_draft.php              # Auto-save functionality
    ├── load_draft.php              # Load saved draft
    └── clear_draft.php             # Clear draft after submission

database/
└── doctor_registration_schema.sql  # Database schema

uploads/
└── doctor_documents/               # Document storage
```

## Registration Steps Details

### Step 1: Personal Information
**Fields:**
- Full Name (required)
- Gender (required)
- Date of Birth (required)
- Mobile Number (required)
- Email Address (required)
- Residential Address (required)
- City (required)
- State (required)
- PIN Code (required)
- Profile Photo (optional)

**Validation:**
- Email format validation
- Phone number format
- PIN code must be 6 digits
- Profile photo max 2MB

### Step 2: Professional Information
**Fields:**
- Medical License Number (required, unique)
- Medical Council Registration (required)
- Primary Specialization (required)
- Secondary Specialization (optional)
- Years of Experience (required)
- Hospital/Clinic Name (required)
- Workplace Address (required)
- Consultation Fee (required)
- Languages Spoken (required)

**Validation:**
- Medical license uniqueness check
- Experience must be 0-60 years
- Consultation fee must be positive

### Step 3: Education & Certifications
**Fields:**
- Degree Name (required)
- University/Medical College (required)
- Graduation Year (required)
- Fellowship Details (optional)
- Additional Certifications (optional)

**Validation:**
- Graduation year between 1950-2026

### Step 4: Verification Documents
**Required Documents:**
- Medical License Certificate (required)
- Degree Certificates (required)
- Government ID Proof (required)
- Experience Certificates (optional)

**Features:**
- Drag-and-drop upload
- File preview
- Progress indicator
- File type validation (PDF, JPG, PNG)
- Max file size: 5MB per document

### Step 5: Availability & Consultation
**Fields:**
- Available Days (required, at least one)
- Time Slots (Start Time, End Time)
- Online Consultation (toggle)
- In-Person Consultation (toggle)
- Emergency Availability (toggle)

**Features:**
- Visual day selector
- Time picker
- Toggle cards for consultation types

### Step 6: Account Setup
**Fields:**
- Username (required, unique)
- Password (required, min 8 characters)
- Confirm Password (required)
- Terms & Conditions (required)
- Data Consent (required)

**Features:**
- Password strength indicator
- Password match validation
- Show/hide password toggle

## Design System

### Colors (Matching Aarunya Platform)
```css
Primary Pink: #F472B6
Dark Pink: #DB2777
Light Pink: #FBCFE8
Background: #0f172a, #1e1b2e, #1a0e2e
Text Primary: #ffffff
Text Secondary: #94a3b8
Success: #10b981
Error: #EF4444
Warning: #F59E0B
```

### Typography
```css
Font Family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif
Headings: 24-28px, font-weight: 800
Body: 14-16px, font-weight: 400
Labels: 13px, font-weight: 600
```

### Spacing (8px Grid System)
```css
Gap Small: 8px
Gap Medium: 16px
Gap Large: 24px
Gap XL: 32px
Padding: 16px, 24px, 32px, 40px
Border Radius: 8px, 12px, 16px
```

### Components
- **Cards**: Glassmorphism with backdrop-filter
- **Buttons**: Gradient backgrounds with hover effects
- **Inputs**: Consistent border, focus states
- **Progress Bar**: Animated gradient fill
- **File Upload**: Drag-and-drop zones
- **Modals**: Centered with overlay

## API Endpoints

### POST /doctor/actions/submit_registration.php
Submit complete registration form.

**Request:** multipart/form-data
**Response:**
```json
{
  "success": true,
  "message": "Registration successful!"
}
```

### POST /doctor/actions/save_draft.php
Auto-save form progress.

**Request:**
```json
{
  "step": 2,
  "data": { ...form fields... }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Draft saved"
}
```

### GET /doctor/actions/load_draft.php
Load saved draft.

**Response:**
```json
{
  "success": true,
  "step": 2,
  "data": { ...form fields... }
}
```

### POST /doctor/actions/clear_draft.php
Clear saved draft after successful submission.

**Response:**
```json
{
  "success": true,
  "message": "Draft cleared"
}
```

## Database Schema

### doctors Table (Extended)
```sql
-- New columns added:
gender VARCHAR(20)
date_of_birth DATE
mobile VARCHAR(20)
address TEXT
city VARCHAR(100)
state VARCHAR(100)
pin_code VARCHAR(10)
profile_photo VARCHAR(255)
medical_license_number VARCHAR(100) UNIQUE
medical_council_registration VARCHAR(100)
secondary_specialization VARCHAR(255)
hospital_name VARCHAR(255)
workplace_address TEXT
consultation_fee DECIMAL(10,2)
languages_spoken TEXT
degree_name VARCHAR(255)
university VARCHAR(255)
graduation_year INT
fellowship_details TEXT
additional_certifications TEXT
license_certificate VARCHAR(255)
degree_certificate VARCHAR(255)
government_id VARCHAR(255)
experience_certificate VARCHAR(255)
available_days TEXT
time_slots TEXT
online_consultation BOOLEAN
in_person_consultation BOOLEAN
emergency_availability BOOLEAN
username VARCHAR(100) UNIQUE
password VARCHAR(255)
registration_status VARCHAR(50) DEFAULT 'pending'
verification_notes TEXT
verified_by INT
verified_at TIMESTAMP
```

### doctor_registration_drafts Table
```sql
id INT AUTO_INCREMENT PRIMARY KEY
session_id VARCHAR(255) NOT NULL
email VARCHAR(255) NOT NULL
step_number INT DEFAULT 1
form_data JSON NOT NULL
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

## Admin Verification Workflow

### Pending Registrations
1. Doctor submits registration
2. Status set to 'pending'
3. Admin receives notification
4. Admin reviews documents
5. Admin approves/rejects

### Approval Process
1. Navigate to Admin → Doctors
2. Filter by status: 'pending'
3. View doctor details
4. Verify documents
5. Update status to 'approved' or 'rejected'
6. Add verification notes
7. Send email notification to doctor

## Security Features

### Data Protection
- Password hashing with bcrypt
- SQL injection prevention (prepared statements)
- File upload validation
- Session management
- CSRF protection (implement tokens)

### File Upload Security
- File type validation
- File size limits (5MB)
- Unique filename generation
- Secure storage location
- Access control

### Input Validation
- Server-side validation
- Client-side validation
- Email format check
- Phone number format
- Medical license uniqueness
- Username uniqueness

## Testing Checklist

- [ ] Step 1: Personal information form validation
- [ ] Step 2: Professional information validation
- [ ] Step 3: Education form validation
- [ ] Step 4: Document upload (all 4 types)
- [ ] Step 5: Availability selection
- [ ] Step 6: Account setup with password strength
- [ ] Auto-save functionality
- [ ] Draft load on page refresh
- [ ] Form submission
- [ ] Success modal display
- [ ] Database record creation
- [ ] File uploads stored correctly
- [ ] Responsive design (mobile, tablet, desktop)
- [ ] Password toggle functionality
- [ ] Progress bar updates
- [ ] Step navigation (prev/next)
- [ ] Required field validation
- [ ] Duplicate email/username check
- [ ] Duplicate medical license check

## Responsive Breakpoints

```css
Desktop: > 768px (2-column grid)
Tablet: 481px - 768px (1-column grid)
Mobile: < 480px (stacked layout)
```

## Browser Compatibility

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers

## Future Enhancements

1. **Email Verification** - Send verification email with OTP
2. **SMS Verification** - Mobile number verification
3. **Video KYC** - Live video verification
4. **Document OCR** - Auto-extract data from documents
5. **AI Verification** - Automated document verification
6. **Payment Integration** - Registration fee payment
7. **Calendar Integration** - Sync availability with calendar
8. **Multi-language Support** - Support for regional languages
9. **Progressive Web App** - Offline capability
10. **Analytics Dashboard** - Registration statistics

## Support

For issues or questions:
- Check documentation: `docs/DOCTOR_REGISTRATION_MODULE.md`
- Review code comments in source files
- Contact system administrator

---

**Version**: 1.0  
**Last Updated**: May 7, 2026  
**For**: Aarunya Maternal Care System
