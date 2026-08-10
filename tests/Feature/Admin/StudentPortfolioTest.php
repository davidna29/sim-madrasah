<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Room;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_portfolio(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_portfolios.view');

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.show', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Portofolio Digital Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee('Kelas VII A')
            ->assertSee('Wali Ahmad')
            ->assertSee('Modul Portofolio Berikutnya');
    }

    // Test QR Code Portofolio
    public function test_student_portfolio_shows_qr_code(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_portfolios.view');

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.show', $student));

        $response
            ->assertStatus(200)
            ->assertSee('QR Code Portofolio')
            ->assertSee(route('admin.students.portfolio.show', $student))
            ->assertSee('<svg', false);
    }

    public function test_user_without_permission_cannot_view_student_portfolio(): void
    {
        $user = User::factory()->create();

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.show', $student));

        $response->assertForbidden();
    }

    // Test user can print student portfolio card PDF
    public function test_user_can_print_student_portfolio_card_pdf(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_portfolios.print');

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.card', $student));

        $response->assertStatus(200);

        $this->assertStringContainsString(
            'application/pdf',
            (string) $response->headers->get('content-type')
        );

        $this->assertStringStartsWith(
            '%PDF',
            $response->getContent()
        );
    }

    // Test user without permission cannot print student portfolio card PDF
    public function test_user_without_permission_cannot_print_student_portfolio_card_pdf(): void
    {
        $user = User::factory()->create();

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.card', $student));

        $response->assertForbidden();
    }

    /**
     * @return array{0: Student}
     */
    private function createPortfolioData(): array
    {
        $academicYear = AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-ganjil',
            'name' => 'Semester Ganjil 2026/2027',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $room = Room::create([
            'code' => 'R-VII-A',
            'name' => 'Ruang VII A',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);

        $classGroup = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $studentPerson = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.siswa@test.local',
        ]);

        $student = Student::create([
            'person_id' => $studentPerson->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $guardianPerson = Person::create([
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
            'phone' => '08123456789',
        ]);

        StudentGuardian::create([
            'student_id' => $student->id,
            'person_id' => $guardianPerson->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);

        return [$student];
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_portfolio_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Portfolio Role',
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
