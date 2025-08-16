<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyQueueCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'date',
        'counter'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
