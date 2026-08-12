# Tahap 12.8 — Data Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 29735–31148.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.8: Data Siswa
Pada tahap ini kita membuat data master Siswa.
Prinsip penting:
Jangan menyimpan kelas langsung di tabel students.
Jangan update kelas siswa di data master siswa.
Kelas siswa nanti disimpan di tabel riwayat kelas.
Saat naik kelas, buat record riwayat baru.
Struktur dasarnya:
people
  ↓
students
  ↓
student_class_histories nanti
Maknanya:
people    = identitas orang
students  = identitas kesiswaan
riwayat   = kelas, tahun ajaran, semester, status penempatan
Tahap ini hanya membuat:
students
CRUD data siswa
Riwayat kelas siswa dibuat pada tahap berikutnya.

Tahap 12.8A: Migration dan Model Student
1. Buat model dan migration
Jalankan:
php artisan make:model Student -m
2. Isi migration students
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_students_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel siswa.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->foreignId('person_id')
                ->constrained('people')
                ->restrictOnDelete();

            $table->foreignId('admission_academic_year_id')
                ->nullable()
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->string('student_number', 50)
                ->nullable()
                ->unique();

            $table->string('nisn', 50)
                ->nullable()
                ->unique();

            $table->string('registration_number', 50)
                ->nullable()
                ->unique();

            $table->date('admission_date')->nullable();
            $table->date('graduation_date')->nullable();

            $table->string('status', 30)->default('active');

            $table->string('previous_school', 150)->nullable();
            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('person_id');

            $table->index('admission_academic_year_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel siswa.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
Mengapa person_id dibuat unique?
Karena satu orang hanya boleh memiliki satu data siswa utama.
Riwayat kelas, pindah kelas, naik kelas, lulus, dan alumni nanti disimpan di tabel lain. Data pokok siswa tidak boleh ditimpa.

Tahap 12.8B: Model Student
Buka:
nano app/Models/Student.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'admission_academic_year_id',
        'student_number',
        'nisn',
        'registration_number',
        'admission_date',
        'graduation_date',
        'status',
        'previous_school',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'date',
            'graduation_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function admissionAcademicYear(): BelongsTo
    {
        return $this->belongsTo(
            AcademicYear::class,
            'admission_academic_year_id'
        );
    }
}

Tahap 12.8C: Tambahkan Relasi ke Person
Buka:
nano app/Models/Person.php
Pastikan import ini ada:
use Illuminate\Database\Eloquent\Relations\HasOne;
Tambahkan method ini di dalam class Person:
public function student(): HasOne
{
    return $this->hasOne(Student::class);
}
Jika HasOne sudah ada dari tahap employee, jangan tambahkan dua kali.

Tahap 12.8D: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'students.view',
    'module' => 'students',
    'action' => 'view',
    'display_name' => 'Melihat Data Siswa',
    'description' => 'Melihat data master siswa.',
],
[
    'name' => 'students.create',
    'module' => 'students',
    'action' => 'create',
    'display_name' => 'Membuat Data Siswa',
    'description' => 'Menambahkan data master siswa.',
],
[
    'name' => 'students.update',
    'module' => 'students',
    'action' => 'update',
    'display_name' => 'Mengubah Data Siswa',
    'description' => 'Mengubah data master siswa.',
],

