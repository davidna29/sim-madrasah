<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmployeeAccountController extends Controller
{
    /**
     * Menampilkan form pembuatan akun pegawai.
     */
    public function create(Employee $employee): View|RedirectResponse
    {
        $employee->load('person.user');

        if ($employee->person?->user !== null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Guru atau pegawai ini sudah memiliki akun.');
        }

        $roles = Role::query()
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();

        return view('admin.employees.accounts.create', [
            'employee' => $employee,
            'roles' => $roles,
            'suggestedUsername' => 'pegawai'.$employee->id,
        ]);
    }

    /**
     * Menyimpan akun login pegawai.
     */
    public function store(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $employee->load('person.user');

        if ($employee->person?->user !== null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Guru atau pegawai ini sudah memiliki akun.');
        }

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
            'role_id' => [
                'required',
                'exists:roles,id',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        DB::transaction(function () use ($validated, $employee, $request): void {
            $user = new User;

            $user->forceFill([
                'person_id' => $employee->person_id,
                'name' => $employee->person->full_name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'internal',
                'status' => $validated['status'],
                'password_changed_at' => now(),
                'failed_login_count' => 0,
                'locked_until' => null,
            ])->save();

            $user->roles()->syncWithoutDetaching([
                $validated['role_id'] => [
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ],
            ]);
        });

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Akun login guru atau pegawai berhasil dibuat.');
    }
}
