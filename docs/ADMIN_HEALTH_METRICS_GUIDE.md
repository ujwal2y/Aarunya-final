# Admin Guide: Managing Health Metrics

## Quick Start Guide for Administrators

### What are Health Metrics?
Health metrics are vital signs and measurements that track the health status of pregnant mothers. These include:
- **Blood Pressure** (Systolic/Diastolic)
- **Hemoglobin** levels
- **Heart Rate**
- **Weight**
- **Temperature** (optional)
- **Glucose Level** (optional)
- **Clinical Notes** (optional)

---

## How to Add Health Metrics

### Step 1: Navigate to Users Management
1. Login to the admin panel
2. Click on **"Users Management"** in the sidebar
3. You'll see a list of all registered mothers

### Step 2: Select a User
1. Find the user you want to add metrics for
2. Click the **eye icon** (👁️) in the Actions column
3. This opens the user's detailed profile

### Step 3: Add New Metrics
1. Scroll down to the **"Health Metrics History"** section
2. Click the **"Add New Metrics"** button (pink button with + icon)
3. A modal form will open

### Step 4: Fill in the Form
**Required Fields** (marked with red *):
- **Blood Pressure**: Enter two values
  - Systolic (top number): e.g., 120
  - Diastolic (bottom number): e.g., 80
  - Normal range: 120/80 mmHg
  
- **Hemoglobin**: e.g., 12.5
  - Normal range: 11-16 g/dL for pregnant women
  
- **Heart Rate**: e.g., 75
  - Normal range: 60-100 bpm
  
- **Weight**: e.g., 65
  - Track weight gain during pregnancy

**Optional Fields**:
- **Temperature**: e.g., 36.5
  - Normal range: 36.5-37.5 °C
  
- **Glucose Level**: e.g., 90
  - Fasting range: 70-100 mg/dL
  
- **Clinical Notes**: Any observations or recommendations
  - Example: "Patient reports feeling well. No complications."

### Step 5: Save the Metrics
1. Review all entered values
2. Click the **"Save Metrics"** button
3. You'll see a success message: "Health metrics added successfully"
4. The new record appears in the health metrics table

---

## How to Edit Health Metrics

### When to Edit:
- Correct a data entry error
- Update values after re-measurement
- Add missing information

### Steps:
1. Go to the user's detail page
2. Find the metric record you want to edit in the table
3. Click the **pencil icon** (✏️) in the Actions column
4. The modal opens with pre-filled values
5. Modify the values you want to change
6. Click **"Update Metrics"**
7. Success message appears: "Health metrics updated successfully"

**Note**: The original recording timestamp is preserved when editing.

---

## How to Delete Health Metrics

### When to Delete:
- Duplicate entries
- Incorrect data that cannot be corrected
- Test entries

### Steps:
1. Go to the user's detail page
2. Find the metric record you want to delete
3. Click the **trash icon** (🗑️) in the Actions column
4. A confirmation dialog appears: "Delete this health metric record?"
5. Click **"OK"** to confirm
6. Success message appears: "Health metrics deleted successfully"

**Warning**: Deletion is permanent and cannot be undone!

---

## Understanding the Health Metrics Table

### Table Columns:
1. **Date & Time**: When the metrics were recorded
2. **Blood Pressure**: Systolic/Diastolic in mmHg (color: pink)
3. **Hemoglobin**: Level in g/dL (color: green)
4. **Heart Rate**: Beats per minute (color: blue)
5. **Weight**: Weight in kg (color: orange)
6. **Temperature**: Body temperature in °C
7. **Glucose**: Blood glucose in mg/dL
8. **Recorded By**: Admin who entered the data
9. **Actions**: Edit and Delete buttons

### Reading the Data:
- **Color-coded values** make it easy to spot different metrics
- **Latest record appears first** (most recent at top)
- **Up to 10 records** are shown per user
- **N/A** appears for optional fields that weren't filled

---

## Normal Ranges Reference

### Blood Pressure
- **Normal**: 120/80 mmHg
- **Elevated**: 120-129 / <80 mmHg
- **High**: ≥130 / ≥80 mmHg
- **Low**: <90 / <60 mmHg

### Hemoglobin (Pregnant Women)
- **Normal**: 11-16 g/dL
- **Low (Anemia)**: <11 g/dL
- **High**: >16 g/dL

### Heart Rate
- **Normal**: 60-100 bpm
- **Low (Bradycardia)**: <60 bpm
- **High (Tachycardia)**: >100 bpm

