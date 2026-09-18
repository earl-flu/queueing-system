<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\QueueItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $department_id = $request->input('department_id', 6);
        $selectedDate = $request->input('date', now()->toDateString());
        $totalData = QueueItem::where('current_department_id', $department_id)
            ->whereDate('created_at', $selectedDate)
            ->selectRaw('
                SUM(CASE WHEN status = "skipped" THEN 1 ELSE 0 END) as skipped,
                SUM(CASE WHEN skipped_at IS NOT NULL THEN 1 ELSE 0 END) as with_skip_history,
                SUM(CASE WHEN status = "waiting" THEN 1 ELSE 0 END) as waiting,
                SUM(CASE WHEN status = "transferred" THEN 1 ELSE 0 END) as transferred,
                MIN(created_at) as first_waiting_time,
                MIN(called_at) as first_called_time,
                MAX(created_at) as last_waiting_time,
                MAX(called_at) as last_called_time,
                COUNT(*) as patients
            ')
            ->first();

        $avgData = QueueItem::whereDate('created_at', $selectedDate)
            ->where('current_department_id', $department_id)
            ->whereNull('skipped_at')
            ->selectRaw(
                'AVG(waiting_duration_seconds) as avg_wait,
                 AVG(serving_duration_seconds) as avg_serve'
            )
            ->first();



        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $patients = QueueItem::leftJoin('patients as pat', 'queue_items.patient_id', '=', 'pat.id')
            ->where('queue_items.current_department_id', $department_id)
            ->whereDate('pat.created_at', $selectedDate)
            ->select(
                'queue_items.queue_number',
                'pat.first_name',
                'pat.last_name',
                'pat.is_priority',
                'queue_items.waiting_duration_seconds',
                'queue_items.serving_duration_seconds',
                'queue_items.skipped_at',
                'queue_items.status',
                'queue_items.original_department_id',
                'queue_items.created_at'
            )
            ->get();

        return Inertia::render('Reports/Index', [
            'departments' => $departments,
            'patients' => $patients,
            'totalData' => $totalData,
            'avgData' => $avgData,
            'filters' => [
                'department_id' => $department_id,
                'date' => $selectedDate
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
