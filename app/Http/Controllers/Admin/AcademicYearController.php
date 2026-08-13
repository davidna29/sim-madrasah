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
}
