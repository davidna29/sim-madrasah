<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentGuardianSeeder extends Seeder
{
    /**
     * Membuat data awal orang tua atau wali siswa.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $students = Student::query()
                ->with('person')
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                $baseName = Str::slug(
                    $student->person?->full_name ?? 'siswa-'.$student->id,
                    '.'
                );

                $guardianPerson = Person::query()->updateOrCreate(
                    [
                        'email' => 'wali.'.$baseName.'@sim-madrasah.test',
                    ],
                    [
                        'full_name' => 'Wali '.$student->person?->full_name,
                        'email' => 'wali.'.$baseName.'@sim-madrasah.test',
                        'phone' => '0800000000',
                        'address' => $student->person?->address,
                    ]
                );

                StudentGuardian::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'person_id' => $guardianPerson->id,
                    ],
                    [
                        'relationship' => 'guardian',
                        'occupation' => null,
                        'education_level' => null,
                        'income_range' => null,
                        'is_primary_contact' => true,
                        'is_emergency_contact' => true,
                        'is_financial_responsible' => true,
                        'is_active' => true,
                        'notes' => null,
                    ]
                );
            }
        });
    }
}
