<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeLevelController extends Controller
{
    public function index(): View
    {
        $gradeLevels = GradeLevel::query()
            ->orderBy('level_number')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.grade-levels.index', [
            'gradeLevels' => $gradeLevels,
        ]);
    }

    public function create(): View
    {
        return view('admin.grade-levels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGradeLevel($request);

        GradeLevel::create($validated);

        return redirect()
            ->route('admin.grade-levels.index')
            ->with('success', 'Tingkat kelas berhasil dibuat.');
    }

    public function edit(GradeLevel $gradeLevel): View
    {
        return view('admin.grade-levels.edit', [
            'gradeLevel' => $gradeLevel,
        ]);
    }

    public function update(
        Request $request,
        GradeLevel $gradeLevel
    ): RedirectResponse {
        $validated = $this->validateGradeLevel(
            $request,
            $gradeLevel
        );

        $gradeLevel->update($validated);

        return redirect()
            ->route('admin.grade-levels.index')
            ->with('success', 'Tingkat kelas berhasil diperbarui.');
    }

    /**
     * Validasi tingkat kelas.
     *
     * @return array<string, mixed>
     */
    private function validateGradeLevel(
        Request $request,
        ?GradeLevel $gradeLevel = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('grade_levels', 'code')
                    ->ignore($gradeLevel),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'level_number' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
