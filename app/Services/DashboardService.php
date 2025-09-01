<?php

namespace App\Services;

use App\Models\Department;
use App\Models\QueueItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get today's overall statistics
     */
    public function getTodayStats(): array
    {
        $today = today();

        return [
            'total_patients' => QueueItem::today()->count(),
            'waiting' => QueueItem::today()->waiting()->count(),
            'serving' => QueueItem::today()->serving()->count(),
            'completed' => QueueItem::today()->where('status', 'done')->count(),
            'transferred' => QueueItem::today()->where('status', 'transferred')->count(),
            'no_show' => QueueItem::today()->where('status', 'no_show')->count(),
            'total_waiting_time' => QueueItem::today()
                ->whereNotNull('waiting_duration_seconds')
                ->sum('waiting_duration_seconds'),
            'total_serving_time' => QueueItem::today()
                ->whereNotNull('serving_duration_seconds')
                ->sum('serving_duration_seconds'),
            'avg_waiting_time' => QueueItem::today()
                ->whereNotNull('waiting_duration_seconds')
                ->avg('waiting_duration_seconds'),
            'avg_serving_time' => QueueItem::today()
                ->whereNotNull('serving_duration_seconds')
                ->avg('serving_duration_seconds'),
        ];
    }

    /**
     * Get department performance statistics
     */
    public function getDepartmentStats(): array
    {
        $departments = Department::where('is_active', true)->get();
        $stats = [];

        foreach ($departments as $department) {
            $todayQueue = QueueItem::today()->where('current_department_id', $department->id);
            $completedQueue = $todayQueue->whereIn('status', ['done', 'transferred']);

            $avgWaitingTime = QueueItem::today()
                ->where('current_department_id', $department->id)
                ->whereNotNull('waiting_duration_seconds')
                ->avg('waiting_duration_seconds');

            $avgServingTime = QueueItem::today()
                ->where('current_department_id', $department->id)
                ->whereNotNull('serving_duration_seconds')
                ->avg('serving_duration_seconds');

            $stats[] = [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'total_patients' => $todayQueue->count(),
                'waiting' => $todayQueue->waiting()->count(),
                'serving' => $todayQueue->serving()->count(),
                'completed' => $completedQueue->count(),
                'avg_waiting_time' => round($avgWaitingTime ?? 0, 2),
                'avg_serving_time' => round($avgServingTime ?? 0, 2),
                'avg_total_time' => round(($avgWaitingTime ?? 0) + ($avgServingTime ?? 0), 2),
                'efficiency_score' => $this->calculateEfficiencyScore($department->id),
                'current_queue_position' => $todayQueue->waiting()->min('queue_position') ?? 0,
            ];
        }

        return $stats;
    }

    /**
     * Get performance metrics for different time periods
     */
    public function getPerformanceMetrics(): array
    {
        $today = today();
        $weekStart = $today->copy()->startOfWeek();
        $monthStart = $today->copy()->startOfMonth();

        return [
            'daily' => [
                'total_patients' => QueueItem::whereDate('created_at', $today)->count(),
                'avg_waiting_time' => QueueItem::whereDate('created_at', $today)
                    ->whereNotNull('waiting_duration_seconds')
                    ->avg('waiting_duration_seconds'),
                'avg_serving_time' => QueueItem::whereDate('created_at', $today)
                    ->whereNotNull('serving_duration_seconds')
                    ->avg('serving_duration_seconds'),
                'completion_rate' => $this->calculateCompletionRate($today),
            ],
            'weekly' => [
                'total_patients' => QueueItem::whereBetween('created_at', [$weekStart, $today])->count(),
                'avg_waiting_time' => QueueItem::whereBetween('created_at', [$weekStart, $today])
                    ->whereNotNull('waiting_duration_seconds')
                    ->avg('waiting_duration_seconds'),
                'avg_serving_time' => QueueItem::whereBetween('created_at', [$weekStart, $today])
                    ->whereNotNull('serving_duration_seconds')
                    ->avg('serving_duration_seconds'),
                'completion_rate' => $this->calculateCompletionRate($weekStart, $today),
            ],
            'monthly' => [
                'total_patients' => QueueItem::whereBetween('created_at', [$monthStart, $today])->count(),
                'avg_waiting_time' => QueueItem::whereBetween('created_at', [$monthStart, $today])
                    ->whereNotNull('waiting_duration_seconds')
                    ->avg('waiting_duration_seconds'),
                'avg_serving_time' => QueueItem::whereBetween('created_at', [$monthStart, $today])
                    ->whereNotNull('serving_duration_seconds')
                    ->avg('serving_duration_seconds'),
                'completion_rate' => $this->calculateCompletionRate($monthStart, $today),
            ]
        ];
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit = 10): array
    {
        return QueueItem::with(['patient', 'currentDepartment', 'servedByUser'])
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'queue_number' => $item->queue_number,
                    'patient_name' => $item->patient->first_name .  $item->patient->last_name ?? 'Unknown',
                    'department' => $item->currentDepartment->name ?? 'Unknown',
                    'status' => $item->status,
                    'created_at' => $item->created_at->format('H:i'),
                    'served_by' => $item->servedByUser->name ?? null,
                    'waiting_time' => $item->waiting_duration_seconds ?
                        gmdate('H:i:s', $item->waiting_duration_seconds) : null,
                    'serving_time' => $item->serving_duration_seconds ?
                        gmdate('H:i:s', $item->serving_duration_seconds) : null,
                ];
            })
            ->toArray();
    }

    /**
     * Get hourly statistics
     */
    public function getHourlyStats(): array
    {
        $today = today();
        $hourlyData = [];

        for ($hour = 8; $hour <= 17; $hour++) {
            $startTime = $today->copy()->setTime($hour, 0, 0);
            $endTime = $today->copy()->setTime($hour, 59, 59);

            $hourlyData[] = [
                'hour' => $hour . ':00',
                'new_patients' => QueueItem::whereBetween('created_at', [$startTime, $endTime])->count(),
                'completed' => QueueItem::whereBetween('completed_at', [$startTime, $endTime])
                    ->whereIn('status', ['done', 'transferred'])->count(),
                'avg_waiting_time' => QueueItem::whereBetween('created_at', [$startTime, $endTime])
                    ->whereNotNull('waiting_duration_seconds')
                    ->avg('waiting_duration_seconds'),
            ];
        }

        return $hourlyData;
    }

    /**
     * Get user-specific statistics
     */
    public function getUserStats(User $user): array
    {
        $today = today();

        return [
            'total_served_today' => $user->servedQueues()
                ->whereDate('created_at', $today)
                ->whereIn('status', ['done', 'transferred'])
                ->count(),
            'avg_serving_time' => $user->servedQueues()
                ->whereDate('created_at', $today)
                ->whereNotNull('serving_duration_seconds')
                ->avg('serving_duration_seconds'),
            'current_serving' => $user->servedQueues()
                ->whereDate('created_at', $today)
                ->where('status', 'serving')
                ->count(),
            'efficiency_score' => $this->calculateUserEfficiencyScore($user->id),
        ];
    }

    /**
     * Get department statistics for a specific user
     */
    public function getDepartmentStatsForUser(User $user): array
    {
        $departments = $user->departments()->where('is_active', true)->get();
        $stats = [];

        foreach ($departments as $department) {
            $todayQueue = QueueItem::today()->where('current_department_id', $department->id);

            $stats[] = [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'waiting' => $todayQueue->waiting()->count(),
                'serving' => $todayQueue->serving()->count(),
                'avg_waiting_time' => QueueItem::today()
                    ->where('current_department_id', $department->id)
                    ->whereNotNull('waiting_duration_seconds')
                    ->avg('waiting_duration_seconds'),
                'next_queue_number' => $todayQueue->waiting()->min('queue_position') ?? 0,
            ];
        }

        return $stats;
    }

    /**
     * Get recent activity for a specific user
     */
    public function getRecentActivityForUser(User $user, int $limit = 5): array
    {
        return QueueItem::with(['patient', 'currentDepartment'])
            ->where('served_by', $user->id)
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'queue_number' => $item->queue_number,
                    'patient_name' => $item->patient->name ?? 'Unknown',
                    'status' => $item->status,
                    'created_at' => $item->created_at->format('H:i'),
                    'serving_time' => $item->serving_duration_seconds ?
                        gmdate('H:i:s', $item->serving_duration_seconds) : null,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate efficiency score for a department
     */
    private function calculateEfficiencyScore(int $departmentId): float
    {
        $today = today();
        $completed = QueueItem::today()
            ->where('current_department_id', $departmentId)
            ->whereIn('status', ['done', 'transferred'])
            ->count();

        $total = QueueItem::today()
            ->where('current_department_id', $departmentId)
            ->count();

        if ($total === 0) return 0;

        $avgWaitingTime = QueueItem::today()
            ->where('current_department_id', $departmentId)
            ->whereNotNull('waiting_duration_seconds')
            ->avg('waiting_duration_seconds') ?? 0;

        // Efficiency score based on completion rate and average waiting time
        $completionRate = ($completed / $total) * 100;
        $timeScore = max(0, 100 - ($avgWaitingTime / 60)); // Penalize long waiting times

        return round(($completionRate * 0.7) + ($timeScore * 0.3), 1);
    }

    /**
     * Calculate efficiency score for a user
     */
    private function calculateUserEfficiencyScore(int $userId): float
    {
        $today = today();
        $served = QueueItem::where('served_by', $userId)
            ->whereDate('created_at', $today)
            ->whereIn('status', ['done', 'transferred'])
            ->count();

        $avgServingTime = QueueItem::where('served_by', $userId)
            ->whereDate('created_at', $today)
            ->whereNotNull('serving_duration_seconds')
            ->avg('serving_duration_seconds') ?? 0;

        // Efficiency based on number of patients served and average serving time
        $servingScore = min(100, $served * 10); // 10 points per patient served
        $timeScore = max(0, 100 - ($avgServingTime / 60)); // Penalize long serving times

        return round(($servingScore * 0.6) + ($timeScore * 0.4), 1);
    }

    /**
     * Calculate completion rate for a date range
     */
    private function calculateCompletionRate(Carbon $startDate, Carbon $endDate = null): float
    {
        $endDate = $endDate ?? $startDate;

        $total = QueueItem::whereBetween('created_at', [$startDate, $endDate])->count();
        $completed = QueueItem::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['done', 'transferred'])
            ->count();

        if ($total === 0) return 0;

        return round(($completed / $total) * 100, 1);
    }

    /**
     * Get queue analytics for a specific department
     */
    public function getDepartmentAnalytics(int $departmentId): array
    {
        $today = today();
        $weekStart = $today->copy()->startOfWeek();
        $monthStart = $today->copy()->startOfMonth();

        return [
            'today' => [
                'total' => QueueItem::today()->where('current_department_id', $departmentId)->count(),
                'waiting' => QueueItem::today()->where('current_department_id', $departmentId)->waiting()->count(),
                'serving' => QueueItem::today()->where('current_department_id', $departmentId)->serving()->count(),
                'completed' => QueueItem::today()->where('current_department_id', $departmentId)
                    ->whereIn('status', ['done', 'transferred'])->count(),
                'avg_waiting_time' => QueueItem::today()->where('current_department_id', $departmentId)
                    ->whereNotNull('waiting_duration_seconds')->avg('waiting_duration_seconds'),
                'avg_serving_time' => QueueItem::today()->where('current_department_id', $departmentId)
                    ->whereNotNull('serving_duration_seconds')->avg('serving_duration_seconds'),
            ],
            'weekly' => [
                'total' => QueueItem::whereBetween('created_at', [$weekStart, $today])
                    ->where('current_department_id', $departmentId)->count(),
                'completed' => QueueItem::whereBetween('created_at', [$weekStart, $today])
                    ->where('current_department_id', $departmentId)
                    ->whereIn('status', ['done', 'transferred'])->count(),
            ],
            'monthly' => [
                'total' => QueueItem::whereBetween('created_at', [$monthStart, $today])
                    ->where('current_department_id', $departmentId)->count(),
                'completed' => QueueItem::whereBetween('created_at', [$monthStart, $today])
                    ->where('current_department_id', $departmentId)
                    ->whereIn('status', ['done', 'transferred'])->count(),
            ]
        ];
    }
}
