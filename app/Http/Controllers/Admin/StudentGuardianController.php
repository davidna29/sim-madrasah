<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentGuardianController extends Controller
{
    public function index(Student $student): View
    {
        $student->load('person');

        $guardians = $student->guardians()
            ->with('person.user')
            ->orderByDesc('is_primary_contact')
            ->orderBy('relationship')
            ->paginate(15);

        return view('admin.students.guardians.index', [
            'student' => $student,
            'guardians' => $guardians,
        ]);
    }

    public function create(Student $student): View
    {
        $student->load('person');

        return view('admin.students.guardians.create', [
            'student' => $student,
            'guardian' => new StudentGuardian,
            'person' => new Person,
        ]);
    }

    public function store(
        Request $request,
        Student $student
    ): RedirectResponse {
        $validated = $this->validateGuardian($request);

        DB::transaction(function () use ($validated, $student): void {
            $person = Person::create([
                'national_id_number' => $validated['national_id_number'] ?? null,
                'full_name' => $validated['full_name'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            if ($validated['is_primary_contact']) {
                $student->guardians()->update([
                    'is_primary_contact' => false,
                ]);
            }

            StudentGuardian::create([
                'student_id' => $student->id,
                'person_id' => $person->id,
                'relationship' => $validated['relationship'],
                'occupation' => $validated['occupation'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'income_range' => $validated['income_range'] ?? null,
                'is_primary_contact' => $validated['is_primary_contact'],
                'is_emergency_contact' => $validated['is_emergency_contact'],
                'is_financial_responsible' => $validated['is_financial_responsible'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.guardians.index', $student)
            ->with('success', 'Data orang tua atau wali siswa berhasil dibuat.');
    }

    public function edit(
        Student $student,
        StudentGuardian $guardian
    ): View {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $student->load('person');
        $guardian->load('person');

        return view('admin.students.guardians.edit', [
            'student' => $student,
            'guardian' => $guardian,
            'person' => $guardian->person,
        ]);
    }

    public function update(
        Request $request,
        Student $student,
        StudentGuardian $guardian
    ): RedirectResponse {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $guardian->load('person');

        $validated = $this->validateGuardian($request, $guardian);

        DB::transaction(function () use ($validated, $student, $guardian): void {
            $guardian->person->update([
                'national_id_number' => $validated['national_id_number'] ?? null,
                'full_name' => $validated['full_name'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            if ($validated['is_primary_contact']) {
                $student->guardians()
                    ->whereKeyNot($guardian->id)
                    ->update([
                        'is_primary_contact' => false,
                    ]);
            }

            $guardian->update([
                'relationship' => $validated['relationship'],
                'occupation' => $validated['occupation'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'income_range' => $validated['income_range'] ?? null,
                'is_primary_contact' => $validated['is_primary_contact'],
                'is_emergency_contact' => $validated['is_emergency_contact'],
                'is_financial_responsible' => $validated['is_financial_responsible'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.guardians.index', $student)
            ->with('success', 'Data orang tua atau wali siswa berhasil diperbarui.');
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

    /**
     * Validasi orang tua atau wali siswa.
     *
     * @return array<string, mixed>
     */
    private function validateGuardian(
        Request $request,
        ?StudentGuardian $guardian = null
    ): array {
        $person = $guardian?->person;

        $validated = $request->validate([
            'national_id_number' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('people', 'national_id_number')
                    ->ignore($person),
            ],
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'birth_place' => [
                'nullable',
                'string',
                'max:100',
            ],
            'birth_date' => [
                'nullable',
                'date',
            ],
            'gender' => [
                'nullable',
                'string',
                'max:20',
            ],
            'religion' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:191',
                Rule::unique('people', 'email')
                    ->ignore($person),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'relationship' => [
                'required',
                'in:father,mother,guardian,other',
            ],
            'occupation' => [
                'nullable',
                'string',
                'max:100',
            ],
            'education_level' => [
                'nullable',
                'string',
                'max:50',
            ],
            'income_range' => [
                'nullable',
                'string',
                'max:50',
            ],
            'is_primary_contact' => [
                'nullable',
                'boolean',
            ],
            'is_emergency_contact' => [
                'nullable',
                'boolean',
            ],
            'is_financial_responsible' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['is_primary_contact'] = $request->boolean('is_primary_contact');
        $validated['is_emergency_contact'] = $request->boolean('is_emergency_contact');
        $validated['is_financial_responsible'] = $request->boolean('is_financial_responsible');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
