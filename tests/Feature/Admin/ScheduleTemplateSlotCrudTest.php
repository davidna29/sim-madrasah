<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupScheduleTemplate;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ScheduleTemplate;
use App\Models\ScheduleTemplateSlot;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTemplateSlotCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_schedule_template_slots_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.view');

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
            ->get(route('admin.schedule-templates.slots.index', $template));

        $response
            ->assertStatus(200)
            ->assertSee('Slot Template Jadwal')
            ->assertSee('Jam Pelajaran 1');
    }

    public function test_user_can_create_kbm_slot(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 1,
                'sort_order' => 1,
                'starts_at' => '07:00',
                'ends_at' => '07:35',
                'slot_type' => 'kbm',
                'label' => 'Jam Pelajaran 1',
                'notes' => 'Slot pembelajaran pertama.',
            ]);

        $response->assertRedirect(route('admin.schedule-templates.slots.index', $template));

        $slot = ScheduleTemplateSlot::where('schedule_template_id', $template->id)
            ->where('sort_order', 1)
            ->firstOrFail();

        $this->assertSame(1, $slot->day_of_week);
        $this->assertSame('07:00', substr($slot->starts_at, 0, 5));
        $this->assertSame('07:35', substr($slot->ends_at, 0, 5));
        $this->assertSame('kbm', $slot->slot_type);
        $this->assertTrue($slot->is_teaching_slot);
    }

    public function test_non_kbm_slot_is_automatically_marked_as_not_teaching_slot(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 1,
                'sort_order' => 2,
                'starts_at' => '09:10',
                'ends_at' => '09:30',
                'slot_type' => 'istirahat',
                'label' => 'Istirahat Pagi',
            ]);

        $response->assertRedirect(route('admin.schedule-templates.slots.index', $template));

        $slot = ScheduleTemplateSlot::where('schedule_template_id', $template->id)
            ->where('sort_order', 2)
            ->firstOrFail();

        $this->assertSame('istirahat', $slot->slot_type);
        $this->assertFalse($slot->is_teaching_slot);
    }

    public function test_user_can_update_slot(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();

        $slot = $template->slots()->create([
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
            ->put(route('admin.schedule-template-slots.update', $slot), [
                'day_of_week' => 5,
                'sort_order' => 1,
                'starts_at' => '07:15',
                'ends_at' => '07:45',
                'slot_type' => 'upacara',
                'label' => 'Kegiatan Jumat',
                'notes' => 'Penyesuaian hari Jumat.',
            ]);

        $response->assertRedirect(route('admin.schedule-templates.slots.index', $template));

        $slot = $slot->fresh();

        $this->assertSame(5, $slot->day_of_week);
        $this->assertSame('07:15', substr($slot->starts_at, 0, 5));
        $this->assertSame('07:45', substr($slot->ends_at, 0, 5));
        $this->assertSame('upacara', $slot->slot_type);
        $this->assertSame('Kegiatan Jumat', $slot->label);
        $this->assertFalse($slot->is_teaching_slot);
    }

    public function test_slot_rejects_duplicate_sort_order_on_same_day(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

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
            ->from(route('admin.schedule-templates.slots.create', $template))
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 1,
                'sort_order' => 1,
                'starts_at' => '07:35',
                'ends_at' => '08:10',
                'slot_type' => 'kbm',
                'label' => 'Jam Pelajaran 2',
            ]);

        $response
            ->assertRedirect(route('admin.schedule-templates.slots.create', $template))
            ->assertSessionHasErrors('sort_order');
    }

    public function test_slot_rejects_overlapping_time_on_same_day(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

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
            ->from(route('admin.schedule-templates.slots.create', $template))
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 1,
                'sort_order' => 2,
                'starts_at' => '07:20',
                'ends_at' => '08:00',
                'slot_type' => 'kbm',
                'label' => 'Bentrok',
            ]);

        $response
            ->assertRedirect(route('admin.schedule-templates.slots.create', $template))
            ->assertSessionHasErrors('starts_at');
    }

    public function test_slot_rejects_day_outside_template_active_days(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate([
            'active_days' => [1, 2, 3, 4, 5],
            'holiday_days' => [6, 7],
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-templates.slots.create', $template))
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 6,
                'sort_order' => 1,
                'starts_at' => '07:00',
                'ends_at' => '07:35',
                'slot_type' => 'kbm',
                'label' => 'Sabtu',
            ]);

        $response
            ->assertRedirect(route('admin.schedule-templates.slots.create', $template))
            ->assertSessionHasErrors('day_of_week');
    }

    public function test_slot_rejects_end_time_before_start_time(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-templates.slots.create', $template))
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 1,
                'sort_order' => 1,
                'starts_at' => '08:00',
                'ends_at' => '07:35',
                'slot_type' => 'kbm',
                'label' => 'Waktu Salah',
            ]);

        $response
            ->assertRedirect(route('admin.schedule-templates.slots.create', $template))
            ->assertSessionHasErrors('ends_at');
    }

    public function test_assigned_template_slots_cannot_be_modified(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();
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
            ->post(route('admin.schedule-templates.slots.store', $template), [
                'day_of_week' => 1,
                'sort_order' => 1,
                'starts_at' => '07:00',
                'ends_at' => '07:35',
                'slot_type' => 'kbm',
                'label' => 'Jam Pelajaran 1',
            ]);

        $response
            ->assertRedirect(route('admin.schedule-templates.slots.index', $template))
            ->assertSessionHas('error');

        $this->assertSame(0, $template->slots()->count());
    }

    public function test_user_can_delete_slot_from_unassigned_template(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $template = $this->createScheduleTemplate();

        $slot = $template->slots()->create([
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
            ->delete(route('admin.schedule-template-slots.destroy', $slot));

        $response
            ->assertRedirect(route('admin.schedule-templates.slots.index', $template))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('schedule_template_slots', [
            'id' => $slot->id,
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
                'name' => 'test_schedule_template_slot_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Schedule Template Slot Role',
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
