<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
