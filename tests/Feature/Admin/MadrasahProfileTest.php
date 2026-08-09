<?php

namespace Tests\Feature\Admin;

use App\Models\Madrasah;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MadrasahProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_madrasah_profile_form(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'madrasah.view');

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Test',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/madrasah');

        $response
            ->assertStatus(200)
            ->assertSee('Identitas Madrasah')
            ->assertSee('Madrasah Test');
    }

    public function test_user_without_permission_cannot_view_madrasah_profile_form(): void
    {
        $user = User::factory()->create();

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Test',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/madrasah');

        $response->assertForbidden();
    }

    public function test_user_with_update_permission_can_update_madrasah_profile(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'madrasah.view');
        $this->grantPermissionToUser($user, 'madrasah.update');

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Lama',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put('/admin/madrasah', [
                'name' => 'Madrasah Baru',
                'nsm' => '1234567890',
                'npsn' => '9876543210',
                'email' => 'info@madrasah.test',
                'phone' => '08123456789',
                'address' => 'Jl. Pendidikan No. 1',
                'village' => 'Desa Ilmu',
                'district' => 'Kecamatan Belajar',
                'city' => 'Kabupaten Cerdas',
                'province' => 'Jawa Timur',
                'postal_code' => '12345',
                'timezone' => 'Asia/Jakarta',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.madrasah.edit'));

        $this->assertDatabaseHas('madrasahs', [
            'code' => 'default',
            'name' => 'Madrasah Baru',
            'nsm' => '1234567890',
            'npsn' => '9876543210',
            'email' => 'info@madrasah.test',
            'city' => 'Kabupaten Cerdas',
            'is_active' => true,
        ]);
    }

    public function test_user_without_update_permission_cannot_update_madrasah_profile(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'madrasah.view');

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Lama',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put('/admin/madrasah', [
                'name' => 'Madrasah Baru',
                'timezone' => 'Asia/Jakarta',
            ]);

        $response->assertForbidden();
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_madrasah_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Madrasah Role',
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
