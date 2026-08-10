<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Person;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Membuat data siswa awal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $academicYear = AcademicYear::query()
                ->where('code', '2026-2027')
                ->first();

            $students = [
                [
                    'full_name' => 'Ahmad Siswa',
                    'email' => 'ahmad.siswa@sim-madrasah.test',
                    'student_number' => 'SIS-001',
                    'nisn' => '1000000001',
                    'registration_number' => 'REG-001',
                ],
                [
                    'full_name' => 'Siti Siswa',
                    'email' => 'siti.siswa@sim-madrasah.test',
                    'student_number' => 'SIS-002',
                    'nisn' => '1000000002',
                    'registration_number' => 'REG-002',
                ],
                [
                    'full_name' => 'Muhammad Siswa',
                    'email' => 'muhammad.siswa@sim-madrasah.test',
                    'student_number' => 'SIS-003',
                    'nisn' => '1000000003',
                    'registration_number' => 'REG-003',
                ],
            ];

            foreach ($students as $studentData) {
                $person = Person::query()->updateOrCreate(
                    [
                        'email' => $studentData['email'],
                    ],
                    [
                        'full_name' => $studentData['full_name'],
                        'email' => $studentData['email'],
                    ]
                );

                Student::query()->updateOrCreate(
                    [
                        'person_id' => $person->id,
                    ],
                    [
                        'admission_academic_year_id' => $academicYear?->id,
                        'student_number' => $studentData['student_number'],
                        'nisn' => $studentData['nisn'],
                        'registration_number' => $studentData['registration_number'],
                        'admission_date' => '2026-07-01',
                        'graduation_date' => null,
                        'status' => 'active',
                        'previous_school' => null,
                        'notes' => null,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
