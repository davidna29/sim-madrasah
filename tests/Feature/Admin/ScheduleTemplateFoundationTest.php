<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupScheduleTemplate;
use App\Models\GradeLevel;
use App\Models\ScheduleTemplate;
use App\Models\Semester;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTemplateFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_template_can_store_active_days_holiday_days_and_slots(): void
    {
        $template = ScheduleTemplate::create([
            'code' => 'REGULER-5-HARI',
            'name' => 'Template Reguler 5 Hari',
            'description' => 'Template jadwal reguler Senin sampai Jumat.',
            'active_days' => [1, 2, 3, 4, 5],
            'holiday_days' => [6, 7],
            'max_slots_per_day' => 10,
            'standard_slot_duration_minutes' => 35,
            'status' => 'draft',
            'is_active' => true,
        ]);

        $template->slots()->create([
            'day_of_week' => 1,
            'sort_order' => 1,
            'starts_at' => '07:00',
            'ends_at' => '07:35',
            'slot_type' => 'kbm',
            'label' => 'Jam Pelajaran 1',
            'is_teaching_slot' => true,
        ]);

        $template->slots()->create([
            'day_of_week' => 1,
            'sort_order' => 2,
            'starts_at' => '07:35',
            'ends_at' => '08:10',
            'slot_type' => 'istirahat',
            'label' => 'Istirahat Pagi',
            'is_teaching_slot' => false,
        ]);

        $freshTemplate = $template->fresh();

        $this->assertSame([1, 2, 3, 4, 5], $freshTemplate->active_days);
        $this->assertSame([6, 7], $freshTemplate->holiday_days);
        $this->assertSame(2, $freshTemplate->slots()->count());

        $this->assertTrue(
            $freshTemplate->slots()
                ->where('slot_type', 'kbm')
                ->first()
                ->is_teaching_slot
        );

        $this->assertFalse(
            $freshTemplate->slots()
                ->where('slot_type', 'istirahat')
                ->first()
                ->is_teaching_slot
        );
    }

    public function test_class_group_can_have_one_schedule_template_assignment_per_semester(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $firstTemplate = $this->createScheduleTemplate('REGULER');
        $secondTemplate = $this->createScheduleTemplate('JUMAT-PENDEK');

        $assignment = ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $firstTemplate->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $assignment->update([
            'schedule_template_id' => $secondTemplate->id,
        ]);

        $this->assertSame(
            $secondTemplate->id,
            $assignment->fresh()->schedule_template_id
        );

        $this->assertSame(
            1,
            ClassGroupScheduleTemplate::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_id', $semester->id)
                ->where('class_group_id', $classGroup->id)
                ->count()
        );
    }

    public function test_database_rejects_duplicate_class_group_assignment_in_same_semester(): void
    {
        $academicYear = $this->createAcademicYear();
        $semester = $this->createSemester($academicYear);
        $classGroup = $this->createClassGroup($academicYear);
        $firstTemplate = $this->createScheduleTemplate('REGULER');
        $secondTemplate = $this->createScheduleTemplate('JUMAT-PENDEK');

        ClassGroupScheduleTemplate::create([
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'schedule_template_id' => $firstTemplate->id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $duplicateWasRejected = false;

        try {
            ClassGroupScheduleTemplate::create([
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'schedule_template_id' => $secondTemplate->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]);
        } catch (QueryException) {
            $duplicateWasRejected = true;
        }

        $this->assertTrue($duplicateWasRejected);
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

    private function createScheduleTemplate(string $code): ScheduleTemplate
    {
        return ScheduleTemplate::create([
            'code' => $code,
            'name' => 'Template '.$code,
            'active_days' => [1, 2, 3, 4, 5],
            'holiday_days' => [6, 7],
            'max_slots_per_day' => 10,
            'standard_slot_duration_minutes' => 35,
            'status' => 'draft',
            'is_active' => true,
        ]);
    }
}
