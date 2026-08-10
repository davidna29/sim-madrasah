<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_guardian_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_guardians.view');

        [$student, $guardian] = $this->createStudentGuardianData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.guardians.index', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Orang Tua/Wali Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee($guardian->person->full_name);
    }

    public function test_user_can_create_student_guardian(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_guardians.create');

        $student = $this->createStudent();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.guardians.store', $student), [
                'full_name' => 'Wali Ahmad',
                'national_id_number' => '1234567890123456',
                'birth_place' => 'Lombok Timur',
                'birth_date' => '1980-01-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'wali.ahmad@test.local',
                'phone' => '08123456789',
                'address' => 'Alamat wali',
                'relationship' => 'father',
                'occupation' => 'Petani',
                'education_level' => 'SMA',
                'income_range' => '1-3 juta',
                'is_primary_contact' => '1',
                'is_emergency_contact' => '1',
                'is_financial_responsible' => '1',
                'is_active' => '1',
                'notes' => 'Data awal.',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseHas('people', [
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
        ]);

        $this->assertDatabaseHas('student_guardians', [
            'student_id' => $student->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_student_guardian(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_guardians.update');

        [$student, $guardian] = $this->createStudentGuardianData();

        $response = $this
            ->actingAs($user)
            ->put(route('admin.students.guardians.update', [$student, $guardian]), [
                'full_name' => 'Wali Ahmad Revisi',
                'national_id_number' => null,
                'birth_place' => null,
                'birth_date' => null,
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'wali.revisi@test.local',
                'phone' => '0811111111',
                'address' => 'Alamat revisi',
                'relationship' => 'guardian',
                'occupation' => 'Wiraswasta',
                'education_level' => 'S1',
                'income_range' => '3-5 juta',
                'is_primary_contact' => '1',
                'is_emergency_contact' => '1',
                'is_financial_responsible' => '1',
                'is_active' => '1',
                'notes' => 'Revisi.',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseHas('people', [
            'id' => $guardian->person_id,
            'full_name' => 'Wali Ahmad Revisi',
            'email' => 'wali.revisi@test.local',
        ]);

        $this->assertDatabaseHas('student_guardians', [
            'id' => $guardian->id,
            'relationship' => 'guardian',
            'occupation' => 'Wiraswasta',
        ]);
    }

    private function createStudentGuardianData(): array
    {
        $student = $this->createStudent();

        $guardianPerson = Person::create([
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
        ]);

        $guardian = StudentGuardian::create([
            'student_id' => $student->id,
            'person_id' => $guardianPerson->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'education_level' => 'SMA',
            'income_range' => '1-3 juta',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);

        return [$student, $guardian];
    }

    private function createStudent(): Student
    {
        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.siswa@test.local',
        ]);

        return Student::create([
            'person_id' => $person->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
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
                'name' => 'test_student_guardian_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Guardian Role',
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
