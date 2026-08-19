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

class ScheduleTemplateAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_schedule_template_assignment_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.view');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $template = $this->createScheduleTemplateWithSlot('REGULER');

        ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $template->id,
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.schedule-template-assignments.index'));

        $response
            ->assertStatus(200)
            ->assertSee('Assignment Template Jadwal')
            ->assertSee('Kelas VII A')
            ->assertSee('Template REGULER');
    }

    public function test_user_can_assign_class_group_to_schedule_template(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $template = $this->createScheduleTemplateWithSlot('REGULER');

        $response = $this
            ->actingAs($user)
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $template->id,
                'notes' => 'Assignment awal.',
            ]);

        $response->assertRedirect(route('admin.schedule-template-assignments.index'));

        $this->assertDatabaseHas('class_group_schedule_templates', [
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $template->id,
            'is_active' => true,
            'assigned_by' => $user->id,
            'notes' => 'Assignment awal.',
        ]);
    }

    public function test_assignment_rejects_semester_from_different_academic_year(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $otherAcademicYear = $this->createAcademicYear([
            'code' => '2027-2028',
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $semester = $this->createSemester($otherAcademicYear, [
            'code' => '2027-2028-ganjil',
            'name' => 'Semester Ganjil 2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2027-12-31',
        ]);

        $classGroup = $this->createClassGroup($academicYear);
        $template = $this->createScheduleTemplateWithSlot('REGULER');

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-template-assignments.create'))
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $template->id,
            ]);

        $response
            ->assertRedirect(route('admin.schedule-template-assignments.create'))
            ->assertSessionHasErrors('semester_id');

        $this->assertSame(0, ClassGroupScheduleTemplate::count());
    }

    public function test_assignment_rejects_class_group_from_different_academic_year(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $otherAcademicYear = $this->createAcademicYear([
            'code' => '2027-2028',
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
            'status' => 'draft',
            'is_active' => false,
        ]);

        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($otherAcademicYear, [
            'code' => 'VIII-A',
            'name' => 'Kelas VIII A',
            'parallel_name' => 'A',
        ]);
        $template = $this->createScheduleTemplateWithSlot('REGULER');

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-template-assignments.create'))
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $template->id,
            ]);

        $response
            ->assertRedirect(route('admin.schedule-template-assignments.create'))
            ->assertSessionHasErrors('class_group_id');

        $this->assertSame(0, ClassGroupScheduleTemplate::count());
    }

    public function test_assignment_rejects_inactive_schedule_template(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $template = $this->createScheduleTemplateWithSlot('REGULER', [
            'is_active' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-template-assignments.create'))
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $template->id,
            ]);

        $response
            ->assertRedirect(route('admin.schedule-template-assignments.create'))
            ->assertSessionHasErrors('schedule_template_id');

        $this->assertSame(0, ClassGroupScheduleTemplate::count());
    }

    public function test_assignment_rejects_schedule_template_without_slots(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $template = $this->createScheduleTemplate('REGULER');

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-template-assignments.create'))
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $template->id,
            ]);

        $response
            ->assertRedirect(route('admin.schedule-template-assignments.create'))
            ->assertSessionHasErrors('schedule_template_id');

        $this->assertSame(0, ClassGroupScheduleTemplate::count());
    }

    public function test_assignment_rejects_duplicate_without_replace(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $firstTemplate = $this->createScheduleTemplateWithSlot('REGULER');
        $secondTemplate = $this->createScheduleTemplateWithSlot('JUMAT-PENDEK');

        ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $firstTemplate->id,
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('admin.schedule-template-assignments.create'))
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $secondTemplate->id,
            ]);

        $response
            ->assertRedirect(route('admin.schedule-template-assignments.create'))
            ->assertSessionHasErrors('class_group_id');

        $this->assertSame(
            $firstTemplate->id,
            ClassGroupScheduleTemplate::firstOrFail()->schedule_template_id
        );
    }

    public function test_assignment_can_replace_existing_template(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $firstTemplate = $this->createScheduleTemplateWithSlot('REGULER');
        $secondTemplate = $this->createScheduleTemplateWithSlot('JUMAT-PENDEK');

        ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $firstTemplate->id,
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.schedule-template-assignments.store'), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $secondTemplate->id,
                'replace_existing' => '1',
                'notes' => 'Diganti ke template Jumat pendek.',
            ]);

        $response->assertRedirect(route('admin.schedule-template-assignments.index'));

        $this->assertSame(1, ClassGroupScheduleTemplate::count());

        $assignment = ClassGroupScheduleTemplate::firstOrFail();

        $this->assertSame($secondTemplate->id, $assignment->schedule_template_id);
        $this->assertSame('Diganti ke template Jumat pendek.', $assignment->notes);
    }

    public function test_user_can_release_schedule_template_assignment(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'schedule_templates.update');

        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $template = $this->createScheduleTemplateWithSlot('REGULER');

        $assignment = ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $template->id,
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('admin.schedule-template-assignments.destroy', $assignment));

        $response
            ->assertRedirect(route('admin.schedule-template-assignments.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('class_group_schedule_templates', [
            'id' => $assignment->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createAcademicYear(array $overrides = []): AcademicYear
    {
        return AcademicYear::create(array_merge([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createSemester(
        AcademicYear $academicYear,
        array $overrides = []
    ): Semester {
        return Semester::create(array_merge([
            'academic_year_id' => $academicYear->id,
            'code' => $academicYear->code.'-ganjil',
            'name' => 'Semester Ganjil '.$academicYear->name,
            'semester_type' => 'ganjil',
            'start_date' => $academicYear->start_date?->format('Y-m-d') ?? '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createClassGroup(
        AcademicYear $academicYear,
        array $overrides = []
    ): ClassGroup {
        $gradeLevel = GradeLevel::create([
            'code' => $overrides['grade_level_code'] ?? 'VII',
            'name' => $overrides['grade_level_name'] ?? 'Kelas VII',
            'level_number' => $overrides['grade_level_number'] ?? 7,
            'is_active' => true,
        ]);

        return ClassGroup::create(array_merge([
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
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createScheduleTemplate(
        string $code,
        array $overrides = []
    ): ScheduleTemplate {
        return ScheduleTemplate::create(array_merge([
            'code' => $code,
            'name' => 'Template '.$code,
            'active_days' => [1, 2, 3, 4, 5],
            'holiday_days' => [6, 7],
            'max_slots_per_day' => 10,
            'standard_slot_duration_minutes' => 35,
            'status' => 'ready',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createScheduleTemplateWithSlot(
        string $code,
        array $overrides = []
    ): ScheduleTemplate {
        $template = $this->createScheduleTemplate($code, $overrides);

        $template->slots()->create([
            'day_of_week' => 1,
            'sort_order' => 1,
            'starts_at' => '07:00',
            'ends_at' => '07:35',
            'slot_type' => 'kbm',
            'label' => 'Jam Pelajaran 1',
            'is_teaching_slot' => true,
        ]);

        return $template;
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_schedule_assignment_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Schedule Assignment Role',
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
