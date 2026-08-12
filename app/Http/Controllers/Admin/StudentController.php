<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Person;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentController extends Controller
{
    // Update Controller Siswa
    public function index(Request $request): View
    {
        // Ambil data status siswa dari method studentStatuses()
        $studentStatuses = $this->studentStatuses();

        $selectedClassGroupId = $request->integer('class_group_id') ?: null;

        $selectedAdmissionAcademicYearId = $request->integer('admission_academic_year_id') ?: null;

        $selectedStatus = (string) $request->query('status', '');
        $selectedStatus = array_key_exists($selectedStatus, $studentStatuses)
            ? $selectedStatus
            : null;

        $search = trim((string) $request->query('q', ''));

        $classGroups = ClassGroup::query()
            ->with([
                'academicYear',
                'gradeLevel',
            ])
            ->where('is_active', true)
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get();

        // Ambil data tahun ajaran untuk filter berdasarkan tahun ajaran masuk siswa
        $admissionAcademicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $students = $this->filteredStudentsQuery(
            selectedClassGroupId: $selectedClassGroupId,
            selectedStatus: $selectedStatus,
            selectedAdmissionAcademicYearId: $selectedAdmissionAcademicYearId,
            search: $search,
        )
            ->with([
                'person.user',
                'admissionAcademicYear',
                'currentClassHistory.academicYear',
                'currentClassHistory.semester',
                'currentClassHistory.classGroup.gradeLevel',
                'currentClassHistory.classGroup.room',
            ])
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('student_number', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%')
                        ->orWhere('registration_number', 'like', '%'.$search.'%')
                        ->orWhereHas(
                            'person',
                            fn ($personQuery) => $personQuery->where(
                                'full_name',
                                'like',
                                '%'.$search.'%'
                            )
                        );
                })
            )
            ->when(
                $selectedClassGroupId,
                fn ($query) => $query->whereHas(
                    'classHistories',
                    fn ($historyQuery) => $historyQuery
                        ->where('class_group_id', $selectedClassGroupId)
                        ->where('is_current', true)
                )
            )
            // Filter berdasarkan status siswa jika ada status yang dipilih
            ->when(
                $selectedStatus !== null,
                fn ($query) => $query->where('status', $selectedStatus)
            )
            // Filter berdasarkan tahun ajaran masuk siswa jika ada tahun ajaran yang dipilih
            ->when(
                $selectedAdmissionAcademicYearId,
                fn ($query) => $query->where(
                    'admission_academic_year_id',
                    $selectedAdmissionAcademicYearId
                )
            )
            ->orderByDesc('is_active')
            ->orderBy('student_number')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', [
            'students' => $students,
            'classGroups' => $classGroups,
            'selectedClassGroupId' => $selectedClassGroupId,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'studentStatuses' => $studentStatuses,
            'admissionAcademicYears' => $admissionAcademicYears,
            'selectedAdmissionAcademicYearId' => $selectedAdmissionAcademicYearId,
        ]);
    }

    // Tambahkan method export untuk mengekspor data siswa ke CSV
    public function export(Request $request): StreamedResponse
    {
        $studentStatuses = $this->studentStatuses();

        $selectedClassGroupId = $request->integer('class_group_id') ?: null;
        $selectedAdmissionAcademicYearId = $request->integer('admission_academic_year_id') ?: null;

        $selectedStatus = (string) $request->query('status', '');
        $selectedStatus = array_key_exists($selectedStatus, $studentStatuses)
            ? $selectedStatus
            : null;

        $search = trim((string) $request->query('q', ''));

        $fileName = 'data-siswa-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use (
            $selectedClassGroupId,
            $selectedStatus,
            $selectedAdmissionAcademicYearId,
            $search,
            $studentStatuses
        ): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'NIS',
                'NISN',
                'Nomor Registrasi',
                'Nama',
                'Tahun Masuk',
                'Kelas Saat Ini',
                'Semester',
                'Status',
                'Aktif',
            ]);

            $this->filteredStudentsQuery(
                selectedClassGroupId: $selectedClassGroupId,
                selectedStatus: $selectedStatus,
                selectedAdmissionAcademicYearId: $selectedAdmissionAcademicYearId,
                search: $search,
            )
                ->with([
                    'person',
                    'admissionAcademicYear',
                    'currentClassHistory.semester',
                    'currentClassHistory.classGroup',
                ])
                ->orderByDesc('is_active')
                ->orderBy('student_number')
                ->chunk(100, function ($students) use ($handle, $studentStatuses): void {
                    foreach ($students as $student) {
                        $currentClassHistory = $student->currentClassHistory;

                        fputcsv($handle, [
                            $student->student_number,
                            $student->nisn,
                            $student->registration_number,
                            $student->person?->full_name,
                            $student->admissionAcademicYear?->name,
                            $currentClassHistory?->classGroup?->name,
                            $currentClassHistory?->semester?->name,
                            $studentStatuses[$student->status] ?? $student->status,
                            $student->is_active ? 'Ya' : 'Tidak',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
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
     * Query dasar daftar siswa yang sudah mengikuti filter aktif.
     *
     * @return Builder<Student>
     */
    private function filteredStudentsQuery(
        ?int $selectedClassGroupId,
        ?string $selectedStatus,
        ?int $selectedAdmissionAcademicYearId,
        string $search,
    ): Builder {
        return Student::query()
            ->when(
                $search !== '',
                fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('student_number', 'like', '%'.$search.'%')
                        ->orWhere('nisn', 'like', '%'.$search.'%')
                        ->orWhere('registration_number', 'like', '%'.$search.'%')
                        ->orWhereHas(
                            'person',
                            fn ($personQuery) => $personQuery->where(
                                'full_name',
                                'like',
                                '%'.$search.'%'
                            )
                        );
                })
            )
            ->when(
                $selectedClassGroupId,
                fn ($query) => $query->whereHas(
                    'classHistories',
                    fn ($historyQuery) => $historyQuery
                        ->where('class_group_id', $selectedClassGroupId)
                        ->where('is_current', true)
                )
            )
            ->when(
                $selectedStatus !== null,
                fn ($query) => $query->where('status', $selectedStatus)
            )
            ->when(
                $selectedAdmissionAcademicYearId,
                fn ($query) => $query->where(
                    'admission_academic_year_id',
                    $selectedAdmissionAcademicYearId
                )
            );
    }

    /**
     * Pilihan status siswa untuk filter dan form.
     *
     * @return array<string, string>
     */
    private function studentStatuses(): array
    {
        return [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'transferred' => 'Pindah',
            'graduated' => 'Lulus',
            'alumni' => 'Alumni',
        ];
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
