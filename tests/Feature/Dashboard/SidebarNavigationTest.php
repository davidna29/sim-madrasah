<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_roles_permission_can_see_role_menu(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'roles.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Role')
            ->assertSee('Administrasi Sistem');
    }

    public function test_user_without_roles_permission_cannot_see_role_menu(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'dashboard.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertDontSee('Administrasi Sistem')
            ->assertDontSee(route('admin.roles.index', [], false))
            ->assertDontSee(route('admin.permissions.index', [], false));
    }

    public function test_user_with_permissions_permission_can_see_permission_menu(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'permissions.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Permission')
            ->assertSee('Administrasi Sistem');
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::create([
            'name' => 'test_sidebar_role_'.str_replace('.', '_', $permissionName),
            'display_name' => 'Test Sidebar Role',
            'is_system' => false,
            'is_active' => true,
        ]);

        // Buat & pasangkan permission 'dashboard.view' agar tidak kena HTTP 403
        $dashboardPermission = Permission::firstOrCreate(
            ['name' => 'dashboard.view'],
            [
                'module' => 'dashboard',
                'action' => 'view',
                'display_name' => 'Dashboard View',
                'is_active' => true,
            ]
        );
        $role->permissions()->attach($dashboardPermission->id);

        // Pasangkan permission spesifik jika bukan 'dashboard.view'
        if ($permissionName !== 'dashboard.view') {
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

            $role->permissions()->attach($permission->id);
        }

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);
    }
}
