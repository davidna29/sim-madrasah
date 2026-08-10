<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'admission_academic_year_id',
        'student_number',
        'nisn',
        'registration_number',
        'admission_date',
        'graduation_date',
        'status',
        'previous_school',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'graduation_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function admissionAcademicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'admission_academic_year_id'
        );
    }

    // Relasi dengan model StudentClassHistory
    public function classHistories(): HasMany
    {
        return $this->hasMany(StudentClassHistory::class);
    }

    public function currentClassHistory(): HasOne
    {
        return $this->hasOne(StudentClassHistory::class)
            ->where('is_current', true);
    }
}
