<?php

namespace App\Http\Controllers;

use App\Events\CallPatient;
use App\Models\Department;
use App\Models\DepartmentFlow;
use App\Models\Patient;
use App\Models\PriorityReason;
use App\Models\QueueItem;
use App\Models\QueueTransfer;
use App\Services\SmsGateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class QueueController extends Controller
{
    public function __construct(SmsGateService $smsGateService)
    {
        $this->smsGateService = $smsGateService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->departments->isEmpty()) {
            abort(403, 'This user does not have a department assigned');
        }

        $departmentId = $request->get('department', $user->isAdmin() ? '' : $user->departments->first()->id);
        $status = $request->get('status', 'all');
        $queueNumber = $request->get('queueNumber');
        $patientFullName = $request->get('patientFullname');

        $query = QueueItem::with(['patient', 'originalDepartment', 'currentDepartment.users', 'servedByUser'])
            ->today()
            ->orderBy('created_at', 'desc');

        if ($departmentId) {
            $query->where('current_department_id', $departmentId);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($queueNumber) {
            $query->where('queue_number', 'like', '%' .  $queueNumber . '%');
        }

        if ($patientFullName) {
            $query->whereHas('patient', function ($subQuery) use ($patientFullName) {
                $subQuery->where('first_name', 'like', '%' . $patientFullName . '%')
                    ->orWhere('last_name', 'like', '%' . $patientFullName . '%')
                    ->orWhere('middle_name', 'like', '%' . $patientFullName . '%');
            });
        }

        $queueItems = $query->paginate(20);
        $departments = Department::where('is_active', true)->with('users')->get();

        return Inertia::render('Queue/Index', [
            'queueItems' => $queueItems,
            'departments' => $departments,
            'filters' => [
                'department' => $departmentId,
                'status' => $status,
                'patientFullname' => $patientFullName,
                'queueNumber' => $queueNumber
            ],
            'user' => auth()->user()->load('departments')
        ]);
    }

    public function testDirectSms()
    {
        try {
            $response = Http::withBasicAuth('sms', 'WuSab1t5')
                ->timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('http://192.168.1.58:8080/v1/message', [
                    'message' => 'Test from Laravel',
                    'phoneNumbers' => ['+639815426706'], // Use your actual number
                    'simSlot' => 1
                ]);

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->body(),
                'data' => $response->successful() ? $response->json() : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isReception()) {
            abort(403);
        }

        // $this->smsGateService->sendSms('+639815426706', 'Hello, this is a test message2');
        // $this->testDirectSms();

        $departments = Department::where('is_active', true)
            ->addSelect([
                'queue_count' => function ($query) {
                    $query->from('queue_items')
                        ->selectRaw('COUNT(DISTINCT patient_id)')
                        ->whereColumn('original_department_id', 'departments.id')
                        ->whereDate('created_at', today());
                }
            ])
            ->whereNotIn('code', ['REG', 'MSS', 'PHIC', 'BIL'])
            ->orderBy('name')
            ->get();

        $priority_reasons = PriorityReason::where('is_active', true)
            ->orderBy('description')->get();

        return Inertia::render('Queue/Create', [
            'departments' => $departments,
            'priority_reasons' => $priority_reasons,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        // Check if user has access to reception or is admin
        if (!$user->isAdmin() && !$user->isReception()) {
            abort(403);
        }

        $validated = $request->validate([
            'patient.first_name' => 'required|string|max:255',
            'patient.last_name' => 'required|string|max:255',
            'patient.middle_name' => 'nullable|string|max:255',
            'patient.phone' => 'nullable|string|max:20',
            'patient.age' => 'nullable|string|max:3',
            'patient.is_priority' => 'boolean',
            'patient.priority_reason_id' => 'nullable|required_if:patient.is_priority,true|exists:priority_reasons,id',
            'patient.suffix' => 'nullable|in:Jr.,Sr.,II,III,IV,V',
            'patient.gender' => 'nullable|in:male,female',
            'final_department_id' => 'required|exists:departments,id'
        ]);

        $queueItem = DB::transaction(function () use ($validated) {
            $patient = Patient::create($validated['patient']);
            $finalDepartment = Department::findOrFail($validated['final_department_id']);

            // Get the first department in the flow
            $firstFlow = DepartmentFlow::getFirstDepartment($finalDepartment->id);

            if (!$firstFlow) {
                // No flow defined, use final department as first department
                $firstDepartment = $finalDepartment;
            } else {
                $firstDepartment = $firstFlow->stepDepartment;
            }

            // Get next queue position for the first department
            $nextPosition = QueueItem::where('current_department_id', $firstDepartment->id)
                ->today()
                ->max('queue_position') + 1;

            return QueueItem::create([
                'queue_number' => $finalDepartment->getNextQueueNumber(),
                'patient_id' => $patient->id,
                'original_department_id' => $finalDepartment->id, // Store final destination
                'current_department_id' => $firstDepartment->id,   // Currently in first department
                'queue_position' => $nextPosition,
                'status' => 'waiting',
                'waiting_started_at' => now()
            ]);
        });

        $patient = Patient::findOrFail($queueItem->patient_id);

        return redirect()->back()->with([
            'queueItemData' => $queueItem,
            'patient' => $patient,
        ]);
    }

    public function skip(QueueItem $queueItem)
    {
        $user = auth()->user();

        // Check if user has access to this department
        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        // Update the queue item status to 'skipped' and record the time
        $queueItem->status = 'skipped';
        $queueItem->skipped_at = now();
        $queueItem->save();

        // Optionally, broadcast an event if the frontend needs to react to skipped items
        // broadcast(new QueueItemUpdated($queueItem));

        return redirect()->back()
            ->with('success', 'Patient queue item has been skipped.');
    }

    public function call(QueueItem $queueItem)
    {
        $user = auth()->user();

        // Check if user has access to this department
        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        $queueItem->setWaitingDuration();
        $queueItem->startServing($user->id);
        $queueItem->addCallCount();

        broadcast(new CallPatient($queueItem));

        return redirect()->back()
            ->with('success', 'Patient called for service.');
    }

    public function callAgain(QueueItem $queueItem)
    {
        $user = auth()->user();

        // Check if user has access to this department
        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        broadcast(new CallPatient($queueItem));
        $queueItem->addCallCount();

        return redirect()->back()
            ->with('success', 'Patient called again.');
    }

    public function complete(QueueItem $queueItem)
    {
        $user = auth()->user();

        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        $queueItem->completeService();

        return redirect()->back()
            ->with('success', 'Service completed.');
    }

    public function completeAndTransfer(QueueItem $queueItem)
    {

        $user = auth()->user();
        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        // Complete the current service
        $queueItem->completeService();

        // Check if there's a next department in the flow
        if ($queueItem->isFinalDepartment()) {
            return redirect()->back()
                ->with('success', 'Service completed. Patient has finished all required steps.');
        }

        // Transfer to next department
        $transferred = $queueItem->transferToNextDepartment();


        if ($transferred) {
            return redirect()->back()
                ->with('success', 'Service completed and patient transferred to next department.');
        } else {
            return redirect()->back()
                ->with('success', 'Service completed.');
        }
    }

    public function noShow(QueueItem $queueItem)
    {
        $user = auth()->user();

        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403);
        }

        $queueItem->markNoShow();

        return redirect()->back()->with('success', 'Marked as no show.');
    }

    public function transfer(Request $request, QueueItem $queueItem)
    {
        $validated = $request->validate([
            'to_department_id' => 'required|exists:departments,id|different:from_department_id',
            'reason' => 'nullable|string|max:500'
        ]);

        $user = auth()->user();

        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
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

        $queueItems = QueueItem::with(['patient', 'originalDepartment', 'currentDepartment', 'patient.priorityReason'])
            ->where('current_department_id', $departmentId)
            ->today()
            ->whereIn('status', ['waiting', 'serving', 'skipped'])
            ->orderBy('queue_position')
            ->get();

        // Add flow information to each queue item
        $queueItems->each(function ($item) {
            $item->is_final_department = $item->isFinalDepartment();
            $item->has_next_department = !$item->isFinalDepartment() && $item->getNextDepartment() !== null;
        });

        return Inertia::render('Queue/Department', [
            'department' => $department,
            'queueItems' => $queueItems,
            'canTransfer' => Department::where('is_active', true)
                ->where('id', '!=', $departmentId)
                ->get(),
            'todayCount' => $department->getTodayQueueCount(),
            'todayServedCount' => $department->getTodayServedQueueCount(),
            'todayComingCount' => $department->getTodayComingQueueCount(),
            'todayWaitingCount' => $department->getTodayWaitingQueueCount(),
            'todayServingCount' => $department->getTodayServingQueueCount(),
            'todaySkippedCount' => $department->getTodaySkippedQueueCount()
        ]);
    }

    public function departmentQueueData($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->departments->contains($department->id)) {
            abort(403);
        }

        $queueItems = QueueItem::with(['patient', 'originalDepartment', 'currentDepartment', 'patient.priorityReason'])
            ->where('current_department_id', $departmentId)
            ->today()
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('queue_position')
            ->get();

        // Add flow information to each queue item
        $queueItems->each(function ($item) {
            $item->is_final_department = $item->isFinalDepartment();
            $item->has_next_department = !$item->isFinalDepartment() && $item->getNextDepartment() !== null;
        });

        return response()->json($queueItems);
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
