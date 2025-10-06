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
        'is_active',
        'description'
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
        $count = $this->currentQueueItems()
            ->whereDate('created_at', today())
            ->count();

        return $count ? $count : 0;
    }

    public function getTodayComingQueueCount(): int // In other queue count
    {

        $allCount = $this->getTodayQueueCount();
        $servedCount = $this->getTodayServedQueueCount();
        $waitingCount = $this->getTodayWaitingQueueCount();
        $skippedCount = $this->getTodaySkippedQueueCount();
        $servingCount = $this->getTodayServingQueueCount();

        $comingCount = $allCount - $servedCount - $waitingCount - $skippedCount - $servingCount;

        return $comingCount ? $comingCount : 0;
    }

    public function getTodayServedQueueCount(): int
    {
        $count = $this->currentQueueItems()
            ->whereDate('created_at', today())
            ->whereIn('status', ['transferred', 'done'])
            ->whereNotNull('completed_at')
            ->count();

        return $count ? $count : 0;
    }

    public function getTodayWaitingQueueCount(): int
    {
        $count = $this->currentQueueItems()
            ->whereDate('created_at', today())
            ->where('status', 'waiting')
            ->count();

        return $count ? $count : 0;
    }

    public function getTodayServingQueueCount(): int
    {
        $count = $this->currentQueueItems()
            ->whereDate('created_at', today())
            ->where('status', 'serving')
            ->count();

        return $count ? $count : 0;
    }

    public function getTodaySkippedQueueCount(): int
    {
        $count = $this->currentQueueItems()
            ->whereDate('created_at', today())
            ->where('status', 'skipped')
            ->count();

        return $count ? $count : 0;
    }
}
