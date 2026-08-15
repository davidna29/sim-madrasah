<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentClassHistoryController extends Controller
{
    public function index(Student $student): View
    {
        $student->load('person');

        $histories = $student->classHistories()
            ->with([
                'academicYear',
                'semester',
                'classGroup.gradeLevel',
                'classGroup.room',
                'assignedBy',
            ])
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->paginate(15);

        return view('admin.students.class-histories.index', [
            'student' => $student,
            'histories' => $histories,
        ]);
    }

    public function create(Student $student): View
    {
        $student->load('person');

        return view('admin.students.class-histories.create', [
            'student' => $student,
        ] + $this->formData());
    }

    public function edit(
        Student $student,
        StudentClassHistory $classHistory
    ): View {
        abort_unless($classHistory->student_id === $student->id, 404);

        $student->load('person');

        return view('admin.students.class-histories.edit', [
            'student' => $student,
            'classHistory' => $classHistory,
        ] + $this->formData());
    }

    public function store(
        Request $request,
        Student $student
    ): RedirectResponse {
        $validated = $this->validateClassHistory($request, $student);

        DB::transaction(function () use ($validated, $student, $request): void {
            if ($validated['is_current']) {
                $student->classHistories()
                    ->update([
                        'is_current' => false,
                    ]);
            }

            StudentClassHistory::create([
                'student_id' => $student->id,
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
                'class_group_id' => $validated['class_group_id'],
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'is_current' => $validated['is_current'],
                'assigned_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.class-histories.index', $student)
            ->with('success', 'Riwayat kelas siswa berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Student $student,
        StudentClassHistory $classHistory
    ): RedirectResponse {
        abort_unless($classHistory->student_id === $student->id, 404);

        $validated = $this->validateClassHistory($request, $student, $classHistory);

        DB::transaction(function () use ($validated, $student, $classHistory): void {
            if ($validated['is_current']) {
                $student->classHistories()
                    ->where('id', '!=', $classHistory->id)
                    ->update([
                        'is_current' => false,
                    ]);
            }

            $classHistory->update([
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
                'class_group_id' => $validated['class_group_id'],
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'is_current' => $validated['is_current'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.class-histories.index', $student)
            ->with('success', 'Riwayat kelas siswa berhasil diperbarui.');
    }

    /**
     * Data pendukung form.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $activeSemester = Semester::query()
            ->with('academicYear')
            ->where('is_active', true)
            ->first();

        return [
            'activeSemester' => $activeSemester,
            'academicYears' => AcademicYear::query()
                ->orderByDesc('start_date')
                ->get(),
            'semesters' => Semester::query()
                ->with('academicYear')
                ->orderByDesc('academic_year_id')
                ->orderBy('semester_type')
                ->get(),
            'classGroups' => ClassGroup::query()
                ->with([
                    'academicYear',
                    'gradeLevel',
                    'room',
                ])
                ->where('is_active', true)
                ->orderByDesc('academic_year_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Validasi riwayat kelas siswa.
     *
     * @return array<string, mixed>
     */
    private function validateClassHistory(
        Request $request,
        Student $student,
        ?StudentClassHistory $ignoringHistory = null
    ): array {
        $messages = [
            'semester_id.unique' => 'Siswa sudah memiliki histori kelas pada semester ini.',
        ];

        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
            'semester_id' => [
                'required',
                'exists:semesters,id',
                Rule::unique('student_class_histories', 'semester_id')
                    ->where(
                        fn ($query) => $query->where(
                            'student_id',
                            $student->id
                        )
                    )
                    ->ignore($ignoringHistory?->id),
            ],
            'class_group_id' => [
                'required',
                'exists:class_groups,id',
            ],
            'status' => [
                'required',
                'in:active,completed,transferred,graduated',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'is_current' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ], $messages);

        $validated['is_current'] = $request->boolean('is_current');

        // Pastikan konteks akademik valid
        $this->ensureAcademicContextIsValid($validated);

        return $validated;
    }

    /**
     * Pastikan tahun ajaran, semester, rombel, dan tanggal mulai saling cocok.
     *
     * @param  array<string, mixed>  $validated
     */
    private function ensureAcademicContextIsValid(array $validated): void
    {
        $semester = Semester::findOrFail((int) $validated['semester_id']);
        $classGroup = ClassGroup::findOrFail((int) $validated['class_group_id']);

        if ((int) $semester->academic_year_id !== (int) $validated['academic_year_id']) {
            throw ValidationException::withMessages([
                'semester_id' => 'Semester harus sesuai dengan tahun ajaran yang dipilih.',
            ]);
        }

        if ((int) $classGroup->academic_year_id !== (int) $validated['academic_year_id']) {
            throw ValidationException::withMessages([
                'class_group_id' => 'Rombongan belajar harus sesuai dengan tahun ajaran yang dipilih.',
            ]);
        }

        if (! empty($validated['start_date'])) {
            $semesterStartDate = Carbon::parse($semester->start_date)->toDateString();
            $semesterEndDate = Carbon::parse($semester->end_date)->toDateString();
            $startDate = Carbon::parse($validated['start_date'])->toDateString();

            if ($startDate < $semesterStartDate || $startDate > $semesterEndDate) {
                throw ValidationException::withMessages([
                    'start_date' => 'Tanggal mulai harus berada dalam rentang tanggal semester yang dipilih.',
                ]);
            }
        }
    }
}
