<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()
            ->with('person.user')
            ->orderByDesc('is_active')
            ->orderBy('employee_type')
            ->orderBy('position')
            ->paginate(15);

        return view('admin.employees.index', [
            'employees' => $employees,
        ]);
    }

    public function create(): View
    {
        return view('admin.employees.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEmployee($request);

        DB::transaction(function () use ($validated): void {
            $person = Person::create([
                'national_id_number' => $validated['national_id_number'] ?? null,
                'full_name' => $validated['full_name'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            Employee::create([
                'person_id' => $person->id,
                'employee_number' => $validated['employee_number'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'nuptk' => $validated['nuptk'] ?? null,
                'employee_type' => $validated['employee_type'],
                'employment_status' => $validated['employment_status'] ?? null,
                'position' => $validated['position'] ?? null,
                'join_date' => $validated['join_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'major' => $validated['major'] ?? null,
                'is_teacher' => $validated['is_teacher'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data guru atau pegawai berhasil dibuat.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load('person');

        return view('admin.employees.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $validated = $this->validateEmployee($request, $employee);

        DB::transaction(function () use ($validated, $employee): void {
            $employee->person->update([
                'national_id_number' => $validated['national_id_number'] ?? null,
                'full_name' => $validated['full_name'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'religion' => $validated['religion'] ?? null,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            $employee->update([
                'employee_number' => $validated['employee_number'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'nuptk' => $validated['nuptk'] ?? null,
                'employee_type' => $validated['employee_type'],
                'employment_status' => $validated['employment_status'] ?? null,
                'position' => $validated['position'] ?? null,
                'join_date' => $validated['join_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'major' => $validated['major'] ?? null,
                'is_teacher' => $validated['is_teacher'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.employees.index')
            ->with('success', 'Data guru atau pegawai berhasil diperbarui.');
    }

    /**
     * Validasi guru dan pegawai.
     *
     * @return array<string, mixed>
     */
    private function validateEmployee(
        Request $request,
        ?Employee $employee = null
    ): array {
        $person = $employee?->person;

        $validated = $request->validate([
            'national_id_number' => [
                'nullable',
                'string',
                'max:32',
                Rule::unique('people', 'national_id_number')
                    ->ignore($person),
            ],
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],
            'birth_place' => [
                'nullable',
                'string',
                'max:100',
            ],
            'birth_date' => [
                'nullable',
                'date',
            ],
            'gender' => [
                'nullable',
                'string',
                'max:20',
            ],
            'religion' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:191',
                Rule::unique('people', 'email')
                    ->ignore($person),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'address' => [
                'nullable',
                'string',
            ],
            'employee_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_number')
                    ->ignore($employee),
            ],
            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employees', 'nip')
                    ->ignore($employee),
            ],
            'nuptk' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employees', 'nuptk')
                    ->ignore($employee),
            ],
            'employee_type' => [
                'required',
                'string',
                'max:30',
            ],
            'employment_status' => [
                'nullable',
                'string',
                'max:50',
            ],
            'position' => [
                'nullable',
                'string',
                'max:100',
            ],
            'join_date' => [
                'nullable',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:join_date',
            ],
            'education_level' => [
                'nullable',
                'string',
                'max:50',
            ],
            'major' => [
                'nullable',
                'string',
                'max:100',
            ],
            'is_teacher' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['is_teacher'] = $request->boolean('is_teacher');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
