<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_roles_view_permission_can_open_role_detail_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'roles.view');

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'description' => 'Mengelola pembayaran siswa.',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'payments.create',
            'module' => 'payments',
            'action' => 'create',
            'display_name' => 'Input Pembayaran',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.roles.show', $role));

        $response
            ->assertStatus(200)
            ->assertSee('Bendahara')
            ->assertSee('payments.create')
            ->assertSee('Input Pembayaran');
    }

    public function test_user_without_roles_view_permission_cannot_open_role_detail_page(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'guru_bk',
            'display_name' => 'Guru BK',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.roles.show', $role));

        $response->assertForbidden();
    }

    public function test_role_detail_page_returns_not_found_for_unknown_role(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'roles.view');

        $response = $this
            ->actingAs($user)
            ->get('/admin/roles/999999');

        $response->assertNotFound();
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::create([
            'name' => 'test_role_viewer',
            'display_name' => 'Test Role Viewer',
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
