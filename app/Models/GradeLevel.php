<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'level_number',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
