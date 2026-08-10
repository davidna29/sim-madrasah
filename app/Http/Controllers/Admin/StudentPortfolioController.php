<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Contracts\View\View;

class StudentPortfolioController extends Controller
{
    /**
     * Menampilkan ringkasan portofolio digital siswa.
     */
    public function show(Student $student): View
    {
        $student->load([
            'person.user',
            'admissionAcademicYear',
            'currentClassHistory.academicYear',
            'currentClassHistory.semester',
            'currentClassHistory.classGroup.gradeLevel',
            'currentClassHistory.classGroup.room',
        ]);

        $classHistories = $student->classHistories()
            ->with([
                'academicYear',
                'semester',
                'classGroup.gradeLevel',
                'classGroup.room',
            ])
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->limit(10)
            ->get();

        $guardians = $student->guardians()
            ->with('person.user')
            ->orderByDesc('is_primary_contact')
            ->orderBy('relationship')
            ->get();

        return view('admin.students.portfolio.show', [
            'student' => $student,
            'classHistories' => $classHistories,
            'guardians' => $guardians,
        ]);
    }
}
