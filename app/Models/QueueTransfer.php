<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueTransfer extends Model
{
    use HasFactory;
    protected $fillable = [
        'queue_item_id',
        'from_department_id',
        'to_department_id',
        'transferred_by',
        'new_queue_number',
        'reason'
    ];

    public function queueItem(): BelongsTo
    {
        return $this->belongsTo(QueueItem::class);
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function transferredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
