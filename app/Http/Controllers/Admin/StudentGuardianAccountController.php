<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentGuardianAccountController extends Controller
{
    /**
     * Menampilkan form pembuatan akun orang tua atau wali.
     */
    public function create(
        Student $student,
        StudentGuardian $guardian
    ): View|RedirectResponse {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $guardian->load('person.user');

        if ($guardian->person?->user !== null) {
            return redirect()
                ->route('admin.students.guardians.index', $student)
                ->with('success', 'Orang tua atau wali ini sudah memiliki akun.');
        }

        $roles = Role::query()
            ->where('is_active', true)
            ->where('name', 'orang_tua')
            ->orderBy('display_name')
            ->get();

        return view('admin.students.guardians.accounts.create', [
            'student' => $student->load('person'),
            'guardian' => $guardian,
            'roles' => $roles,
            'suggestedUsername' => 'wali'.$guardian->id,
        ]);
    }

    /**
     * Menyimpan akun login orang tua atau wali.
     */
    public function store(
        Request $request,
        Student $student,
        StudentGuardian $guardian
    ): RedirectResponse {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $guardian->load('person.user');

        if ($guardian->person?->user !== null) {
            return redirect()
                ->route('admin.students.guardians.index', $student)
                ->with('success', 'Orang tua atau wali ini sudah memiliki akun.');
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
                Rule::exists('roles', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('name', 'orang_tua')
                            ->where('is_active', true)
                    ),
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        DB::transaction(function () use ($validated, $guardian, $request): void {
            $user = new User;

            $user->forceFill([
                'person_id' => $guardian->person_id,
                'name' => $guardian->person->full_name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'parent',
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
            ->route('admin.students.guardians.index', $student)
            ->with('success', 'Akun login orang tua atau wali berhasil dibuat.');
    }

    private function ensureGuardianBelongsToStudent(
        Student $student,
        StudentGuardian $guardian
    ): void {
        abort_unless(
            (int) $guardian->student_id === (int) $student->id,
            404
        );
    }
}
