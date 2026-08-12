# Tahap 12.11 — Data Orang Tua/Wali Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 33162–34611.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.11: Data Orang Tua/Wali Siswa
Modul ini menyimpan data orang tua atau wali yang terhubung ke siswa.
Struktur datanya:
people
  ↓
student_guardians
  ↓
students
Maknanya:
people             = identitas orang tua/wali
student_guardians = hubungan orang tua/wali dengan siswa
students           = data siswa
Satu orang tua bisa punya lebih dari satu anak. Jadi data orang tua tidak boleh langsung dimasukkan ke tabel students.

Tahap 12.11A: Model dan Migration
Jalankan:
php artisan make:model StudentGuardian -m
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_student_guardians_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel orang tua atau wali siswa.
     */
    public function up(): void
    {
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('person_id')
                ->constrained('people')
                ->restrictOnDelete();

            $table->string('relationship', 30);
            $table->string('occupation', 100)->nullable();
            $table->string('education_level', 50)->nullable();
            $table->string('income_range', 50)->nullable();

            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_financial_responsible')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'person_id']);

            $table->index('relationship');
            $table->index('is_primary_contact');
            $table->index('is_emergency_contact');
            $table->index('is_financial_responsible');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel orang tua atau wali siswa.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_guardians');
    }
};

Tahap 12.11B: Model StudentGuardian
Buka:
nano app/Models/StudentGuardian.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentGuardian extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'person_id',
        'relationship',
        'occupation',
        'education_level',
        'income_range',
        'is_primary_contact',
        'is_emergency_contact',
        'is_financial_responsible',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary_contact' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'is_financial_responsible' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}

Tahap 12.11C: Tambahkan Relasi ke Model Student
Buka:
nano app/Models/Student.php
Pastikan import ini ada:
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
Tambahkan method ini di dalam class Student:
public function guardians(): HasMany
{
    return $this->hasMany(StudentGuardian::class);
}

public function primaryGuardian(): HasOne
{
    return $this->hasOne(StudentGuardian::class)
        ->where('is_primary_contact', true);
}

Tahap 12.11D: Tambahkan Relasi ke Model Person
Buka:
nano app/Models/Person.php
Jika belum ada, tambahkan import:
use Illuminate\Database\Eloquent\Relations\HasMany;
Tambahkan method:
public function studentGuardianLinks(): HasMany
{
    return $this->hasMany(StudentGuardian::class);
}

Tahap 12.11E: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'student_guardians.view',
    'module' => 'student_guardians',
    'action' => 'view',
    'display_name' => 'Melihat Orang Tua/Wali Siswa',
    'description' => 'Melihat data orang tua atau wali siswa.',
],
[
    'name' => 'student_guardians.create',
    'module' => 'student_guardians',
    'action' => 'create',
    'display_name' => 'Membuat Orang Tua/Wali Siswa',
    'description' => 'Menambahkan data orang tua atau wali siswa.',
],
[
    'name' => 'student_guardians.update',
    'module' => 'student_guardians',
    'action' => 'update',
    'display_name' => 'Mengubah Orang Tua/Wali Siswa',
    'description' => 'Mengubah data orang tua atau wali siswa.',
],

