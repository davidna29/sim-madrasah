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

class StudentClassHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_class_history_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.view');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.class-histories.index', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Riwayat Kelas Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee('Kelas VII A');
    }

    public function test_user_can_create_student_class_history(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.class-histories.store', $student), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2026-07-01',
                'end_date' => null,
                'is_current' => '1',
                'notes' => 'Penempatan awal.',
            ]);

        $response->assertRedirect(
            route('admin.students.class-histories.index', $student)
        );

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'is_current' => true,
        ]);
    }

    public function test_student_cannot_have_duplicate_class_history_in_same_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.class-histories.store', $student), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2026-07-01',
                'is_current' => '1',
            ]);

        // Pastikan validasi gagal karena siswa sudah memiliki histori kelas aktif pada semester ini
        $response->assertSessionHasErrors([
            'semester_id' => 'Siswa sudah memiliki histori kelas pada semester ini.',
        ]);

        $this->assertDatabaseCount('student_class_histories', 1);

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'is_current' => true,
        ]);
    }

    // Menguji bahwa siswa TIDAK BISA menambahkan histori kelas non-aktif di semester yang sama
    public function test_user_cannot_add_non_current_class_history_in_same_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $secondClassGroup = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $classGroup->grade_level_id,
            'room_id' => $classGroup->room_id,
            'code' => 'VII-B',
            'name' => 'Kelas VII B',
            'parallel_name' => 'B',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.class-histories.store', $student), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $secondClassGroup->id,
                'status' => 'active',
                'start_date' => '2026-08-01',
                'is_current' => '0',
                'notes' => 'Catatan riwayat nonaktif.',
            ]);

        $response->assertSessionHasErrors('semester_id');

        $this->assertDatabaseCount('student_class_histories', 1);

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $student->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'is_current' => true,
        ]);
    }

    /**
     * @return array{0: Student, 1: AcademicYear, 2: Semester, 3: ClassGroup}
     */
    private function createStudentClassData(): array
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

        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad@test.local',
        ]);

        $student = Student::create([
            'person_id' => $person->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        return [
            $student,
            $academicYear,
            $semester,
            $classGroup,
        ];
    }

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
}
