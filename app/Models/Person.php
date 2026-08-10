<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi melalui mass assignment.
     *
     * Relasi dengan User akan ditambahkan setelah
     * struktur tabel users selesai disesuaikan.
     */
    protected $fillable = [
        'national_id_number',
        'full_name',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'email',
        'phone',
        'address',
        'photo_file_id',
    ];

    /**
     * Mengubah tipe data database menjadi tipe PHP yang sesuai.
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    /**
     * Akun login yang terhubung dengan identitas ini.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    // Relasi dengan model Employee
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    // Relasi dengan model Student
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    // Relasi dengan model Guardian
    public function studentGuardianLinks(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }
}
