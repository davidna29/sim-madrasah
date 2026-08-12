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

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.view');

        $academicYear = $this->createAcademicYear();

        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad@test.local',
        ]);

        Student::create([
            'person_id' => $person->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/students');

        $response
            ->assertStatus(200)
            ->assertSee('Data Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee('SIS-001');
    }

    public function test_user_can_create_student(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.create');

        $academicYear = $this->createAcademicYear();

        $response = $this
            ->actingAs($user)
            ->post('/admin/students', [
                'full_name' => 'Siti Siswa',
                'national_id_number' => '1234567890123456',
                'birth_place' => 'Lombok Timur',
                'birth_date' => '2012-01-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'email' => 'siti@test.local',
                'phone' => '08123456789',
                'address' => 'Alamat siswa',
                'admission_academic_year_id' => $academicYear->id,
                'student_number' => 'SIS-002',
                'nisn' => '1000000002',
                'registration_number' => 'REG-002',
                'admission_date' => '2026-07-01',
                'graduation_date' => null,
                'status' => 'active',
                'previous_school' => 'SD Test',
                'notes' => 'Data awal.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('people', [
            'full_name' => 'Siti Siswa',
            'email' => 'siti@test.local',
        ]);

        $this->assertDatabaseHas('students', [
            'student_number' => 'SIS-002',
            'nisn' => '1000000002',
            'registration_number' => 'REG-002',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_student(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.update');

        $academicYear = $this->createAcademicYear();

        $person = Person::create([
            'full_name' => 'Siswa Lama',
            'email' => 'lama@test.local',
        ]);

        $student = Student::create([
            'person_id' => $person->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-003',
            'nisn' => '1000000003',
            'registration_number' => 'REG-003',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.students.update', $student), [
                'full_name' => 'Siswa Revisi',
                'national_id_number' => null,
                'birth_place' => null,
                'birth_date' => null,
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'revisi@test.local',
                'phone' => '0811111111',
                'address' => 'Alamat revisi',
                'admission_academic_year_id' => $academicYear->id,
                'student_number' => 'SIS-003',
                'nisn' => '1000000003',
                'registration_number' => 'REG-003',
                'admission_date' => '2026-07-01',
                'graduation_date' => null,
                'status' => 'active',
                'previous_school' => 'SD Revisi',
                'notes' => 'Revisi.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'full_name' => 'Siswa Revisi',
            'email' => 'revisi@test.local',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'previous_school' => 'SD Revisi',
        ]);
    }

    private function createAcademicYear(): AcademicYear
    {
        return AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Role',
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

    // Test Filter Siswa Berdasarkan Rombongan Belajar
    public function test_user_can_filter_students_by_current_class_group(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.view');

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

        $roomA = Room::create([
            'code' => 'R-VII-A',
            'name' => 'Ruang VII A',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);

        $roomB = Room::create([
            'code' => 'R-VII-B',
            'name' => 'Ruang VII B',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);

        $classGroupA = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $roomA->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $classGroupB = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $roomB->id,
            'code' => 'VII-B',
            'name' => 'Kelas VII B',
            'parallel_name' => 'B',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $personA = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad@test.local',
        ]);

        $studentA = Student::create([
            'person_id' => $personA->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $personB = Person::create([
            'full_name' => 'Siti Siswa',
            'email' => 'siti@test.local',
        ]);

        $studentB = Student::create([
            'person_id' => $personB->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-002',
            'nisn' => '1000000002',
            'registration_number' => 'REG-002',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        StudentClassHistory::create([
            'student_id' => $studentA->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroupA->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        StudentClassHistory::create([
            'student_id' => $studentB->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroupB->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/students?class_group_id='.$classGroupA->id);

        $response
            ->assertStatus(200)
            ->assertSee('Ahmad Siswa')
            ->assertSee('Kelas VII A')
            ->assertDontSee('Siti Siswa');
    }

    // Test Pencarian Siswa Berdasarkan Nama, NIS, dan NISN
    public function test_user_can_search_students_by_name_nis_and_nisn(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.view');

        $academicYear = $this->createAcademicYear();

        $personA = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.search@test.local',
        ]);

        Student::create([
            'person_id' => $personA->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $personB = Person::create([
            'full_name' => 'Siti Siswa',
            'email' => 'siti.search@test.local',
        ]);

        Student::create([
            'person_id' => $personB->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-002',
            'nisn' => '1000000002',
            'registration_number' => 'REG-002',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $responseByName = $this
            ->actingAs($user)
            ->get('/admin/students?q=Ahmad');

        $responseByName
            ->assertStatus(200)
            ->assertSee('Ahmad Siswa')
            ->assertDontSee('Siti Siswa');

        $responseByNis = $this
            ->actingAs($user)
            ->get('/admin/students?q=SIS-002');

        $responseByNis
            ->assertStatus(200)
            ->assertSee('Siti Siswa')
            ->assertDontSee('Ahmad Siswa');

        $responseByNisn = $this
            ->actingAs($user)
            ->get('/admin/students?q=1000000001');

        $responseByNisn
            ->assertStatus(200)
            ->assertSee('Ahmad Siswa')
            ->assertDontSee('Siti Siswa');
    }

    // Test Filter Siswa Berdasarkan Status Siswa (Aktif, Lulus, Keluar, Dikeluarkan)
    public function test_user_can_filter_students_by_status(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.view');

        $academicYear = $this->createAcademicYear();

        $activePerson = Person::create([
            'full_name' => 'Ahmad Aktif',
            'email' => 'ahmad.active@test.local',
        ]);

        Student::create([
            'person_id' => $activePerson->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-AKTIF',
            'nisn' => '2000000001',
            'registration_number' => 'REG-AKTIF',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $graduatedPerson = Person::create([
            'full_name' => 'Siti Lulus',
            'email' => 'siti.graduated@test.local',
        ]);

        Student::create([
            'person_id' => $graduatedPerson->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-LULUS',
            'nisn' => '2000000002',
            'registration_number' => 'REG-LULUS',
            'admission_date' => '2026-07-01',
            'status' => 'graduated',
            'is_active' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/students?status=graduated');

        $response
            ->assertStatus(200)
            ->assertSee('Siti Lulus')
            ->assertDontSee('Ahmad Aktif');
    }

    // Test Filter Siswa Berdasarkan Tahun Ajaran Masuk
    public function test_user_can_filter_students_by_admission_academic_year(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.view');

        $academicYear2026 = AcademicYear::create([
            'code' => '2026-2027-filter',
            'name' => '2026/2027 Filter',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $academicYear2025 = AcademicYear::create([
            'code' => '2025-2026-filter',
            'name' => '2025/2026 Filter',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'status' => 'inactive',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $personA = Person::create([
            'full_name' => 'Ahmad Tahun Baru',
            'email' => 'ahmad.year@test.local',
        ]);

        Student::create([
            'person_id' => $personA->id,
            'admission_academic_year_id' => $academicYear2026->id,
            'student_number' => 'SIS-TAHUN-2026',
            'nisn' => '3000000001',
            'registration_number' => 'REG-TAHUN-2026',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $personB = Person::create([
            'full_name' => 'Siti Tahun Lama',
            'email' => 'siti.year@test.local',
        ]);

        Student::create([
            'person_id' => $personB->id,
            'admission_academic_year_id' => $academicYear2025->id,
            'student_number' => 'SIS-TAHUN-2025',
            'nisn' => '3000000002',
            'registration_number' => 'REG-TAHUN-2025',
            'admission_date' => '2025-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/students?admission_academic_year_id='.$academicYear2026->id);

        $response
            ->assertStatus(200)
            ->assertSee('Ahmad Tahun Baru')
            ->assertSee('2026/2027 Filter')
            ->assertDontSee('Siti Tahun Lama');
    }
}
