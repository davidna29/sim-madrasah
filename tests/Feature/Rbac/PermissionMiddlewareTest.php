<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'dashboard.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_user_without_permission_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertForbidden();
    }

    public function test_super_admin_can_access_dashboard_without_explicit_permission(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::create([
            'name' => 'test_dashboard_role',
            'display_name' => 'Test Dashboard Role',
            'is_system' => false,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => $permissionName,
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);
    }
}
