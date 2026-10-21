<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class LogBookController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->toDateString());

        $startDate = Carbon::parse($selectedDate)->startOfDay();
        $endDate = $startDate->copy()->addDay();

        $departments = DB::table('queue_items as q')
            ->leftJoin('departments as d', 'q.original_department_id', '=', 'd.id')
            ->select(
                'd.id',
                'd.name',
                DB::raw('COUNT(DISTINCT q.patient_id) AS total_logged_patient')
            )
            ->where('q.created_at', '>=', $startDate)
            ->where('q.created_at', '<', $endDate)
            ->groupBy('d.id', 'd.name')
            ->orderBy('d.name')
            ->get();

        return Inertia::render('LogBook/Index', [
            'departments' => $departments,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function show(Request $request, int $id, string $date)
    {
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = $startDate->copy()->addDay();

        $patients = DB::select("
            WITH daily_rows AS (
                SELECT
                    q.id,
                    q.patient_id,
                    q.queue_number,
                    q.original_department_id,
                    q.current_department_id,
                    q.status,
                    q.created_at,
                    q.completed_at,
                    ROW_NUMBER() OVER (
                        PARTITION BY q.queue_number
                        ORDER BY q.created_at DESC, q.id DESC
                    ) AS rn
                FROM queue_items q
                WHERE q.created_at >= :start_date
                  AND q.created_at <  :end_date
                  AND q.original_department_id = :department_id
            ),
            daily_summary AS (
                SELECT
                    queue_number,
                    MIN(created_at) AS start_time,
                    MAX(
                        CASE
                            WHEN status = 'done' THEN completed_at
                        END
                    ) AS finished_time
                FROM daily_rows
                GROUP BY queue_number
            )
            SELECT
                s.queue_number,
                CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name, p.suffix) AS full_name,
                (
                    SELECT df.step_order
                    FROM department_flows df
                    WHERE df.final_department_id = latest.original_department_id
                      AND df.step_department_id = latest.current_department_id
                ) AS step,
                d.name AS dept,
                latest.status AS status,
                s.start_time,
                s.finished_time
            FROM daily_summary s
            JOIN daily_rows latest
                ON latest.queue_number = s.queue_number
               AND latest.rn = 1
            LEFT JOIN departments d
                ON d.id = latest.current_department_id
            LEFT JOIN patients p ON latest.patient_id = p.id
            ORDER BY s.queue_number
        ", [
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'department_id' => $id,
        ]);

        $totalLogged = DB::table('queue_items')
            ->where('original_department_id', $id)
            ->where('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $endDate->format('Y-m-d H:i:s'))
            ->distinct('queue_number')
            ->count('queue_number');

        $totalCompleted = DB::table('queue_items')
            ->where('original_department_id', $id)
            ->where('created_at', '>=', $startDate->format('Y-m-d H:i:s'))
            ->where('created_at', '<', $endDate->format('Y-m-d H:i:s'))
            ->where('status', 'done')
            ->count('queue_number');
        $department = DB::table('departments')->where('id', $id)->first();

        return Inertia::render('LogBook/Show', [
            'patients' => $patients,
            'department' => $department,
            'selectedDate' => $date,
            'totalLogged' => $totalLogged,
            'totalCompleted' => $totalCompleted
        ]);
    }
}
