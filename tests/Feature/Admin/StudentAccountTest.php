<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_student_account_form(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'students.account.create');

        $student = $this->createStudent();

        Role::create([
            'name' => 'siswa',
            'display_name' => 'Siswa',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.students.accounts.create', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Buat Akun Siswa')
            ->assertSee('Ahmad Siswa');
    }

    public function test_user_can_create_student_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'students.account.create');

        $student = $this->createStudent();

        $role = Role::create([
            'name' => 'siswa',
            'display_name' => 'Siswa',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.accounts.store', $student), [
                'username' => 'siswa_test',
                'email' => 'siswa.login@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', [
            'person_id' => $student->person_id,
            'username' => 'siswa_test',
            'email' => 'siswa.login@test.local',
            'account_type' => 'student',
            'status' => 'active',
        ]);

        $createdUser = User::query()
            ->where('username', 'siswa_test')
            ->firstOrFail();

        $this->assertTrue(
            $createdUser->hasRole('siswa')
        );
    }

    public function test_student_cannot_have_duplicate_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'students.account.create');

        $student = $this->createStudent();

        User::factory()->create([
            'person_id' => $student->person_id,
            'username' => 'existing_student',
            'email' => 'existing.student@test.local',
        ]);

        $role = Role::create([
            'name' => 'siswa',
            'display_name' => 'Siswa',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.accounts.store', $student), [
                'username' => 'new_student',
                'email' => 'new.student@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('users', [
            'username' => 'new_student',
        ]);
    }

    private function createStudent(): Student
    {
        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.siswa@test.local',
        ]);

        return Student::create([
            'person_id' => $person->id,
            'student_number' => 'SIS-100',
            'nisn' => '1000000100',
            'registration_number' => 'REG-100',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_account_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Account Role',
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
