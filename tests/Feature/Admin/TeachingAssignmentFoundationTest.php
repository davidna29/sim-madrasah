<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingAssignmentFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teaching_assignment_can_store_teacher_subject_class_group_and_weekly_hours(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = User::factory()->create([
            'name' => 'Bu Siti',
            'status' => 'active',
        ]);
        $creator = User::factory()->create([
            'name' => 'Admin Kurikulum',
            'status' => 'active',
        ]);

        $assignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 5,
            'status' => 'active',
            'is_active' => true,
            'notes' => 'Plotting awal Matematika VII A.',
            'created_by' => $creator->id,
        ]);

        $this->assertDatabaseHas('teaching_assignments', [
            'id' => $assignment->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 5,
            'status' => 'active',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $freshAssignment = $assignment->fresh();

        $this->assertSame('2026/2027', $freshAssignment->academicYear->name);
        $this->assertSame('Semester Ganjil 2026/2027', $freshAssignment->semester->name);
        $this->assertSame('Kelas VII A', $freshAssignment->classGroup->name);
        $this->assertSame('Matematika', $freshAssignment->subject->name);
        $this->assertSame('Bu Siti', $freshAssignment->teacher->name);
        $this->assertSame('Admin Kurikulum', $freshAssignment->createdBy->name);
        $this->assertSame(5, $freshAssignment->weekly_hours);
        $this->assertTrue($freshAssignment->is_active);
    }

    public function test_teaching_assignment_rejects_duplicate_same_teacher_subject_class_group_and_semester(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = User::factory()->create([
            'status' => 'active',
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 5,
            'status' => 'active',
            'is_active' => true,
        ]);

        $duplicateWasRejected = false;

        try {
            TeachingAssignment::create([
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'subject_id' => $subject->id,
                'teacher_user_id' => $teacher->id,
                'weekly_hours' => 4,
                'status' => 'active',
                'is_active' => true,
            ]);
        } catch (QueryException) {
            $duplicateWasRejected = true;
        }

        $this->assertTrue($duplicateWasRejected);
        $this->assertSame(1, TeachingAssignment::count());
    }

    public function test_teaching_assignment_allows_same_subject_class_group_with_different_teacher(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $firstTeacher = User::factory()->create([
            'name' => 'Guru Pertama',
            'status' => 'active',
        ]);
        $secondTeacher = User::factory()->create([
            'name' => 'Guru Kedua',
            'status' => 'active',
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $firstTeacher->id,
            'weekly_hours' => 3,
            'status' => 'active',
            'is_active' => true,
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $secondTeacher->id,
            'weekly_hours' => 2,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->assertSame(2, TeachingAssignment::count());
    }

    public function test_related_models_can_access_teaching_assignments(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = User::factory()->create([
            'status' => 'active',
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 5,
            'status' => 'active',
            'is_active' => true,
            'created_by' => $teacher->id,
        ]);

        $this->assertSame(1, $academicYear->teachingAssignments()->count());
        $this->assertSame(1, $semester->teachingAssignments()->count());
        $this->assertSame(1, $classGroup->teachingAssignments()->count());
        $this->assertSame(1, $subject->teachingAssignments()->count());
        $this->assertSame(1, $teacher->teachingAssignmentsAsTeacher()->count());
        $this->assertSame(1, $teacher->createdTeachingAssignments()->count());
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

    private function createClassGroup(AcademicYear $academicYear): ClassGroup
    {
        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        return ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => null,
            'homeroom_teacher_user_id' => null,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function createSubject(): Subject
    {
        return Subject::create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'subject_group' => 'umum',
            'is_local_content' => false,
            'is_religious' => false,
            'is_active' => true,
            'description' => 'Mata pelajaran Matematika.',
        ]);
    }
}