Tahap 12.8E: Seeder Siswa Awal
Buat seeder:
php artisan make:seeder StudentSeeder
Buka:
nano database/seeders/StudentSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Person;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Membuat data siswa awal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $academicYear = AcademicYear::query()
                ->where('code', '2026-2027')
                ->first();

            $students = [
                [
                    'full_name' => 'Ahmad Siswa',
                    'email' => 'ahmad.siswa@sim-madrasah.test',
                    'student_number' => 'SIS-001',
                    'nisn' => '1000000001',
                    'registration_number' => 'REG-001',
                ],
                [
                    'full_name' => 'Siti Siswa',
                    'email' => 'siti.siswa@sim-madrasah.test',
                    'student_number' => 'SIS-002',
                    'nisn' => '1000000002',
                    'registration_number' => 'REG-002',
                ],
                [
                    'full_name' => 'Muhammad Siswa',
                    'email' => 'muhammad.siswa@sim-madrasah.test',
                    'student_number' => 'SIS-003',
                    'nisn' => '1000000003',
                    'registration_number' => 'REG-003',
                ],
            ];

            foreach ($students as $studentData) {
                $person = Person::query()->updateOrCreate(
                    [
                        'email' => $studentData['email'],
                    ],
                    [
                        'full_name' => $studentData['full_name'],
                        'email' => $studentData['email'],
                    ]
                );

                Student::query()->updateOrCreate(
                    [
                        'person_id' => $person->id,
                    ],
                    [
                        'admission_academic_year_id' => $academicYear?->id,
                        'student_number' => $studentData['student_number'],
                        'nisn' => $studentData['nisn'],
                        'registration_number' => $studentData['registration_number'],
                        'admission_date' => '2026-07-01',
                        'graduation_date' => null,
                        'status' => 'active',
                        'previous_school' => null,
                        'notes' => null,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}

Tahap 12.8F: Controller Siswa
Buat controller:
php artisan make:controller Admin/StudentController
Buka:
nano app/Http/Controllers/Admin/StudentController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Person;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(): View
    {
        $students = Student::query()
            ->with([
                'person',
                'admissionAcademicYear',
            ])
            ->orderByDesc('is_active')
            ->orderBy('student_number')
            ->paginate(15);

        return view('admin.students.index', [
            'students' => $students,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStudent($request);

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

            Student::create([
                'person_id' => $person->id,
                'admission_academic_year_id' => $validated['admission_academic_year_id'] ?? null,
                'student_number' => $validated['student_number'] ?? null,
                'nisn' => $validated['nisn'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'graduation_date' => $validated['graduation_date'] ?? null,
                'status' => $validated['status'],
                'previous_school' => $validated['previous_school'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'],
            ]);
        });

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil dibuat.');
    }

    public function edit(Student $student): View
    {
        $student->load('person');

        return view('admin.students.edit', [
            'student' => $student,
        ] + $this->formData());
    }

    public function update(
        Request $request,
        Student $student
    ): RedirectResponse {
        $validated = $this->validateStudent($request, $student);

        DB::transaction(function () use ($validated, $student): void {
            $student->person->update([
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

            $student->update([
                'admission_academic_year_id' => $validated['admission_academic_year_id'] ?? null,
                'student_number' => $validated['student_number'] ?? null,
                'nisn' => $validated['nisn'] ?? null,
                'registration_number' => $validated['registration_number'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'graduation_date' => $validated['graduation_date'] ?? null,
                'status' => $validated['status'],
                'previous_school' => $validated['previous_school'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'is_active' => $validated['is_active'],
            ]);
        });

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    /**
     * Data pendukung form siswa.
     *
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'academicYears' => AcademicYear::query()
                ->orderByDesc('start_date')
                ->get(),
        ];
    }

    /**
     * Validasi data siswa.
     *
     * @return array<string, mixed>
     */
    private function validateStudent(
        Request $request,
        ?Student $student = null
    ): array {
        $person = $student?->person;

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
            'admission_academic_year_id' => [
                'nullable',
                'exists:academic_years,id',
            ],
            'student_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'student_number')
                    ->ignore($student),
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'nisn')
                    ->ignore($student),
            ],
            'registration_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('students', 'registration_number')
                    ->ignore($student),
            ],
            'admission_date' => [
                'nullable',
                'date',
            ],
            'graduation_date' => [
                'nullable',
                'date',
                'after_or_equal:admission_date',
            ],
            'status' => [
                'required',
                'in:active,inactive,transferred,graduated,alumni',
            ],
            'previous_school' => [
                'nullable',
                'string',
                'max:150',
            ],
            'notes' => [
                'nullable',
                'string',
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

Tahap 12.8G: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\StudentController;
Di dalam group admin, tambahkan:
Route::get('/students', [StudentController::class, 'index'])
    ->middleware('permission:students.view')
    ->name('students.index');

Route::get('/students/create', [StudentController::class, 'create'])
    ->middleware('permission:students.create')
    ->name('students.create');

Route::post('/students', [StudentController::class, 'store'])
    ->middleware('permission:students.create')
    ->name('students.store');

Route::get('/students/{student}/edit', [StudentController::class, 'edit'])
    ->middleware('permission:students.update')
    ->name('students.edit');

Route::put('/students/{student}', [StudentController::class, 'update'])
    ->middleware('permission:students.update')
    ->name('students.update');

Tahap 12.8H: View Siswa
Buat folder:
mkdir -p resources/views/admin/students/partials
1. Index
Buat file:
nano resources/views/admin/students/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Data Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data master siswa tanpa mengubah histori kelas.
                </p>
            </div>

            @can('permission', 'students.create')
                <a
                    href="{{ route('admin.students.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Siswa
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">NIS</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">NISN</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Masuk</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $student->person?->full_name }}
                                        </div>

                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ $student->registration_number ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $student->student_number ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 font-mono">
                                        {{ $student->nisn ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $student->admissionAcademicYear?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $student->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $student->person?->phone ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ $student->status }}
                                            </span>

                                            @if ($student->is_active)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @can('permission', 'students.update')
                                            <a
                                                href="{{ route('admin.students.edit', $student) }}"
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
                                        Belum ada data siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
2. Create
Buat file:
nano resources/views/admin/students/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Siswa
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan data master siswa baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.partials.form', [
                'student' => new \App\Models\Student(),
                'person' => new \App\Models\Person(),
                'action' => route('admin.students.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Siswa',
            ])
        </div>
    </div>
</x-app-layout>
3. Edit
Buat file:
nano resources/views/admin/students/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Siswa
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data master siswa.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.partials.form', [
                'student' => $student,
                'person' => $student->person,
                'action' => route('admin.students.update', $student),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Siswa',
            ])
        </div>
    </div>
</x-app-layout>
4. Form partial
Buat file:
nano resources/views/admin/students/partials/form.blade.php
Isi:
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

                    <select
                        id="gender"
                        name="gender"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    >
                        <option value="">Pilih</option>
                        <option value="male" @selected(old('gender', $person->gender) === 'male')>
                            Laki-laki
                        </option>
                        <option value="female" @selected(old('gender', $person->gender) === 'female')>
                            Perempuan
                        </option>
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
            <h3 class="font-semibold text-gray-900">Data Kesiswaan</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="student_number" value="NIS" />

                    <x-text-input
                        id="student_number"
                        name="student_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('student_number', $student->student_number)"
                    />

                    <x-input-error :messages="$errors->get('student_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="nisn" value="NISN" />

                    <x-text-input
                        id="nisn"
                        name="nisn"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('nisn', $student->nisn)"
                    />

                    <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="registration_number" value="Nomor Registrasi" />

                    <x-text-input
                        id="registration_number"
                        name="registration_number"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('registration_number', $student->registration_number)"
                    />

                    <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="admission_academic_year_id" value="Tahun Ajaran Masuk" />

                    <select
                        id="admission_academic_year_id"
                        name="admission_academic_year_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    >
                        <option value="">Pilih Tahun Ajaran</option>

                        @foreach ($academicYears as $academicYear)
                            <option
                                value="{{ $academicYear->id }}"
                                @selected((string) old('admission_academic_year_id', $student->admission_academic_year_id) === (string) $academicYear->id)
                            >
                                {{ $academicYear->name }}
                            </option>
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('admission_academic_year_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="admission_date" value="Tanggal Masuk" />

                    <x-text-input
                        id="admission_date"
                        name="admission_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('admission_date', $student->admission_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="graduation_date" value="Tanggal Lulus" />

                    <x-text-input
                        id="graduation_date"
                        name="graduation_date"
                        type="date"
                        class="mt-1 block w-full"
                        :value="old('graduation_date', $student->graduation_date?->format('Y-m-d'))"
                    />
                </div>

                <div>
                    <x-input-label for="status" value="Status Siswa" />

                    <select
                        id="status"
                        name="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        required
                    >
                        @foreach ([
                            'active' => 'Aktif',
                            'inactive' => 'Nonaktif',
                            'transferred' => 'Pindah',
                            'graduated' => 'Lulus',
                            'alumni' => 'Alumni',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('status', $student->status ?: 'active') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="previous_school" value="Sekolah Asal" />

                    <x-text-input
                        id="previous_school"
                        name="previous_school"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('previous_school', $student->previous_school)"
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
                >{{ old('notes', $student->notes) }}</textarea>
            </div>

            <div class="mt-6">
                <label class="flex items-center gap-2">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_active', $student->is_active ?? true))
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
            href="{{ route('admin.students.index') }}"
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

Tahap 12.8I: Sidebar dan Dashboard Shortcut
1. Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Tambahkan link:
@can('permission', 'students.view')
    <x-ui.sidebar-link
        :href="route('admin.students.index')"
        :active="request()->routeIs('admin.students.*')"
    >
        Data Siswa
    </x-ui.sidebar-link>
@endcan
Jika kondisi section belum memeriksa permission ini, tambahkan:
auth()->user()?->can('permission', 'students.view') ||
2. Dashboard shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Data Siswa',
    'description' => 'Kelola data master siswa, NIS, NISN, status siswa, dan tahun masuk.',
    'href' => route('admin.students.index'),
    'label' => 'Buka Siswa',
],

Tahap 12.8J: Test Data Siswa
Buat test:
php artisan make:test Admin/StudentTest
Buka:
nano tests/Feature/Admin/StudentTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.view');

        $academicYear = $this->createAcademicYear();

        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad@test.local',
        ]);

        Student::create([
            'person_id' => $person->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/students');

        $response
            ->assertStatus(200)
            ->assertSee('Data Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee('SIS-001');
    }

    public function test_user_can_create_student(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.create');

        $academicYear = $this->createAcademicYear();

        $response = $this
            ->actingAs($user)
            ->post('/admin/students', [
                'full_name' => 'Siti Siswa',
                'national_id_number' => '1234567890123456',
                'birth_place' => 'Lombok Timur',
                'birth_date' => '2012-01-01',
                'gender' => 'female',
                'religion' => 'Islam',
                'email' => 'siti@test.local',
                'phone' => '08123456789',
                'address' => 'Alamat siswa',
                'admission_academic_year_id' => $academicYear->id,
                'student_number' => 'SIS-002',
                'nisn' => '1000000002',
                'registration_number' => 'REG-002',
                'admission_date' => '2026-07-01',
                'graduation_date' => null,
                'status' => 'active',
                'previous_school' => 'SD Test',
                'notes' => 'Data awal.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('people', [
            'full_name' => 'Siti Siswa',
            'email' => 'siti@test.local',
        ]);

        $this->assertDatabaseHas('students', [
            'student_number' => 'SIS-002',
            'nisn' => '1000000002',
            'registration_number' => 'REG-002',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_student(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'students.update');

        $academicYear = $this->createAcademicYear();

        $person = Person::create([
            'full_name' => 'Siswa Lama',
            'email' => 'lama@test.local',
        ]);

        $student = Student::create([
            'person_id' => $person->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-003',
            'nisn' => '1000000003',
            'registration_number' => 'REG-003',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.students.update', $student), [
                'full_name' => 'Siswa Revisi',
                'national_id_number' => null,
                'birth_place' => null,
                'birth_date' => null,
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'revisi@test.local',
                'phone' => '0811111111',
                'address' => 'Alamat revisi',
                'admission_academic_year_id' => $academicYear->id,
                'student_number' => 'SIS-003',
                'nisn' => '1000000003',
                'registration_number' => 'REG-003',
                'admission_date' => '2026-07-01',
                'graduation_date' => null,
                'status' => 'active',
                'previous_school' => 'SD Revisi',
                'notes' => 'Revisi.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'full_name' => 'Siswa Revisi',
            'email' => 'revisi@test.local',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'previous_school' => 'SD Revisi',
        ]);
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

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Role',
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

Tahap 12.8K: Jalankan Migration, Seeder, Test, Build
Jalankan:
php artisan migrate
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=students
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/students
GET|HEAD  admin/students/create
POST      admin/students
GET|HEAD  admin/students/{student}/edit
PUT       admin/students/{student}
Target test:
93+ tests passed
build berhasil

Tahap 12.8L: Uji Manual
Jalankan:
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin, lalu buka:
open http://sim-madrasah.test/admin/students
Coba tambah siswa baru.
Contoh data:
Nama Lengkap: Ali Siswa
NIS: SIS-004
NISN: 1000000004
Nomor Registrasi: REG-004
Tahun Ajaran Masuk: 2026/2027
Tanggal Masuk: 2026-07-01
Status: Aktif
Sekolah Asal: SD Negeri Contoh
Pastikan data muncul di halaman daftar siswa.

Tahap 12.8M: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/Student.php \
        app/Models/Person.php \
        app/Http/Controllers/Admin/StudentController.php \
        database/migrations/ \
        database/seeders/StudentSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/ \
        resources/views/layouts/sidebar.blade.php \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/StudentTest.php
Commit:
git commit -m "feat: add student module"
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
Setelah berhasil, kita lanjut ke Tahap 12.9: Riwayat Kelas Siswa.














