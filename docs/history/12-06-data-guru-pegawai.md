# Tahap 12.6 — Data Guru dan Pegawai

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 27625–29021.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.6: Data Guru dan Pegawai
Pada tahap ini kita mulai membuat data Guru dan Tenaga Kependidikan.
Kita tidak akan menyimpan biodata guru langsung di tabel employees. Biodata tetap berada di tabel people yang sudah kita buat sejak awal.
Strukturnya:
people
  ↓
employees
  ↓
users
Maknanya:
people     = identitas manusia
employees  = status kepegawaian di madrasah
users      = akun login aplikasi
Contoh:
Nama lengkap, NIK, tanggal lahir, email, telepon → people
NIP, NUPTK, jenis pegawai, jabatan, status aktif → employees
username, password, role, permission → users
Ini penting agar data identitas tidak ganda.

Tahap 12.6A: Migration dan Model Employee
1. Buat model dan migration
Jalankan:
php artisan make:model Employee -m

2. Isi migration employees
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_employees_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel guru dan pegawai.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('people')
                ->restrictOnDelete();

            $table->string('employee_number', 50)
                ->nullable()
                ->unique();

            $table->string('nip', 50)
                ->nullable()
                ->unique();

            $table->string('nuptk', 50)
                ->nullable()
                ->unique();

            $table->string('employee_type', 30);
            $table->string('employment_status', 50)->nullable();
            $table->string('position', 100)->nullable();

            $table->date('join_date')->nullable();
            $table->date('end_date')->nullable();

            $table->string('education_level', 50)->nullable();
            $table->string('major', 100)->nullable();

            $table->boolean('is_teacher')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique('person_id');

            $table->index('employee_type');
            $table->index('employment_status');
            $table->index('position');
            $table->index('is_teacher');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel guru dan pegawai.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
Mengapa person_id dibuat unique?
Karena satu orang cukup punya satu data kepegawaian aktif di madrasah.
Jika nanti ada riwayat jabatan, riwayat mutasi, atau riwayat penugasan, kita buat tabel terpisah. Kita tidak menimpa data pokok.

3. Isi model Employee
Buka:
nano app/Models/Employee.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'employee_number',
        'nip',
        'nuptk',
        'employee_type',
        'employment_status',
        'position',
        'join_date',
        'end_date',
        'education_level',
        'major',
        'is_teacher',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'end_date' => 'date',
            'is_teacher' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}

Tahap 12.6B: Tambahkan Relasi ke Model Person
Buka:
nano app/Models/Person.php
Tambahkan import:
use Illuminate\Database\Eloquent\Relations\HasOne;
Tambahkan method ini di dalam class Person:
public function employee(): HasOne
{
    return $this->hasOne(Employee::class);
}
Jika model Person belum memiliki import Employee, tidak perlu ditambahkan karena berada dalam namespace yang sama App\Models.

Tahap 12.6C: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'employees.view',
    'module' => 'employees',
    'action' => 'view',
    'display_name' => 'Melihat Guru dan Pegawai',
    'description' => 'Melihat data guru dan tenaga kependidikan.',
],
[
    'name' => 'employees.create',
    'module' => 'employees',
    'action' => 'create',
    'display_name' => 'Membuat Guru dan Pegawai',
    'description' => 'Menambahkan data guru dan tenaga kependidikan.',
],
[
    'name' => 'employees.update',
    'module' => 'employees',
    'action' => 'update',
    'display_name' => 'Mengubah Guru dan Pegawai',
    'description' => 'Mengubah data guru dan tenaga kependidikan.',
],

