<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Contracts\View\View;

class PermissionController extends Controller
{
    /**
     * Menampilkan daftar permission.
     */
    public function index(): View
    {
        $permissions = Permission::query()
            ->withCount('roles')
            ->orderBy('module')
            ->orderBy('action')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.permissions.index', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Menampilkan detail permission.
     */
    public function show(Permission $permission): View
    {
        $permission->load([
            'roles' => function ($query): void {
                $query
                    ->orderBy('name');
            },
        ]);

        return view('admin.permissions.show', [
            'permission' => $permission,
        ]);
    }
}
