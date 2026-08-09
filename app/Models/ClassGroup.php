<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'grade_level_id',
        'room_id',
        'homeroom_teacher_user_id',
        'code',
        'name',
        'parallel_name',
        'capacity',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'homeroom_teacher_user_id'
        );
    }
}
