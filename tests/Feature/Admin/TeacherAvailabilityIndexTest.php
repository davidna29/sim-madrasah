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

class TeacherAvailabilityIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_seeder_registers_teacher_availability_view_permission(): void
    {
        $this->seed(RbacSeeder::class);

        $this->assertDatabaseHas('permissions', [
            'name' => 'teacher_availabilities.view',
            'module' => 'teacher_availabilities',
            'action' => 'view',
            'display_name' => 'Melihat Ketersediaan Guru',
            'is_active' => true,
        ]);
    }

    public function test_user_with_permission_can_view_teacher_availability_list(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.view');

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
            'reason' => 'Tugas luar',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/teacher-availabilities');

        $response
            ->assertStatus(200)
            ->assertSee('Ketersediaan Guru')
            ->assertSee('Pak Ahmad')
            ->assertSee('Senin')
            ->assertSee('07:00')
            ->assertSee('09:00')
            ->assertSee('Tidak tersedia')
            ->assertSee('Tugas luar');
    }

    public function test_user_without_permission_cannot_view_teacher_availability_list(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin/teacher-availabilities');

        $response->assertStatus(403);
    }

    public function test_teacher_availability_list_can_be_filtered_by_academic_year_and_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.view');

        $firstAcademicYear = $this->createAcademicYear('2026/2027');
        $firstSemester = $this->createSemester($firstAcademicYear);
        $firstTeacher = $this->createTeacher('Pak Ahmad');

        $secondAcademicYear = $this->createAcademicYear('2027/2028');
        $secondSemester = $this->createSemester($secondAcademicYear);
        $secondTeacher = $this->createTeacher('Bu Budi');

        TeacherAvailability::create([
            'academic_year_id' => $firstAcademicYear->id,
            'semester_id' => $firstSemester->id,
            'teacher_user_id' => $firstTeacher->id,
            'day_of_week' => 1,
            'starts_at' => '07:00',
            'ends_at' => '09:00',
            'availability_type' => 'unavailable',
            'reason' => 'Tugas luar',
            'status' => 'active',
            'is_active' => true,
        ]);

        TeacherAvailability::create([
            'academic_year_id' => $secondAcademicYear->id,
            'semester_id' => $secondSemester->id,
            'teacher_user_id' => $secondTeacher->id,
            'day_of_week' => 2,
            'starts_at' => '10:00',
            'ends_at' => '11:00',
            'availability_type' => 'unavailable',
            'reason' => 'Rapat MGMP',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/teacher-availabilities?academic_year_id='.$firstAcademicYear->id.'&semester_id='.$firstSemester->id);

        $response
            ->assertStatus(200)
            ->assertSee('Pak Ahmad')
            ->assertSee('Tugas luar')
            ->assertSee('Tugas luar')
            ->assertDontSee('Rapat MGMP');
    }

    public function test_teacher_availability_list_can_be_filtered_by_teacher(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'teacher_availabilities.view');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $firstTeacher = $this->createTeacher('Pak Ahmad');
        $secondTeacher = $this->createTeacher('Bu Budi');

        TeacherAvailability::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $firstTeacher->id,
            'day_of_week' => 1,
            'starts_at' => '07:00',
            'ends_at' => '09:00',
            'availability_type' => 'unavailable',
            'reason' => 'Tugas luar',
            'status' => 'active',
            'is_active' => true,
        ]);

        TeacherAvailability::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'teacher_user_id' => $secondTeacher->id,
            'day_of_week' => 3,
            'starts_at' => '12:00',
            'ends_at' => '13:00',
            'availability_type' => 'unavailable',
            'reason' => 'Pembinaan',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/teacher-availabilities?teacher_user_id='.$secondTeacher->id);

        $response
            ->assertStatus(200)
            ->assertSee('Bu Budi')
            ->assertSee('Pembinaan')
            ->assertDontSee('Tugas luar');
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
            ['name' => 'test_teacher_availability_index_role'],
            [
                'display_name' => 'Test Teacher Availability Index Role',
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
