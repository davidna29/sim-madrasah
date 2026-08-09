<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_class_group_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.view');

        $academicYear = $this->createAcademicYear();
        $gradeLevel = $this->createGradeLevel();
        $room = $this->createRoom();

        ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/class-groups');

        $response
            ->assertStatus(200)
            ->assertSee('Rombongan Belajar')
            ->assertSee('Kelas VII A');
    }

    public function test_user_can_create_class_group(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.create');

        $academicYear = $this->createAcademicYear();
        $gradeLevel = $this->createGradeLevel();
        $room = $this->createRoom();

        $response = $this
            ->actingAs($user)
            ->post('/admin/class-groups', [
                'academic_year_id' => $academicYear->id,
                'grade_level_id' => $gradeLevel->id,
                'room_id' => $room->id,
                'homeroom_teacher_user_id' => null,
                'code' => 'VII-A',
                'name' => 'Kelas VII A',
                'parallel_name' => 'A',
                'capacity' => 32,
                'status' => 'active',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.class-groups.index'));

        $this->assertDatabaseHas('class_groups', [
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_class_group(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.update');

        $academicYear = $this->createAcademicYear();
        $gradeLevel = $this->createGradeLevel();
        $room = $this->createRoom();

        $classGroup = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.class-groups.update', $classGroup), [
                'academic_year_id' => $academicYear->id,
                'grade_level_id' => $gradeLevel->id,
                'room_id' => $room->id,
                'homeroom_teacher_user_id' => null,
                'code' => 'VII-A',
                'name' => 'Kelas VII A Revisi',
                'parallel_name' => 'A',
                'capacity' => 34,
                'status' => 'active',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.class-groups.index'));

        $this->assertDatabaseHas('class_groups', [
            'id' => $classGroup->id,
            'name' => 'Kelas VII A Revisi',
            'capacity' => 34,
        ]);
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

    private function createGradeLevel(): GradeLevel
    {
        return GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);
    }

    private function createRoom(): Room
    {
        return Room::create([
            'code' => 'R-VII-A',
            'name' => 'Ruang VII A',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_class_group_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Class Group Role',
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