Tahap 12.6D: Seeder Guru dan Pegawai Awal
Buat seeder:
php artisan make:seeder EmployeeSeeder
Buka:
nano database/seeders/EmployeeSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Membuat data guru dan pegawai awal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $employees = [
                [
                    'full_name' => 'Kepala Madrasah',
                    'email' => 'kepala@sim-madrasah.test',
                    'employee_number' => 'EMP-001',
                    'employee_type' => 'teacher',
                    'employment_status' => 'permanent',
                    'position' => 'Kepala Madrasah',
                    'is_teacher' => true,
                ],
                [
                    'full_name' => 'Guru Mata Pelajaran',
                    'email' => 'guru@sim-madrasah.test',
                    'employee_number' => 'EMP-002',
                    'employee_type' => 'teacher',
                    'employment_status' => 'permanent',
                    'position' => 'Guru Mata Pelajaran',
                    'is_teacher' => true,
                ],
                [
                    'full_name' => 'Tata Usaha',
                    'email' => 'tu@sim-madrasah.test',
                    'employee_number' => 'EMP-003',
                    'employee_type' => 'staff',
                    'employment_status' => 'permanent',
                    'position' => 'Tata Usaha',
                    'is_teacher' => false,
                ],
            ];

            foreach ($employees as $employeeData) {
                $person = Person::query()->updateOrCreate(
                    [
                        'email' => $employeeData['email'],
                    ],
                    [
                        'full_name' => $employeeData['full_name'],
                        'email' => $employeeData['email'],
                    ]
                );

                Employee::query()->updateOrCreate(
                    [
                        'person_id' => $person->id,
                    ],
                    [
                        'employee_number' => $employeeData['employee_number'],
                        'nip' => null,
                        'nuptk' => null,
                        'employee_type' => $employeeData['employee_type'],
                        'employment_status' => $employeeData['employment_status'],
                        'position' => $employeeData['position'],
                        'join_date' => now()->toDateString(),
                        'end_date' => null,
                        'education_level' => null,
                        'major' => null,
                        'is_teacher' => $employeeData['is_teacher'],
                        'is_active' => true,
                        'notes' => null,
                    ]
                );
            }
        });
    }
}

Tahap 12.6E: Controller Guru dan Pegawai
Buat controller:
php artisan make:controller Admin/EmployeeController
Buka:
nano app/Http/Controllers/Admin/EmployeeController.php
Isi:
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
            ->with('person')
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

Tahap 12.6F: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\EmployeeController;
Di dalam group admin, tambahkan:
Route::get('/employees', [EmployeeController::class, 'index'])
    ->middleware('permission:employees.view')
    ->name('employees.index');

Route::get('/employees/create', [EmployeeController::class, 'create'])
    ->middleware('permission:employees.create')
    ->name('employees.create');

Route::post('/employees', [EmployeeController::class, 'store'])
    ->middleware('permission:employees.create')
    ->name('employees.store');

Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])
    ->middleware('permission:employees.update')
    ->name('employees.edit');

Route::put('/employees/{employee}', [EmployeeController::class, 'update'])
    ->middleware('permission:employees.update')
    ->name('employees.update');

Tahap 12.6G: View Guru dan Pegawai
Buat folder:
mkdir -p resources/views/admin/employees/partials
1. Index
Buat file:
nano resources/views/admin/employees/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Guru dan Pegawai
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data guru dan tenaga kependidikan madrasah.
                </p>
            </div>

            @can('permission', 'employees.create')
                <a
                    href="{{ route('admin.employees.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Guru/Pegawai
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nomor Pegawai</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Jenis</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Jabatan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $employee->person?->full_name }}
                                        </div>

                                        @if ($employee->is_teacher)
                                            <div class="mt-1 text-xs text-green-700">
                                                Guru
                                            </div>
                                        @else
                                            <div class="mt-1 text-xs text-gray-500">
                                                Tenaga Kependidikan
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $employee->employee_number ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $employee->employee_type }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $employee->position ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $employee->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $employee->person?->phone ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($employee->is_active)
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        @can('permission', 'employees.update')
                                            <a
                                                href="{{ route('admin.employees.edit', $employee) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada data guru atau pegawai.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

2. Create
Buat file:
nano resources/views/admin/employees/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Guru/Pegawai
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan data guru atau tenaga kependidikan.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.employees.partials.form', [
                'employee' => new \App\Models\Employee(),
                'person' => new \App\Models\Person(),
                'action' => route('admin.employees.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Data',
            ])
        </div>
    </div>
</x-app-layout>

3. Edit
Buat file:
nano resources/views/admin/employees/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Guru/Pegawai
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data guru atau tenaga kependidikan.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.employees.partials.form', [
                'employee' => $employee,
                'person' => $employee->person,
                'action' => route('admin.employees.update', $employee),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Data',
            ])
        </div>
    </div>
</x-app-layout>

