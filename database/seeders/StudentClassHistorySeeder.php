<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentClassHistorySeeder extends Seeder
{
    /**
     * Membuat data awal riwayat kelas siswa.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $academicYear = AcademicYear::query()
                ->where('code', '2026-2027')
                ->first();

            if ($academicYear === null) {
                return;
            }

            $semester = Semester::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_type', 'ganjil')
                ->first();

            $classGroup = ClassGroup::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('code', 'VII-A')
                ->first();

            if ($semester === null || $classGroup === null) {
                return;
            }

            $students = Student::query()
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                StudentClassHistory::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'academic_year_id' => $academicYear->id,
                        'class_group_id' => $classGroup->id,
                        'status' => 'active',
                        'start_date' => $semester->start_date,
                        'end_date' => null,
                        'is_current' => true,
                        'assigned_by' => null,
                        'notes' => null,
                    ]
                );
            }
        });
    }
}
