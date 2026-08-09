<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    /**
     * Membuat data tingkat kelas awal.
     */
    public function run(): void
    {
        $gradeLevels = [
            [
                'code' => 'VII',
                'name' => 'Kelas VII',
                'level_number' => 7,
            ],
            [
                'code' => 'VIII',
                'name' => 'Kelas VIII',
                'level_number' => 8,
            ],
            [
                'code' => 'IX',
                'name' => 'Kelas IX',
                'level_number' => 9,
            ],
        ];

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevel::query()->updateOrCreate(
                [
                    'code' => $gradeLevel['code'],
                ],
                [
                    'name' => $gradeLevel['name'],
                    'level_number' => $gradeLevel['level_number'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}
