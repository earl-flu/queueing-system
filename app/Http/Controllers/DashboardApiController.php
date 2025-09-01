<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get today's statistics
     */
    public function getTodayStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getTodayStats()
        ]);
    }

    /**
     * Get department performance statistics
     */
    public function getDepartmentStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getDepartmentStats()
        ]);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getPerformanceMetrics()
        ]);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getRecentActivity($limit)
        ]);
    }

    /**
     * Get hourly statistics
     */
    public function getHourlyStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getHourlyStats()
        ]);
    }

    /**
     * Get user statistics
     */
    public function getUserStats(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getUserStats($user)
        ]);
    }

    /**
     * Get department statistics for current user
     */
    public function getUserDepartmentStats(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getDepartmentStatsForUser($user)
        ]);
    }

    /**
     * Get recent activity for current user
     */
    public function getUserRecentActivity(Request $request): JsonResponse
    {
        $user = auth()->user();
        $limit = $request->get('limit', 5);

        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getRecentActivityForUser($user, $limit)
        ]);
    }

    /**
     * Get department analytics for a specific department
     */
    public function getDepartmentAnalytics(int $departmentId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->dashboardService->getDepartmentAnalytics($departmentId)
        ]);
    }

    /**
     * Get all dashboard data for admin
     */
    public function getAdminDashboardData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'todayStats' => $this->dashboardService->getTodayStats(),
                'departmentStats' => $this->dashboardService->getDepartmentStats(),
                'performanceMetrics' => $this->dashboardService->getPerformanceMetrics(),
                'recentActivity' => $this->dashboardService->getRecentActivity(),
                'hourlyStats' => $this->dashboardService->getHourlyStats(),
            ]
        ]);
    }

    /**
     * Get all dashboard data for staff
     */
    public function getStaffDashboardData(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => [
                'userStats' => $this->dashboardService->getUserStats($user),
                'departmentStats' => $this->dashboardService->getDepartmentStatsForUser($user),
                'recentActivity' => $this->dashboardService->getRecentActivityForUser($user),
            ]
        ]);
    }
}
