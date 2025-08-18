<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Window extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_window')
            ->withPivot('position')
            ->orderBy('department_window.position');
    }
}
