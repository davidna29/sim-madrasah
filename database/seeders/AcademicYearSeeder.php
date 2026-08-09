<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Membuat tahun ajaran dan semester awal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $academicYear = AcademicYear::query()->updateOrCreate(
                [
                    'code' => '2026-2027',
                ],
                [
                    'name' => '2026/2027',
                    'start_date' => '2026-07-01',
                    'end_date' => '2027-06-30',
                    'status' => 'active',
                    'is_active' => true,
                    'is_locked' => false,
                ]
            );

            Semester::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'semester_type' => 'ganjil',
                ],
                [
                    'code' => '2026-2027-ganjil',
                    'name' => 'Semester Ganjil 2026/2027',
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-12-31',
                    'status' => 'active',
                    'is_active' => true,
                    'is_locked' => false,
                ]
            );

            Semester::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'semester_type' => 'genap',
                ],
                [
                    'code' => '2026-2027-genap',
                    'name' => 'Semester Genap 2026/2027',
                    'start_date' => '2027-01-01',
                    'end_date' => '2027-06-30',
                    'status' => 'draft',
                    'is_active' => false,
                    'is_locked' => false,
                ]
            );
        });
    }
}
