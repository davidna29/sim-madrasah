<?php

namespace Tests\Feature\Admin;

use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLevelRoomCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_grade_level_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.view');

        GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/grade-levels');

        $response
            ->assertStatus(200)
            ->assertSee('Tingkat Kelas')
            ->assertSee('Kelas VII');
    }

    public function test_user_can_create_grade_level(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/grade-levels', [
                'code' => 'X',
                'name' => 'Kelas X',
                'level_number' => 10,
                'description' => 'Tingkat kelas MA.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.grade-levels.index'));

        $this->assertDatabaseHas('grade_levels', [
            'code' => 'X',
            'name' => 'Kelas X',
            'level_number' => 10,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_grade_level(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.update');

        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.grade-levels.update', $gradeLevel), [
                'code' => 'VII',
                'name' => 'Kelas VII Revisi',
                'level_number' => 7,
                'description' => 'Revisi.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.grade-levels.index'));

        $this->assertDatabaseHas('grade_levels', [
            'id' => $gradeLevel->id,
            'name' => 'Kelas VII Revisi',
        ]);
    }

    public function test_user_can_view_room_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.view');

        Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/rooms');

        $response
            ->assertStatus(200)
            ->assertSee('Ruangan')
            ->assertSee('Laboratorium Komputer');
    }

    public function test_user_can_create_room(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/rooms', [
                'code' => 'R-AULA',
                'name' => 'Aula Madrasah',
                'room_type' => 'hall',
                'capacity' => 200,
                'location' => 'Gedung Utama',
                'description' => 'Aula kegiatan.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'code' => 'R-AULA',
            'name' => 'Aula Madrasah',
            'room_type' => 'hall',
            'capacity' => 200,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_room(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.update');

        $room = Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.rooms.update', $room), [
                'code' => 'LAB-KOM',
                'name' => 'Laboratorium Komputer Revisi',
                'room_type' => 'laboratory',
                'capacity' => 35,
                'location' => 'Gedung B',
                'description' => 'Revisi.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Laboratorium Komputer Revisi',
            'capacity' => 35,
        ]);
    }

    public function test_user_can_toggle_grade_level_active_status(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.update');

        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.grade-levels.toggle-active', $gradeLevel));

        $response
            ->assertRedirect(route('admin.grade-levels.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('grade_levels', [
            'id' => $gradeLevel->id,
            'is_active' => false,
        ]);

        $this
            ->actingAs($user)
            ->put(route('admin.grade-levels.toggle-active', $gradeLevel));

        $this->assertDatabaseHas('grade_levels', [
            'id' => $gradeLevel->id,
            'is_active' => true,
        ]);
    }

    public function test_user_can_toggle_room_active_status(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.update');

        $room = Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.rooms.toggle-active', $room));

        $response
            ->assertRedirect(route('admin.rooms.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'is_active' => false,
        ]);

        $this
            ->actingAs($user)
            ->put(route('admin.rooms.toggle-active', $room));

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_grade_room_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Grade Room Role',
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
