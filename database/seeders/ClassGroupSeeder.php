<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    /**
     * Membuat data rombongan belajar awal.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()
            ->where('code', '2026-2027')
            ->first();

        if ($academicYear === null) {
            $academicYear = AcademicYear::query()->first();
        }

        if ($academicYear === null) {
            return;
        }

        $gradeLevels = GradeLevel::query()
            ->whereIn('code', ['VII', 'VIII', 'IX'])
            ->get()
            ->keyBy('code');

        $rooms = Room::query()
            ->whereIn('code', [
                'R-VII-A',
                'R-VIII-A',
                'R-IX-A',
            ])
            ->get()
            ->keyBy('code');

        $classGroups = [
            [
                'code' => 'VII-A',
                'name' => 'Kelas VII A',
                'parallel_name' => 'A',
                'grade_level_code' => 'VII',
                'room_code' => 'R-VII-A',
                'capacity' => 32,
            ],
            [
                'code' => 'VIII-A',
                'name' => 'Kelas VIII A',
                'parallel_name' => 'A',
                'grade_level_code' => 'VIII',
                'room_code' => 'R-VIII-A',
                'capacity' => 32,
            ],
            [
                'code' => 'IX-A',
                'name' => 'Kelas IX A',
                'parallel_name' => 'A',
                'grade_level_code' => 'IX',
                'room_code' => 'R-IX-A',
                'capacity' => 32,
            ],
        ];

        foreach ($classGroups as $classGroup) {
            $gradeLevel = $gradeLevels->get(
                $classGroup['grade_level_code']
            );

            if ($gradeLevel === null) {
                continue;
            }

            ClassGroup::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'code' => $classGroup['code'],
                ],
                [
                    'grade_level_id' => $gradeLevel->id,
                    'room_id' => $rooms
                        ->get($classGroup['room_code'])
                        ?->id,
                    'homeroom_teacher_user_id' => null,
                    'name' => $classGroup['name'],
                    'parallel_name' => $classGroup['parallel_name'],
                    'capacity' => $classGroup['capacity'],
                    'status' => 'active',
                    'is_active' => true,
                ]
            );
        }
    }
}
