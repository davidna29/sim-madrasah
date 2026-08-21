<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TeacherAvailability;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TeacherAvailabilityController extends Controller
{
    public function index(Request $request): View
    {
        $selectedAcademicYearId = $request->integer('academic_year_id') ?: null;
        $selectedSemesterId = $request->integer('semester_id') ?: null;
        $selectedTeacherUserId = $request->integer('teacher_user_id') ?: null;

        $teacherAvailabilities = TeacherAvailability::query()
            ->with([
                'academicYear',
                'semester.academicYear',
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
            ->when(
                $selectedTeacherUserId,
                fn ($query) => $query->where('teacher_user_id', $selectedTeacherUserId)
            )
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->orderBy('teacher_user_id')
            ->orderBy('day_of_week')
            ->orderBy('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.teacher-availabilities.index', [
            'teacherAvailabilities' => $teacherAvailabilities,
            'academicYears' => $this->academicYears(),
            'semesters' => $this->semesters(),
            'teachers' => $this->teachers(),
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedTeacherUserId' => $selectedTeacherUserId,
        ]);
    }

    public function create(Request $request): View
    {
        $selectedAcademicYearId = $request->integer('academic_year_id') ?: null;
        $selectedSemesterId = $request->integer('semester_id') ?: null;
        $selectedTeacherUserId = $request->integer('teacher_user_id') ?: null;

        return view('admin.teacher-availabilities.create', [
            'academicYears' => $this->academicYears(),
            'semesters' => $this->semesters(),
            'teachers' => $this->teachers(),
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId' => $selectedSemesterId,
            'selectedTeacherUserId' => $selectedTeacherUserId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'teacher_user_id' => ['required', 'exists:users,id'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'availability_type' => ['required', 'in:unavailable'],
            'reason' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $duplicate = TeacherAvailability::where([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id' => $validated['semester_id'],
            'teacher_user_id' => $validated['teacher_user_id'],
            'day_of_week' => $validated['day_of_week'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'availability_type' => $validated['availability_type'],
            'is_active' => true,
        ])->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'starts_at' => 'Data ketersediaan guru dengan konteks, hari, jam, dan tipe yang sama sudah ada.',
            ]);
        }

        TeacherAvailability::create([
            ...$validated,
            'status' => 'active',
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('admin.teacher-availabilities.index', [
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
                'teacher_user_id' => $validated['teacher_user_id'],
            ])
            ->with('success', 'Ketersediaan guru berhasil ditambahkan.');
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

    private function teachers()
    {
        $teacherRoleNames = ['guru_mata_pelajaran', 'wali_kelas', 'guru_bk'];

        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('roles.name', $teacherRoleNames))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
