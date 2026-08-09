<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNextDevelopmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_see_next_development_section(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
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
            ->assertSee('Tahap Pengembangan Berikutnya')
            ->assertSee('Identitas Madrasah')
            ->assertSee('Tahun Ajaran dan Semester')
            ->assertSee('Data Guru dan Pegawai')
            ->assertSee('Data Siswa')
            ->assertSee('Kelas dan Ruangan')
            ->assertSee('Mata Pelajaran');
    }

    public function test_non_super_admin_cannot_see_next_development_section(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
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
            ->assertDontSee('Tahap Pengembangan Berikutnya')
            ->assertDontSee('Identitas Madrasah')
            ->assertDontSee('Tahun Ajaran dan Semester');
    }
}
