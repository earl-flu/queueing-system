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
        'notes'
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'completed_at' => 'datetime'
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
}
