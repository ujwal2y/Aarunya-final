# Testing Guide - Aarunya Healthcare Platform

## Quick Start Testing

### 🚀 Before You Start

1. **Clear Browser Cache**
   - Press `Ctrl + Shift + Delete`
   - Select "Cached images and files"
   - Click "Clear data"

2. **Hard Refresh**
   - Press `Ctrl + Shift + F5`
   - Or `Ctrl + F5`
   - This forces browser to reload all CSS/JS files

3. **Open Developer Tools**
   - Press `F12`
   - Check Console tab for any errors
   - Check Network tab to verify CSS files are loading

---

## 🔐 Login Credentials

### Admin Panel
- **URL**: `http://localhost/Aarunya%20final/Aarunya/admin/pages/dashboard.php`
- **Email**: `admin@aarunya.com`
- **Password**: `admin123`

### Doctor Panel
- **URL**: `http://localhost/Aarunya%20final/Aarunya/doctor/pages/dashboard.php`
- **Email**: `doctor@aarunya.com`
- **Password**: `doctor123`

### Client Panel (Patient)
- **URL**: `http://localhost/Aarunya%20final/Aarunya/client/dashboard.php`
- **Email**: `patient@aarunya.com`
- **Password**: `patient123`

---

## ✅ Visual Testing Checklist

