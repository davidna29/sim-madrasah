<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_role(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'description' => 'Mengelola pembelajaran dan nilai.',
            'is_system' => true,
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $this->assertTrue(
            $user->hasRole('guru_mata_pelajaran')
        );
    }

    public function test_user_can_have_permission_through_role(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'scores.input',
            'module' => 'scores',
            'action' => 'input',
            'display_name' => 'Input Nilai',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $this->assertTrue(
            $user->hasPermission('scores.input')
        );
    }

    public function test_direct_deny_permission_overrides_role_permission(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'payments.correct',
            'module' => 'payments',
            'action' => 'correct',
            'display_name' => 'Koreksi Pembayaran',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $user->directPermissions()->attach($permission->id, [
            'permission_mode' => 'deny',
            'assigned_at' => now(),
        ]);

        $this->assertFalse(
            $user->hasPermission('payments.correct')
        );
    }

    public function test_expired_role_does_not_grant_permission(): void
    {
        $user = User::factory()->create();

        $role = Role::create([
            'name' => 'editor_berita',
            'display_name' => 'Editor Berita',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'news.publish',
            'module' => 'news',
            'action' => 'publish',
            'display_name' => 'Publikasi Berita',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse(
            $user->hasPermission('news.publish')
        );
    }
}
