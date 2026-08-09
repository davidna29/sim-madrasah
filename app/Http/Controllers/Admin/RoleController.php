<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    /**
     * Halaman pengaturan permission role.
     */
    public function permissions(Role $role): View
    {
        $permissions = Permission::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        $rolePermissionIds = $role
            ->permissions()
            ->pluck('permissions.id')
            ->toArray();

        return view('admin.roles.permissions', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissionIds' => $rolePermissionIds,
        ]);
    }

    /**
     * Menyimpan permission role.
     */
    public function updatePermissions(
        Request $request,
        Role $role
    ): RedirectResponse {

        $validated = $request->validate([
            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'exists:permissions,id',
            ],
        ]);

        $role
            ->permissions()
            ->sync(
                $validated['permissions'] ?? []
            );

        return redirect()
            ->route(
                'admin.roles.show',
                $role
            )
            ->with(
                'success',
                'Permission role berhasil diperbarui.'
            );
    }
}
