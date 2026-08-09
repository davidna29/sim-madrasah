<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Membuat data guru dan pegawai awal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $employees = [
                [
                    'full_name' => 'Kepala Madrasah',
                    'email' => 'kepala@sim-madrasah.test',
                    'employee_number' => 'EMP-001',
                    'employee_type' => 'teacher',
                    'employment_status' => 'permanent',
                    'position' => 'Kepala Madrasah',
                    'is_teacher' => true,
                ],
                [
                    'full_name' => 'Guru Mata Pelajaran',
                    'email' => 'guru@sim-madrasah.test',
                    'employee_number' => 'EMP-002',
                    'employee_type' => 'teacher',
                    'employment_status' => 'permanent',
                    'position' => 'Guru Mata Pelajaran',
                    'is_teacher' => true,
                ],
                [
                    'full_name' => 'Tata Usaha',
                    'email' => 'tu@sim-madrasah.test',
                    'employee_number' => 'EMP-003',
                    'employee_type' => 'staff',
                    'employment_status' => 'permanent',
                    'position' => 'Tata Usaha',
                    'is_teacher' => false,
                ],
            ];

            foreach ($employees as $employeeData) {
                $person = Person::query()->updateOrCreate(
                    [
                        'email' => $employeeData['email'],
                    ],
                    [
                        'full_name' => $employeeData['full_name'],
                        'email' => $employeeData['email'],
                    ]
                );

                Employee::query()->updateOrCreate(
                    [
                        'person_id' => $person->id,
                    ],
                    [
                        'employee_number' => $employeeData['employee_number'],
                        'nip' => null,
                        'nuptk' => null,
                        'employee_type' => $employeeData['employee_type'],
                        'employment_status' => $employeeData['employment_status'],
                        'position' => $employeeData['position'],
                        'join_date' => now()->toDateString(),
                        'end_date' => null,
                        'education_level' => null,
                        'major' => null,
                        'is_teacher' => $employeeData['is_teacher'],
                        'is_active' => true,
                        'notes' => null,
                    ]
                );
            }
        });
    }
}