4. Form partial
Buat file:
nano resources/views/admin/employees/partials/form.blade.php
Karena form ini cukup panjang, isi dengan kode berikut:
<form method="POST" action="{{ $action }}" class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="p-6 space-y-8">
        <div>
            <h3 class="font-semibold text-gray-900">Identitas Pribadi</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="full_name" value="Nama Lengkap" />

                    <x-text-input
                        id="full_name"
                        name="full_name"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('full_name', $person->full_name)"
                        required
                    />

                    <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="national_id_number" value="NIK" />

                    <x-text-input
                        id="national_id_number"
                        name="national_id_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('national_id_number', $person->national_id_number)"
                    />

                    <x-input-error :messages="$errors->get('national_id_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="birth_place" value="Tempat Lahir" />

                    <x-text-input
                        id="birth_place"
                        name="birth_place"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('birth_place', $person->birth_place)"
                    />
                </div>

                <div>
                    <x-input-label for="birth_date" value="Tanggal Lahir" />

                    <x-text-input
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('birth_date', $person->birth_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="gender" value="Jenis Kelamin" />

                    <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <option value="">Pilih</option>
                        <option value="male" @selected(old('gender', $person->gender) === 'male')>Laki-laki</option>
                        <option value="female" @selected(old('gender', $person->gender) === 'female')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <x-input-label for="religion" value="Agama" />

                    <x-text-input
                        id="religion"
                        name="religion"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('religion', $person->religion)"
                    />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />

                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        class="mt-1 block w-full"
                        :value="old('email', $person->email)"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="phone" value="Telepon" />

                    <x-text-input
                        id="phone"
                        name="phone"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('phone', $person->phone)"
                    />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="address" value="Alamat" />

                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >{{ old('address', $person->address) }}</textarea>
            </div>
        </div>

        <div>
            <h3 class="font-semibold text-gray-900">Data Kepegawaian</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="employee_number" value="Nomor Pegawai" />

                    <x-text-input
                        id="employee_number"
                        name="employee_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('employee_number', $employee->employee_number)"
                    />

                    <x-input-error :messages="$errors->get('employee_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nip" value="NIP" />

                    <x-text-input
                        id="nip"
                        name="nip"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('nip', $employee->nip)"
                    />
                </div>

                <div>
                    <x-input-label for="nuptk" value="NUPTK" />

                    <x-text-input
                        id="nuptk"
                        name="nuptk"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('nuptk', $employee->nuptk)"
                    />
                </div>

                <div>
                    <x-input-label for="employee_type" value="Jenis Pegawai" />

                    <select
                        id="employee_type"
                        name="employee_type"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        required
                    >
                        <option value="teacher" @selected(old('employee_type', $employee->employee_type ?: 'teacher') === 'teacher')>
                            Guru
                        </option>
                        <option value="staff" @selected(old('employee_type', $employee->employee_type) === 'staff')>
                            Tenaga Kependidikan
                        </option>
                    </select>
                </div>

                <div>
                    <x-input-label for="employment_status" value="Status Kepegawaian" />

                    <x-text-input
                        id="employment_status"
                        name="employment_status"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('employment_status', $employee->employment_status)"
                        placeholder="permanent / contract / honorary"
                    />
                </div>

                <div>
                    <x-input-label for="position" value="Jabatan" />

                    <x-text-input
                        id="position"
                        name="position"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('position', $employee->position)"
                        placeholder="Guru Mata Pelajaran"
                    />
                </div>

                <div>
                    <x-input-label for="join_date" value="Tanggal Mulai" />

                    <x-text-input
                        id="join_date"
                        name="join_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('join_date', $employee->join_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="end_date" value="Tanggal Selesai" />

                    <x-text-input
                        id="end_date"
                        name="end_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('end_date', $employee->end_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="education_level" value="Pendidikan Terakhir" />

                    <x-text-input
                        id="education_level"
                        name="education_level"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('education_level', $employee->education_level)"
                        placeholder="S1 / S2"
                    />
                </div>

                <div>
                    <x-input-label for="major" value="Jurusan" />

                    <x-text-input
                        id="major"
                        name="major"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('major', $employee->major)"
                    />
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="notes" value="Catatan" />

                <textarea
                    id="notes"
                    name="notes"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >{{ old('notes', $employee->notes) }}</textarea>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center gap-2">
                    <input
                        name="is_teacher"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_teacher', $employee->is_teacher))
                    >

                    <span class="text-sm text-gray-700">
                        Termasuk guru
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_active', $employee->is_active ?? true))
                    >

                    <span class="text-sm text-gray-700">
                        Aktif
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.employees.index') }}"
            class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        >
            Batal
        </a>

        <button
            type="submit"
            class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
        >
            {{ $buttonLabel }}
        </button>
    </div>
</form>

Tahap 12.6H: Sidebar dan Dashboard Shortcut
1. Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Tambahkan link:
@can('permission', 'employees.view')
    <x-ui.sidebar-link
        :href="route('admin.employees.index')"
        :active="request()->routeIs('admin.employees.*')"
    >
        Guru dan Pegawai
    </x-ui.sidebar-link>
@endcan
Jika kondisi section belum memeriksa permission ini, tambahkan:
auth()->user()?->can('permission', 'employees.view') ||

2. Dashboard shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Guru dan Pegawai',
    'description' => 'Kelola data guru, tenaga kependidikan, jabatan, dan status kepegawaian.',
    'href' => route('admin.employees.index'),
    'label' => 'Buka Pegawai',
],

