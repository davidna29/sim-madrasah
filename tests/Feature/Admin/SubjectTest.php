<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_subject_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'subjects.view');

        Subject::create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'subject_group' => 'general',
            'is_local_content' => false,
            'is_religious' => false,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/subjects');

        $response
            ->assertStatus(200)
            ->assertSee('Mata Pelajaran')
            ->assertSee('Matematika');
    }

    public function test_user_can_create_subject(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'subjects.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/subjects', [
                'code' => 'IPA',
                'name' => 'Ilmu Pengetahuan Alam',
                'subject_group' => 'general',
                'description' => 'Mata pelajaran sains.',
                'is_local_content' => '0',
                'is_religious' => '0',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'code' => 'IPA',
            'name' => 'Ilmu Pengetahuan Alam',
            'subject_group' => 'general',
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_subject(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'subjects.update');

        $subject = Subject::create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'subject_group' => 'general',
            'is_local_content' => false,
            'is_religious' => false,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.subjects.update', $subject), [
                'code' => 'MTK',
                'name' => 'Matematika Revisi',
                'subject_group' => 'general',
                'description' => 'Revisi.',
                'is_local_content' => '0',
                'is_religious' => '0',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Matematika Revisi',
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_subject_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Subject Role',
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