### Weight Gain (During Pregnancy)
- **Underweight (BMI <18.5)**: 28-40 lbs
- **Normal weight (BMI 18.5-24.9)**: 25-35 lbs
- **Overweight (BMI 25-29.9)**: 15-25 lbs
- **Obese (BMI ≥30)**: 11-20 lbs

### Temperature
- **Normal**: 36.5-37.5 °C (97.7-99.5 °F)
- **Low**: <36.5 °C
- **Fever**: >37.5 °C

### Glucose (Fasting)
- **Normal**: 70-100 mg/dL
- **Prediabetes**: 100-125 mg/dL
- **Diabetes**: ≥126 mg/dL

---

## Best Practices

### 1. Regular Monitoring
- Record metrics at each appointment
- Track trends over time
- Note any significant changes

### 2. Accurate Data Entry
- Double-check values before saving
- Use consistent units (mmHg, g/dL, bpm, kg)
- Record immediately after measurement

### 3. Clinical Notes
- Document any symptoms or concerns
- Note medications or treatments
- Record patient feedback

### 4. Data Privacy
- Only authorized admins should access health data
- Don't share sensitive information
- Follow HIPAA guidelines (if applicable)

### 5. Communication
- Inform patients when metrics are recorded
- Explain any abnormal values
- Provide recommendations based on data

---

## How Users See Their Metrics

### Client Side Display:
When users login and visit their **Health Tracking** page, they see:

1. **Four Health Cards** showing their latest metrics:
   - Blood Pressure card (pink icon)
   - Hemoglobin card (red icon)
   - Heart Rate card (red icon)
   - Weight card (scale icon)

2. **Last Updated** timestamp showing when metrics were last recorded

3. **Pregnancy Progress** section with:
   - Current pregnancy week
   - Due date
   - Days remaining

4. **Health Records** section (if available)

**Note**: Users can only VIEW their metrics, not edit them. Only admins can add/edit/delete health metrics.

---

## Troubleshooting

### Problem: Modal doesn't open
**Solution**: Refresh the page and try again. Check if JavaScript is enabled.

### Problem: Form won't submit
**Solution**: Ensure all required fields are filled. Check for validation errors.

### Problem: Success message doesn't appear
**Solution**: The data may still be saved. Refresh the page to verify.

### Problem: Metrics not showing on client side
**Solution**: Ensure at least one metric record exists for the user.

### Problem: Can't delete a metric
**Solution**: Check if you have admin permissions. Try refreshing the page.

---

## Keyboard Shortcuts

- **Tab**: Move to next form field
- **Shift + Tab**: Move to previous form field
- **Enter**: Submit form (when focused on submit button)
- **Escape**: Close modal
- **Ctrl + F**: Search for users

---

## Tips for Efficient Data Entry

1. **Use Tab key** to move between fields quickly
2. **Keep a checklist** of metrics to record
3. **Use templates** for common clinical notes
4. **Record in batches** if multiple patients have appointments
5. **Review data** before saving to avoid corrections later

---

## Reporting & Analytics

### Current Features:
- View last 10 metric records per user
- See who recorded each metric
- Track changes over time

### Coming Soon:
- Charts and graphs for trend analysis
- Export metrics to PDF/CSV
- Automated alerts for abnormal values
- Comparison reports across time periods

---

## Support & Questions

If you encounter any issues or have questions:
1. Check this guide first
2. Review the testing guide: `docs/TESTING_HEALTH_METRICS.md`
3. Check the technical documentation: `docs/HEALTH_METRICS_FEATURE.md`
4. Contact your system administrator

---

## Quick Reference Card

```
┌─────────────────────────────────────────────┐
│         HEALTH METRICS QUICK GUIDE          │
├─────────────────────────────────────────────┤
│                                             │
│  ADD METRICS:                               │
│  Users → View Details → Add New Metrics     │
│                                             │
│  EDIT METRICS:                              │
│  Click pencil icon → Modify → Update        │
│                                             │
│  DELETE METRICS:                            │
│  Click trash icon → Confirm                 │
│                                             │
│  REQUIRED FIELDS:                           │
│  • Blood Pressure (120/80)                  │
│  • Hemoglobin (12.5)                        │
│  • Heart Rate (75)                          │
│  • Weight (65)                              │
│                                             │
│  OPTIONAL FIELDS:                           │
│  • Temperature (36.5)                       │
│  • Glucose (90)                             │
│  • Clinical Notes                           │
│                                             │
└─────────────────────────────────────────────┘
```

---

**Last Updated**: May 7, 2026  
**Version**: 1.0  
**For**: Aarunya Maternal Care System
