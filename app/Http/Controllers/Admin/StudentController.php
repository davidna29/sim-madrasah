<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Person;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = Student::query()
            ->with([
                'person.user',
                'admissionAcademicYear',
            ])
            ->orderByDesc('is_active')
            ->orderBy('student_number')
            ->paginate(15);

        return view('admin.students.index', [
            'students' => $students,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStudent($request);

        DB::transaction(function () use ($validated): void {
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

            Student::create([
                'person_id' => $person->id,
                'admission_academic_year_id' => $validated['admission_academic_year_id'] ?? null,
                'student_number' => $validated['student_number'] ?? null,
                'nisn' => $validated['nisn'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'graduation_date' => $validated['graduation_date'] ?? null,
                'status' => $validated['status'],
                'previous_school' => $validated['previous_school'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'],
            ]);
        });

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil dibuat.');
    }

    public function edit(Student $student): View
    {
        $student->load('person');

        return view('admin.students.edit', [
            'student' => $student,
        ] + $this->formData());
    }

    public function update(
        Request $request,
        Student $student
    ): RedirectResponse {
        $validated = $this->validateStudent($request, $student);

        DB::transaction(function () use ($validated, $student): void {
            $student->person->update([
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

            $student->update([
                'admission_academic_year_id' => $validated['admission_academic_year_id'] ?? null,
                'student_number' => $validated['student_number'] ?? null,
                'nisn' => $validated['nisn'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'graduation_date' => $validated['graduation_date'] ?? null,
                'status' => $validated['status'],
                'previous_school' => $validated['previous_school'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'],
            ]);
        });

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Data pendukung form siswa.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'academicYears' => AcademicYear::query()
                ->orderByDesc('start_date')
                ->get(),
        ];
    }

    /**
     * Validasi data siswa.
     *
     * @return array<string, mixed>
     */
    private function validateStudent(
        Request $request,
        ?Student $student = null
    ): array {
        $person = $student?->person;

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
            'admission_academic_year_id' => [
                'nullable',
                'exists:academic_years,id',
            ],
            'student_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')
                    ->ignore($student),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'nisn')
                    ->ignore($student),
            ],
            'registration_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'registration_number')
                    ->ignore($student),
            ],
            'admission_date' => [
                'nullable',
                'date',
            ],
            'graduation_date' => [
                'nullable',
                'date',
                'after_or_equal:admission_date',
            ],
            'status' => [
                'required',
                'in:active,inactive,transferred,graduated,alumni',
            ],
            'previous_school' => [
                'nullable',
                'string',
                'max:150',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
