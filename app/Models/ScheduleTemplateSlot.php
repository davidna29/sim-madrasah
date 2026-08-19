<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleTemplateSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_template_id',
        'day_of_week',
        'sort_order',
        'starts_at',
        'ends_at',
        'slot_type',
        'label',
        'is_teaching_slot',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'sort_order' => 'integer',
            'is_teaching_slot' => 'boolean',
        ];
    }

    public function scheduleTemplate(): BelongsTo
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }
}
