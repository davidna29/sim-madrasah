<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Kolom yang dapat diisi melalui mass assignment.
     *
     * Kolom keamanan seperti failed_login_count dan locked_until
     * tidak dimasukkan agar tidak dapat diubah sembarangan
     * melalui request pengguna.
     */
    protected $fillable = [
        'person_id',
        'name',
        'username',
        'email',
        'password',
        'account_type',
        'status',
    ];

    /**
     * Kolom yang tidak ditampilkan saat model diubah
     * menjadi array atau JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Konversi tipe data database ke tipe data PHP.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'locked_until' => 'datetime',
            'failed_login_count' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * Identitas orang yang memiliki akun ini.
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
