<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QueueItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'queue_number',
        'patient_id',
        'original_department_id',
        'current_department_id',
        'served_by',
        'status',
        'queue_position',
        'called_at',
        'served_at',
        'completed_at',
        'notes',
        'waiting_started_at',
        'serving_started_at',
        'waiting_duration_seconds',
        'serving_duration_seconds'
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime',
        'waiting_started_at' => 'datetime',
        'serving_started_at' => 'datetime'
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function originalDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'original_department_id');
    }

    public function currentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    // Keep backward compatibility
    public function department(): BelongsTo
    {
        return $this->currentDepartment();
    }

    public function servedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(QueueTransfer::class);
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeServing($query)
    {
        return $query->where('status', 'serving');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('current_department_id', $departmentId);
    }

    /**
     * Start waiting time tracking
     */
    public function startWaiting()
    {
        $this->update([
            'waiting_started_at' => now(),
            'status' => 'waiting'
        ]);
    }

    /**
     * Start serving time tracking
     */
    public function startServing($userId = null)
    {
        $this->update([
            'serving_started_at' => now(),
            'status' => 'serving',
            'served_by' => $userId,
            'called_at' => now(),
            'served_at' => now()
        ]);
    }

    /**
     * Complete service and calculate durations
     */
    public function completeService()
    {
        $waitingDuration = $this->waiting_started_at ? now()->diffInSeconds($this->waiting_started_at) : 0;
        $servingDuration = $this->serving_started_at ? now()->diffInSeconds($this->serving_started_at) : 0;

        $this->update([
            'status' => 'done',
            'completed_at' => now(),
            'waiting_duration_seconds' => $waitingDuration,
            'serving_duration_seconds' => $servingDuration
        ]);
    }

    /**
     * Get the next department in the flow
     */
    public function getNextDepartment()
    {
        return DepartmentFlow::getNextDepartment($this->original_department_id, $this->current_department_id);
    }

    /**
     * Check if this is the final department in the flow
     */
    public function isFinalDepartment()
    {
        return DepartmentFlow::isFinalDepartment($this->original_department_id, $this->current_department_id);
    }

    /**
     * Transfer to next department in the flow
     */
    public function transferToNextDepartment()
    {
        $nextFlow = $this->getNextDepartment();

        if (!$nextFlow) {
            return false; // No next department, patient is done
        }

        // Check if already forwarded to next department - to avoid duplicate
        $queueNumber = $this->queue_number;
        $isAlreadyForwardedToNextDept = QueueItem::where('queue_number', $queueNumber)->where('current_department_id', $nextFlow['step_department_id'])->exists();
        if ($isAlreadyForwardedToNextDept) {
            return;
        }

        $nextDepartment = $nextFlow->stepDepartment;

        // Get next queue position in target department
        $nextPosition = QueueItem::where('current_department_id', $nextDepartment->id)
            ->today()
            ->max('queue_position') + 1;

        // Create new queue item for next department
        QueueItem::create([
            'queue_number' => $this->queue_number, // Keep same queue number
            'patient_id' => $this->patient_id,
            'original_department_id' => $this->original_department_id, // Keep original
            'current_department_id' => $nextDepartment->id,
            'queue_position' => $nextPosition,
            'status' => 'waiting',
            'waiting_started_at' => now()
        ]);

        // Mark current queue item as transferred
        $this->update([
            'status' => 'transferred',
            'completed_at' => now()
        ]);

        return true;
    }

    /**
     * Mark the queue item as no show and finalize durations
     */
    public function markNoShow()
    {
        $waitingDuration = $this->waiting_started_at ? now()->diffInSeconds($this->waiting_started_at) : 0;
        $servingDuration = $this->serving_started_at ? now()->diffInSeconds($this->serving_started_at) : 0;

        $this->update([
            'status' => 'no_show',
            'completed_at' => now(),
            'waiting_duration_seconds' => $waitingDuration,
            'serving_duration_seconds' => $servingDuration
        ]);
    }

    public function isComingTo($targetDept, $currentDept)
    {
        //Get the target Department Flow
        //[step: 1 = Registration, step:2 = MSS, step:3 Billing,]

        //Get the current department        
        // MSS = 2

        // Get the patients where current department in [Registration, MSS] and is waiting then count
    }
}
