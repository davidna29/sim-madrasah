<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\ClassGroupScheduleTemplate;
use App\Models\ScheduleTemplate;
use App\Models\Semester;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ScheduleTemplateAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $selectedAcademicYearId = $request->integer('academic_year_id') ?: null;
        $selectedSemesterId = $request->integer('semester_id') ?: null;

        $assignments = ClassGroupScheduleTemplate::query()
            ->with([
                'academicYear',
                'semester',
                'classGroup.gradeLevel',
                'scheduleTemplate',
                'assignedBy',
            ])
            ->when(
                $selectedAcademicYearId,
                fn ($query) => $query->where('academic_year_id', $selectedAcademicYearId)
            )
            ->when(
                $selectedSemesterId,
                fn ($query) => $query->where('semester_id', $selectedSemesterId)
            )
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->orderBy('class_group_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.schedule-template-assignments.index', [
            'assignments' => $assignments,
            'academicYears' => $this->academicYears(),
            'semesters' => $this->semesters(),
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedSemesterId' => $selectedSemesterId,
        ]);
    }

    public function create(): View
    {
        return view('admin.schedule-template-assignments.create', [
            'assignment' => new ClassGroupScheduleTemplate([
                'is_active' => true,
            ]),
        ] + $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAssignment($request);

        $existingAssignment = ClassGroupScheduleTemplate::query()
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('class_group_id', $validated['class_group_id'])
            ->first();

        if ($existingAssignment !== null) {
            $existingAssignment->update([
                'schedule_template_id' => $validated['schedule_template_id'],
                'is_active' => true,
                'assigned_at' => now(),
                'assigned_by' => $request->user()?->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()
                ->route('admin.schedule-template-assignments.index')
                ->with('success', 'Assignment template jadwal berhasil diganti.');
        }

        ClassGroupScheduleTemplate::create([
            'academic_year_id' => $validated['academic_year_id'],
            'semester_id' => $validated['semester_id'],
            'class_group_id' => $validated['class_group_id'],
            'schedule_template_id' => $validated['schedule_template_id'],
            'is_active' => true,
            'assigned_at' => now(),
            'assigned_by' => $request->user()?->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.schedule-template-assignments.index')
            ->with('success', 'Assignment template jadwal berhasil dibuat.');
    }

    public function destroy(
        ClassGroupScheduleTemplate $classGroupScheduleTemplate
    ): RedirectResponse {
        $classGroupScheduleTemplate->delete();

        return redirect()
            ->route('admin.schedule-template-assignments.index')
            ->with('success', 'Assignment template jadwal berhasil dilepas.');
    }

    /**
     * Validasi assignment rombel ke template jadwal.
     *
     * @return array<string, mixed>
     */
    private function validateAssignment(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id'),
            ],
            'semester_id' => [
                'required',
                'integer',
                Rule::exists('semesters', 'id'),
            ],
            'class_group_id' => [
                'required',
                'integer',
                Rule::exists('class_groups', 'id'),
            ],
            'schedule_template_id' => [
                'required',
                'integer',
                Rule::exists('schedule_templates', 'id'),
            ],
            'replace_existing' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $academicYearId = (int) $request->input('academic_year_id');
            $semesterId = (int) $request->input('semester_id');
            $classGroupId = (int) $request->input('class_group_id');
            $scheduleTemplateId = (int) $request->input('schedule_template_id');

            $semester = Semester::find($semesterId);

            if (
                $semester !== null
                && (int) $semester->academic_year_id !== $academicYearId
            ) {
                $validator->errors()->add(
                    'semester_id',
                    'Semester harus sesuai dengan tahun ajaran yang dipilih.'
                );
            }

            $classGroup = ClassGroup::find($classGroupId);

            if (
                $classGroup !== null
                && (int) $classGroup->academic_year_id !== $academicYearId
            ) {
                $validator->errors()->add(
                    'class_group_id',
                    'Rombel harus berasal dari tahun ajaran yang dipilih.'
                );
            }

            $scheduleTemplate = ScheduleTemplate::find($scheduleTemplateId);

            if ($scheduleTemplate !== null && ! $scheduleTemplate->is_active) {
                $validator->errors()->add(
                    'schedule_template_id',
                    'Template jadwal yang dipilih harus aktif.'
                );
            }

            if (
                $scheduleTemplate !== null
                && ! $scheduleTemplate->slots()->exists()
            ) {
                $validator->errors()->add(
                    'schedule_template_id',
                    'Template jadwal harus memiliki slot sebelum dipakai rombel.'
                );
            }

            $existingAssignment = ClassGroupScheduleTemplate::query()
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->where('class_group_id', $classGroupId)
                ->first();

            if (
                $existingAssignment !== null
                && ! $request->boolean('replace_existing')
            ) {
                $validator->errors()->add(
                    'class_group_id',
                    'Rombel ini sudah memiliki template jadwal pada semester yang sama. Centang opsi replace jika ingin mengganti.'
                );
            }
        });

        $validated = $validator->validate();

        $validated['academic_year_id'] = (int) $validated['academic_year_id'];
        $validated['semester_id'] = (int) $validated['semester_id'];
        $validated['class_group_id'] = (int) $validated['class_group_id'];
        $validated['schedule_template_id'] = (int) $validated['schedule_template_id'];

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'academicYears' => $this->academicYears(),
            'semesters' => $this->semesters(),
            'classGroups' => ClassGroup::query()
                ->with([
                    'academicYear',
                    'gradeLevel',
                ])
                ->where('is_active', true)
                ->orderByDesc('academic_year_id')
                ->orderBy('name')
                ->get(),
            'scheduleTemplates' => ScheduleTemplate::query()
                ->withCount('slots')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    private function academicYears()
    {
        return AcademicYear::query()
            ->orderByDesc('start_date')
            ->get();
    }

    private function semesters()
    {
        return Semester::query()
            ->with('academicYear')
            ->orderByDesc('academic_year_id')
            ->orderBy('semester_type')
            ->get();
    }
}
