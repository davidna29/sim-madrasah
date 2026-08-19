<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'active_days',
        'holiday_days',
        'max_slots_per_day',
        'standard_slot_duration_minutes',
        'status',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'active_days' => 'array',
            'holiday_days' => 'array',
            'max_slots_per_day' => 'integer',
            'standard_slot_duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(ScheduleTemplateSlot::class);
    }

    public function classGroupAssignments(): HasMany
    {
        return $this->hasMany(ClassGroupScheduleTemplate::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
