<?php

namespace App\Http\Controllers;

use App\Events\CallPatient;
use App\Models\Department;
use App\Models\DepartmentFlow;
use App\Models\Patient;
use App\Models\PriorityReason;
use App\Models\QueueItem;
use App\Models\QueueTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class QueueController extends Controller
{
    /**
     * Display a listing of queue items.
     *
     * @param Request $request
     * @return \Inertia\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
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



    /**
     * Show the form for creating a new queue item.
     *
     * @return \Inertia\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isReception()) {
            abort(403);
        }

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

    /**
     * Store a new queue item for a patient.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorizeQueueCreation();

        $validated = $this->validateQueueRequest($request);

        $queueItem = DB::transaction(function () use ($validated) {
            return $this->createQueueItem($validated);
        });

        return redirect()->back()->with([
            'queueItemData' => $queueItem,
            'patient' => $queueItem->patient,
        ]);
    }

    /**
     * Authorize queue creation based on user permissions.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeQueueCreation(): void
    {
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->isReception()) {
            abort(403, 'Unauthorized: Only administrators and reception staff can create queue items.');
        }
    }

    /**
     * Validate the queue creation request.
     *
     * @param Request $request
     * @return array
     */
    private function validateQueueRequest(Request $request): array
    {
        return $request->validate([
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
    }

    /**
     * Create a new queue item with patient and department flow logic.
     *
     * @param array $validated
     * @return QueueItem
     */
    private function createQueueItem(array $validated): QueueItem
    {
        $patient = Patient::create($validated['patient']);
        $finalDepartment = Department::findOrFail($validated['final_department_id']);
        $firstDepartment = $this->determineFirstDepartment($finalDepartment);
        $nextPosition = $this->getNextQueuePosition($firstDepartment);

        return QueueItem::create([
            'queue_number' => $finalDepartment->getNextQueueNumber(),
            'patient_id' => $patient->id,
            'original_department_id' => $finalDepartment->id,
            'current_department_id' => $firstDepartment->id,
            'queue_position' => $nextPosition,
            'status' => 'waiting',
            'waiting_started_at' => now()
        ]);
    }

    /**
     * Determine the first department in the flow or use final department.
     *
     * @param Department $finalDepartment
     * @return Department
     */
    private function determineFirstDepartment(Department $finalDepartment): Department
    {
        $firstFlow = DepartmentFlow::getFirstDepartment($finalDepartment->id);

        return $firstFlow ? $firstFlow->stepDepartment : $finalDepartment;
    }

    /**
     * Get the next queue position for a department.
     *
     * @param Department $department
     * @return int
     */
    private function getNextQueuePosition(Department $department): int
    {
        return QueueItem::where('current_department_id', $department->id)
            ->today()
            ->max('queue_position') + 1;
    }

    /**
     * Authorize department access for queue operations.
     *
     * @param QueueItem $queueItem
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    private function authorizeDepartmentAccess(QueueItem $queueItem): void
    {
        $user = auth()->user();

        if ($user->isReception() && !$user->isAdmin() && !$user->departments->contains($queueItem->current_department_id)) {
            abort(403, 'Unauthorized: You do not have access to this department.');
        }
    }

    /**
     * Skip a queue item.
     *
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function skip(QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        $queueItem->update([
            'status' => 'skipped',
            'skipped_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Patient queue item has been skipped.');
    }

    /**
     * Call a patient for service.
     *
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function call(QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        $user = auth()->user();
        $queueItem->setWaitingDuration();
        $queueItem->startServing($user->id);
        $queueItem->addCallCount();

        broadcast(new CallPatient($queueItem));

        return redirect()->back()
            ->with('success', 'Patient called for service.');
    }

    /**
     * Call a patient again.
     *
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function callAgain(QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        broadcast(new CallPatient($queueItem));
        $queueItem->addCallCount();

        return redirect()->back()
            ->with('success', 'Patient called again.');
    }

    /**
     * Complete service for a queue item.
     *
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function complete(QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        $queueItem->completeService();

        return redirect()->back()
            ->with('success', 'Service completed.');
    }

    /**
     * Complete service and transfer to next department.
     *
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function completeAndTransfer(QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        $queueItem->completeService();

        if ($queueItem->isFinalDepartment()) {
            return redirect()->back()
                ->with('success', 'Service completed. Patient has finished all required steps.');
        }

        $transferred = $queueItem->transferToNextDepartment();

        $message = $transferred
            ? 'Service completed and patient transferred to next department.'
            : 'Service completed.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Mark a queue item as no show.
     *
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function noShow(QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        $queueItem->markNoShow();

        return redirect()->back()->with('success', 'Marked as no show.');
    }

    /**
     * Transfer a queue item to another department.
     *
     * @param Request $request
     * @param QueueItem $queueItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function transfer(Request $request, QueueItem $queueItem)
    {
        $this->authorizeDepartmentAccess($queueItem);

        $validated = $request->validate([
            'to_department_id' => 'required|exists:departments,id|different:from_department_id',
            'reason' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($queueItem, $validated) {
            $this->processQueueTransfer($queueItem, $validated);
        });

        return redirect()->back()
            ->with('success', 'Patient transferred successfully.');
    }

    /**
     * Process the queue transfer logic.
     *
     * @param QueueItem $queueItem
     * @param array $validated
     * @return void
     */
    private function processQueueTransfer(QueueItem $queueItem, array $validated): void
    {
        $toDepartment = Department::findOrFail($validated['to_department_id']);
        $user = auth()->user();
        $nextPosition = $this->getNextQueuePosition($toDepartment);

        // Record the transfer
        QueueTransfer::create([
            'queue_item_id' => $queueItem->id,
            'from_department_id' => $queueItem->current_department_id,
            'to_department_id' => $validated['to_department_id'],
            'transferred_by' => $user->id,
            'new_queue_number' => $queueItem->queue_number,
            'reason' => $validated['reason']
        ]);

        // Create new queue item for target department
        QueueItem::create([
            'queue_number' => $queueItem->queue_number,
            'patient_id' => $queueItem->patient_id,
            'original_department_id' => $queueItem->original_department_id,
            'current_department_id' => $validated['to_department_id'],
            'queue_position' => $nextPosition,
            'status' => 'waiting'
        ]);

        // Mark current queue item as transferred
        $queueItem->update([
            'status' => 'transferred',
            'completed_at' => now()
        ]);
    }

    /**
     * Display department queue view.
     *
     * @param int $departmentId
     * @return \Inertia\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function departmentQueue($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->departments->contains($department->id)) {
            abort(403, 'Unauthorized: You do not have access to this department.');
        }

        $queueItems = $this->getDepartmentQueueItems($departmentId);
        $this->addFlowInformationToQueueItems($queueItems);

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

    /**
     * Get department queue data as JSON.
     *
     * @param int $departmentId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function departmentQueueData($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $user = auth()->user();

        if (!$user->isAdmin() && !$user->departments->contains($department->id)) {
            abort(403, 'Unauthorized: You do not have access to this department.');
        }

        $queueItems = QueueItem::with(['patient', 'originalDepartment', 'currentDepartment', 'patient.priorityReason'])
            ->where('current_department_id', $departmentId)
            ->today()
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('queue_position')
            ->get();

        $this->addFlowInformationToQueueItems($queueItems);

        return response()->json($queueItems);
    }

    /**
     * Reset daily counter for a department.
     *
     * @param Request $request
     * @param int $departmentId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function resetCounter(Request $request, $departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $user = auth()->user();

        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized: Only administrators can reset counters.');
        }

        $date = $request->get('date')
            ? \Carbon\Carbon::parse($request->get('date'))
            : today();

        $department->resetDailyCounter($date);

        return redirect()->back()
            ->with('success', 'Daily queue counter reset successfully for ' . $date->format('Y-m-d'));
    }

    /**
     * Get queue items for a department.
     *
     * @param int $departmentId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getDepartmentQueueItems(int $departmentId)
    {
        return QueueItem::with(['patient', 'originalDepartment', 'currentDepartment', 'patient.priorityReason'])
            ->where('current_department_id', $departmentId)
            ->today()
            ->whereIn('status', ['waiting', 'serving', 'skipped'])
            ->orderBy('queue_position')
            ->get();
    }

    /**
     * Add flow information to queue items.
     *
     * @param \Illuminate\Database\Eloquent\Collection $queueItems
     * @return void
     */
    private function addFlowInformationToQueueItems($queueItems): void
    {
        $queueItems->each(function ($item) {
            $item->is_final_department = $item->isFinalDepartment();
            $item->has_next_department = !$item->isFinalDepartment() && $item->getNextDepartment() !== null;
        });
    }
}