### Colors & Theme
- [ ] Background is dark (#0F172A)
- [ ] Text is light and readable
- [ ] Purple (#C4A7FF) used for primary elements
- [ ] Cyan (#00D1FF) used for accents
- [ ] Gradients visible on buttons
- [ ] Glassmorphism effect on cards

### Layout
- [ ] Sidebar is vertical (260px wide) on left
- [ ] Dashboard cards are side by side (4 columns)
- [ ] Cards stack properly on mobile
- [ ] Tables are responsive
- [ ] Modals center on screen

### Interactions
- [ ] Hover effects work on cards (lift + glow)
- [ ] Buttons have hover states
- [ ] Links change color on hover
- [ ] Animations are smooth (60fps)
- [ ] Click interactions work

---

## 🧪 Functional Testing

### Admin Panel Tests

#### Dashboard
1. Navigate to admin dashboard
2. Verify 7 stat cards display correctly
3. Click on each stat card - modal should open
4. Check income/spend chart displays
5. Verify visitors bubble chart shows
6. Test filter tabs (Daily/Weekly/Monthly)

#### Users Management
1. Go to Users page
2. Search for a user by name
3. Click "View" on a user
4. Add new health metrics
5. Edit existing metrics
6. Delete a metric
7. Export users as CSV
8. Export users as JSON

#### Doctors Management
1. Go to Doctors page
2. Click "Add Doctor" button
3. Fill form and submit
4. Toggle doctor status (Active/Inactive)
5. View doctor details
6. Export doctors data

#### Appointments
1. Go to Appointments page
2. Filter by status (Pending/Approved/Completed)
3. View appointment details
4. Update appointment status
5. Export appointments data

#### Emergency Requests
1. Go to Emergency page
2. Filter by priority (High/Medium/Low)
3. View emergency details
4. Update request status
5. Verify priority indicators

### Client Panel Tests

#### Dashboard
1. Login as patient
2. Verify stats cards display
3. Check upcoming appointments
4. Test quick action buttons

#### Health Tracking
1. Go to Health page
2. View health metrics
3. Check pregnancy progress
4. Download health report

#### Appointments
1. Go to Appointments page
2. View appointment list
3. Book new appointment
4. Cancel appointment

#### Profile
1. Go to Profile page
2. Upload profile photo
3. Edit personal information
4. Save changes

#### Emergency
1. Go to Emergency page
2. Verify 108 number displays
3. Fill emergency form
4. Submit request

#### Settings
1. Go to Settings page
2. Toggle notification settings
3. Change password
4. Update preferences

### Doctor Panel Tests

#### Dashboard
1. Login as doctor
2. View appointment stats
3. Check today's schedule
4. Test quick actions

#### Appointments
1. Go to Appointments page
2. View patient appointments
3. Update appointment status
4. Add notes to appointment

#### Patients
1. Go to Patients page
2. View patient list
3. Search for patient
4. View patient details

---

## 📱 Responsive Testing

### Desktop (1920px)
- [ ] 4-column grid for dashboard cards
- [ ] Sidebar visible on left
- [ ] Tables display all columns
- [ ] Modals centered

### Laptop (1366px)
- [ ] 4-column grid maintained
- [ ] Sidebar still visible
- [ ] Content fits without horizontal scroll

### Tablet (768px)
- [ ] 2-column grid for cards
- [ ] Sidebar collapses
- [ ] Mobile menu toggle appears
- [ ] Tables scroll horizontally

### Mobile (375px)
- [ ] 1-column grid for cards
- [ ] Sidebar hidden by default
- [ ] Touch-friendly buttons (44px min)
- [ ] Forms stack vertically

---

## 🌐 Browser Testing

### Chrome/Edge
- [ ] All features work
- [ ] Animations smooth
- [ ] Glassmorphism visible
- [ ] No console errors

### Firefox
- [ ] All features work
- [ ] Backdrop-filter works
- [ ] Gradients display correctly

### Safari
- [ ] Webkit prefixes working
- [ ] Animations smooth
- [ ] Layout correct

---

## 🐛 Common Issues & Solutions

### Issue: Styles Not Applying

**Symptoms:**
- Old colors still showing
- Layout looks broken
- Sidebar horizontal instead of vertical

**Solution:**
1. Clear browser cache completely
2. Hard refresh (Ctrl + Shift + F5)
3. Check if CSS file is loading in Network tab
4. Verify file path: `admin/assets/css/premium-design-system.css`

### Issue: Database Connection Error

**Symptoms:**
- "Connection failed" message
- No data displaying
- Login fails

**Solution:**
1. Check MySQL is running in XAMPP
2. Verify port is 3307 in `.env` file
3. Check database name is `aarunya_db`
4. Verify credentials: user=`root`, password=(empty)

### Issue: Sidebar Not Showing

**Symptoms:**
- No sidebar visible
- Content takes full width
- Navigation missing

**Solution:**
1. Check browser console for JavaScript errors
2. Verify `sidebar.php` is included in page
3. Check CSS for `.sidebar` class
4. Verify `app-layout` wrapper exists

### Issue: Cards Stacking Vertically

**Symptoms:**
- Dashboard cards in single column
- Should be 4 columns side by side

**Solution:**
1. Check CSS for `.stats-grid` class
2. Verify `grid-template-columns: repeat(4, 1fr)`
3. Check browser width (should be > 1024px for 4 columns)
4. Clear cache and refresh

### Issue: Modal Not Opening

**Symptoms:**
- Click on card, nothing happens
- Modal stays hidden

**Solution:**
1. Check browser console for JavaScript errors
2. Verify `onclick` attribute exists on card
3. Check modal display style
4. Verify modal JavaScript functions are defined

---

## 📊 Performance Testing

### Page Load Time
- [ ] Dashboard loads in < 2 seconds
- [ ] Images load progressively
- [ ] No layout shift (CLS)

### Animation Performance
- [ ] Hover effects smooth (60fps)
- [ ] Transitions don't lag
- [ ] No jank on scroll

### Network
- [ ] CSS files load quickly
- [ ] No 404 errors
- [ ] Images optimized

---

## 🔍 Debugging Tips

### Check Console
```javascript
// Open browser console (F12)
// Look for errors in red
// Common errors:
// - "Failed to load resource" (missing file)
// - "Uncaught TypeError" (JavaScript error)
// - "Syntax error" (CSS/JS syntax issue)
```

### Check Network Tab
```
// Open DevTools > Network tab
// Reload page
// Look for:
// - Red items (failed requests)
// - Large file sizes (> 1MB)
// - Slow requests (> 1s)
```

### Check Elements Tab
```
// Open DevTools > Elements tab
// Inspect element
// Check computed styles
// Verify CSS variables are defined
```

---

## 📝 Test Report Template

```markdown
## Test Report - [Date]

### Environment
- Browser: [Chrome/Firefox/Safari]
- Version: [Browser version]
- OS: [Windows/Mac/Linux]
- Screen: [1920x1080/1366x768/etc]

### Tests Performed
- [ ] Visual testing
- [ ] Functional testing
- [ ] Responsive testing
- [ ] Performance testing

### Issues Found
1. [Issue description]
   - Severity: [High/Medium/Low]
   - Steps to reproduce: [Steps]
   - Expected: [Expected behavior]
   - Actual: [Actual behavior]

### Screenshots
[Attach screenshots if needed]

### Notes
[Any additional observations]
```

---

## 🎯 Success Criteria

### Visual
- ✅ All pages have dark backgrounds
- ✅ Text is readable (light on dark)
- ✅ Purple/cyan colors consistent
- ✅ Glassmorphism effects visible
- ✅ Animations smooth

### Functional
- ✅ All CRUD operations work
- ✅ Forms submit successfully
- ✅ Data displays correctly
- ✅ Export functions work
- ✅ Search/filter works

### Responsive
- ✅ Works on desktop
- ✅ Works on tablet
- ✅ Works on mobile
- ✅ Touch interactions work

### Performance
- ✅ Page loads < 2s
- ✅ Animations 60fps
- ✅ No console errors
- ✅ No network errors

---

## 📞 Need Help?

### Check Documentation
1. `COMPLETE_UI_UPDATE_SUMMARY.md` - Full update details
2. `ADMIN_MODULES_UI_FIXED.md` - Admin panel specifics
3. `DEFAULT_CREDENTIALS.md` - Login credentials

### Check Logs
1. PHP errors: `c:\xampp\apache\logs\error.log`
2. MySQL errors: `c:\xampp\mysql\data\*.err`
3. Browser console: Press F12

### Common Commands
```bash
# Start XAMPP services
c:\xampp\xampp-control.exe

# Check MySQL status
mysql -u root -p

# View PHP errors
tail -f c:\xampp\apache\logs\error.log
```

---

## ✅ Final Checklist

Before marking testing complete:

- [ ] All admin pages tested
- [ ] All client pages tested
- [ ] All doctor pages tested
- [ ] Responsive on all devices
- [ ] No console errors
- [ ] No network errors
- [ ] All features working
- [ ] Performance acceptable
- [ ] Documentation reviewed
- [ ] Screenshots taken (if needed)

---

**Happy Testing! 🎉**

If everything looks good, the platform is ready for production use!

---

**Last Updated**: May 12, 2026
**Version**: 1.0.0
