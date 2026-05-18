# Registration Form Update: Last Menstrual Period (LMP)

## What Changed?

The registration form has been updated to use **Last Menstrual Period (LMP)** date instead of manually entering pregnancy week. This is the standard medical practice for pregnancy tracking.

## Changes Made

### 1. Registration Form (`client/register.php`)
- **Removed**: Manual "Pregnancy Week" input field
- **Removed**: Manual "Due Date" input field
- **Added**: "Last Menstrual Period (LMP)" date picker
- **Added**: Automatic calculation display showing:
  - Calculated pregnancy week
  - Calculated due date

### 2. Automatic Calculations
When a user enters their LMP date:
- **Pregnancy Week** = Days since LMP ÷ 7
- **Due Date** = LMP + 280 days (40 weeks)

### 3. Database Update
- Added `lmp_date` column to `users` table
- Stores the original LMP date for reference
- Pregnancy week and due date are calculated and stored

## How It Works

### For Users:
1. Go to registration page
2. Fill in basic information (name, email, password, age)
3. Select **Last Menstrual Period (LMP)** date
4. System automatically calculates and displays:
   - Current pregnancy week
   - Expected due date
5. Complete registration

### Example:
```
LMP Date: January 1, 2026
Today: May 7, 2026

Calculation:
- Days since LMP: 126 days
- Pregnancy Week: 18 weeks
- Due Date: October 8, 2026
```

## Setup Instructions

### Step 1: Add Database Column
Run this script to add the `lmp_date` column:
```
http://localhost/Aarunya/add_lmp_column.php
```

This will:
- Add `lmp_date DATE` column to users table
- Show success message
- Provide links to test the registration form

### Step 2: Test Registration
1. Go to: `http://localhost/Aarunya/client/register.php`
2. Fill in the form
3. Select an LMP date (e.g., 3 months ago)
4. Watch the automatic calculation appear
5. Complete registration

## Medical Accuracy

### Why LMP is Better:
- **Standard Practice**: Used by doctors worldwide
- **More Accurate**: Based on actual menstrual cycle
- **Automatic Calculation**: Reduces user error
- **Consistent**: Everyone calculates the same way

### Pregnancy Calculation Formula:
```
Naegele's Rule:
Due Date = LMP + 280 days (40 weeks)
Pregnancy Week = (Today - LMP) / 7 days
```

## User Interface

### Before:
```
[Pregnancy Week] [Enter number 1-42]
[Due Date] [Select date]
```

### After:
```
[Last Menstrual Period (LMP)] [Select date]
↓ (Automatic calculation appears)
✓ Pregnancy Week: 18 weeks
✓ Due Date: October 8, 2026
```

## Features

### 1. Date Validation
- Maximum date: Today (can't select future dates)
- Reasonable range: Typically 0-42 weeks ago
- Invalid dates show warning

### 2. Real-time Calculation
- Calculates as soon as LMP date is selected
- Updates instantly when date changes
- Shows formatted, readable dates

### 3. Visual Feedback
- Pink-themed info box
- Icons for pregnancy week and due date
- Smooth animations

## Database Schema

### New Column:
```sql
ALTER TABLE users 
ADD COLUMN lmp_date DATE DEFAULT NULL 
AFTER age;
```

### Updated Insert Query:
```sql
INSERT INTO users 
(name, email, password, age, lmp_date, pregnancy_week, due_date, created_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
```

## Benefits

### For Users:
- ✅ Easier to remember LMP date than pregnancy week
- ✅ More accurate pregnancy tracking
- ✅ Automatic calculations reduce errors
- ✅ Standard medical practice

### For Admins:
- ✅ Consistent data across all users
- ✅ Can recalculate pregnancy week anytime
- ✅ Historical LMP data preserved
- ✅ Better reporting and analytics

### For Doctors:
- ✅ Standard medical information
- ✅ Accurate gestational age
- ✅ Reliable due date estimation
- ✅ Better prenatal care planning

## Backward Compatibility

### Existing Users:
- Old users without LMP date will continue to work
- `lmp_date` column allows NULL values
- Pregnancy week and due date remain in database
- No data loss

### Migration:
If you want to calculate LMP for existing users:
```sql
UPDATE users 
SET lmp_date = DATE_SUB(CURDATE(), INTERVAL (pregnancy_week * 7) DAY)
WHERE lmp_date IS NULL AND pregnancy_week > 0;
```

## Testing Checklist

- [ ] Database column added successfully
- [ ] Registration form shows LMP date picker
- [ ] Automatic calculation appears when date selected
- [ ] Pregnancy week calculates correctly
- [ ] Due date calculates correctly (LMP + 280 days)
- [ ] Form submits successfully
- [ ] Data saves to database correctly
- [ ] User can login after registration
- [ ] Dashboard shows correct pregnancy week
- [ ] Health tracking shows correct due date

## Troubleshooting

### Issue: Column doesn't exist error
**Solution**: Run `add_lmp_column.php` to add the column

### Issue: Calculation doesn't appear
**Solution**: Check browser console for JavaScript errors

### Issue: Wrong pregnancy week
**Solution**: Verify LMP date is correct, calculation uses 7-day weeks

### Issue: Wrong due date
**Solution**: Due date = LMP + 280 days (40 weeks), verify calculation

## Future Enhancements

1. **Trimester Display**: Show which trimester based on week
2. **Week-by-Week Guide**: Show fetal development for current week
3. **Milestone Alerts**: Notify important pregnancy milestones
4. **LMP Edit**: Allow users to update LMP if incorrect
5. **Multiple Pregnancies**: Track twins/triplets with adjusted dates

## Medical Disclaimer

This calculation uses the standard Naegele's Rule (LMP + 280 days). Actual due dates may vary based on:
- Individual cycle length
- Ovulation timing
- Ultrasound measurements
- Medical conditions

Always consult with healthcare providers for accurate pregnancy dating.

---

**Last Updated**: May 7, 2026  
**Version**: 2.0  
**For**: Aarunya Maternal Care System
