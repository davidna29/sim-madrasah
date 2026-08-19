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

class TeachingAssignmentWorkloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_teacher_workload_report(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.view');

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments/teacher-workload');

        $response
            ->assertStatus(200)
            ->assertSee('Rekap Beban Guru')
            ->assertSee('Total Jam/Minggu');
    }

    public function test_user_without_permission_cannot_view_teacher_workload_report(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments/teacher-workload');

        $response->assertStatus(403);
    }

    public function test_teacher_workload_report_sums_only_active_assignments_and_respects_filters(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.view');

        $academicYear = $this->createAcademicYear('2026/2027');
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);

        $otherAcademicYear = $this->createAcademicYear('2027/2028');
        $otherSemester = $this->createSemester($otherAcademicYear);
        $otherClassGroup = $this->createClassGroup($otherAcademicYear);

        $teacher = $this->createTeacher('Pak Ahmad');
        $otherTeacher = $this->createTeacher('Bu Budi');

        $math = $this->createSubject('MTK', 'Matematika');
        $science = $this->createSubject('IPA', 'Ilmu Pengetahuan Alam');
        $inactiveSubject = $this->createSubject('BIN', 'Bahasa Indonesia');

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $math->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 3,
            'status' => 'active',
            'is_active' => true,
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $science->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 4,
            'status' => 'active',
            'is_active' => true,
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'subject_id' => $inactiveSubject->id,
            'teacher_user_id' => $teacher->id,
            'weekly_hours' => 8,
            'status' => 'active',
            'is_active' => false,
        ]);

        TeachingAssignment::create([
            'academic_year_id' => $otherAcademicYear->id,
            'semester_id' => $otherSemester->id,
            'class_group_id' => $otherClassGroup->id,
            'subject_id' => $math->id,
            'teacher_user_id' => $otherTeacher->id,
            'weekly_hours' => 9,
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments/teacher-workload?academic_year_id='.$academicYear->id.'&semester_id='.$semester->id);

        $response
            ->assertStatus(200)
            ->assertSee('Pak Ahmad')
            ->assertSee('2 plotting')
            ->assertSee('7 jam')
            ->assertDontSee('Bu Budi')
            ->assertDontSee('15 jam');
    }

    private function createAcademicYear(string $name): AcademicYear
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

    private function createSubject(string $code, string $name): Subject
    {
        return Subject::create([
            'code' => $code,
            'name' => $name,
            'subject_group' => 'umum',
            'is_local_content' => false,
            'is_religious' => false,
            'is_active' => true,
            'description' => 'Mata pelajaran '.$name.'.',
        ]);
    }

    private function createTeacher(string $name): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'guru_mata_pelajaran'],
            [
                'display_name' => 'Guru Mata Pelajaran',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $teacher = User::factory()->create([
            'name' => $name,
            'status' => 'active',
        ]);

        $teacher->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()],
        ]);

        return $teacher;
    }

    private function grantPermissionToUser(User $user, string $permissionName): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'test_teaching_assignment_workload_role'],
            [
                'display_name' => 'Test Teaching Assignment Workload Role',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $dashboardPermission = Permission::firstOrCreate(
            ['name' => 'dashboard.view'],
            [
                'module' => 'dashboard',
                'action' => 'view',
                'display_name' => 'Melihat Dashboard',
                'is_active' => true,
            ]
        );

        [$module, $action] = explode('.', $permissionName);

        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            [
                'module' => $module,
                'action' => $action,
                'display_name' => $permissionName,
                'is_active' => true,
            ]
        );

        $role->permissions()->syncWithoutDetaching([
            $dashboardPermission->id,
            $permission->id,
        ]);

        $user->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()],
        ]);
    }
}
