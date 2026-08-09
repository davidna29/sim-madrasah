<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_system',
        'is_active',
    ];

    /**
     * Konversi tipe data.
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Permission yang dimiliki role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        );
    }

    /**
     * User yang memiliki role ini.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_roles',
            'role_id',
            'user_id'
        )->withPivot([
            'assigned_by',
            'assigned_at',
            'expires_at',
        ]);
    }
}
