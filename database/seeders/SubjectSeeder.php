<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Membuat data mata pelajaran awal.
     */
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'PAI-QH',
                'name' => "Al-Qur'an Hadis",
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'PAI-AA',
                'name' => 'Akidah Akhlak',
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'PAI-FQ',
                'name' => 'Fiqih',
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'PAI-SKI',
                'name' => 'Sejarah Kebudayaan Islam',
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'BIN',
                'name' => 'Bahasa Indonesia',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'BIG',
                'name' => 'Bahasa Inggris',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'MTK',
                'name' => 'Matematika',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'IPA',
                'name' => 'Ilmu Pengetahuan Alam',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'IPS',
                'name' => 'Ilmu Pengetahuan Sosial',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'PJOK',
                'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'SBK',
                'name' => 'Seni Budaya',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'INF',
                'name' => 'Informatika',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'BAR',
                'name' => 'Bahasa Arab',
                'subject_group' => 'language',
                'is_religious' => false,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(
                [
                    'code' => $subject['code'],
                ],
                [
                    'name' => $subject['name'],
                    'subject_group' => $subject['subject_group'],
                    'is_local_content' => $subject['is_local_content'] ?? false,
                    'is_religious' => $subject['is_religious'],
                    'is_active' => true,
                    'description' => null,
                ]
            );
        }
    }
}