Tahap 12.6I: Test Guru dan Pegawai
Buat test:
php artisan make:test Admin/EmployeeTest
Buka:
nano tests/Feature/Admin/EmployeeTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_employee_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'employees.view');

        $person = Person::create([
            'full_name' => 'Guru Test',
            'email' => 'guru@test.local',
        ]);

        Employee::create([
            'person_id' => $person->id,
            'employee_number' => 'EMP-001',
            'employee_type' => 'teacher',
            'position' => 'Guru Mata Pelajaran',
            'is_teacher' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/employees');

        $response
            ->assertStatus(200)
            ->assertSee('Guru dan Pegawai')
            ->assertSee('Guru Test');
    }

    public function test_user_can_create_employee(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'employees.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/employees', [
                'full_name' => 'Guru Baru',
                'national_id_number' => '1234567890123456',
                'birth_place' => 'Lombok Timur',
                'birth_date' => '1990-01-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'guru.baru@test.local',
                'phone' => '08123456789',
                'address' => 'Alamat guru',
                'employee_number' => 'EMP-002',
                'nip' => null,
                'nuptk' => null,
                'employee_type' => 'teacher',
                'employment_status' => 'permanent',
                'position' => 'Guru Mata Pelajaran',
                'join_date' => '2026-07-01',
                'end_date' => null,
                'education_level' => 'S1',
                'major' => 'Pendidikan Matematika',
                'is_teacher' => '1',
                'is_active' => '1',
                'notes' => 'Data awal.',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('people', [
            'full_name' => 'Guru Baru',
            'email' => 'guru.baru@test.local',
        ]);

        $this->assertDatabaseHas('employees', [
            'employee_number' => 'EMP-002',
            'employee_type' => 'teacher',
            'position' => 'Guru Mata Pelajaran',
            'is_teacher' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_employee(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'employees.update');

        $person = Person::create([
            'full_name' => 'Guru Lama',
            'email' => 'guru.lama@test.local',
        ]);

        $employee = Employee::create([
            'person_id' => $person->id,
            'employee_number' => 'EMP-003',
            'employee_type' => 'teacher',
            'position' => 'Guru',
            'is_teacher' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.employees.update', $employee), [
                'full_name' => 'Guru Revisi',
                'national_id_number' => null,
                'birth_place' => null,
                'birth_date' => null,
                'gender' => 'female',
                'religion' => 'Islam',
                'email' => 'guru.revisi@test.local',
                'phone' => '0811111111',
                'address' => 'Alamat revisi',
                'employee_number' => 'EMP-003',
                'nip' => null,
                'nuptk' => null,
                'employee_type' => 'teacher',
                'employment_status' => 'permanent',
                'position' => 'Wali Kelas',
                'join_date' => '2026-07-01',
                'end_date' => null,
                'education_level' => 'S1',
                'major' => 'Pendidikan Bahasa Indonesia',
                'is_teacher' => '1',
                'is_active' => '1',
                'notes' => 'Revisi.',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'full_name' => 'Guru Revisi',
            'email' => 'guru.revisi@test.local',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'position' => 'Wali Kelas',
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_employee_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Employee Role',
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

Tahap 12.6J: Jalankan Migration, Seeder, Test, Build
Jalankan:
php artisan migrate
php artisan db:seed --class=EmployeeSeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=employees
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/employees
GET|HEAD  admin/employees/create
POST      admin/employees
GET|HEAD  admin/employees/{employee}/edit
PUT       admin/employees/{employee}
Target test:
87+ tests passed
build berhasil

Tahap 12.6K: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/Employee.php \
        app/Models/Person.php \
        app/Http/Controllers/Admin/EmployeeController.php \
        database/migrations/ \
        database/seeders/EmployeeSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/employees/ \
        resources/views/layouts/sidebar.blade.php \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/EmployeeTest.php
Commit:
git commit -m "feat: add employee module"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean
Kirim hasil:
php artisan test
npm run build
git status
Setelah berhasil, kita lanjut ke Tahap 12.7: Akun Guru dan Pegawai dari Data Employee.
















