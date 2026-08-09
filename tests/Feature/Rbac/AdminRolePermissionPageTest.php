<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRolePermissionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_roles_view_permission_can_open_roles_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'roles.view');

        Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/roles');

        $response
            ->assertStatus(200)
            ->assertSee('Guru Mata Pelajaran');
    }

    public function test_user_without_roles_view_permission_cannot_open_roles_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/roles');

        $response->assertForbidden();
    }

    public function test_user_with_permissions_view_permission_can_open_permissions_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'permissions.view');

        Permission::create([
            'name' => 'users.view',
            'module' => 'users',
            'action' => 'view',
            'display_name' => 'Melihat Pengguna',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/permissions');

        $response
            ->assertStatus(200)
            ->assertSee('Melihat Pengguna');
    }

    public function test_user_without_permissions_view_permission_cannot_open_permissions_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/permissions');

        $response->assertForbidden();
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::create([
            'name' => 'test_admin_role',
            'display_name' => 'Test Admin Role',
            'is_system' => false,
            'is_active' => true,
        ]);

        [$module, $action] = explode('.', $permissionName);

        $permission = Permission::create([
            'name' => $permissionName,
            'module' => $module,
            'action' => $action,
            'display_name' => $permissionName,
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);
    }
}
