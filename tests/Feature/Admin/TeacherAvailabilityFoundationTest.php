<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\TeacherAvailability;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAvailabilityFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_availability_can_be_stored_with_relations(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = User::factory()->create([
            'name' => 'Pak Ahmad',
            'status' => 'active',
        ]);
        $admin = User::factory()->create();

        $availability = TeacherAvailability::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $teacher->id,
            'day_of_week' => 1,
            'starts_at' => '07:00',
            'ends_at' => '09:00',
            'availability_type' => 'unavailable',
            'reason' => 'Tugas luar',
            'notes' => 'Tidak bisa mengajar pada jam pertama dan kedua.',
            'status' => 'active',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $freshAvailability = $availability->fresh();

        $this->assertTrue($freshAvailability->academicYear->is($academicYear));
        $this->assertTrue($freshAvailability->semester->is($semester));
        $this->assertTrue($freshAvailability->teacher->is($teacher));
        $this->assertTrue($freshAvailability->createdBy->is($admin));

        $this->assertSame(1, $freshAvailability->day_of_week);
        $this->assertSame('07:00', $freshAvailability->starts_at);
        $this->assertSame('09:00', $freshAvailability->ends_at);
        $this->assertSame('unavailable', $freshAvailability->availability_type);
        $this->assertSame('Tugas luar', $freshAvailability->reason);
        $this->assertTrue($freshAvailability->is_active);
    }

    public function test_database_rejects_duplicate_active_teacher_availability_context(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = User::factory()->create([
            'status' => 'active',
        ]);

        TeacherAvailability::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $teacher->id,
            'day_of_week' => 1,
            'starts_at' => '07:00',
            'ends_at' => '09:00',
            'availability_type' => 'unavailable',
            'status' => 'active',
            'is_active' => true,
        ]);

        $duplicateWasRejected = false;

        try {
            TeacherAvailability::create([
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'teacher_user_id' => $teacher->id,
                'day_of_week' => 1,
                'starts_at' => '07:00',
                'ends_at' => '09:00',
                'availability_type' => 'unavailable',
                'status' => 'active',
                'is_active' => true,
            ]);
        } catch (QueryException) {
            $duplicateWasRejected = true;
        }

        $this->assertTrue($duplicateWasRejected);
    }

    public function test_academic_year_semester_and_user_can_access_teacher_availabilities(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = User::factory()->create([
            'status' => 'active',
        ]);

        TeacherAvailability::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $teacher->id,
            'day_of_week' => 2,
            'starts_at' => '10:00',
            'ends_at' => '11:00',
            'availability_type' => 'unavailable',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->assertSame(1, $academicYear->teacherAvailabilities()->count());
        $this->assertSame(1, $semester->teacherAvailabilities()->count());
        $this->assertSame(1, $teacher->teacherAvailabilities()->count());
    }

    private function createAcademicYear(): AcademicYear
    {
        return AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);
    }

    private function createSemester(AcademicYear $academicYear): Semester
    {
        return Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-ganjil',
            'name' => 'Semester Ganjil 2026/2027',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);
    }
}
