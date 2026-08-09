<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionDetailPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_open_permission_detail()
    {

        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'guru',
            'display_name' => 'Guru',
            'is_active' => true,
            'is_system' => true,
        ]);

        $viewPermission = Permission::create([
            'name' => 'permissions.view',
            'module' => 'permissions',
            'action' => 'view',
            'display_name' => 'Melihat Permission',
            'is_active' => true,
        ]);

        $targetPermission = Permission::create([
            'name' => 'students.view',
            'module' => 'students',
            'action' => 'view',
            'display_name' => 'Melihat Data Siswa',
            'is_active' => true,
        ]);

        $role->permissions()->attach($targetPermission);

        $user->roles()->attach($role);

        $role->permissions()->attach($viewPermission);

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'admin.permissions.show',
                    $targetPermission
                )
            );

        $response
            ->assertStatus(200)
            ->assertSee('students.view')
            ->assertSee('Guru');

    }

    public function test_user_without_permission_cannot_open_permission_detail()
    {

        $user = User::factory()->create();

        $permission = Permission::create([
            'name' => 'students.view',
            'module' => 'students',
            'action' => 'view',
            'display_name' => 'Melihat Siswa',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'admin.permissions.show',
                    $permission
                )
            );

        $response->assertForbidden();

    }
}
