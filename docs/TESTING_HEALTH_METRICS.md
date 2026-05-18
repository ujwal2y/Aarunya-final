# Testing Guide: Health Metrics Feature

## Prerequisites
- XAMPP/WAMP running with Apache and MySQL
- Aarunya project installed and configured
- Admin and user accounts created
- At least one user (mother) registered in the system

## Test Scenarios

### Test 1: Database Table Creation
**Objective**: Verify the health_metrics table is created automatically

**Steps**:
1. Open phpMyAdmin
2. Select `aarunya_db` database
3. Check if `health_metrics` table exists
4. If not, visit `admin/actions/update_health_metrics.php` once to trigger creation

**Expected Result**: Table `health_metrics` exists with all required columns

---

### Test 2: Add New Health Metrics (Admin Side)
**Objective**: Test adding new health metrics for a user

**Steps**:
1. Login as admin (admin@aarunya.com / admin123)
2. Navigate to **Users Management** page
3. Click "View Details" (eye icon) on any user
4. Scroll to "Health Metrics History" section
5. Click "Add New Metrics" button
6. Fill in the form:
   - Blood Pressure: 120 / 80
   - Hemoglobin: 12.5
   - Heart Rate: 75
   - Weight: 65
   - Temperature: 36.5 (optional)
   - Glucose: 90 (optional)
   - Notes: "Regular checkup - all vitals normal"
7. Click "Save Metrics"

**Expected Result**:
- Success message appears: "Health metrics added successfully"
- New record appears in the health metrics table
- All values are displayed correctly
- Timestamp shows current date/time
- "Recorded By" shows admin name

---

### Test 3: Edit Existing Health Metrics
**Objective**: Test editing health metrics

**Steps**:
1. From the health metrics table, click "Edit" (pencil icon) on any record
2. Modal opens with pre-filled values
3. Change some values:
   - Blood Pressure: 125 / 82
   - Weight: 66
4. Click "Update Metrics"

**Expected Result**:
- Success message: "Health metrics updated successfully"
- Record shows updated values
- Timestamp remains the same (original recording time)
- Other unchanged values remain intact

---

### Test 4: Delete Health Metrics
**Objective**: Test deleting health metrics

**Steps**:
1. From the health metrics table, click "Delete" (trash icon) on any record
2. Confirm deletion in the popup dialog
3. Click "OK"

**Expected Result**:
- Success message: "Health metrics deleted successfully"
- Record is removed from the table
- Page refreshes showing updated list

---

### Test 5: View Health Metrics (Client Side)
**Objective**: Verify users can see their health metrics

**Steps**:
1. Logout from admin panel
2. Login as a user (test@example.com / test123)
3. Navigate to **Health Tracking** page from sidebar
4. Check the health metric cards at the top

**Expected Result**:
- Blood Pressure card shows latest value (e.g., "120/80 mmHg")
- Hemoglobin card shows latest value (e.g., "12.5 g/dL")
- Heart Rate card shows latest value (e.g., "75 bpm")
- Weight card shows latest value (e.g., "65 kg")
- "Last Updated" timestamp shows when metrics were recorded
- If no metrics exist, default values are shown (120/80, 12.5, 75, 65)

---

### Test 6: Multiple Metrics History
**Objective**: Test viewing multiple health metric records

**Steps**:
1. Login as admin
2. Add 3-5 different health metric records for the same user with different values
3. View user details
4. Check the health metrics table

**Expected Result**:
- All records are displayed in descending order (latest first)
- Each record shows complete information
- Different values are clearly visible
- Timestamps are in correct order

---

### Test 7: Form Validation
**Objective**: Test form validation rules

**Steps**:
1. Open "Add New Metrics" modal
2. Try to submit with empty required fields
3. Try to enter invalid values:
   - Blood Pressure: 300 / 200 (too high)
   - Hemoglobin: 50 (too high)
   - Heart Rate: 300 (too high)
   - Weight: 500 (too high)

**Expected Result**:
- Form prevents submission with empty required fields
- Browser validation shows error messages
- Min/max constraints are enforced
- Form only submits with valid values

---

### Test 8: Modal Interactions
**Objective**: Test modal open/close functionality

**Steps**:
1. Click "Add New Metrics" button
2. Modal opens
3. Click the X button in top-right corner
4. Modal closes
5. Open modal again
6. Press Escape key
7. Click outside the modal (on overlay)

**Expected Result**:
- Modal opens smoothly with animation
- X button closes modal
- Escape key closes modal
- Clicking overlay closes modal
- Form is reset when modal closes

---

### Test 9: Responsive Design
**Objective**: Test on different screen sizes

**Steps**:
1. Open admin users page on desktop (1920x1080)
2. Resize browser to tablet size (768px)
3. Resize to mobile size (375px)
4. Test modal on each size

