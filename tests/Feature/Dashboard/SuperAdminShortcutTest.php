<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminShortcutTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_see_rbac_shortcuts(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $user->roles()->attach($superAdminRole->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Shortcut Administrasi Sistem')
            ->assertSee('Kelola Role')
            ->assertSee('Kelola Permission')
            ->assertSee('Detail Role Super Admin');
    }

    public function test_non_super_admin_cannot_see_rbac_shortcuts(): void
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
            ->assertDontSee('Shortcut Administrasi Sistem')
            ->assertDontSee('Kelola Role')
            ->assertDontSee('Kelola Permission')
            ->assertDontSee('Detail Role Super Admin');
    }
}
