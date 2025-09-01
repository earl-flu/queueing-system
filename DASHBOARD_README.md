# Queueing System Dashboard

A comprehensive dashboard system for managing and monitoring queue operations in real-time.

## Features

### Admin Dashboard

-   **Real-time Statistics**: Total patients, waiting, serving, completed counts
-   **Department Performance**: Individual department metrics with efficiency scores
-   **Performance Metrics**: Daily, weekly, and monthly analytics
-   **Recent Activity**: Live feed of queue activities
-   **Hourly Statistics**: Breakdown of operations by hour (8 AM - 5 PM)
-   **Average Time Analytics**: Waiting and serving time calculations

### Staff Dashboard

-   **Personal Performance**: Individual staff metrics and efficiency scores
-   **Department Queues**: Real-time queue status for assigned departments
-   **Recent Activity**: Personal activity history
-   **Quick Actions**: Direct access to common operations

## Dashboard Components

### 1. Statistics Cards

-   **Total Patients**: Number of patients in the system today
-   **Currently Waiting**: Patients in waiting status
-   **Currently Serving**: Patients being served
-   **Completed**: Successfully completed patients
-   **Average Wait Time**: Mean waiting time across all departments
-   **Average Serving Time**: Mean serving time across all departments

### 2. Department Performance Table

-   **Department Name & Code**: Department identification
-   **Queue Status**: Waiting, serving, and completed counts
-   **Time Metrics**: Average waiting and serving times
-   **Efficiency Score**: Performance rating (0-100%)
-   **Next Queue Position**: Current queue position

### 3. Performance Metrics

-   **Daily Metrics**: Today's performance indicators
-   **Weekly Metrics**: This week's aggregated data
-   **Monthly Metrics**: This month's aggregated data
-   **Completion Rate**: Percentage of completed vs total patients

### 4. Recent Activity Feed

-   **Patient Information**: Name and queue number
-   **Department**: Current department
-   **Status**: Current queue status
-   **Timestamps**: Creation and completion times
-   **Duration**: Waiting and serving times

### 5. Hourly Statistics

-   **Hour Breakdown**: 8:00 AM to 5:00 PM
-   **New Patients**: Patients added per hour
-   **Completed**: Patients completed per hour
-   **Average Wait Time**: Mean waiting time per hour

## API Endpoints

### Dashboard Data APIs

#### Admin Endpoints

```http
GET /api/dashboard/today-stats
GET /api/dashboard/department-stats
GET /api/dashboard/performance-metrics
GET /api/dashboard/recent-activity
GET /api/dashboard/hourly-stats
GET /api/dashboard/admin-data
```

#### Staff Endpoints

```http
GET /api/dashboard/user-stats
GET /api/dashboard/user-department-stats
GET /api/dashboard/user-recent-activity
GET /api/dashboard/staff-data
```

#### Department Analytics

```http
GET /api/dashboard/department/{departmentId}/analytics
```

### Response Format

All API endpoints return JSON responses in the following format:

```json
{
    "success": true,
    "data": {
        // Specific data structure for each endpoint
    }
}
```

## Efficiency Score Calculation

### Department Efficiency

-   **Completion Rate (70%)**: Percentage of completed/transferred patients
-   **Time Score (30%)**: Based on average waiting time (penalizes long waits)
-   **Formula**: `(completion_rate * 0.7) + (time_score * 0.3)`

### User Efficiency

-   **Serving Score (60%)**: Based on number of patients served (10 points each)
-   **Time Score (40%)**: Based on average serving time (penalizes long serves)
-   **Formula**: `(serving_score * 0.6) + (time_score * 0.4)`

## Time Formatting

All time values are returned in seconds and formatted as:

-   **Short Format**: `MM:SS` (e.g., "05:30")
-   **Long Format**: `HH:MM:SS` (e.g., "01:25:45")

## Routes

### Web Routes

```php
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/admin', [DashboardController::class, 'adminDashboard'])->name('dashboard.admin')->middleware('admin');
Route::get('/dashboard/staff', [DashboardController::class, 'staffDashboard'])->name('dashboard.staff');
```

### API Routes

```php
Route::prefix('api/dashboard')->name('dashboard.api.')->group(function () {
    // All dashboard API endpoints
});
```

## Components Structure

```
resources/js/Pages/Dashboard/
├── Admin.vue          # Admin dashboard component
└── Staff.vue          # Staff dashboard component
```

## Services

### DashboardService

Located at `app/Services/DashboardService.php`, this service handles all dashboard calculations and data processing:

-   `getTodayStats()`: Overall daily statistics
-   `getDepartmentStats()`: Department performance metrics
-   `getPerformanceMetrics()`: Time-based performance data
-   `getRecentActivity()`: Recent queue activities
-   `getHourlyStats()`: Hourly breakdown statistics
-   `getUserStats()`: Individual user performance
-   `getDepartmentStatsForUser()`: User's department statistics
-   `getRecentActivityForUser()`: User's recent activities
-   `getDepartmentAnalytics()`: Specific department analytics

## Controllers

### DashboardController

Main controller for web dashboard views:

-   `index()`: Redirects based on user role
-   `adminDashboard()`: Admin dashboard view
-   `staffDashboard()`: Staff dashboard view

### DashboardApiController

API controller for AJAX requests and mobile apps:

-   Individual endpoint methods for each data type
-   JSON responses for real-time updates

## Middleware

### AdminMiddleware

-   Checks if user has admin role
-   Applied to admin-only routes
-   Returns 403 error for unauthorized access

## Usage Examples

### Accessing Dashboard

1. **Admin Users**: Automatically redirected to `/dashboard/admin`
2. **Staff Users**: Automatically redirected to `/dashboard/staff`

### Real-time Updates

```javascript
// Fetch today's statistics
fetch("/api/dashboard/today-stats")
    .then((response) => response.json())
    .then((data) => {
        console.log(data.data);
    });

// Fetch department performance
fetch("/api/dashboard/department-stats")
    .then((response) => response.json())
    .then((data) => {
        console.log(data.data);
    });
```

### Vue Component Usage

```vue
<script setup>
import { ref, onMounted } from "vue";

const stats = ref({});

onMounted(async () => {
    const response = await fetch("/api/dashboard/today-stats");
    const data = await response.json();
    stats.value = data.data;
});
</script>
```

## Performance Considerations

-   **Caching**: Consider implementing Redis caching for frequently accessed data
-   **Database Optimization**: Use database indexes on frequently queried columns
-   **Real-time Updates**: Implement WebSocket connections for live updates
-   **Pagination**: Use pagination for large datasets in recent activity feeds

## Security

-   **Authentication**: All dashboard routes require authentication
-   **Authorization**: Admin routes require admin role
-   **CSRF Protection**: All forms include CSRF tokens
-   **Input Validation**: All API inputs are validated

## Future Enhancements

-   **Real-time Notifications**: Push notifications for queue updates
-   **Export Functionality**: PDF/Excel export of dashboard data
-   **Customizable Widgets**: User-configurable dashboard layouts
-   **Advanced Analytics**: Machine learning insights and predictions
-   **Mobile App**: Native mobile application for dashboard access
