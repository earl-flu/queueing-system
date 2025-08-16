<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Patient;
use App\Models\QueueItem;
use App\Models\QueueTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QueueController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $departmentId = $request->get('department', $user->isAdmin() ? '' : $user->departments->first()->id);
        $status = $request->get('status', 'all');

        $query = QueueItem::with(['patient', 'originalDepartment', 'currentDepartment.users', 'servedByUser'])
            ->today()
            ->orderBy('created_at', 'desc');

        if ($departmentId) {
            $query->where('current_department_id', $departmentId);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $queueItems = $query->paginate(20);
        $departments = Department::where('is_active', true)->with('users')->get();

        return Inertia::render('Queue/Index', [
            'queueItems' => $queueItems,
            'departments' => $departments,
            'filters' => [
                'department' => $departmentId,
                'status' => $status
            ],
            'user' => auth()->user()->load('departments')
        ]);
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        return Inertia::render('Queue/Create', [
            'departments' => $departments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient.first_name' => 'required|string|max:255',
            'patient.last_name' => 'required|string|max:255',
            'patient.middle_name' => 'nullable|string|max:255',
            'patient.phone' => 'nullable|string|max:20',
            'patient.birth_date' => 'nullable|date',
            'patient.suffix' => 'nullable|in:Jr.,Sr.,II,III,IV,V',
            'patient.gender' => 'nullable|in:male,female',
            'department_id' => 'required|exists:departments,id'
        ]);

        DB::transaction(function () use ($validated) {
            $patient = Patient::create($validated['patient']);
            $department = Department::findOrFail($validated['department_id']);

            // Get next queue position for this department
            $nextPosition = QueueItem::where('current_department_id', $department->id)
                ->today()
                ->max('queue_position') + 1;

            QueueItem::create([
                'queue_number' => $department->getNextQueueNumber(),
                'patient_id' => $patient->id,
                'original_department_id' => $department->id, // Store original department
                'current_department_id' => $department->id,   // Currently in this department
                'queue_position' => $nextPosition,
                'status' => 'waiting'
            ]);
        });

        return redirect()->route('queue.index')
            ->with('success', 'Patient added to queue successfully.');
    }

    public function call(QueueItem $queueItem)
    {
        $user = auth()->user();

        // Check if user has access to this department
        if (!$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        $queueItem->update([
            'status' => 'serving',
            'served_by' => $user->id,
            'called_at' => now(),
            'served_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Patient called for service.');
    }

    public function complete(QueueItem $queueItem)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        $queueItem->update([
            'status' => 'done',
            'completed_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Service completed.');
    }

    public function transfer(Request $request, QueueItem $queueItem)
    {
        $validated = $request->validate([
            'to_department_id' => 'required|exists:departments,id|different:from_department_id',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = auth()->user();

        if (!$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        DB::transaction(function () use ($queueItem, $validated, $user) {
            $toDepartment = Department::findOrFail($validated['to_department_id']);

            // Get next queue position in target department
            $nextPosition = QueueItem::where('current_department_id', $toDepartment->id)
                ->today()
                ->max('queue_position') + 1;

            // Record the transfer
            QueueTransfer::create([
                'queue_item_id' => $queueItem->id,
                'from_department_id' => $queueItem->current_department_id,
                'to_department_id' => $validated['to_department_id'],
                'transferred_by' => $user->id,
                'new_queue_number' => $queueItem->queue_number, // Keep original queue number
                'reason' => $validated['reason']
            ]);

            // Create new queue item for target department with SAME queue number
            QueueItem::create([
                'queue_number' => $queueItem->queue_number, // Keep original queue number
                'patient_id' => $queueItem->patient_id,
                'original_department_id' => $queueItem->original_department_id, // Keep original
                'current_department_id' => $validated['to_department_id'],
                'queue_position' => $nextPosition,
                'status' => 'waiting'
            ]);

            // Mark current queue item as transferred
            $queueItem->update([
                'status' => 'transferred',
                'completed_at' => now()
            ]);
        });

        return redirect()->back()
            ->with('success', 'Patient transferred successfully.');
    }

    public function departmentQueue($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->departments->contains($department->id)) {
            abort(403);
        }

        $queueItems = QueueItem::with(['patient', 'originalDepartment', 'currentDepartment'])
            ->where('current_department_id', $departmentId)
            ->today()
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('queue_position')
            ->get();

        return Inertia::render('Queue/Department', [
            'department' => $department,
            'queueItems' => $queueItems,
            'canTransfer' => Department::where('is_active', true)
                ->where('id', '!=', $departmentId)
                ->get(),
            'todayCount' => $department->getTodayQueueCount()
        ]);
    }

    public function resetCounter(Request $request, $departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $user = auth()->user();

        // Only admins can reset counters
        if (!$user->isAdmin()) {
            abort(403);
        }

        $date = $request->get('date') ? \Carbon\Carbon::parse($request->get('date')) : today();

        $department->resetDailyCounter($date);

        return redirect()->back()
            ->with('success', 'Daily queue counter reset successfully for ' . $date->format('Y-m-d'));
    }
}
