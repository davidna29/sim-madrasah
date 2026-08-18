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
use Illuminate\Validation\ValidationException;

class StudentBulkClassAssignmentController extends Controller
{
    public function create(): View
    {
        $students = Student::query()
            ->with([
                'person',
                'admissionAcademicYear',
                'currentClassHistory.classGroup',
                'currentClassHistory.semester',
            ])
            ->where('is_active', true)
            ->orderBy('student_number')
            ->get();

        $academicYears = AcademicYear::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        $activeSemester = Semester::query()
            ->with('academicYear')
            ->where('is_active', true)
            ->first();

        $semesters = Semester::query()
            ->with('academicYear')
            ->orderByDesc('academic_year_id')
            ->orderBy('semester_type')
            ->get();

        $classGroups = ClassGroup::query()
            ->with([
                'academicYear',
                'gradeLevel',
                'room',
            ])
            ->where('is_active', true)
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->get();

        return view('admin.students.bulk-class-assignment', [
            'students' => $students,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'classGroups' => $classGroups,
            'activeSemester' => $activeSemester,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'class_group_id' => ['required', 'integer', 'exists:class_groups,id'],
            'start_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->ensureAcademicContextIsValid($validated);

        $studentIds = collect($validated['student_ids'])
            ->map(fn ($studentId) => (int) $studentId)
            ->unique()
            ->values();

        $duplicateStudents = Student::query()
            ->with('person')
            ->whereIn('id', $studentIds)
            ->whereHas(
                'classHistories',
                fn ($query) => $query->where(
                    'semester_id',
                    (int) $validated['semester_id']
                )
            )
            ->get();

        if ($duplicateStudents->isNotEmpty()) {
            $names = $duplicateStudents
                ->map(fn (Student $student) => $student->person?->full_name ?? $student->student_number)
                ->implode(', ');

            throw ValidationException::withMessages([
                'student_ids' => 'Siswa berikut sudah memiliki histori kelas pada semester yang dipilih: '.$names,
            ]);
        }

        DB::transaction(function () use ($validated, $studentIds, $request): void {
            StudentClassHistory::query()
                ->whereIn('student_id', $studentIds)
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                ]);

            foreach ($studentIds as $studentId) {
                StudentClassHistory::create([
                    'student_id' => $studentId,
                    'academic_year_id' => (int) $validated['academic_year_id'],
                    'semester_id' => (int) $validated['semester_id'],
                    'class_group_id' => (int) $validated['class_group_id'],
                    'status' => 'active',
                    'start_date' => $validated['start_date'],
                    'is_current' => true,
                    'assigned_by' => $request->user()?->id,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Bulk assignment siswa ke rombel berhasil disimpan.');
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
