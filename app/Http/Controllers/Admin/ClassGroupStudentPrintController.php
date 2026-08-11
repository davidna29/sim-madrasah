<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\StudentClassHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ClassGroupStudentPrintController extends Controller
{
    /**
     * Mencetak daftar siswa per rombongan belajar.
     */
    public function __invoke(
        Request $request,
        ClassGroup $classGroup
    ): Response {
        $classGroup->load([
            'academicYear',
            'gradeLevel',
            'room',
            'homeroomTeacher',
        ]);

        $semester = $this->resolveSemester($request, $classGroup);

        $histories = StudentClassHistory::query()
            ->with([
                'student.person',
                'semester',
                'academicYear',
                'classGroup',
            ])
            ->where('class_group_id', $classGroup->id)
            ->when(
                $semester !== null,
                fn ($query) => $query->where('semester_id', $semester->id)
            )
            ->get()
            ->sortBy(fn ($history) => $history->student?->person?->full_name)
            ->values();

        $filename = 'daftar-siswa-'.Str::slug($classGroup->code).'.pdf';

        $pdf = Pdf::loadView('admin.class-groups.students-pdf', [
            'classGroup' => $classGroup,
            'semester' => $semester,
            'histories' => $histories,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }

    private function resolveSemester(
        Request $request,
        ClassGroup $classGroup
    ): ?Semester {
        if ($request->filled('semester_id')) {
            return Semester::query()
                ->where('academic_year_id', $classGroup->academic_year_id)
                ->whereKey($request->integer('semester_id'))
                ->first();
        }

        return Semester::query()
            ->where('academic_year_id', $classGroup->academic_year_id)
            ->where('status', 'active')
            ->first()
            ?? Semester::query()
                ->where('academic_year_id', $classGroup->academic_year_id)
                ->orderBy('start_date')
                ->first();
    }
}
