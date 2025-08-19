<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'age',
        'gender',
        'is_priority',
        'priority_reason_id'
    ];

    protected $casts = [
        'birth_date' => 'date'
    ];

    public function queueItems(): HasMany
    {
        return $this->hasMany(QueueItem::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function priorityReason(): BelongsTo
    {
        return $this->belongsTo(PriorityReason::class);
    }
}
