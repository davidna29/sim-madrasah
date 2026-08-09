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
}
