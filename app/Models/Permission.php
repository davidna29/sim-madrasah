<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    /**
     * Kolom yang boleh diisi.
     */
    protected $fillable = [
        'name',
        'module',
        'action',
        'display_name',
        'description',
        'is_active',
    ];

    /**
     * Konversi tipe data.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Role yang memiliki permission ini.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id'
        );
    }

    /**
     * User yang mendapat permission langsung.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_permissions',
            'permission_id',
            'user_id'
        )->withPivot([
            'permission_mode',
            'assigned_by',
            'assigned_at',
            'expires_at',
        ]);
    }
}
