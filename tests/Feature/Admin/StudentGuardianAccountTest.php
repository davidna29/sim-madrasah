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

class StudentGuardianAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_guardian_account_form(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        Role::create([
            'name' => 'orang_tua',
            'display_name' => 'Orang Tua',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.students.guardians.accounts.create', [$student, $guardian]));

        $response
            ->assertStatus(200)
            ->assertSee('Buat Akun Orang Tua/Wali')
            ->assertSee('Wali Ahmad');
    }

    public function test_user_can_create_guardian_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        $role = Role::create([
            'name' => 'orang_tua',
            'display_name' => 'Orang Tua',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.guardians.accounts.store', [$student, $guardian]), [
                'username' => 'wali_test',
                'email' => 'wali.login@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseHas('users', [
            'person_id' => $guardian->person_id,
            'username' => 'wali_test',
            'email' => 'wali.login@test.local',
            'account_type' => 'parent',
            'status' => 'active',
        ]);

        $createdUser = User::query()
            ->where('username', 'wali_test')
            ->firstOrFail();

        $this->assertTrue(
            $createdUser->hasRole('orang_tua')
        );
    }

    public function test_guardian_cannot_have_duplicate_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        User::factory()->create([
            'person_id' => $guardian->person_id,
            'username' => 'existing_wali',
            'email' => 'existing.wali@test.local',
        ]);

        $role = Role::create([
            'name' => 'orang_tua',
            'display_name' => 'Orang Tua',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.guardians.accounts.store', [$student, $guardian]), [
                'username' => 'new_wali',
                'email' => 'new.wali@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseMissing('users', [
            'username' => 'new_wali',
        ]);
    }

    public function test_cannot_create_account_for_guardian_from_different_student_route(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        $otherStudent = $this->createStudent(
            'Siswa Lain',
            'siswa.lain@test.local',
            'SIS-002'
        );

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.students.guardians.accounts.create', [$otherStudent, $guardian]));

        $response->assertNotFound();
    }

    /**
     * @return array{0: Student, 1: StudentGuardian}
     */
    private function createStudentGuardianData(): array
    {
        $student = $this->createStudent(
            'Ahmad Siswa',
            'ahmad.siswa@test.local',
            'SIS-001'
        );

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

    private function createStudent(
        string $fullName,
        string $email,
        string $studentNumber
    ): Student {
        $person = Person::create([
            'full_name' => $fullName,
            'email' => $email,
        ]);

        return Student::create([
            'person_id' => $person->id,
            'student_number' => $studentNumber,
            'nisn' => fake()->unique()->numerify('##########'),
            'registration_number' => 'REG-'.$studentNumber,
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
                'name' => 'test_guardian_account_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Guardian Account Role',
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
