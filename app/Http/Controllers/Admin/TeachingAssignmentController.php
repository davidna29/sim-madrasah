<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeachingAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $selectedAcademicYearId = $request->integer('academic_year_id') ?: null;
        $selectedSemesterId = $request->integer('semester_id') ?: null;

        $teachingAssignments = TeachingAssignment::query()
            ->with([
                'academicYear',
                'semester',
                'classGroup.gradeLevel',
                'subject',
                'teacher',
            ])
            ->when(
                $selectedAcademicYearId,
                fn ($query) => $query->where('academic_year_id', $selectedAcademicYearId)
            )
            ->when(
                $selectedSemesterId,
                fn ($query) => $query->where('semester_id', $selectedSemesterId)
            )
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->orderBy('class_group_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.teaching-assignments.index', [
            'teachingAssignments'    => $teachingAssignments,
            'academicYears'          => $this->academicYears(),
            'semesters'              => $this->semesters(),
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId'     => $selectedSemesterId,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedAcademicYearId = $request->integer('academic_year_id') ?: null;
        $selectedSemesterId = $request->integer('semester_id') ?: null;

        return view('admin.teaching-assignments.create', [
            'academicYears'          => $this->academicYears(),
            'semesters'              => $this->semesters(),
            'classGroups'            => ClassGroup::orderBy('name')->get(),
            'subjects'               => Subject::where('is_active', true)->orderBy('name')->get(),
            'teachers'               => $this->teachers(),
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId'     => $selectedSemesterId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
            'class_group_id'   => ['required', 'exists:class_groups,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'teacher_user_id'  => ['required', 'exists:users,id'],
            'weekly_hours'     => ['required', 'integer', 'min:1', 'max:40'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $duplicate = TeachingAssignment::where([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id'      => $validated['semester_id'],
            'class_group_id'   => $validated['class_group_id'],
            'subject_id'       => $validated['subject_id'],
            'teacher_user_id'  => $validated['teacher_user_id'],
        ])->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors(['teacher_user_id' => 'Kombinasi guru, mata pelajaran, rombel, dan semester ini sudah ada.']);
        }

        TeachingAssignment::create([
            ...$validated,
            'status'     => 'active',
            'is_active'  => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.teaching-assignments.index', [
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id'      => $validated['semester_id'],
            ])
            ->with('success', 'Plotting beban mengajar berhasil ditambahkan.');
    }

    public function toggleActive(TeachingAssignment $teachingAssignment): RedirectResponse
    {
        $teachingAssignment->update([
            'is_active' => ! $teachingAssignment->is_active,
        ]);

        return redirect()
            ->route('admin.teaching-assignments.index')
            ->with('success', $teachingAssignment->is_active
                ? 'Plotting beban mengajar berhasil diaktifkan.'
                : 'Plotting beban mengajar berhasil dinonaktifkan.');
    }

    public function edit(TeachingAssignment $teachingAssignment): View
    {
        return view('admin.teaching-assignments.edit', [
            'teachingAssignment' => $teachingAssignment,
            'academicYears'      => $this->academicYears(),
            'semesters'          => $this->semesters(),
            'classGroups'        => ClassGroup::orderBy('name')->get(),
            'subjects'           => Subject::where('is_active', true)->orderBy('name')->get(),
            'teachers'           => $this->teachers(),
        ]);
    }

    public function update(
        Request $request,
        TeachingAssignment $teachingAssignment
    ): RedirectResponse {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id'      => ['required', 'exists:semesters,id'],
            'class_group_id'   => ['required', 'exists:class_groups,id'],
            'subject_id'       => ['required', 'exists:subjects,id'],
            'teacher_user_id'  => ['required', 'exists:users,id'],
            'weekly_hours'     => ['required', 'integer', 'min:1', 'max:40'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $duplicate = TeachingAssignment::where([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id'      => $validated['semester_id'],
            'class_group_id'   => $validated['class_group_id'],
            'subject_id'       => $validated['subject_id'],
            'teacher_user_id'  => $validated['teacher_user_id'],
        ])
            ->where('id', '!=', $teachingAssignment->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors(['teacher_user_id' => 'Kombinasi guru, mata pelajaran, rombel, dan semester ini sudah ada.']);
        }

        $teachingAssignment->update($validated);

        return redirect()
            ->route('admin.teaching-assignments.index', [
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id'      => $validated['semester_id'],
            ])
            ->with('success', 'Plotting beban mengajar berhasil diperbarui.');
    }

    private function teachers()
    {
        $teacherRoleNames = ['guru_mata_pelajaran', 'wali_kelas', 'guru_bk'];
        $teacherRoleIds = Role::whereIn('name', $teacherRoleNames)->pluck('id');

        return \App\Models\User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('roles.id', $teacherRoleIds))
            ->where('status', 'active')
            ->with('person')
            ->orderBy('name')
            ->get();
    }

    private function academicYears()
    {
        return AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();
    }

    private function semesters()
    {
        return Semester::query()
            ->with('academicYear')
            ->orderByDesc('academic_year_id')
            ->orderBy('semester_type')
            ->get();
    }
}