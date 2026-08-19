<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * Role yang dimiliki user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )->withPivot([
            'assigned_by',
            'assigned_at',
            'expires_at',
        ]);
    }

    /**
     * Permission langsung yang diberikan ke user.
     *
     * Permission langsung dipakai hanya untuk pengecualian khusus.
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permissions',
            'user_id',
            'permission_id'
        )->withPivot([
            'permission_mode',
            'assigned_by',
            'assigned_at',
            'expires_at',
        ]);
    }

    /**
     * Memeriksa apakah user memiliki role aktif.
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('roles.name', $roleName)
            ->where('roles.is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('user_roles.expires_at')
                    ->orWhere('user_roles.expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Memeriksa apakah user memiliki permission tertentu.
     *
     * Urutan pemeriksaan:
     * 1. Permission langsung dengan mode deny menolak akses.
     * 2. Permission langsung dengan mode allow memberi akses.
     * 3. Permission dari role aktif memberi akses.
     */
    public function hasPermission(string $permissionName): bool
    {
        $directPermission = $this->directPermissions()
            ->where('permissions.name', $permissionName)
            ->where('permissions.is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('user_permissions.expires_at')
                    ->orWhere('user_permissions.expires_at', '>', now());
            })
            ->first();

        if ($directPermission !== null) {
            return $directPermission->pivot->permission_mode === 'allow';
        }

        return $this->roles()
            ->where('roles.is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('user_roles.expires_at')
                    ->orWhere('user_roles.expires_at', '>', now());
            })
            ->whereHas('permissions', function ($query) use ($permissionName): void {
                $query
                    ->where('permissions.name', $permissionName)
                    ->where('permissions.is_active', true);
            })
            ->exists();
    }

    // Relasi dengan model TeachingAssignment
    public function teachingAssignmentsAsTeacher(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'teacher_user_id');
    }

    public function createdTeachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'created_by');
    }
}
