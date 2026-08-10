<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_employee_account_form(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'employees.account.create');

        $employee = $this->createEmployee();

        Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.employees.accounts.create', $employee));

        $response
            ->assertStatus(200)
            ->assertSee('Buat Akun Guru/Pegawai')
            ->assertSee('Guru Test');
    }

    public function test_user_can_create_employee_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'employees.account.create');

        $employee = $this->createEmployee();

        $role = Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.employees.accounts.store', $employee), [
                'username' => 'guru_test',
                'email' => 'guru.login@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('users', [
            'person_id' => $employee->person_id,
            'username' => 'guru_test',
            'email' => 'guru.login@test.local',
            'account_type' => 'internal',
            'status' => 'active',
        ]);

        $createdUser = User::query()
            ->where('username', 'guru_test')
            ->firstOrFail();

        $this->assertTrue(
            $createdUser->hasRole('guru_mata_pelajaran')
        );
    }

    public function test_employee_cannot_have_duplicate_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'employees.account.create');

        $employee = $this->createEmployee();

        User::factory()->create([
            'person_id' => $employee->person_id,
            'username' => 'existing.user',
            'email' => 'existing@test.local',
        ]);

        $role = Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.employees.accounts.store', $employee), [
                'username' => 'new.user',
                'email' => 'new@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseMissing('users', [
            'username' => 'new.user',
        ]);
    }

    private function createEmployee(): Employee
    {
        $person = Person::create([
            'full_name' => 'Guru Test',
            'email' => 'guru@test.local',
        ]);

        return Employee::create([
            'person_id' => $person->id,
            'employee_number' => 'EMP-100',
            'employee_type' => 'teacher',
            'position' => 'Guru Mata Pelajaran',
            'is_teacher' => true,
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_employee_account_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Employee Account Role',
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
