<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_super_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Administrator SIM Madrasah',
        ]);

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

        $response
            ->assertStatus(200)
            ->assertSee('Dashboard Super Admin')
            ->assertSee('Pengguna dan Hak Akses');
    }

    public function test_bendahara_can_view_bendahara_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Bendahara Madrasah',
        ]);

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Dashboard Bendahara')
            ->assertSee('Tagihan Siswa')
            ->assertSee('Rekap SPP');
    }

    public function test_user_without_dashboard_permission_cannot_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertForbidden();
    }
}
