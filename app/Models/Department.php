<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'room',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'department_users');
    }

    public function currentQueueItems(): HasMany
    {
        return $this->hasMany(QueueItem::class, 'current_department_id');
    }

    public function originalQueueItems(): HasMany
    {
        return $this->hasMany(QueueItem::class, 'original_department_id');
    }
    public function activeQueue(): HasMany
    {
        return $this->hasMany(QueueItem::class, 'current_department_id')
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('queue_position');
    }

    public function dailyCounters(): HasMany
    {
        return $this->hasMany(DailyQueueCounter::class);
    }

    public function getNextQueueNumber(): string
    {
        $today = today();

        // Get or create today's counter
        $counter = DailyQueueCounter::firstOrCreate(
            [
                'department_id' => $this->id,
                'date' => $today
            ],
            ['counter' => 0]
        );


        // Increment counter
        $counter->increment('counter');

        // Format queue number
        $nextNumber = str_pad($counter->counter, 3, '0', STR_PAD_LEFT);

        return $this->code . $nextNumber;
    }

    public function resetDailyCounter(?\Carbon\Carbon $date = null): void
    {
        $date = $date ?: today();

        DailyQueueCounter::updateOrCreate(
            [
                'department_id' => $this->id,
                'date' => $date
            ],
            ['counter' => 0]
        );
    }
    public function getTodayQueueCount(): int
    {
        $counter = $this->dailyCounters()
            ->where('date', today())
            ->first();

        return $counter ? $counter->counter : 0;
    }
}
