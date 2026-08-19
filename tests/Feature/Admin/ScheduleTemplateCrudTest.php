<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupScheduleTemplate;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ScheduleTemplate;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTemplateCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_schedule_template_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.view');

        ScheduleTemplate::create([
            'code' => 'REGULER-5-HARI',
            'name' => 'Template Reguler 5 Hari',
            'active_days' => [1, 2, 3, 4, 5],
            'holiday_days' => [6, 7],
            'max_slots_per_day' => 10,
            'standard_slot_duration_minutes' => 35,
            'status' => 'draft',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.schedule-templates.index'));

        $response
            ->assertStatus(200)
            ->assertSee('Template Jadwal')
            ->assertSee('Template Reguler 5 Hari');
    }

    public function test_user_can_create_schedule_template(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.create');

        $response = $this
            ->actingAs($user)
            ->post(route('admin.schedule-templates.store'), [
                'code' => 'REGULER-5-HARI',
                'name' => 'Template Reguler 5 Hari',
                'description' => 'Template jadwal reguler.',
                'active_days' => [1, 2, 3, 4, 5],
                'holiday_days' => [6, 7],
                'max_slots_per_day' => 10,
                'standard_slot_duration_minutes' => 35,
                'status' => 'draft',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.schedule-templates.index'));

        $this->assertDatabaseHas('schedule_templates', [
            'code' => 'REGULER-5-HARI',
            'name' => 'Template Reguler 5 Hari',
            'max_slots_per_day' => 10,
            'standard_slot_duration_minutes' => 35,
            'status' => 'draft',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        $template = ScheduleTemplate::where('code', 'REGULER-5-HARI')->firstOrFail();

        $this->assertSame([1, 2, 3, 4, 5], $template->active_days);
        $this->assertSame([6, 7], $template->holiday_days);
    }

    public function test_user_can_update_schedule_template(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();

        $response = $this
            ->actingAs($user)
            ->put(route('admin.schedule-templates.update', $template), [
                'code' => 'REGULER-REVISI',
                'name' => 'Template Reguler Revisi',
                'description' => 'Template revisi.',
                'active_days' => [1, 2, 3, 4],
                'holiday_days' => [5, 6, 7],
                'max_slots_per_day' => 8,
                'standard_slot_duration_minutes' => 40,
                'status' => 'ready',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.schedule-templates.index'));

        $template = $template->fresh();

        $this->assertSame('REGULER-REVISI', $template->code);
        $this->assertSame('Template Reguler Revisi', $template->name);
        $this->assertSame([1, 2, 3, 4], $template->active_days);
        $this->assertSame([5, 6, 7], $template->holiday_days);
        $this->assertSame(8, $template->max_slots_per_day);
        $this->assertSame(40, $template->standard_slot_duration_minutes);
        $this->assertSame('ready', $template->status);
    }

    public function test_schedule_template_validation_rejects_overlapping_active_and_holiday_days(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.create');

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-templates.create'))
            ->post(route('admin.schedule-templates.store'), [
                'code' => 'SALAH',
                'name' => 'Template Salah',
                'active_days' => [1, 2, 3],
                'holiday_days' => [3, 4],
                'max_slots_per_day' => 10,
                'standard_slot_duration_minutes' => 35,
                'status' => 'draft',
                'is_active' => '1',
            ]);

        $response
            ->assertRedirect(route('admin.schedule-templates.create'))
            ->assertSessionHasErrors('holiday_days');

        $this->assertDatabaseMissing('schedule_templates', [
            'code' => 'SALAH',
        ]);
    }

    public function test_user_can_clone_schedule_template_with_slots(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.create');

        $template = $this->createScheduleTemplate();

        $template->slots()->create([
            'day_of_week' => 1,
            'sort_order' => 1,
            'starts_at' => '07:00',
            'ends_at' => '07:35',
            'slot_type' => 'kbm',
            'label' => 'Jam Pelajaran 1',
            'is_teaching_slot' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.schedule-templates.clone', $template));

        $response->assertRedirect(route('admin.schedule-templates.index'));

        $copy = ScheduleTemplate::where('code', 'REGULER-COPY')->firstOrFail();

        $this->assertSame('Template Reguler 5 Hari - Salinan', $copy->name);
        $this->assertFalse($copy->is_active);
        $this->assertSame('draft', $copy->status);
        $this->assertSame(1, $copy->slots()->count());
    }

    public function test_active_schedule_template_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.delete');

        $template = $this->createScheduleTemplate([
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('admin.schedule-templates.destroy', $template));

        $response
            ->assertRedirect(route('admin.schedule-templates.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('schedule_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_assigned_schedule_template_cannot_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.delete');

        $template = $this->createScheduleTemplate([
            'is_active' => false,
        ]);

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);

        ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $template->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('admin.schedule-templates.destroy', $template));

        $response
            ->assertRedirect(route('admin.schedule-templates.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('schedule_templates', [
            'id' => $template->id,
        ]);
    }

    public function test_inactive_unassigned_schedule_template_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.delete');

        $template = $this->createScheduleTemplate([
            'is_active' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('admin.schedule-templates.destroy', $template));

        $response
            ->assertRedirect(route('admin.schedule-templates.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('schedule_templates', [
            'id' => $template->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createScheduleTemplate(array $overrides = []): ScheduleTemplate
    {
        return ScheduleTemplate::create(array_merge([
            'code' => 'REGULER',
            'name' => 'Template Reguler 5 Hari',
            'active_days' => [1, 2, 3, 4, 5],
            'holiday_days' => [6, 7],
            'max_slots_per_day' => 10,
            'standard_slot_duration_minutes' => 35,
            'status' => 'draft',
            'is_active' => true,
        ], $overrides));
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

    private function createSemester(AcademicYear $academicYear): Semester
    {
        return Semester::create([
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
    }

    private function createClassGroup(AcademicYear $academicYear): ClassGroup
    {
        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        return ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => null,
            'homeroom_teacher_user_id' => null,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
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
                'name' => 'test_schedule_template_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Schedule Template Role',
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
