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

        public function test_user_with_permission_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.create');

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments/create');

        $response
            ->assertStatus(200)
            ->assertSee('Tambah Plotting Beban Mengajar');
    }

    public function test_user_without_permission_cannot_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/teaching-assignments/create');

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_store_teaching_assignment(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.create');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $response = $this
            ->actingAs($user)
            ->post('/admin/teaching-assignments', [
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'class_group_id'   => $classGroup->id,
                'subject_id'       => $subject->id,
                'teacher_user_id'  => $teacher->id,
                'weekly_hours'     => 3,
                'notes'            => 'Catatan test',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teaching_assignments', [
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);
    }

    public function test_store_rejects_duplicate_teaching_assignment(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.create');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/admin/teaching-assignments', [
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'class_group_id'   => $classGroup->id,
                'subject_id'       => $subject->id,
                'teacher_user_id'  => $teacher->id,
                'weekly_hours'     => 3,
            ]);

        $response->assertSessionHasErrors('teacher_user_id');

        $this->assertSame(
            1,
            TeachingAssignment::count()
        );
    }

    public function test_store_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/teaching-assignments', [
                'academic_year_id' => null,
                'semester_id'      => null,
                'class_group_id'   => null,
                'subject_id'       => null,
                'teacher_user_id'  => null,
                'weekly_hours'     => null,
            ]);

        $response->assertSessionHasErrors([
            'academic_year_id',
            'semester_id',
            'class_group_id',
            'subject_id',
            'teacher_user_id',
            'weekly_hours',
        ]);
    }

    public function test_user_with_permission_can_toggle_teaching_assignment_active_status(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 4,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/teaching-assignments/{$teachingAssignment->id}/toggle-active");

        $response->assertRedirect('/admin/teaching-assignments');

        $this->assertFalse(
            $teachingAssignment->fresh()->is_active
        );
    }

    public function test_user_without_permission_cannot_toggle_teaching_assignment_active_status(): void
    {
        $user = User::factory()->create();

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 4,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/teaching-assignments/{$teachingAssignment->id}/toggle-active");

        $response->assertStatus(403);

        $this->assertTrue(
            $teachingAssignment->fresh()->is_active
        );
    }

    public function test_user_with_permission_can_view_edit_form(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/admin/teaching-assignments/{$teachingAssignment->id}/edit");

        $response
            ->assertStatus(200)
            ->assertSee('Edit Plotting Beban Mengajar');
    }

    public function test_user_without_permission_cannot_view_edit_form(): void
    {
        $user = User::factory()->create();

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/admin/teaching-assignments/{$teachingAssignment->id}/edit");

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_update_teaching_assignment(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();
        $newTeacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/teaching-assignments/{$teachingAssignment->id}", [
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'class_group_id'   => $classGroup->id,
                'subject_id'       => $subject->id,
                'teacher_user_id'  => $newTeacher->id,
                'weekly_hours'     => 5,
                'notes'            => 'Catatan diperbarui',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teaching_assignments', [
            'id'               => $teachingAssignment->id,
            'teacher_user_id'  => $newTeacher->id,
            'weekly_hours'     => 5,
            'notes'            => 'Catatan diperbarui',
        ]);
    }

    public function test_update_does_not_reject_saving_unchanged_data(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/teaching-assignments/{$teachingAssignment->id}", [
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'class_group_id'   => $classGroup->id,
                'subject_id'       => $subject->id,
                'teacher_user_id'  => $teacher->id,
                'weekly_hours'     => 6,
            ]);

        $response->assertSessionDoesntHaveErrors();

        $this->assertSame(
            6,
            $teachingAssignment->fresh()->weekly_hours
        );
    }

    public function test_update_rejects_duplicate_with_other_teaching_assignment(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacherA = $this->createTeacher();
        $teacherB = $this->createTeacher();

        TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacherA->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $teachingAssignmentB = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacherB->id,
            'weekly_hours'     => 4,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/teaching-assignments/{$teachingAssignmentB->id}", [
                'academic_year_id' => $academicYear->id,
                'semester_id'      => $semester->id,
                'class_group_id'   => $classGroup->id,
                'subject_id'       => $subject->id,
                'teacher_user_id'  => $teacherA->id,
                'weekly_hours'     => 4,
            ]);

        $response->assertSessionHasErrors('teacher_user_id');

        $this->assertSame(
            $teacherB->id,
            $teachingAssignmentB->fresh()->teacher_user_id
        );
    }

    public function test_update_rejects_invalid_data(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teaching_assignments.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $subject = $this->createSubject();
        $teacher = $this->createTeacher();

        $teachingAssignment = TeachingAssignment::create([
            'academic_year_id' => $academicYear->id,
            'semester_id'      => $semester->id,
            'class_group_id'   => $classGroup->id,
            'subject_id'       => $subject->id,
            'teacher_user_id'  => $teacher->id,
            'weekly_hours'     => 3,
            'status'           => 'active',
            'is_active'        => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/teaching-assignments/{$teachingAssignment->id}", [
                'academic_year_id' => null,
                'semester_id'      => null,
                'class_group_id'   => null,
                'subject_id'       => null,
                'teacher_user_id'  => null,
                'weekly_hours'     => null,
            ]);

        $response->assertSessionHasErrors([
            'academic_year_id',
            'semester_id',
            'class_group_id',
            'subject_id',
            'teacher_user_id',
            'weekly_hours',
        ]);
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

    private function createTeacher(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'guru_mata_pelajaran'],
            [
                'display_name' => 'Guru Mata Pelajaran',
                'is_system'    => false,
                'is_active'    => true,
            ]
        );

        $teacher = User::factory()->create([
            'name'   => 'Pak Guru Test',
            'status' => 'active',
        ]);

        $teacher->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()],
        ]);

        return $teacher;
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