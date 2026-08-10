<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        $portfolioUrl = route('admin.students.portfolio.show', $student);

        $qrCodeSvg = QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($portfolioUrl);

        return view('admin.students.portfolio.show', [
            'student' => $student,
            'classHistories' => $classHistories,
            'guardians' => $guardians,
            'portfolioUrl' => $portfolioUrl,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Mencetak kartu portofolio siswa dalam PDF.
     */
    public function card(Student $student): Response
    {
        $student->load([
            'person',
            'admissionAcademicYear',
            'currentClassHistory.academicYear',
            'currentClassHistory.semester',
            'currentClassHistory.classGroup.gradeLevel',
            'currentClassHistory.classGroup.room',
        ]);

        $portfolioUrl = route('admin.students.portfolio.show', $student);

        $qrCodeSvg = (string) QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($portfolioUrl);

        $qrCodeDataUri = 'data:image/svg+xml;base64,'.base64_encode($qrCodeSvg);

        $filename = 'kartu-portofolio-'.Str::slug(
            $student->student_number ?: $student->person?->full_name ?: 'siswa-'.$student->id
        ).'.pdf';

        $pdf = Pdf::loadView('admin.students.portfolio.card-pdf', [
            'student' => $student,
            'portfolioUrl' => $portfolioUrl,
            'qrCodeDataUri' => $qrCodeDataUri,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }
}
