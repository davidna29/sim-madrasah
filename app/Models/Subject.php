<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'subject_group',
        'is_local_content',
        'is_religious',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_local_content' => 'boolean',
            'is_religious' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }
}
