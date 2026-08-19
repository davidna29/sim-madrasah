<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TeacherAvailability;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
