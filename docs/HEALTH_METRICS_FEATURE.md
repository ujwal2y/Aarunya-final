# Health Metrics Management Feature

## Overview
This feature allows administrators to add, edit, and delete health metrics for users (mothers) from the admin panel. The health metrics are then displayed on the client-side health tracking page, showing real-time data instead of hardcoded values.

## Features Implemented

### 1. Database Table: `health_metrics`
Created a new table to store health metrics with the following fields:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `blood_pressure_systolic` - Systolic blood pressure (mmHg)
- `blood_pressure_diastolic` - Diastolic blood pressure (mmHg)
- `hemoglobin` - Hemoglobin level (g/dL)
- `heart_rate` - Heart rate (bpm)
- `weight` - Weight (kg)
- `temperature` - Body temperature (°C) - Optional
- `glucose_level` - Blood glucose level (mg/dL) - Optional
- `notes` - Clinical notes - Optional
- `recorded_by` - Admin ID who recorded the metrics
- `recorded_at` - Timestamp of recording

### 2. Admin Panel - Users Management (`admin/pages/users.php`)

#### Health Metrics Section
- Added a dedicated "Health Metrics History" section in the user details view
- Displays the last 10 health metric records in a table format
- Shows all vital signs with color-coded values
- Displays who recorded the metrics and when

#### Interactive Modal
- **Add New Metrics**: Click "Add New Metrics" button to open a modal form
- **Edit Metrics**: Click edit icon on any metric record to modify values
- **Delete Metrics**: Click delete icon with confirmation prompt

#### Modal Form Fields
- Blood Pressure (Systolic/Diastolic) - Required
- Hemoglobin - Required
- Heart Rate - Required
- Weight - Required
- Temperature - Optional
- Glucose Level - Optional
- Clinical Notes - Optional

#### Features
- Form validation with min/max values
- Normal range indicators for each metric
- Smooth animations and transitions
- Success/error message notifications
- Auto-redirect back to user details after save

### 3. Backend Handler (`admin/actions/update_health_metrics.php`)

#### Actions Supported
- **Add**: Create new health metric record
- **Update**: Modify existing health metric record
- **Delete**: Remove health metric record

#### Features
- Automatic table creation if not exists
- PDO prepared statements for security
- Error handling with try-catch blocks
- Success/error redirects with messages
- Foreign key constraint to users table

### 4. Client Side - Health Tracking (`client/health.php`)

#### Dynamic Health Metrics Display
- Fetches latest health metrics from database
- Displays current values in health cards:
  - Blood Pressure
  - Hemoglobin
  - Heart Rate
  - Weight
- Shows "Last Updated" timestamp
- Falls back to default values if no metrics recorded

#### Features
- Real-time data from database
- Responsive card layout
- Color-coded icons for each metric
- Smooth hover animations
- Mobile-friendly design

## Usage Workflow

### For Administrators:
1. Navigate to **Admin Panel → Users Management**
2. Click "View Details" (eye icon) on any user
3. Scroll to "Health Metrics History" section
4. Click "Add New Metrics" button
5. Fill in the health metrics form
6. Click "Save Metrics"
7. Metrics are saved and displayed in the table
8. Edit or delete metrics as needed

### For Users (Mothers):
1. Navigate to **Health Tracking** page from sidebar
2. View current health metrics (automatically updated)
3. See "Last Updated" timestamp
4. View health metrics history
5. Download health report if needed

## Technical Details

### Security
- Admin authentication required
- PDO prepared statements prevent SQL injection
- Input validation on both client and server side
- Foreign key constraints maintain data integrity

### Database Design
- Normalized structure with foreign keys
- Indexed fields for performance (user_id, recorded_at)
- Cascade delete when user is deleted
- UTF-8 character encoding

### UI/UX
- Pink theme consistency (#F472B6, #C4A7FF, #F8BBD0)
- 8px grid spacing system
- 12-16px border radius
- Smooth 0.2-0.3s transitions
- Glassmorphism effects
- Responsive design for all screen sizes

### JavaScript Functions
- `openAddMetricsModal(userId)` - Opens modal for adding new metrics
- `editMetric(metricId, metricData)` - Opens modal with pre-filled data for editing
- `closeMetricsModal()` - Closes the modal and resets form
- Escape key support to close modal
- Form validation before submission

## Files Modified/Created

### Created:
- `admin/actions/update_health_metrics.php` - Backend handler for CRUD operations
- `docs/HEALTH_METRICS_FEATURE.md` - This documentation file

### Modified:
- `admin/pages/users.php` - Added health metrics section, modal, and JavaScript functions
- `client/health.php` - Updated to fetch and display real-time health metrics from database

## Testing Checklist

- [x] Database table creation
- [x] Add new health metrics
- [x] Edit existing health metrics
- [x] Delete health metrics
- [x] Display metrics in admin panel
- [x] Display metrics on client side
- [x] Form validation
- [x] Success/error messages
- [x] Responsive design
- [x] Modal animations
- [x] Data persistence

## Future Enhancements

1. **Charts & Graphs**: Add trend charts for health metrics over time
2. **Alerts**: Automatic alerts when metrics are outside normal ranges
3. **Export**: Export health metrics as PDF or CSV
4. **Notifications**: Notify users when new metrics are added
5. **Mobile App**: Integrate with mobile app for real-time tracking
6. **AI Insights**: Generate AI-powered health insights based on metrics
7. **Comparison**: Compare metrics across different time periods
8. **Goals**: Set health goals and track progress

## Notes

- Health metrics are displayed in descending order (latest first)
- Only the latest metric is shown on the client health tracking page
- All historical metrics are visible in the admin panel
- Metrics can only be added/edited by administrators
- Users can only view their metrics, not edit them
- The system gracefully handles missing data with default values
