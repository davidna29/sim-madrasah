<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Semester;
use App\Models\TeacherAvailability;
use App\Models\User;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAvailabilityCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_seeder_registers_teacher_availability_create_permission(): void
    {
        $this->seed(RbacSeeder::class);

        $this->assertDatabaseHas('permissions', [
            'name' => 'teacher_availabilities.create',
            'module' => 'teacher_availabilities',
            'action' => 'create',
            'display_name' => 'Membuat Ketersediaan Guru',
            'is_active' => true,
        ]);
    }

    public function test_user_with_permission_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.create');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = $this->createTeacher('Pak Ahmad');

        $response = $this
            ->actingAs($user)
            ->get('/admin/teacher-availabilities/create?academic_year_id='.$academicYear->id.'&semester_id='.$semester->id.'&teacher_user_id='.$teacher->id);

        $response
            ->assertStatus(200)
            ->assertSee('Tambah Ketersediaan Guru')
            ->assertSee('Pak Ahmad')
            ->assertSee('Semester Ganjil 2026/2027')
            ->assertSee('Simpan Ketersediaan');
    }

    public function test_user_without_permission_cannot_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/teacher-availabilities/create');

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_store_teacher_availability(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.create');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = $this->createTeacher('Pak Ahmad');

        $response = $this
            ->actingAs($user)
            ->post('/admin/teacher-availabilities', [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'teacher_user_id' => $teacher->id,
                'day_of_week' => 1,
                'starts_at' => '07:00',
                'ends_at' => '09:00',
                'availability_type' => 'unavailable',
                'reason' => 'Tugas luar',
                'notes' => 'Tidak bisa mengajar jam pertama dan kedua.',
            ]);

        $response
            ->assertRedirect(route('admin.teacher-availabilities.index', [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'teacher_user_id' => $teacher->id,
            ]))
            ->assertSessionHas('success', 'Ketersediaan guru berhasil ditambahkan.');

        $this->assertDatabaseHas('teacher_availabilities', [
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $teacher->id,
            'day_of_week' => 1,
            'availability_type' => 'unavailable',
            'reason' => 'Tugas luar',
            'notes' => 'Tidak bisa mengajar jam pertama dan kedua.',
            'status' => 'active',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $availability = TeacherAvailability::firstOrFail();

        $this->assertSame('07:00', substr((string) $availability->starts_at, 0, 5));
        $this->assertSame('09:00', substr((string) $availability->ends_at, 0, 5));
    }

    public function test_user_without_permission_cannot_store_teacher_availability(): void
    {
        $user = User::factory()->create();

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = $this->createTeacher('Pak Ahmad');

        $response = $this
            ->actingAs($user)
            ->post('/admin/teacher-availabilities', [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'teacher_user_id' => $teacher->id,
                'day_of_week' => 1,
                'starts_at' => '07:00',
                'ends_at' => '09:00',
                'availability_type' => 'unavailable',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseCount('teacher_availabilities', 0);
    }

    public function test_store_rejects_invalid_teacher_availability_data(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/teacher-availabilities', [
                'academic_year_id' => '',
                'semester_id' => '',
                'teacher_user_id' => '',
                'day_of_week' => 8,
                'starts_at' => '09:00',
                'ends_at' => '08:00',
                'availability_type' => 'available',
            ]);

        $response
            ->assertSessionHasErrors([
                'academic_year_id',
                'semester_id',
                'teacher_user_id',
                'day_of_week',
                'ends_at',
                'availability_type',
            ]);

        $this->assertDatabaseCount('teacher_availabilities', 0);
    }

    public function test_store_rejects_duplicate_active_teacher_availability(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.create');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $teacher = $this->createTeacher('Pak Ahmad');

        TeacherAvailability::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $teacher->id,
            'day_of_week' => 1,
            'starts_at' => '07:00',
            'ends_at' => '09:00',
            'availability_type' => 'unavailable',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/admin/teacher-availabilities', [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'teacher_user_id' => $teacher->id,
                'day_of_week' => 1,
                'starts_at' => '07:00',
                'ends_at' => '09:00',
                'availability_type' => 'unavailable',
            ]);

        $response->assertSessionHasErrors(['starts_at']);

        $this->assertDatabaseCount('teacher_availabilities', 1);
    }

    private function createAcademicYear(string $name = '2026/2027'): AcademicYear
    {
        return AcademicYear::create([
            'code' => str_replace('/', '-', $name),
            'name' => $name,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);
    }

    private function createSemester(AcademicYear $academicYear): Semester
    {
        return Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => str_replace('/', '-', $academicYear->name).'-ganjil',
            'name' => 'Semester Ganjil '.$academicYear->name,
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);
    }

    private function createTeacher(string $name): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'guru_mata_pelajaran'],
            [
                'display_name' => 'Guru Mata Pelajaran',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        $teacher = User::factory()->create([
            'name' => $name,
            'status' => 'active',
        ]);

        $teacher->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()],
        ]);

        return $teacher;
    }

    private function grantPermissionToUser(User $user, string $permissionName): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'test_teacher_availability_create_role'],
            [
                'display_name' => 'Test Teacher Availability Create Role',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $dashboardPermission = Permission::firstOrCreate(
            ['name' => 'dashboard.view'],
            [
                'module' => 'dashboard',
                'action' => 'view',
                'display_name' => 'Melihat Dashboard',
                'is_active' => true,
            ]
        );

        [$module, $action] = explode('.', $permissionName);

        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            [
                'module' => $module,
                'action' => $action,
                'display_name' => $permissionName,
                'is_active' => true,
            ]
        );

        $role->permissions()->syncWithoutDetaching([
            $dashboardPermission->id,
            $permission->id,
        ]);

        $user->roles()->syncWithoutDetaching([
            $role->id => ['assigned_at' => now()],
        ]);
    }
}
