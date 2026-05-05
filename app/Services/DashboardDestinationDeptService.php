<?php

namespace App\Services;

use App\Models\Department;
use App\Models\QueueItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardDestinationDeptService
{
    /**
     * Per-department stats for patients whose journey destination (original department) is that dept.
     */
    public function getDestinationDepartmentDashboard(Carbon $date): array
    {
        $departments = Department::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return $departments->map(function (Department $dept) use ($date) {
            return $this->buildDepartmentPayload($dept, $date);
        })->values()->all();
    }

    private function buildDepartmentPayload(Department $dept, Carbon $date): array
    {
        $deptId = $dept->id;
        $day = $date->toDateString();

        $totalPatients = (int) QueueItem::query()
            ->whereDate('created_at', $day)
            ->where('original_department_id', $deptId)
            ->selectRaw('COUNT(DISTINCT patient_id) as cnt')
            ->value('cnt');

        $totalServed = (int) QueueItem::query()
            ->whereDate('created_at', $day)
            ->where('original_department_id', $deptId)
            ->where('current_department_id', $deptId)
            ->whereIn('status', ['transferred', 'done'])
            ->selectRaw('COUNT(DISTINCT patient_id) as cnt')
            ->value('cnt');

        $atDestinationBase = QueueItem::query()
            ->whereDate('created_at', $day)
            ->where('original_department_id', $deptId)
            ->where('current_department_id', $deptId);

        $avgWaiting = (clone $atDestinationBase)
            ->whereNotNull('waiting_duration_seconds')
            ->avg('waiting_duration_seconds');

        $avgServing = (clone $atDestinationBase)
            ->whereNotNull('serving_duration_seconds')
            ->avg('serving_duration_seconds');

        $avgTimeToDestination = $this->averageSecondsToReachDestination($deptId, $day);

        $patients = $this->patientsForDestinationDepartment($deptId, $day);

        return [
            'id' => $dept->id,
            'name' => $dept->name,
            'code' => $dept->code,
            'total_patients' => $totalPatients,
            'total_served' => $totalServed,
            'avg_waiting_seconds' => $avgWaiting !== null ? round((float) $avgWaiting, 2) : null,
            'avg_serving_seconds' => $avgServing !== null ? round((float) $avgServing, 2) : null,
            'avg_time_to_destination_seconds' => $avgTimeToDestination !== null ? round($avgTimeToDestination, 2) : null,
            'patients' => $patients,
        ];
    }

    /**
     * From first queue ticket that day (same destination) until first row where they arrive at the destination dept.
     */
    private function averageSecondsToReachDestination(int $departmentId, string $day): ?float
    {
        $result = DB::selectOne(
            'SELECT AVG(TIMESTAMPDIFF(SECOND, first_step.first_at, dest_step.dest_at)) AS avg_secs
            FROM (
                SELECT queue_number, MIN(created_at) AS first_at
                FROM queue_items
                WHERE DATE(created_at) = ? AND original_department_id = ?
                GROUP BY queue_number
            ) AS first_step
            INNER JOIN (
                SELECT queue_number, MIN(created_at) AS dest_at
                FROM queue_items
                WHERE DATE(created_at) = ?
                  AND original_department_id = ?
                  AND current_department_id = ?
                GROUP BY queue_number
            ) AS dest_step ON first_step.queue_number = dest_step.queue_number',
            [$day, $departmentId, $day, $departmentId, $departmentId]
        );

        if ($result === null || $result->avg_secs === null) {
            return null;
        }

        return (float) $result->avg_secs;
    }

    /**
     * Latest queue row per patient for this destination (same calendar day, same original_department_id).
     */
    private function patientsForDestinationDepartment(int $departmentId, string $day): array
    {
        $latestIds = QueueItem::query()
            ->selectRaw('MAX(id) as id')
            ->whereDate('created_at', $day)
            ->where('original_department_id', $departmentId)
            ->groupBy('patient_id')
            ->pluck('id');

        if ($latestIds->isEmpty()) {
            return [];
        }

        $items = QueueItem::query()
            ->with(['patient', 'currentDepartment'])
            ->whereIn('id', $latestIds)
            ->orderBy('queue_number')
            ->get();

        // Served at destination is any row that day (not necessarily the latest per patient) where
        // the patient reached this dept as both journey destination and current location with a terminal status.
        $servedPatientIds = QueueItem::query()
            ->whereDate('created_at', $day)
            ->where('original_department_id', $departmentId)
            ->where('current_department_id', $departmentId)
            ->whereIn('status', ['transferred', 'done'])
            ->distinct()
            ->pluck('patient_id');

        $queueNumbers = $items->pluck('queue_number')->unique()->values()->all();
        $secondsToDestinationByQueue = $this->secondsToDestinationByQueueNumber(
            $departmentId,
            $day,
            $queueNumbers
        );
        $finishedLifecycleByQueue = $this->finishedLifecycleByQueueNumber(
            $departmentId,
            $day,
            $queueNumbers
        );

        $destVisitByQueue = QueueItem::query()
            ->whereDate('created_at', $day)
            ->where('original_department_id', $departmentId)
            ->where('current_department_id', $departmentId)
            ->whereIn('queue_number', $queueNumbers)
            ->orderByDesc('id')
            ->get()
            ->unique('queue_number')
            ->keyBy(fn(QueueItem $row) => (string) $row->queue_number);

        return $items->map(function (QueueItem $item) use ($departmentId, $servedPatientIds, $secondsToDestinationByQueue, $finishedLifecycleByQueue, $destVisitByQueue) {
            $atDestination = (int) $item->current_department_id === $departmentId;
            $servedAtDestination = $servedPatientIds->contains($item->patient_id);
            $qKey = (string) $item->queue_number;

            $secondsToDestination = $secondsToDestinationByQueue[$qKey] ?? null;
            $finishedLifecycle = $finishedLifecycleByQueue[$qKey] ?? null;
            $destVisit = $destVisitByQueue->get($qKey);

            return [
                'queue_number' => $item->queue_number,
                'patient_name' => $item->patient
                    ? $item->patient->full_name
                    : '—',
                'status' => $item->status,
                'current_department_name' => $item->currentDepartment?->name ?? '—',
                'at_destination' => $atDestination,
                'served_at_destination' => $servedAtDestination,
                'seconds_to_destination' => $secondsToDestination,
                'waiting_seconds_at_destination' => $this->nullableIntSeconds($destVisit?->waiting_duration_seconds),
                'serving_seconds_at_destination' => $this->nullableIntSeconds($destVisit?->serving_duration_seconds),
                'time_start' => $finishedLifecycle['time_start'] ?? null,
                'time_finished' => $finishedLifecycle['time_finished'] ?? null,
                'total_seconds_finished' => $finishedLifecycle['total_seconds_finished'] ?? null,
            ];
        })->values()->all();
    }

    /**
     * Per ticket: seconds from first queue row that day until first row where the patient is in the destination dept queue.
     *
     * @return array<string, int|null> queue_number => seconds, or null if they never reached the destination queue that day
     */
    private function secondsToDestinationByQueueNumber(int $departmentId, string $day, array $queueNumbers): array
    {
        if ($queueNumbers === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($queueNumbers), '?'));
        $bindings = array_merge(
            [$day, $departmentId],
            $queueNumbers,
            [$day, $departmentId, $departmentId],
            $queueNumbers
        );

        $rows = DB::select(
            "SELECT
                fs.queue_number,
                TIMESTAMPDIFF(SECOND, fs.first_at, ds.dest_at) AS seconds_to_destination
            FROM (
                SELECT queue_number, MIN(created_at) AS first_at
                FROM queue_items
                WHERE DATE(created_at) = ? AND original_department_id = ? AND queue_number IN ({$placeholders})
                GROUP BY queue_number
            ) AS fs
            LEFT JOIN (
                SELECT queue_number, MIN(created_at) AS dest_at
                FROM queue_items
                WHERE DATE(created_at) = ? AND original_department_id = ? AND current_department_id = ? AND queue_number IN ({$placeholders})
                GROUP BY queue_number
            ) AS ds ON fs.queue_number = ds.queue_number",
            $bindings
        );

        $out = [];
        foreach ($rows as $row) {
            $key = (string) $row->queue_number;
            $secs = $row->seconds_to_destination;
            $out[$key] = $secs !== null ? (int) $secs : null;
        }

        return $out;
    }

    /**
     * Per ticket lifecycle from first queue row that day until the last row with done status.
     *
     * @return array<string, array{time_start:?string,time_finished:?string,total_seconds_finished:?int}>
     */
    private function finishedLifecycleByQueueNumber(int $departmentId, string $day, array $queueNumbers): array
    {
        if ($queueNumbers === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($queueNumbers), '?'));
        $bindings = array_merge(
            [$day, $departmentId],
            $queueNumbers,
            [$day, $departmentId],
            $queueNumbers
        );

        $rows = DB::select(
            "SELECT
                fs.queue_number,
                fs.start_at,
                fd.finished_at,
                TIMESTAMPDIFF(SECOND, fs.start_at, fd.finished_at) AS total_seconds_finished
            FROM (
                SELECT queue_number, MIN(created_at) AS start_at
                FROM queue_items
                WHERE DATE(created_at) = ? AND original_department_id = ? AND queue_number IN ({$placeholders})
                GROUP BY queue_number
            ) AS fs
            LEFT JOIN (
                SELECT queue_number, MAX(created_at) AS finished_at
                FROM queue_items
                WHERE DATE(created_at) = ? AND original_department_id = ? AND status = 'done' AND queue_number IN ({$placeholders})
                GROUP BY queue_number
            ) AS fd ON fs.queue_number = fd.queue_number",
            $bindings
        );

        $out = [];
        foreach ($rows as $row) {
            $key = (string) $row->queue_number;
            $out[$key] = [
                'time_start' => $this->nullableTimeString($row->start_at),
                'time_finished' => $this->nullableTimeString($row->finished_at),
                'total_seconds_finished' => $this->nullableIntSeconds($row->total_seconds_finished),
            ];
        }

        return $out;
    }

    private function nullableIntSeconds(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) $value;
    }

    private function nullableTimeString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value)->format('H:i:s');
    }
}
