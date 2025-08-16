<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\QueueItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->staffDashboard();
    }

    private function adminDashboard()
    {
        $departments = Department::with(['activeQueue.patient'])
            ->where('is_active', true)
            ->get();

        $todayStats = [
            'total_patients' => QueueItem::today()->count(),
            'waiting' => QueueItem::today()->waiting()->count(),
            'serving' => QueueItem::today()->serving()->count(),
            'completed' => QueueItem::today()->where('status', 'done')->count(),
        ];

        return Inertia::render('Dashboard/Admin', [
            'departments' => $departments,
            'stats' => $todayStats
        ]);
    }

    private function staffDashboard()
    {
        $user = auth()->user();
        $departments = $user->departments()
            ->with(['activeQueue.patient'])
            ->where('is_active', true)
            ->get();

        return Inertia::render('Dashboard/Staff', [
            'departments' => $departments
        ]);
    }
}
