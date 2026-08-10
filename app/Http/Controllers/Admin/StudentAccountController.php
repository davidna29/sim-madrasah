<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StudentAccountController extends Controller
{
    /**
     * Menampilkan form pembuatan akun siswa.
     */
    public function create(Student $student): View|RedirectResponse
    {
        $student->load('person.user');

        if ($student->person?->user !== null) {
            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Siswa ini sudah memiliki akun.');
        }

        $roles = Role::query()
            ->where('is_active', true)
            ->whereIn('name', [
                'siswa',
                'orang_tua',
            ])
            ->orderBy('display_name')
            ->get();

        return view('admin.students.accounts.create', [
            'student' => $student,
            'roles' => $roles,
            'suggestedUsername' => $this->suggestUsername($student),
        ]);
    }

    /**
     * Menyimpan akun login siswa.
     */
    public function store(
        Request $request,
        Student $student
    ): RedirectResponse {
        $student->load('person.user');

        if ($student->person?->user !== null) {
            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Siswa ini sudah memiliki akun.');
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

        DB::transaction(function () use ($validated, $student, $request): void {
            $user = new User;

            $user->forceFill([
                'person_id' => $student->person_id,
                'name' => $student->person->full_name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'student',
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
            ->route('admin.students.index')
            ->with('success', 'Akun login siswa berhasil dibuat.');
    }

    private function suggestUsername(Student $student): string
    {
        if ($student->student_number) {
            return Str::lower(
                Str::slug($student->student_number, '.')
            );
        }

        return 'siswa'.$student->id;
    }
}
