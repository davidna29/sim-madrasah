<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madrasah extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'code',
        'name',
        'nsm',
        'npsn',
        'email',
        'phone',
        'address',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'logo_file_id',
        'timezone',
        'is_active',
    ];

    /**
     * Konversi tipe data database.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
