<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\QueueItem;
use App\Models\User;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard(request());
        }

        return $this->staffDashboard(request());
    }

    public function adminDashboard(Request $request, $date = null)
    {
        // Parse date parameter or use today
        $selectedDate = $date ? Carbon::parse($date) : today();

        $departments = Department::with(['activeQueue.patient'])
            ->where('is_active', true)
            ->get();

        $todayStats = $this->dashboardService->getStatsForDate($selectedDate);
        $departmentStats = $this->dashboardService->getDepartmentStatsForDate($selectedDate);
        $performanceMetrics = $this->dashboardService->getPerformanceMetricsForDate($selectedDate);
        $recentActivity = $this->dashboardService->getRecentActivityForDate($selectedDate);
        $hourlyStats = $this->dashboardService->getHourlyStatsForDate($selectedDate);

        return Inertia::render('Dashboard/Admin', [
            'departments' => $departments,
            'stats' => $todayStats,
            'departmentStats' => $departmentStats,
            'performanceMetrics' => $performanceMetrics,
            'recentActivity' => $recentActivity,
            'hourlyStats' => $hourlyStats,
            'selectedDate' => $selectedDate->format('Y-m-d')
        ]);
    }

    public function staffDashboard(Request $request, $date = null)
    {
        // Parse date parameter or use today
        $selectedDate = $date ? Carbon::parse($date) : today();

        $user = auth()->user();
        $departments = $user->departments()
            ->with(['activeQueue.patient'])
            ->where('is_active', true)
            ->get();

        $userStats = $this->dashboardService->getUserStatsForDate($user, $selectedDate);
        $departmentStats = $this->dashboardService->getDepartmentStatsForUserForDate($user, $selectedDate);
        $recentActivity = $this->dashboardService->getRecentActivityForUserForDate($user, $selectedDate);

        return Inertia::render('Dashboard/Staff', [
            'departments' => $departments,
            'userStats' => $userStats,
            'departmentStats' => $departmentStats,
            'recentActivity' => $recentActivity,
            'selectedDate' => $selectedDate->format('Y-m-d')
        ]);
    }
}
