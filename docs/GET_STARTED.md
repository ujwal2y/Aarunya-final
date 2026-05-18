# Get Started with Aarunya

Welcome to Aarunya - Your Maternal Healthcare Management System!

## What is Aarunya?

Aarunya is a complete web-based maternal healthcare system that helps:
- **Mothers**: Track pregnancy, book appointments, access health records
- **Doctors**: Manage consultations and patient care
- **Admins**: Oversee the entire system, manage users and appointments

## Quick Setup (5 Minutes)

### Step 1: Import Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `aarunya`
3. Import file: `database/aarunya_complete.sql`

### Step 2: Configure Database
Edit these files with your MySQL credentials:
- `admin/includes/db.php`
- `server/config/database.php`

### Step 3: Start Using
- Visit: `http://localhost/Aarunya/client/index.html`
- Login with default credentials (see below)

## Default Accounts

### Admin Access
```
Email: admin@aarunya.com
Password: admin123
```
Access admin panel at: `/admin/pages/dashboard.php`

### Test User Access
```
Email: test@example.com
Password: test123
```
Access user portal at: `/client/login.php`

## Main Features

### For Users (Mothers)
- ✅ Modern landing page with features
- ✅ Register and create profile
- ✅ Track pregnancy progress
- ✅ View health records
- ✅ Book doctor appointments
- ✅ Send emergency requests
- ✅ Dashboard with health metrics

### For Admins
- ✅ Modern sidebar dashboard with statistics
- ✅ Manage all users (mothers)
- ✅ Manage doctors
- ✅ Handle appointments (approve/reject)
- ✅ Respond to emergencies
- ✅ Generate reports with charts

## Navigation

### User Portal
```
Landing Page (client/index.html)
     ↓
Login (client/login.php) → Dashboard (client/dashboard.php)
     ↓                           ↓
Register (client/register.php)   Doctors → Appointments → Profile → Health
```

### Admin Panel
```
Login → Dashboard (admin/pages/dashboard.php)
              ↓
        Users → Doctors → Appointments → Emergency → Reports
```

## File Structure

```
Aarunya/
├── client/                 # User interface
│   ├── index.html         # Landing page
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   ├── dashboard.php      # User dashboard
│   ├── doctors.php        # View doctors
│   ├── appointments.php   # User appointments
│   ├── profile.php        # User profile
│   ├── health.php         # Health records
│   ├── book-appointment.php # Book new appointment
│   ├── styles/            # CSS files
│   └── includes/          # Shared components
├── admin/                  # Admin panel
│   ├── pages/             # Admin pages
│   │   ├── dashboard.php  # Admin dashboard
│   │   ├── users.php      # Manage users
│   │   ├── doctors.php    # Manage doctors
│   │   ├── appointments.php # Manage appointments
│   │   ├── emergency.php  # Emergency requests
│   │   └── reports.php    # Reports & analytics
│   ├── includes/          # Admin includes
│   │   ├── header.php     # Sidebar layout
│   │   ├── footer.php     # Footer
│   │   ├── auth.php       # Authentication
│   │   └── db.php         # Database connection
│   └── assets/css/        # Admin CSS
│       └── dark-theme.css # Modern dark theme
├── server/                 # Backend logic
│   ├── config/            # Configuration
│   └── includes/          # Server includes
└── database/               # SQL files
    └aarunya_complete.sql  # Complete database schema
```

## How to Run

### Prerequisites
- **XAMPP/WAMP/MAMP** (Apache + MySQL + PHP)
- **Web Browser** (Chrome, Firefox, Safari)

### Steps
1. **Start Services**
   ```
   Start Apache and MySQL in XAMPP Control Panel
   ```

2. **Setup Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `aarunya`
   - Import: `database/aarunya_complete.sql`

3. **Configure Database Connection**
   - Edit `admin/includes/db.php`
   - Update MySQL username/password if needed

4. **Access the Application**
   - **Landing Page**: `http://localhost/Aarunya/client/index.html`
   - **User Login**: `http://localhost/Aarunya/client/login.php`
   - **Admin Dashboard**: `http://localhost/Aarunya/admin/pages/dashboard.php`

## Default Login Credentials

### Admin Login
- **URL**: `/admin/pages/dashboard.php`
- **Email**: `admin@aarunya.com`
- **Password**: `admin123`

### Test User Login
- **URL**: `/client/login.php`
- **Email**: `test@example.com`
- **Password**: `test123`

## Features Overview

### Modern UI/UX
- **Dark Theme**: Professional healthcare-focused design
- **Glassmorphism**: Modern card-based layouts
- **Responsive**: Works on desktop, tablet, and mobile
- **Charts**: Interactive data visualization with Chart.js

### User Features
- Landing page with health statistics
- Split-screen login/register forms
- Dashboard with health metrics
- Doctor profiles and appointment booking
- Health record tracking
- Emergency request system

### Admin Features
- Sidebar navigation dashboard
- Real-time statistics and trends
- User and doctor management
- Appointment approval system
- Emergency alert notifications
- Data visualization charts

## Next Steps

1. **Test the System**
   - Login as admin and explore dashboard
   - Register as new user and book appointment
   - Check appointment approval workflow

2. **Customize**
   - Update colors in `client/styles/` and `admin/assets/css/`
   - Add your organization logo
   - Modify content and branding

3. **Add Data**
   - Add real doctors through admin panel
   - Create user accounts
   - Test appointment booking flow

## Security Notes

⚠️ **Before Production:**
- Change all default passwords
- Use environment variables for database credentials
- Enable HTTPS
- Implement proper input validation
- Add rate limiting
- Regular security updates

---

**Ready to start?** 
1. Start XAMPP
2. Import database
3. Visit `http://localhost/Aarunya/client/index.html`

🚀 **Enjoy your maternal healthcare system!**
