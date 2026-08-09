<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_employee_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'employees.view');

        $person = Person::create([
            'full_name' => 'Guru Test',
            'email' => 'guru@test.local',
        ]);

        Employee::create([
            'person_id' => $person->id,
            'employee_number' => 'EMP-001',
            'employee_type' => 'teacher',
            'position' => 'Guru Mata Pelajaran',
            'is_teacher' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/employees');

        $response
            ->assertStatus(200)
            ->assertSee('Guru dan Pegawai')
            ->assertSee('Guru Test');
    }

    public function test_user_can_create_employee(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'employees.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/employees', [
                'full_name' => 'Guru Baru',
                'national_id_number' => '1234567890123456',
                'birth_place' => 'Lombok Timur',
                'birth_date' => '1990-01-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'guru.baru@test.local',
                'phone' => '08123456789',
                'address' => 'Alamat guru',
                'employee_number' => 'EMP-002',
                'nip' => null,
                'nuptk' => null,
                'employee_type' => 'teacher',
                'employment_status' => 'permanent',
                'position' => 'Guru Mata Pelajaran',
                'join_date' => '2026-07-01',
                'end_date' => null,
                'education_level' => 'S1',
                'major' => 'Pendidikan Matematika',
                'is_teacher' => '1',
                'is_active' => '1',
                'notes' => 'Data awal.',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('people', [
            'full_name' => 'Guru Baru',
            'email' => 'guru.baru@test.local',
        ]);

        $this->assertDatabaseHas('employees', [
            'employee_number' => 'EMP-002',
            'employee_type' => 'teacher',
            'position' => 'Guru Mata Pelajaran',
            'is_teacher' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_employee(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'employees.update');

        $person = Person::create([
            'full_name' => 'Guru Lama',
            'email' => 'guru.lama@test.local',
        ]);

        $employee = Employee::create([
            'person_id' => $person->id,
            'employee_number' => 'EMP-003',
            'employee_type' => 'teacher',
            'position' => 'Guru',
            'is_teacher' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.employees.update', $employee), [
                'full_name' => 'Guru Revisi',
                'national_id_number' => null,
                'birth_place' => null,
                'birth_date' => null,
                'gender' => 'female',
                'religion' => 'Islam',
                'email' => 'guru.revisi@test.local',
                'phone' => '0811111111',
                'address' => 'Alamat revisi',
                'employee_number' => 'EMP-003',
                'nip' => null,
                'nuptk' => null,
                'employee_type' => 'teacher',
                'employment_status' => 'permanent',
                'position' => 'Wali Kelas',
                'join_date' => '2026-07-01',
                'end_date' => null,
                'education_level' => 'S1',
                'major' => 'Pendidikan Bahasa Indonesia',
                'is_teacher' => '1',
                'is_active' => '1',
                'notes' => 'Revisi.',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'full_name' => 'Guru Revisi',
            'email' => 'guru.revisi@test.local',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'position' => 'Wali Kelas',
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_employee_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Employee Role',
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
