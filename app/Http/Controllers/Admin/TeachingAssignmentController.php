<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TeachingAssignment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
            'teachingAssignments' => $teachingAssignments,
            'academicYears' => $this->academicYears(),
            'semesters' => $this->semesters(),
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId' => $selectedSemesterId,
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
}
