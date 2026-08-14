<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_academic_years(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.view');

        AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/academic-years');

        $response
            ->assertStatus(200)
            ->assertSee('Tahun Ajaran dan Semester')
            ->assertSee('2026/2027');
    }

    public function test_user_with_permission_can_create_academic_year_with_semesters(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.view');
        $this->grantPermissionToUser($user, 'academic_years.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/academic-years', [
                'code' => '2027-2028',
                'name' => '2027/2028',
                'start_date' => '2027-07-01',
                'end_date' => '2028-06-30',
                'ganjil_start_date' => '2027-07-01',
                'ganjil_end_date' => '2027-12-31',
                'genap_start_date' => '2028-01-01',
                'genap_end_date' => '2028-06-30',
            ]);

        $response->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'code' => '2027-2028',
            'name' => '2027/2028',
        ]);

        $this->assertDatabaseHas('semesters', [
            'code' => '2027-2028-ganjil',
            'semester_type' => 'ganjil',
        ]);

        $this->assertDatabaseHas('semesters', [
            'code' => '2027-2028-genap',
            'semester_type' => 'genap',
        ]);
    }

    public function test_user_can_activate_one_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.activate');

        $academicYear = AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-ganjil',
            'name' => 'Semester Ganjil 2026/2027',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.semesters.activate', $semester));

        $response->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'id' => $academicYear->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    public function test_user_can_lock_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.lock');

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

        $response = $this
            ->actingAs($user)
            ->put(route('admin.semesters.lock', $semester));

        $response->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'is_locked' => true,
            'status' => 'locked',
            'locked_by' => $user->id,
        ]);
    }

    // Menguji bahwa pengguna dapat melihat semester aktif sistem pada halaman tahun akademik
    public function test_user_can_see_active_system_semester_on_academic_year_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.view');

        $academicYear = AcademicYear::create([
            'code' => '2026-2027-active-view',
            'name' => '2026/2027 Active View',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-active-view-ganjil',
            'name' => 'Semester Ganjil 2026/2027 Active View',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.academic-years.index'));

        $response
            ->assertStatus(200)
            ->assertSee('Semester Aktif Sistem')
            ->assertSee('2026/2027 Active View')
            ->assertSee('Semester Ganjil 2026/2027 Active View');
    }

    // Menguji bahwa mengaktifkan semester baru akan menonaktifkan semester aktif sebelumnya
    public function test_activating_semester_deactivates_previous_active_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.activate');

        $oldAcademicYear = AcademicYear::create([
            'code' => '2026-2027-old-active',
            'name' => '2026/2027 Old Active',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $oldSemester = Semester::create([
            'academic_year_id' => $oldAcademicYear->id,
            'code' => '2026-2027-old-active-ganjil',
            'name' => 'Semester Ganjil 2026/2027 Old Active',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $newAcademicYear = AcademicYear::create([
            'code' => '2027-2028-new-active',
            'name' => '2027/2028 New Active',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $newSemester = Semester::create([
            'academic_year_id' => $newAcademicYear->id,
            'code' => '2027-2028-new-active-ganjil',
            'name' => 'Semester Ganjil 2027/2028 New Active',
            'semester_type' => 'ganjil',
            'start_date' => '2027-07-01',
            'end_date' => '2027-12-31',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.semesters.activate', $newSemester));

        $response
            ->assertRedirect(route('admin.academic-years.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('academic_years', [
            'id' => $oldAcademicYear->id,
            'is_active' => false,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('semesters', [
            'id' => $oldSemester->id,
            'is_active' => false,
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('academic_years', [
            'id' => $newAcademicYear->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('semesters', [
            'id' => $newSemester->id,
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_activate_locked_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.activate');

        $academicYear = AcademicYear::create([
            'code' => '2026-2027-locked',
            'name' => '2026/2027 Locked',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-locked-ganjil',
            'name' => 'Semester Ganjil 2026/2027 Locked',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'locked',
            'is_active' => false,
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.semesters.activate', $semester));

        $response->assertSessionHasErrors('semester_id');

        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'is_active' => false,
            'status' => 'locked',
            'is_locked' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_academic_year_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Academic Year Role',
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
