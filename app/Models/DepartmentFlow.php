<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'final_department_id',
        'step_department_id',
        'step_order',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function finalDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'final_department_id');
    }

    public function stepDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'step_department_id');
    }

    /**
     * Get the department flow for given department
     */
    public static function getDepartmentFlowNames($finalDepartmentId)
    {
        return self::with(['stepDepartment:id,name,room']) // only load id & name
            ->where('final_department_id', $finalDepartmentId)
            ->orderBy('step_order')
            ->get()
            ->map(function ($flow) {
                return [
                    'id' => $flow->id,
                    'step_order' => $flow->step_order,
                    'department_name' => $flow->stepDepartment->name . ' ' . $flow->stepDepartment->room,
                ];
            });
    }

    /**
     * Get the first department in the flow for a given final department
     */
    public static function getFirstDepartment($finalDepartmentId)
    {
        return self::where('final_department_id', $finalDepartmentId)
            ->orderBy('step_order')
            ->first();
    }

    /**
     * Get the next department in the flow
     */
    public static function getNextDepartment($finalDepartmentId, $currentDepartmentId)
    {
        $currentStep = self::where('final_department_id', $finalDepartmentId)
            ->where('step_department_id', $currentDepartmentId)
            ->first();

        if (!$currentStep) {
            return null;
        }

        return self::where('final_department_id', $finalDepartmentId)
            ->where('step_order', $currentStep->step_order + 1)
            ->first();
    }

    /**
     * Check if this is the final department in the flow
     */
    public static function isFinalDepartment($finalDepartmentId, $currentDepartmentId)
    {
        $currentStep = self::where('final_department_id', $finalDepartmentId)
            ->where('step_department_id', $currentDepartmentId)
            ->first();

        if (!$currentStep) {
            return false;
        }

        $maxStep = self::where('final_department_id', $finalDepartmentId)
            ->max('step_order');

        return $currentStep->step_order >= $maxStep;
    }
}
