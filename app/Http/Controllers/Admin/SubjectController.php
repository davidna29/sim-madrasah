<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->orderBy('subject_group')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.subjects.index', [
            'subjects' => $subjects,
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubject($request);

        Subject::create($validated);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dibuat.');
    }

    public function toggleActive(Subject $subject): RedirectResponse
    {
        $subject->update([
            'is_active' => ! $subject->is_active,
        ]);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', $subject->is_active
                ? 'Mata pelajaran berhasil diaktifkan.'
                : 'Mata pelajaran berhasil dinonaktifkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', [
            'subject' => $subject,
        ]);
    }

    public function update(
        Request $request,
        Subject $subject
    ): RedirectResponse {
        $validated = $this->validateSubject($request, $subject);

        $subject->update($validated);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Validasi mata pelajaran.
     *
     * @return array<string, mixed>
     */
    private function validateSubject(
        Request $request,
        ?Subject $subject = null
    ): array {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('subjects', 'code')->ignore($subject),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'subject_group' => [
                'required',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_local_content' => [
                'nullable',
                'boolean',
            ],
            'is_religious' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['is_local_content'] = $request->boolean('is_local_content');
        $validated['is_religious'] = $request->boolean('is_religious');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
