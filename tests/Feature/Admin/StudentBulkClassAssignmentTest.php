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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentBulkClassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_bulk_class_assignment_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.bulk-class-assignment.create'));

        $response
            ->assertStatus(200)
            ->assertSee('Bulk Assignment Siswa ke Rombel')
            ->assertSee('Pilih Siswa');
    }

    // Menguji bahwa halaman bulk assignment menampilkan semester aktif dan terpilih secara default
    public function test_user_can_see_active_semester_default_on_bulk_assignment_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$academicYear, $semester] = $this->createBulkAssignmentData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.bulk-class-assignment.create'));

        $response
            ->assertStatus(200)
            ->assertSee('Semester aktif sistem:')
            ->assertSee($semester->name)
            ->assertSeeInOrder([
                'value="'.$academicYear->id.'"',
                'selected',
            ], false)
            ->assertSeeInOrder([
                'value="'.$semester->id.'"',
                'selected',
            ], false);
    }

    // Menguji bahwa tanggal mulai pada form bulk assignment terisi otomatis sesuai tanggal mulai semester aktif
    public function test_user_can_see_active_semester_start_date_in_bulk_assignment_form(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        $academicYear = AcademicYear::create([
            'code' => '2027-2028-active',
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2027-2028-genap',
            'name' => 'Semester Genap 2027/2028',
            'semester_type' => 'genap',
            'start_date' => '2026-08-13',
            'end_date' => '2027-01-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.bulk-class-assignment.create'));

        $response
            ->assertStatus(200)
            ->assertSee('Semester aktif sistem')
            ->assertSee('value="2026-08-13"', false);
    }

    public function test_user_can_bulk_assign_students_to_class_group(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$academicYear, $semester, $classGroup, $studentA, $studentB] = $this->createBulkAssignmentData();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.bulk-class-assignment.store'), [
                'student_ids' => [
                    $studentA->id,
                    $studentB->id,
                ],
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'start_date' => '2026-07-01',
                'notes' => 'Assignment massal.',
            ]);

        $response
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('student_class_histories', 2);

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $studentA->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $studentB->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'is_current' => true,
        ]);
    }

    public function test_bulk_assignment_rejects_student_that_already_has_history_in_same_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$academicYear, $semester, $classGroup, $studentA, $studentB] = $this->createBulkAssignmentData();

        StudentClassHistory::create([
            'student_id' => $studentA->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.bulk-class-assignment.store'), [
                'student_ids' => [
                    $studentA->id,
                    $studentB->id,
                ],
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'start_date' => '2026-07-01',
                'notes' => 'Assignment massal.',
            ]);

        $response->assertSessionHasErrors('student_ids');

        $this->assertDatabaseCount('student_class_histories', 1);

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $studentA->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'is_current' => true,
        ]);

        $this->assertDatabaseMissing('student_class_histories', [
            'student_id' => $studentB->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
        ]);
    }

    /**
     * @return array{0: AcademicYear, 1: Semester, 2: ClassGroup, 3: Student, 4: Student}
     */
    private function createBulkAssignmentData(): array
    {
        $academicYear = AcademicYear::create([
            'code' => '2026-2027-bulk',
            'name' => '2026/2027 Bulk',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-bulk-ganjil',
            'name' => 'Semester Ganjil 2026/2027 Bulk',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $gradeLevel = GradeLevel::create([
            'code' => 'VII-BULK',
            'name' => 'Kelas VII Bulk',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $room = Room::create([
            'code' => 'R-BULK',
            'name' => 'Ruang Bulk',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);

        $classGroup = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-BULK-A',
            'name' => 'Kelas VII Bulk A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $personA = Person::create([
            'full_name' => 'Ahmad Bulk',
            'email' => 'ahmad.bulk@test.local',
        ]);

        $studentA = Student::create([
            'person_id' => $personA->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'BULK-001',
            'nisn' => '5000000001',
            'registration_number' => 'REG-BULK-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $personB = Person::create([
            'full_name' => 'Siti Bulk',
            'email' => 'siti.bulk@test.local',
        ]);

        $studentB = Student::create([
            'person_id' => $personB->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'BULK-002',
            'nisn' => '5000000002',
            'registration_number' => 'REG-BULK-002',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        return [
            $academicYear,
            $semester,
            $classGroup,
            $studentA,
            $studentB,
        ];
    }

    // Grants a specific permission to a user
    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_class_history_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Class History Role',
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

    public function test_bulk_assignment_rejects_semester_from_different_academic_year(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$academicYear, $semester, $classGroup, $studentA, $studentB] = $this->createBulkAssignmentData();

        $otherAcademicYear = AcademicYear::create([
            'code' => '2027-2028-bulk',
            'name' => '2027/2028 Bulk',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.bulk-class-assignment.store'), [
                'student_ids' => [
                    $studentA->id,
                    $studentB->id,
                ],
                'academic_year_id' => $otherAcademicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'start_date' => '2027-08-13',
                'notes' => 'Assignment salah tahun.',
            ]);

        $response->assertSessionHasErrors('semester_id');

        $this->assertDatabaseCount('student_class_histories', 0);
    }

    public function test_bulk_assignment_rejects_start_date_outside_semester_range(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$academicYear, $semester, $classGroup, $studentA, $studentB] = $this->createBulkAssignmentData();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.bulk-class-assignment.store'), [
                'student_ids' => [
                    $studentA->id,
                    $studentB->id,
                ],
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'start_date' => '2027-08-13',
                'notes' => 'Assignment tanggal salah.',
            ]);

        $response->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('student_class_histories', 0);
    }
}