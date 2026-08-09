<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_permission_to_role()
    {

        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_active' => true,
            'is_system' => false,
        ]);

        $assignPermission = Permission::create([
            'name' => 'roles.permission.assign',
            'module' => 'roles',
            'action' => 'permission_assign',
            'display_name' => 'Assign Permission',
            'is_active' => true,
        ]);

        $role->permissions()->attach(
            $assignPermission
        );

        $user->roles()->attach($role);

        $paymentPermission = Permission::create([
            'name' => 'payments.view',
            'module' => 'payments',
            'action' => 'view',
            'display_name' => 'Lihat Pembayaran',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(
                route(
                    'admin.roles.permissions.update',
                    $role
                ),
                [
                    'permissions' => [
                        $paymentPermission->id,
                    ],
                ]
            );

        $response->assertRedirect();

        $this->assertTrue(
            $role
                ->fresh()
                ->permissions
                ->contains(
                    $paymentPermission
                )
        );

    }
}
