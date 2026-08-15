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

    // Menguji bahwa halaman create riwayat kelas menampilkan semester aktif dan terpilih secara default
    public function test_user_can_see_active_semester_default_on_create_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester] = $this->createStudentClassData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.class-histories.create', $student));

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

    public function test_user_can_view_edit_class_history_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.update');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $history = StudentClassHistory::create([
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
            ->get(route('admin.students.class-histories.edit', [$student, $history]));

        $response
            ->assertStatus(200)
            ->assertSee('Edit Riwayat Kelas')
            ->assertSeeInOrder([
                'value="'.$semester->id.'"',
                'selected',
            ], false);
    }

    public function test_user_can_update_student_class_history(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.update');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $history = StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
            'notes' => 'Catatan awal.',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.students.class-histories.update', [$student, $history]), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'completed',
                'start_date' => '2026-07-01',
                'end_date' => '2026-12-01',
                'is_current' => '0',
                'notes' => 'Catatan setelah koreksi.',
            ]);

        $response->assertRedirect(
            route('admin.students.class-histories.index', $student)
        );

        $this->assertDatabaseHas('student_class_histories', [
            'id' => $history->id,
            'status' => 'completed',
            'is_current' => false,
            'notes' => 'Catatan setelah koreksi.',
        ]);
    }

    // Menguji bahwa mengedit histori menjadi "kelas saat ini" menonaktifkan histori aktif lain milik siswa yang sama
    public function test_updating_history_to_current_deactivates_other_current_history(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.update');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $currentHistory = StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $otherSemester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-genap',
            'name' => 'Semester Genap 2026/2027',
            'semester_type' => 'genap',
            'start_date' => '2027-01-01',
            'end_date' => '2027-06-30',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $editedHistory = StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $otherSemester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2027-01-01',
            'is_current' => false,
        ]);

        $this
            ->actingAs($user)
            ->put(route('admin.students.class-histories.update', [$student, $editedHistory]), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $otherSemester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2027-01-01',
                'is_current' => '1',
            ]);

        $this->assertDatabaseHas('student_class_histories', [
            'id' => $editedHistory->id,
            'is_current' => true,
        ]);

        $this->assertDatabaseHas('student_class_histories', [
            'id' => $currentHistory->id,
            'is_current' => false,
        ]);
    }

    // Menguji bahwa menyimpan ulang histori dengan semester yang sama TIDAK menabrak validasi unique milik dirinya sendiri
    public function test_updating_history_with_same_semester_does_not_trigger_unique_error(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.update');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $history = StudentClassHistory::create([
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
            ->put(route('admin.students.class-histories.update', [$student, $history]), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2026-08-01',
                'is_current' => '1',
                'notes' => 'Tanggal mulai dikoreksi.',
            ]);

        $response->assertSessionDoesntHaveErrors('semester_id');

        $this->assertDatabaseHas('student_class_histories', [
            'id' => $history->id,
            'start_date' => '2026-08-01 00:00:00',
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

    // Menguji bahwa siswa TIDAK BISA menambahkan histori kelas dengan semester dari tahun akademik yang berbeda
    public function test_student_class_history_rejects_semester_from_different_academic_year(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $otherAcademicYear = AcademicYear::create([
            'code' => '2027-2028-history',
            'name' => '2027/2028 History',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.class-histories.store', $student), [
                'academic_year_id' => $otherAcademicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2027-08-13',
                'is_current' => '1',
            ]);

        $response->assertSessionHasErrors('semester_id');

        $this->assertDatabaseCount('student_class_histories', 0);
    }

    // Menguji bahwa siswa TIDAK BISA menambahkan histori kelas dengan tanggal mulai di luar rentang semester
    public function test_student_class_history_rejects_start_date_outside_semester_range(): void
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
                'start_date' => '2027-08-13',
                'is_current' => '1',
            ]);

        $response->assertSessionHasErrors('start_date');

        $this->assertDatabaseCount('student_class_histories', 0);
    }
}
