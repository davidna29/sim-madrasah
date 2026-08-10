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

    /**
     * Data pendukung form.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
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
        Student $student
    ): array {
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
                    ),
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
        ]);

        $semester = Semester::query()
            ->findOrFail($validated['semester_id']);

        if ((int) $semester->academic_year_id !== (int) $validated['academic_year_id']) {
            throw ValidationException::withMessages([
                'semester_id' => 'Semester tidak sesuai dengan tahun ajaran yang dipilih.',
            ]);
        }

        $classGroup = ClassGroup::query()
            ->findOrFail($validated['class_group_id']);

        if ((int) $classGroup->academic_year_id !== (int) $validated['academic_year_id']) {
            throw ValidationException::withMessages([
                'class_group_id' => 'Rombongan belajar tidak sesuai dengan tahun ajaran yang dipilih.',
            ]);
        }

        $validated['is_current'] = $request->boolean('is_current');

        return $validated;
    }
}