**Expected Result**:
- Layout adapts to screen size
- Modal is readable on all sizes
- Form fields stack properly on mobile
- Buttons remain accessible
- No horizontal scrolling

---

### Test 10: No Metrics Scenario
**Objective**: Test when user has no health metrics

**Steps**:
1. Create a new user account
2. Login as admin
3. View the new user's details
4. Check health metrics section
5. Login as the new user
6. Visit Health Tracking page

**Expected Result**:
- Admin panel shows: "No health metrics recorded yet"
- Shows "Add First Health Metrics" button
- Client side shows default values (120/80, 12.5, 75, 65)
- "Last Updated" shows "Not recorded yet"

---

## Common Issues & Solutions

### Issue 1: Modal doesn't open
**Solution**: Check browser console for JavaScript errors. Ensure jQuery/JavaScript is loaded.

### Issue 2: Form submission fails
**Solution**: Check database connection. Verify `health_metrics` table exists.

### Issue 3: Metrics not showing on client side
**Solution**: Ensure user has at least one health metric record. Check database query in `client/health.php`.

### Issue 4: Success message not appearing
**Solution**: Check URL parameters after redirect. Verify message handling code in `admin/pages/users.php`.

### Issue 5: Edit button not working
**Solution**: Check JSON encoding in the onclick attribute. Verify `editMetric()` function exists.

---

## Database Verification Queries

### Check if table exists:
```sql
SHOW TABLES LIKE 'health_metrics';
```

### View all health metrics:
```sql
SELECT * FROM health_metrics ORDER BY recorded_at DESC;
```

### View metrics for specific user:
```sql
SELECT * FROM health_metrics WHERE user_id = 1 ORDER BY recorded_at DESC;
```

### View latest metric for each user:
```sql
SELECT hm.*, u.name as user_name 
FROM health_metrics hm
INNER JOIN users u ON hm.user_id = u.id
WHERE hm.id IN (
    SELECT MAX(id) FROM health_metrics GROUP BY user_id
);
```

### Count metrics per user:
```sql
SELECT user_id, COUNT(*) as metric_count 
FROM health_metrics 
GROUP BY user_id;
```

---

## Performance Testing

### Test with Large Dataset:
1. Add 100+ health metric records for a single user
2. Check page load time
3. Verify pagination or limit works (currently limited to 10 records)
4. Check database query performance

### Expected Performance:
- Page load: < 2 seconds
- Modal open: < 0.3 seconds
- Form submission: < 1 second
- Database query: < 100ms

---

## Security Testing

### Test 1: SQL Injection
Try entering SQL commands in form fields:
```
'; DROP TABLE health_metrics; --
```
**Expected**: Input is sanitized, no SQL execution

### Test 2: XSS Attack
Try entering JavaScript in notes field:
```
<script>alert('XSS')</script>
```
**Expected**: Script is escaped and displayed as text

### Test 3: Unauthorized Access
Try accessing update_health_metrics.php without login:
```
http://localhost/Aarunya/admin/actions/update_health_metrics.php
```
**Expected**: Redirect to login page

---

## Accessibility Testing

1. **Keyboard Navigation**: Tab through form fields
2. **Screen Reader**: Test with NVDA/JAWS
3. **Color Contrast**: Verify text is readable
4. **Focus Indicators**: Check visible focus states
5. **ARIA Labels**: Verify form labels are associated

---

## Browser Compatibility

Test on:
- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Edge (latest)
- ✅ Safari (latest)
- ✅ Mobile Chrome
- ✅ Mobile Safari

---

## Sign-off Checklist

- [ ] All 10 test scenarios pass
- [ ] No console errors
- [ ] Responsive design works
- [ ] Form validation works
- [ ] Database queries are optimized
- [ ] Security measures in place
- [ ] Success/error messages display correctly
- [ ] Modal interactions smooth
- [ ] Client side displays correct data
- [ ] Documentation is complete

---

## Test Results Template

```
Test Date: _______________
Tester Name: _______________
Environment: XAMPP / WAMP / Other: _______________

Test 1: Database Table Creation       [ PASS / FAIL ]
Test 2: Add New Health Metrics         [ PASS / FAIL ]
Test 3: Edit Existing Health Metrics   [ PASS / FAIL ]
Test 4: Delete Health Metrics          [ PASS / FAIL ]
Test 5: View Health Metrics (Client)   [ PASS / FAIL ]
Test 6: Multiple Metrics History       [ PASS / FAIL ]
Test 7: Form Validation                [ PASS / FAIL ]
Test 8: Modal Interactions             [ PASS / FAIL ]
Test 9: Responsive Design              [ PASS / FAIL ]
Test 10: No Metrics Scenario           [ PASS / FAIL ]

Overall Result: [ PASS / FAIL ]

Notes:
_________________________________________________
_________________________________________________
_________________________________________________
```
