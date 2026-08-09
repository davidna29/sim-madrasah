<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassGroupController extends Controller
{
    public function index(): View
    {
        $classGroups = ClassGroup::query()
            ->with([
                'academicYear',
                'gradeLevel',
                'room',
                'homeroomTeacher',
            ])
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.class-groups.index', [
            'classGroups' => $classGroups,
        ]);
    }

    public function create(): View
    {
        return view('admin.class-groups.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateClassGroup($request);

        ClassGroup::create($validated);

        return redirect()
            ->route('admin.class-groups.index')
            ->with('success', 'Rombongan belajar berhasil dibuat.');
    }

    public function edit(ClassGroup $classGroup): View
    {
        return view('admin.class-groups.edit', [
            'classGroup' => $classGroup,
        ] + $this->formData());
    }

    public function update(
        Request $request,
        ClassGroup $classGroup
    ): RedirectResponse {
        $validated = $this->validateClassGroup(
            $request,
            $classGroup
        );

        $classGroup->update($validated);

        return redirect()
            ->route('admin.class-groups.index')
            ->with('success', 'Rombongan belajar berhasil diperbarui.');
    }

    /**
     * Data pendukung form.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'academicYears' => AcademicYear::query()
                ->orderByDesc('start_date')
                ->get(),
            'gradeLevels' => GradeLevel::query()
                ->where('is_active', true)
                ->orderBy('level_number')
                ->get(),
            'rooms' => Room::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'homeroomTeachers' => User::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Validasi rombongan belajar.
     *
     * @return array<string, mixed>
     */
    private function validateClassGroup(
        Request $request,
        ?ClassGroup $classGroup = null
    ): array {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
            'grade_level_id' => [
                'required',
                'exists:grade_levels,id',
            ],
            'room_id' => [
                'nullable',
                'exists:rooms,id',
            ],
            'homeroom_teacher_user_id' => [
                'nullable',
                'exists:users,id',
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('class_groups', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'academic_year_id',
                            $request->input('academic_year_id')
                        )
                    )
                    ->ignore($classGroup),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'parallel_name' => [
                'nullable',
                'string',
                'max:30',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'status' => [
                'required',
                'string',
                'max:30',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
