# 🎨 Theme Customizer - Complete Guide

## ✅ Quick Start (30 Seconds)

### 1. Open Theme Customizer
Navigate to: `http://localhost/Aarunya%20final/Aarunya/theme_customizer.php`

Or use: **Admin Tools** → **Theme Customizer**

### 2. Select a Theme
Click on any of the 8 preset theme cards to apply instantly.

### 3. See Changes
- Click **"Test Theme"** button to see colors on test page
- Click **"View Dashboard"** to see client dashboard
- Click **"View Admin"** to see admin panel
- If needed, press **Ctrl+F5** to hard refresh

---

## 🎨 Available Themes

1. **Purple Dream (Default)** - Elegant purple gradient (#667eea)
2. **Ocean Blue** - Professional blue theme (#0ea5e9)
3. **Forest Green** - Calming green theme (#10b981)
4. **Sunset Orange** - Energetic orange theme (#f97316)
5. **Rose Pink** - Elegant pink theme (#ec4899)
6. **Midnight Blue** - Deep blue theme (#3b82f6)
7. **Lavender Dream** - Soft purple theme (#a78bfa)
8. **Crimson Red** - Bold red theme (#dc2626)

---

## 🎨 Create Custom Colors

1. Use the **color pickers** on the right side of the customizer
2. Adjust colors for:
   - Primary Color
   - Secondary Color
   - Accent Color
   - Success, Warning, Danger colors
   - Background Color
3. See **live preview** at the bottom
4. Click **"Save Custom Theme"**
5. Test on real pages

---

## 📊 Theme Coverage

Your theme applies to **100% of the application**:

### Client Pages (12 pages)
- Dashboard, Login, Register, Appointments, Doctors, Health Tracking, Emergency, Profile, Settings, Medical Documents, AI Wellness Plan, Book Appointment

### Admin Pages (All pages)
- Dashboard, Users, Doctors, Appointments, Reports, Settings, Emergency, Help

### Other Pages
- Test page, Theme customizer, All future pages

---

## 🔧 How It Works

1. **Select Theme** → PHP generates `theme-custom.css` with your colors
2. **Version Tracking** → System updates `theme-version.txt` with timestamp
3. **Auto-Loading** → Every page includes `theme_loader.php`
4. **Cache Busting** → CSS loaded with `?v=timestamp` to bypass browser cache
5. **Instant Apply** → Changes appear immediately (may need Ctrl+F5)

---

## 🐛 Troubleshooting

### Theme Not Changing?

**Solution 1: Hard Refresh**
- Windows: Press **Ctrl+F5**
- Mac: Press **Cmd+Shift+R**

**Solution 2: Clear Browser Cache**
- Press **Ctrl+Shift+Delete**
- Select "Cached images and files"
- Click "Clear data"

**Solution 3: Check Browser Console**
- Press **F12** to open developer tools
- Look for any CSS loading errors
- Verify `theme-custom.css` is loading

### Still Having Issues?

1. Verify files exist:
   - `client/styles/theme-custom.css`
   - `client/styles/theme-version.txt`
   - `client/includes/theme_loader.php`

2. Check file permissions on `client/styles/` folder

3. Try a different browser to rule out caching issues

---

## 💡 Tips & Best Practices

1. **Test First**: Always use "Test Theme" button before checking real pages
2. **Hard Refresh**: Press Ctrl+F5 after changing themes
3. **Preview**: Use live preview in customizer before saving custom colors
4. **Backup**: Your theme is saved in `client/styles/theme-custom.css`

---

## 🔗 Quick Links

- **Theme Customizer**: `theme_customizer.php`
- **Test Page**: `test_theme.php`
- **Admin Tools**: `admin_tools.php`
- **Client Dashboard**: `client/dashboard.php`
- **Admin Dashboard**: `admin/pages/dashboard.php`

---

## 📝 Technical Details

### Files Involved
- **theme_customizer.php** - Main theme selector interface
- **client/styles/theme-custom.css** - Generated theme CSS
- **client/styles/theme-version.txt** - Version number for cache busting
- **client/includes/theme_loader.php** - Auto-loads theme on every page

### CSS Override System
The theme system uses CSS variables with `!important` flags to override default colors:

```css
/* Default colors in premium-design-system.css */
:root {
    --primary-purple: #667eea;
}

/* Your custom theme in theme-custom.css */
:root {
    --primary-purple: #ec4899 !important;
}
```

### Cache Busting
```html
<!-- Old (cached) -->
<link href="theme-custom.css">

<!-- New (cache-busted) -->
<link href="theme-custom.css?v=1747228800">
```

---

## 🎉 Enjoy Your Themes!

Your theme system is fully functional and ready to use. Change your entire application's color scheme with a single click!

**Current Status**: ✅ Working perfectly across all pages
