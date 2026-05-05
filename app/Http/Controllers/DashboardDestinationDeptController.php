<?php

namespace App\Http\Controllers;

use App\Services\DashboardDestinationDeptService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardDestinationDeptController extends Controller
{
    public function __construct(
        protected DashboardDestinationDeptService $destinationDeptService
    ) {}

    public function index(Request $request)
    {
        $selectedDate = $request->filled('date')
            ? Carbon::parse($request->string('date'))
            : today();

        $departments = $this->destinationDeptService->getDestinationDepartmentDashboard($selectedDate);

        return Inertia::render('Dashboard/DestinationDept', [
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'departments' => $departments,
        ]);
    }
}
