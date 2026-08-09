<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_admin_module_navigation(): void
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
            ->assertSee('Navigasi Modul')
            ->assertSee('Pengguna dan Hak Akses')
            ->assertSee('Manajemen Permission')
            ->assertSee('Keamanan Akun');
    }

    public function test_bendahara_sees_bendahara_module_navigation(): void
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
            ->assertSee('Input Pembayaran')
            ->assertSee('Rekap SPP');
    }
}
