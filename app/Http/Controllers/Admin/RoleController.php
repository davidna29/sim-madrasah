<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Contracts\View\View;

class RoleController extends Controller
{
    /**
     * Menampilkan daftar role.
     */
    public function index(): View
    {
        $roles = Role::query()
            ->withCount([
                'users',
                'permissions',
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.roles.index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Menampilkan detail role dan permission yang dimiliki.
     */
    public function show(Role $role): View
    {
        $role->loadCount([
            'users',
            'permissions',
        ]);

        $role->load([
            'permissions' => function ($query): void {
                $query
                    ->orderBy('module')
                    ->orderBy('action')
                    ->orderBy('name');
            },
        ]);

        $permissionsByModule = $role->permissions
            ->groupBy('module');

        return view('admin.roles.show', [
            'role' => $role,
            'permissionsByModule' => $permissionsByModule,
        ]);
    }
}