Tahap 12.11F: Seeder Orang Tua/Wali Siswa
Buat seeder:
php artisan make:seeder StudentGuardianSeeder
Buka:
nano database/seeders/StudentGuardianSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentGuardianSeeder extends Seeder
{
    /**
     * Membuat data awal orang tua atau wali siswa.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $students = Student::query()
                ->with('person')
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                $baseName = Str::slug(
                    $student->person?->full_name ?? 'siswa-'.$student->id,
                    '.'
                );

                $guardianPerson = Person::query()->updateOrCreate(
                    [
                        'email' => 'wali.'.$baseName.'@sim-madrasah.test',
                    ],
                    [
                        'full_name' => 'Wali '.$student->person?->full_name,
                        'email' => 'wali.'.$baseName.'@sim-madrasah.test',
                        'phone' => '0800000000',
                        'address' => $student->person?->address,
                    ]
                );

                StudentGuardian::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'person_id' => $guardianPerson->id,
                    ],
                    [
                        'relationship' => 'guardian',
                        'occupation' => null,
                        'education_level' => null,
                        'income_range' => null,
                        'is_primary_contact' => true,
                        'is_emergency_contact' => true,
                        'is_financial_responsible' => true,
                        'is_active' => true,
                        'notes' => null,
                    ]
                );
            }
        });
    }
}

Tahap 12.11G: Controller Orang Tua/Wali Siswa
Buat controller:
php artisan make:controller Admin/StudentGuardianController
Buka:
nano app/Http/Controllers/Admin/StudentGuardianController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Student;
use App\Models\StudentGuardian;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentGuardianController extends Controller
{
    public function index(Student $student): View
    {
        $student->load('person');

        $guardians = $student->guardians()
            ->with('person')
            ->orderByDesc('is_primary_contact')
            ->orderBy('relationship')
            ->paginate(15);

        return view('admin.students.guardians.index', [
            'student' => $student,
            'guardians' => $guardians,
        ]);
    }

    public function create(Student $student): View
    {
        $student->load('person');

        return view('admin.students.guardians.create', [
            'student' => $student,
            'guardian' => new StudentGuardian(),
            'person' => new Person(),
        ]);
    }

    public function store(
        Request $request,
        Student $student
    ): RedirectResponse {
        $validated = $this->validateGuardian($request);

        DB::transaction(function () use ($validated, $student): void {
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

            if ($validated['is_primary_contact']) {
                $student->guardians()->update([
                    'is_primary_contact' => false,
                ]);
            }

            StudentGuardian::create([
                'student_id' => $student->id,
                'person_id' => $person->id,
                'relationship' => $validated['relationship'],
                'occupation' => $validated['occupation'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'income_range' => $validated['income_range'] ?? null,
                'is_primary_contact' => $validated['is_primary_contact'],
                'is_emergency_contact' => $validated['is_emergency_contact'],
                'is_financial_responsible' => $validated['is_financial_responsible'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.guardians.index', $student)
            ->with('success', 'Data orang tua atau wali siswa berhasil dibuat.');
    }

    public function edit(
        Student $student,
        StudentGuardian $guardian
    ): View {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $student->load('person');
        $guardian->load('person');

        return view('admin.students.guardians.edit', [
            'student' => $student,
            'guardian' => $guardian,
            'person' => $guardian->person,
        ]);
    }

    public function update(
        Request $request,
        Student $student,
        StudentGuardian $guardian
    ): RedirectResponse {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $guardian->load('person');

        $validated = $this->validateGuardian($request, $guardian);

        DB::transaction(function () use ($validated, $student, $guardian): void {
            $guardian->person->update([
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

            if ($validated['is_primary_contact']) {
                $student->guardians()
                    ->whereKeyNot($guardian->id)
                    ->update([
                        'is_primary_contact' => false,
                    ]);
            }

            $guardian->update([
                'relationship' => $validated['relationship'],
                'occupation' => $validated['occupation'] ?? null,
                'education_level' => $validated['education_level'] ?? null,
                'income_range' => $validated['income_range'] ?? null,
                'is_primary_contact' => $validated['is_primary_contact'],
                'is_emergency_contact' => $validated['is_emergency_contact'],
                'is_financial_responsible' => $validated['is_financial_responsible'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.guardians.index', $student)
            ->with('success', 'Data orang tua atau wali siswa berhasil diperbarui.');
    }

    private function ensureGuardianBelongsToStudent(
        Student $student,
        StudentGuardian $guardian
    ): void {
        abort_unless(
            (int) $guardian->student_id === (int) $student->id,
            404
        );
    }

    /**
     * Validasi orang tua atau wali siswa.
     *
     * @return array<string, mixed>
     */
    private function validateGuardian(
        Request $request,
        ?StudentGuardian $guardian = null
    ): array {
        $person = $guardian?->person;

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
            'relationship' => [
                'required',
                'in:father,mother,guardian,other',
            ],
            'occupation' => [
                'nullable',
                'string',
                'max:100',
            ],
            'education_level' => [
                'nullable',
                'string',
                'max:50',
            ],
            'income_range' => [
                'nullable',
                'string',
                'max:50',
            ],
            'is_primary_contact' => [
                'nullable',
                'boolean',
            ],
            'is_emergency_contact' => [
                'nullable',
                'boolean',
            ],
            'is_financial_responsible' => [
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

        $validated['is_primary_contact'] = $request->boolean('is_primary_contact');
        $validated['is_emergency_contact'] = $request->boolean('is_emergency_contact');
        $validated['is_financial_responsible'] = $request->boolean('is_financial_responsible');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}

Tahap 12.11H: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\StudentGuardianController;
Di dalam group admin, tambahkan setelah route students:
Route::get('/students/{student}/guardians', [StudentGuardianController::class, 'index'])
    ->middleware('permission:student_guardians.view')
    ->name('students.guardians.index');

Route::get('/students/{student}/guardians/create', [StudentGuardianController::class, 'create'])
    ->middleware('permission:student_guardians.create')
    ->name('students.guardians.create');

Route::post('/students/{student}/guardians', [StudentGuardianController::class, 'store'])
    ->middleware('permission:student_guardians.create')
    ->name('students.guardians.store');

Route::get('/students/{student}/guardians/{guardian}/edit', [StudentGuardianController::class, 'edit'])
    ->middleware('permission:student_guardians.update')
    ->name('students.guardians.edit');

Route::put('/students/{student}/guardians/{guardian}', [StudentGuardianController::class, 'update'])
    ->middleware('permission:student_guardians.update')
    ->name('students.guardians.update');

Tahap 12.11I: Update View Daftar Siswa
Buka:
nano resources/views/admin/students/index.blade.php
Pada bagian aksi siswa, tambahkan link berikut di dalam <div class="flex flex-wrap gap-3">:
@can('permission', 'student_guardians.view')
    <a
        href="{{ route('admin.students.guardians.index', $student) }}"
        class="text-sm font-medium text-purple-700 hover:text-purple-900"
    >
        Wali
    </a>
@endcan
Aksi siswa nanti berisi:
Edit
Riwayat Kelas
Wali

Tahap 12.11J: View Orang Tua/Wali Siswa
Buat folder:
mkdir -p resources/views/admin/students/guardians/partials
1. Index
Buat file:
nano resources/views/admin/students/guardians/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Orang Tua/Wali Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->person?->full_name }}
                </p>
            </div>

            @can('permission', 'student_guardians.create')
                <a
                    href="{{ route('admin.students.guardians.create', $student) }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Wali
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900">
                        Data Siswa
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                        <div>
                            <div class="text-gray-500">Nama</div>
                            <div class="font-medium text-gray-900">
                                {{ $student->person?->full_name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">NIS</div>
                            <div class="font-medium text-gray-900">
                                {{ $student->student_number ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">NISN</div>
                            <div class="font-medium text-gray-900">
                                {{ $student->nisn ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Hubungan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Pekerjaan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Peran</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($guardians as $guardian)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $guardian->person?->full_name }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $guardian->person?->national_id_number ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $guardian->relationship }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div>{{ $guardian->person?->email ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $guardian->person?->phone ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $guardian->occupation ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($guardian->is_primary_contact)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Kontak Utama
                                                </span>
                                            @endif

                                            @if ($guardian->is_emergency_contact)
                                                <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                    Darurat
                                                </span>
                                            @endif

                                            @if ($guardian->is_financial_responsible)
                                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                                    Keuangan
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($guardian->is_active)
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
                                        @can('permission', 'student_guardians.update')
                                            <a
                                                href="{{ route('admin.students.guardians.edit', [$student, $guardian]) }}"
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
                                        Belum ada data orang tua atau wali.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $guardians->links() }}
                    </div>
                </div>
            </div>

            <div>
                <a
                    href="{{ route('admin.students.index') }}"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    Kembali ke Data Siswa
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

2. Create
Buat file:
nano resources/views/admin/students/guardians/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Orang Tua/Wali
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $student->person?->full_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.guardians.partials.form', [
                'student' => $student,
                'guardian' => $guardian,
                'person' => $person,
                'action' => route('admin.students.guardians.store', $student),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Wali',
            ])
        </div>
    </div>
</x-app-layout>

3. Edit
Buat file:
nano resources/views/admin/students/guardians/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Orang Tua/Wali
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $student->person?->full_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @include('admin.students.guardians.partials.form', [
                'student' => $student,
                'guardian' => $guardian,
                'person' => $person,
                'action' => route('admin.students.guardians.update', [$student, $guardian]),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Wali',
            ])
        </div>
    </div>
</x-app-layout>

4. Form partial
Buat file:
nano resources/views/admin/students/guardians/partials/form.blade.php
Isi:
<form method="POST" action="{{ $action }}" class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="p-6 space-y-8">
        <div>
            <h3 class="font-semibold text-gray-900">Identitas Orang Tua/Wali</h3>

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
            <h3 class="font-semibold text-gray-900">Hubungan dengan Siswa</h3>

            <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <x-input-label for="relationship" value="Hubungan" />

                    <select
                        id="relationship"
                        name="relationship"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        required
                    >
                        @foreach ([
                            'father' => 'Ayah',
                            'mother' => 'Ibu',
                            'guardian' => 'Wali',
                            'other' => 'Lainnya',
                        ] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('relationship', $guardian->relationship ?: 'guardian') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('relationship')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="occupation" value="Pekerjaan" />

                    <x-text-input
                        id="occupation"
                        name="occupation"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('occupation', $guardian->occupation)"
                    />
                </div>

                <div>
                    <x-input-label for="education_level" value="Pendidikan Terakhir" />

                    <x-text-input
                        id="education_level"
                        name="education_level"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('education_level', $guardian->education_level)"
                    />
                </div>

                <div>
                    <x-input-label for="income_range" value="Rentang Penghasilan" />

                    <x-text-input
                        id="income_range"
                        name="income_range"
                        type="text"
                        class="mt-1 block w-full"
                        :value="old('income_range', $guardian->income_range)"
                        placeholder="Contoh: 1-3 juta"
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
                >{{ old('notes', $guardian->notes) }}</textarea>
            </div>

            <div class="mt-6 space-y-3">
                <label class="flex items-center gap-2">
                    <input
                        name="is_primary_contact"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_primary_contact', $guardian->is_primary_contact))
                    >

                    <span class="text-sm text-gray-700">
                        Kontak utama
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_emergency_contact"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_emergency_contact', $guardian->is_emergency_contact))
                    >

                    <span class="text-sm text-gray-700">
                        Kontak darurat
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_financial_responsible"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_financial_responsible', $guardian->is_financial_responsible))
                    >

                    <span class="text-sm text-gray-700">
                        Penanggung jawab keuangan
                    </span>
                </label>

                <label class="flex items-center gap-2">
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                        @checked(old('is_active', $guardian->is_active ?? true))
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
            href="{{ route('admin.students.guardians.index', $student) }}"
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

Tahap 12.11K: Dashboard Shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Orang Tua/Wali Siswa',
    'description' => 'Kelola data orang tua, wali, kontak darurat, dan penanggung jawab keuangan siswa.',
    'href' => route('admin.students.index'),
    'label' => 'Buka Siswa',
],
Kita arahkan ke halaman siswa karena data wali melekat pada masing-masing siswa.

Tahap 12.11L: Test Orang Tua/Wali Siswa
Buat test:
php artisan make:test Admin/StudentGuardianTest
Buka:
nano tests/Feature/Admin/StudentGuardianTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_guardian_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_guardians.view');

        [$student, $guardian] = $this->createStudentGuardianData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.guardians.index', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Orang Tua/Wali Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee($guardian->person->full_name);
    }

    public function test_user_can_create_student_guardian(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_guardians.create');

        $student = $this->createStudent();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.guardians.store', $student), [
                'full_name' => 'Wali Ahmad',
                'national_id_number' => '1234567890123456',
                'birth_place' => 'Lombok Timur',
                'birth_date' => '1980-01-01',
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'wali.ahmad@test.local',
                'phone' => '08123456789',
                'address' => 'Alamat wali',
                'relationship' => 'father',
                'occupation' => 'Petani',
                'education_level' => 'SMA',
                'income_range' => '1-3 juta',
                'is_primary_contact' => '1',
                'is_emergency_contact' => '1',
                'is_financial_responsible' => '1',
                'is_active' => '1',
                'notes' => 'Data awal.',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseHas('people', [
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
        ]);

        $this->assertDatabaseHas('student_guardians', [
            'student_id' => $student->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_student_guardian(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_guardians.update');

        [$student, $guardian] = $this->createStudentGuardianData();

        $response = $this
            ->actingAs($user)
            ->put(route('admin.students.guardians.update', [$student, $guardian]), [
                'full_name' => 'Wali Ahmad Revisi',
                'national_id_number' => null,
                'birth_place' => null,
                'birth_date' => null,
                'gender' => 'male',
                'religion' => 'Islam',
                'email' => 'wali.revisi@test.local',
                'phone' => '0811111111',
                'address' => 'Alamat revisi',
                'relationship' => 'guardian',
                'occupation' => 'Wiraswasta',
                'education_level' => 'S1',
                'income_range' => '3-5 juta',
                'is_primary_contact' => '1',
                'is_emergency_contact' => '1',
                'is_financial_responsible' => '1',
                'is_active' => '1',
                'notes' => 'Revisi.',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseHas('people', [
            'id' => $guardian->person_id,
            'full_name' => 'Wali Ahmad Revisi',
            'email' => 'wali.revisi@test.local',
        ]);

        $this->assertDatabaseHas('student_guardians', [
            'id' => $guardian->id,
            'relationship' => 'guardian',
            'occupation' => 'Wiraswasta',
        ]);
    }

    private function createStudentGuardianData(): array
    {
        $student = $this->createStudent();

        $guardianPerson = Person::create([
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
        ]);

        $guardian = StudentGuardian::create([
            'student_id' => $student->id,
            'person_id' => $guardianPerson->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'education_level' => 'SMA',
            'income_range' => '1-3 juta',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);

        return [$student, $guardian];
    }

    private function createStudent(): Student
    {
        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.siswa@test.local',
        ]);

        return Student::create([
            'person_id' => $person->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_guardian_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Guardian Role',
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

Tahap 12.11M: Jalankan Migration, Seeder, Test, Build
Jalankan:
php artisan migrate
php artisan db:seed --class=StudentGuardianSeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=guardians
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/students/{student}/guardians
GET|HEAD  admin/students/{student}/guardians/create
POST      admin/students/{student}/guardians
GET|HEAD  admin/students/{student}/guardians/{guardian}/edit
PUT       admin/students/{student}/guardians/{guardian}
Target test:
102+ tests passed
build berhasil

Tahap 12.11N: Uji Manual
Jalankan seeder pendukung jika perlu:
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentGuardianSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Pada salah satu siswa, klik:
Wali
Coba tambah wali:
Nama Lengkap: Wali Ahmad
Hubungan: Ayah
Telepon: 08123456789
Pekerjaan: Petani
Kontak utama: centang
Kontak darurat: centang
Penanggung jawab keuangan: centang
Aktif: centang

Tahap 12.11O: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/StudentGuardian.php \
        app/Models/Student.php \
        app/Models/Person.php \
        app/Http/Controllers/Admin/StudentGuardianController.php \
        database/migrations/ \
        database/seeders/StudentGuardianSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/index.blade.php \
        resources/views/admin/students/guardians/ \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/StudentGuardianTest.php
Commit:
git commit -m "feat: add student guardian module"
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
Setelah berhasil, kita lanjut ke Tahap 12.12: Akun Orang Tua/Wali Siswa.













