<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'employee_number',
        'nip',
        'nuptk',
        'employee_type',
        'employment_status',
        'position',
        'join_date',
        'end_date',
        'education_level',
        'major',
        'is_teacher',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'end_date' => 'date',
            'is_teacher' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
