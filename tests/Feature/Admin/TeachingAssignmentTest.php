<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_teaching_assignment_list(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.view');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = User::factory()->create([
            'name' => 'Bu Siti',
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

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments');

        $response
            ->assertStatus(200)
            ->assertSee('Plotting Beban Mengajar')
            ->assertSee('Bu Siti')
            ->assertSee($subject->name)
            ->assertSee($classGroup->name);
    }

    public function test_user_without_permission_cannot_view_teaching_assignment_list(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments');

        $response->assertStatus(403);
    }

    public function test_teaching_assignment_list_can_be_filtered_by_academic_year(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.view');

        $firstAcademicYear = $this->createAcademicYear('2025/2026');
        $firstSemester = $this->createSemester($firstAcademicYear);
        $firstClassGroup = $this->createClassGroup($firstAcademicYear);

        $secondAcademicYear = $this->createAcademicYear('2026/2027');
        $secondSemester = $this->createSemester($secondAcademicYear);
        $secondClassGroup = $this->createClassGroup($secondAcademicYear);

        $subject = $this->createSubject();
        $teacher = User::factory()->create([
            'name' => 'Pak Budi',
            'status' => 'active',
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $firstAcademicYear->id,
            'semester_id' => $firstSemester->id,
            'class_group_id' => $firstClassGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 4,
            'status' => 'active',
            'is_active' => true,
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $secondAcademicYear->id,
            'semester_id' => $secondSemester->id,
            'class_group_id' => $secondClassGroup->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 6,
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments?academic_year_id='.$firstAcademicYear->id);

        $response
            ->assertStatus(200)
            ->assertSee($firstClassGroup->name)
            ->assertDontSee($secondClassGroup->name);
    }

    private function createAcademicYear(string $name = '2026/2027'): AcademicYear
    {
        return AcademicYear::create([
            'code' => str_replace('/', '-', $name),
            'name' => $name,
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
            'code' => str_replace('/', '-', $academicYear->name).'-ganjil',
            'name' => 'Semester Ganjil '.$academicYear->name,
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
        $gradeLevel = GradeLevel::firstOrCreate(
            ['code' => 'VII'],
            [
                'name' => 'Kelas VII',
                'level_number' => 7,
                'is_active' => true,
            ]
        );

        return ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => null,
            'homeroom_teacher_user_id' => null,
            'code' => 'VII-A-'.$academicYear->id,
            'name' => 'Kelas VII A '.$academicYear->name,
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function createSubject(): Subject
    {
        return Subject::firstOrCreate(
            ['code' => 'MTK'],
            [
                'name' => 'Matematika',
                'subject_group' => 'umum',
                'is_local_content' => false,
                'is_religious' => false,
                'is_active' => true,
                'description' => 'Mata pelajaran Matematika.',
            ]
        );
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_teaching_assignment_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Teaching Assignment Role',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $dashboardPermission = Permission::firstOrCreate(
            [
                'name' => 'dashboard.view',
            ],
            [
                'module' => 'dashboard',
                'action' => 'view',
                'display_name' => 'Melihat Dashboard',
                'is_active' => true,
            ]
        );

        $role->permissions()->syncWithoutDetaching([
            $dashboardPermission->id,
        ]);

        [$module, $action] = explode('.', $permissionName);

        $permission = Permission::firstOrCreate(
            [
                'name' => $permissionName,
            ],
            [
                'module' => $module,
                'action' => $action,
                'display_name' => $permissionName,
                'is_active' => true,
            ]
        );

        $role->permissions()->syncWithoutDetaching([
            $permission->id,
        ]);

        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'assigned_at' => now(),
            ],
        ]);
    }
}
