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
            return $this->adminDashboard();
        }

        return $this->staffDashboard();
    }

    public function adminDashboard()
    {
        $departments = Department::with(['activeQueue.patient'])
            ->where('is_active', true)
            ->get();

        $todayStats = $this->dashboardService->getTodayStats();
        $departmentStats = $this->dashboardService->getDepartmentStats();
        $performanceMetrics = $this->dashboardService->getPerformanceMetrics();
        $recentActivity = $this->dashboardService->getRecentActivity();
        $hourlyStats = $this->dashboardService->getHourlyStats();

        return Inertia::render('Dashboard/Admin', [
            'departments' => $departments,
            'stats' => $todayStats,
            'departmentStats' => $departmentStats,
            'performanceMetrics' => $performanceMetrics,
            'recentActivity' => $recentActivity,
            'hourlyStats' => $hourlyStats
        ]);
    }

    public function staffDashboard()
    {
        $user = auth()->user();
        $departments = $user->departments()
            ->with(['activeQueue.patient'])
            ->where('is_active', true)
            ->get();

        $userStats = $this->dashboardService->getUserStats($user);
        $departmentStats = $this->dashboardService->getDepartmentStatsForUser($user);
        $recentActivity = $this->dashboardService->getRecentActivityForUser($user);

        return Inertia::render('Dashboard/Staff', [
            'departments' => $departments,
            'userStats' => $userStats,
            'departmentStats' => $departmentStats,
            'recentActivity' => $recentActivity
        ]);
    }
}
