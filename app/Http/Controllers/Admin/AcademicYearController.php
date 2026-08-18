<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $activeSemester = Semester::query()
            ->with('academicYear')
            ->where('is_active', true)
            ->first();

        $academicYears = AcademicYear::query()
            ->with('semesters')
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('admin.academic-years.index', [
            'academicYears' => $academicYears,
            'activeSemester' => $activeSemester,
        ]);
    }

    public function create(): View
    {
        return view('admin.academic-years.create');
    }

    public function edit(AcademicYear $academicYear): View
    {
        if ($academicYear->is_locked) {
            abort(403, 'Tahun ajaran yang sudah dikunci tidak bisa diedit.');
        }

        return view('admin.academic-years.edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAcademicYear($request);

        DB::transaction(function () use ($validated): void {
            $academicYear = AcademicYear::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'status' => 'draft',
                'is_active' => false,
                'is_locked' => false,
            ]);

            Semester::create([
                'academic_year_id' => $academicYear->id,
                'code' => $validated['code'].'-ganjil',
                'name' => 'Semester Ganjil '.$validated['name'],
                'semester_type' => 'ganjil',
                'start_date' => $validated['ganjil_start_date'],
                'end_date' => $validated['ganjil_end_date'],
                'status' => 'draft',
                'is_active' => false,
                'is_locked' => false,
            ]);

            Semester::create([
                'academic_year_id' => $academicYear->id,
                'code' => $validated['code'].'-genap',
                'name' => 'Semester Genap '.$validated['name'],
                'semester_type' => 'genap',
                'start_date' => $validated['genap_start_date'],
                'end_date' => $validated['genap_end_date'],
                'status' => 'draft',
                'is_active' => false,
                'is_locked' => false,
            ]);
        });

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_locked) {
            throw ValidationException::withMessages([
                'code' => 'Tahun ajaran yang sudah dikunci tidak bisa diedit.',
            ]);
        }

        $validated = $this->validateAcademicYearUpdate($request, $academicYear);

        $academicYear->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function activateSemester(Semester $semester): RedirectResponse
    {
        if ($semester->is_locked) {
            throw ValidationException::withMessages([
                'semester_id' => 'Semester terkunci tidak bisa diaktifkan kembali.',
            ]);
        }

        DB::transaction(function () use ($semester): void {
            AcademicYear::query()->update([
                'is_active' => false,
            ]);

            AcademicYear::query()
                ->where('is_locked', false)
                ->update([
                    'status' => 'draft',
                ]);

            Semester::query()->update([
                'is_active' => false,
            ]);

            Semester::query()
                ->where('is_locked', false)
                ->update([
                    'status' => 'draft',
                ]);

            $semester->academicYear->update([
                'is_active' => true,
                'status' => 'active',
            ]);

            $semester->update([
                'is_active' => true,
                'status' => 'active',
            ]);
        });

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'Semester aktif sistem berhasil diperbarui.');
    }

    public function lockSemester(Request $request, Semester $semester): RedirectResponse
    {
        $semester->update([
            'is_locked' => true,
            'status' => 'locked',
            'locked_at' => now(),
            'locked_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'Semester berhasil dikunci.');
    }

    public function editSemester(Semester $semester): View
    {
        if ($semester->is_locked) {
            abort(403, 'Semester yang sudah dikunci tidak bisa diedit.');
        }

        return view('admin.academic-years.semesters.edit', [
            'semester' => $semester,
        ]);
    }

    public function updateSemester(Request $request, Semester $semester): RedirectResponse
    {
        if ($semester->is_locked) {
            throw ValidationException::withMessages([
                'code' => 'Semester yang sudah dikunci tidak bisa diedit.',
            ]);
        }

        $validated = $this->validateSemesterUpdate($request, $semester);

        $semester->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'semester_type' => $validated['semester_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return redirect()
            ->route('admin.academic-years.index')
            ->with('success', 'Semester berhasil diperbarui.');
    }

    /**
     * Validasi input tahun ajaran.
     *
     * @return array<string, mixed>
     */
    private function validateAcademicYear(Request $request): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:academic_years,code',
            ],
            'name' => [
                'required',
                'string',
                'max:30',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
            'ganjil_start_date' => [
                'required',
                'date',
            ],
            'ganjil_end_date' => [
                'required',
                'date',
                'after:ganjil_start_date',
            ],
            'genap_start_date' => [
                'required',
                'date',
            ],
            'genap_end_date' => [
                'required',
                'date',
                'after:genap_start_date',
            ],
        ]);
    }

    /**
     * Validasi input edit tahun ajaran.
     *
     * @return array<string, mixed>
     */
    private function validateAcademicYearUpdate(
        Request $request,
        AcademicYear $academicYear
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_years', 'code')->ignore($academicYear->id),
            ],
            'name' => [
                'required',
                'string',
                'max:30',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
        ]);
    }


    /**
     * Validasi input edit semester.
     *
     * @return array<string, mixed>
     */
    private function validateSemesterUpdate(
        Request $request,
        Semester $semester
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('semesters', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'academic_year_id',
                            $semester->academic_year_id
                        )
                    )
                    ->ignore($semester->id),
            ],
            'name' => [
                'required',
                'string',
                'max:50',
            ],
            'semester_type' => [
                'required',
                'in:ganjil,genap',
                Rule::unique('semesters', 'semester_type')
                    ->where(
                        fn ($query) => $query->where(
                            'academic_year_id',
                            $semester->academic_year_id
                        )
                    )
                    ->ignore($semester->id),
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
        ]);
    }
}
