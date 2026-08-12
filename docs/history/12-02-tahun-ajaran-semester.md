# Tahap 12.2 — Tahun Ajaran dan Semester

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 22398–23659.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.2: Modul Tahun Ajaran dan Semester
Modul ini sangat penting karena seluruh histori akademik akan bergantung pada tahun ajaran dan semester. Pada desain database, academic_years menyimpan tahun ajaran, sedangkan semesters menjadi periode aktif untuk transaksi seperti penempatan siswa, jadwal, absensi, nilai, rapor, tahfidz, dan pembiasaan.
Prinsip utamanya:
Data lama tidak ditimpa.
Tahun ajaran lama tidak dihapus.
Semester lama dapat dikunci.
Hanya satu semester aktif pada satu waktu.

Tahap 12.2A: Migration dan Model
1. Buat model dan migration
Jalankan:
php artisan make:model AcademicYear -m
php artisan make:model Semester -m

2. Migration academic_years
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_academic_years_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel tahun ajaran.
     */
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();
            $table->string('name', 30);

            $table->date('start_date');
            $table->date('end_date');

            $table->string('status', 30)->default('draft');

            $table->boolean('is_active')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->timestamp('locked_at')->nullable();

            $table->foreignId('locked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
            $table->index('is_locked');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Menghapus tabel tahun ajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};

3. Migration semesters
Buka:
nano $(ls -t database/migrations/*_create_semesters_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel semester.
     */
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('code', 30);
            $table->string('name', 50);

            $table->string('semester_type', 20);

            $table->date('start_date');
            $table->date('end_date');

            $table->string('status', 30)->default('draft');

            $table->boolean('is_active')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->timestamp('locked_at')->nullable();

            $table->foreignId('locked_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['academic_year_id', 'semester_type']);
            $table->unique(['academic_year_id', 'code']);

            $table->index('semester_type');
            $table->index('status');
            $table->index('is_active');
            $table->index('is_locked');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Menghapus tabel semester.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};

Tahap 12.2B: Model
1. Model AcademicYear
Buka:
nano app/Models/AcademicYear.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }
}

2. Model Semester
Buka:
nano app/Models/Semester.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'code',
        'name',
        'semester_type',
        'start_date',
        'end_date',
        'status',
        'is_active',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}

Tahap 12.2C: Permission Tahun Ajaran dan Semester
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'academic_years.view',
    'module' => 'academic_years',
    'action' => 'view',
    'display_name' => 'Melihat Tahun Ajaran',
    'description' => 'Melihat daftar tahun ajaran dan semester.',
],
[
    'name' => 'academic_years.create',
    'module' => 'academic_years',
    'action' => 'create',
    'display_name' => 'Membuat Tahun Ajaran',
    'description' => 'Membuat tahun ajaran dan semester baru.',
],
[
    'name' => 'academic_years.update',
    'module' => 'academic_years',
    'action' => 'update',
    'display_name' => 'Mengubah Tahun Ajaran',
    'description' => 'Mengubah data tahun ajaran dan semester.',
],
[
    'name' => 'academic_years.activate',
    'module' => 'academic_years',
    'action' => 'activate',
    'display_name' => 'Mengaktifkan Semester',
    'description' => 'Menetapkan satu semester sebagai semester aktif.',
],
[
    'name' => 'academic_years.lock',
    'module' => 'academic_years',
    'action' => 'lock',
    'display_name' => 'Mengunci Semester',
    'description' => 'Mengunci semester agar transaksi periode tersebut tidak diubah sembarangan.',
],

Tahap 12.2D: Seeder Tahun Ajaran Awal
Buat seeder:
php artisan make:seeder AcademicYearSeeder
Buka:
nano database/seeders/AcademicYearSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    /**
     * Membuat tahun ajaran dan semester awal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $academicYear = AcademicYear::query()->updateOrCreate(
                [
                    'code' => '2026-2027',
                ],
                [
                    'name' => '2026/2027',
                    'start_date' => '2026-07-01',
                    'end_date' => '2027-06-30',
                    'status' => 'active',
                    'is_active' => true,
                    'is_locked' => false,
                ]
            );

            Semester::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'semester_type' => 'ganjil',
                ],
                [
                    'code' => '2026-2027-ganjil',
                    'name' => 'Semester Ganjil 2026/2027',
                    'start_date' => '2026-07-01',
                    'end_date' => '2026-12-31',
                    'status' => 'active',
                    'is_active' => true,
                    'is_locked' => false,
                ]
            );

            Semester::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'semester_type' => 'genap',
                ],
                [
                    'code' => '2026-2027-genap',
                    'name' => 'Semester Genap 2026/2027',
                    'start_date' => '2027-01-01',
                    'end_date' => '2027-06-30',
                    'status' => 'draft',
                    'is_active' => false,
                    'is_locked' => false,
                ]
            );
        });
    }
}

Tahap 12.2E: Controller Tahun Ajaran
Buat controller:
php artisan make:controller Admin/AcademicYearController
Buka:
nano app/Http/Controllers/Admin/AcademicYearController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $academicYears = AcademicYear::query()
            ->with('semesters')
            ->orderByDesc('start_date')
            ->paginate(10);

        return view('admin.academic-years.index', [
            'academicYears' => $academicYears,
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
        DB::transaction(function () use ($semester): void {
            AcademicYear::query()->update([
                'is_active' => false,
            ]);

            Semester::query()->update([
                'is_active' => false,
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
            ->with('success', 'Semester aktif berhasil diperbarui.');
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

Tahap 12.2F: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\AcademicYearController;
Di dalam group admin, tambahkan:
Route::get('/academic-years', [AcademicYearController::class, 'index'])
    ->middleware('permission:academic_years.view')
    ->name('academic-years.index');

Route::get('/academic-years/create', [AcademicYearController::class, 'create'])
    ->middleware('permission:academic_years.create')
    ->name('academic-years.create');

Route::post('/academic-years', [AcademicYearController::class, 'store'])
    ->middleware('permission:academic_years.create')
    ->name('academic-years.store');

Route::put('/semesters/{semester}/activate', [AcademicYearController::class, 'activateSemester'])
    ->middleware('permission:academic_years.activate')
    ->name('semesters.activate');

Route::put('/semesters/{semester}/lock', [AcademicYearController::class, 'lockSemester'])
    ->middleware('permission:academic_years.lock')
    ->name('semesters.lock');

Tahap 12.2G: View Daftar Tahun Ajaran
Buat folder:
mkdir -p resources/views/admin/academic-years
Buat file:
nano resources/views/admin/academic-years/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tahun Ajaran dan Semester
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola periode akademik tanpa menghapus histori data lama.
                </p>
            </div>

            @can('permission', 'academic_years.create')
                <a
                    href="{{ route('admin.academic-years.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Tahun Ajaran
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

            @forelse ($academicYears as $academicYear)
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $academicYear->name }}
                                </h3>

                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $academicYear->start_date->format('d M Y') }}
                                    sampai
                                    {{ $academicYear->end_date->format('d M Y') }}
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($academicYear->is_active)
                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        Tahun Ajaran Aktif
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                        Tidak Aktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-5 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Semester
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Periode
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Status
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($academicYear->semesters as $semester)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="font-medium text-gray-900">
                                                    {{ $semester->name }}
                                                </div>

                                                <div class="text-xs text-gray-500">
                                                    {{ $semester->code }}
                                                </div>
                                            </td>

                                            <td class="px-4 py-3 text-gray-700">
                                                {{ $semester->start_date->format('d M Y') }}
                                                sampai
                                                {{ $semester->end_date->format('d M Y') }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    @if ($semester->is_active)
                                                        <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                            Aktif
                                                        </span>
                                                    @else
                                                        <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                                            Tidak Aktif
                                                        </span>
                                                    @endif

                                                    @if ($semester->is_locked)
                                                        <span class="rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                                            Terkunci
                                                        </span>
                                                    @else
                                                        <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700">
                                                            Terbuka
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-2">
                                                    @can('permission', 'academic_years.activate')
                                                        @unless ($semester->is_active)
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.semesters.activate', $semester) }}"
                                                            >
                                                                @csrf
                                                                @method('PUT')

                                                                <button
                                                                    type="submit"
                                                                    class="text-sm font-medium text-green-700 hover:text-green-900"
                                                                >
                                                                    Aktifkan
                                                                </button>
                                                            </form>
                                                        @endunless
                                                    @endcan

                                                    @can('permission', 'academic_years.lock')
                                                        @unless ($semester->is_locked)
                                                            <form
                                                                method="POST"
                                                                action="{{ route('admin.semesters.lock', $semester) }}"
                                                            >
                                                                @csrf
                                                                @method('PUT')

                                                                <button
                                                                    type="submit"
                                                                    class="text-sm font-medium text-red-700 hover:text-red-900"
                                                                >
                                                                    Kunci
                                                                </button>
                                                            </form>
                                                        @endunless
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6 text-center text-sm text-gray-500">
                        Belum ada tahun ajaran.
                    </div>
                </div>
            @endforelse

            {{ $academicYears->links() }}
        </div>
    </div>
</x-app-layout>

Tahap 12.2H: View Tambah Tahun Ajaran
Buat file:
nano resources/views/admin/academic-years/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Tahun Ajaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat tahun ajaran beserta semester ganjil dan genap.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('admin.academic-years.store') }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="code" value="Kode Tahun Ajaran" />

                            <x-text-input
                                id="code"
                                name="code"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('code') }}"
                                placeholder="2026-2027"
                                required
                            />

                            <x-input-error :messages="$errors->get('code')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="name" value="Nama Tahun Ajaran" />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                value="{{ old('name') }}"
                                placeholder="2026/2027"
                                required
                            />

                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai Tahun Ajaran" />

                            <x-text-input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                value="{{ old('start_date') }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_date" value="Tanggal Selesai Tahun Ajaran" />

                            <x-text-input
                                id="end_date"
                                name="end_date"
                                type="date"
                                class="mt-1 block w-full"
                                value="{{ old('end_date') }}"
                                required
                            />

                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <h3 class="font-semibold text-gray-900">
                            Semester Ganjil
                        </h3>

                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="ganjil_start_date" value="Mulai" />

                                <x-text-input
                                    id="ganjil_start_date"
                                    name="ganjil_start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('ganjil_start_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('ganjil_start_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="ganjil_end_date" value="Selesai" />

                                <x-text-input
                                    id="ganjil_end_date"
                                    name="ganjil_end_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('ganjil_end_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('ganjil_end_date')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <h3 class="font-semibold text-gray-900">
                            Semester Genap
                        </h3>

                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-input-label for="genap_start_date" value="Mulai" />

                                <x-text-input
                                    id="genap_start_date"
                                    name="genap_start_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('genap_start_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('genap_start_date')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="genap_end_date" value="Selesai" />

                                <x-text-input
                                    id="genap_end_date"
                                    name="genap_end_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('genap_end_date') }}"
                                    required
                                />

                                <x-input-error :messages="$errors->get('genap_end_date')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <a
                        href="{{ route('admin.academic-years.index') }}"
                        class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                        Simpan Tahun Ajaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

Tahap 12.2I: Sidebar dan Dashboard Shortcut
1. Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Tambahkan link di section Administrasi Sistem atau Data Master:
@can('permission', 'academic_years.view')
    <x-ui.sidebar-link
        :href="route('admin.academic-years.index')"
        :active="request()->routeIs('admin.academic-years.*')"
    >
        Tahun Ajaran
    </x-ui.sidebar-link>
@endcan
Jika kondisi section belum memeriksa academic_years.view, tambahkan:
auth()->user()?->can('permission', 'academic_years.view') ||

2. Dashboard shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Tahun Ajaran',
    'description' => 'Kelola tahun ajaran, semester aktif, dan penguncian periode akademik.',
    'href' => route('admin.academic-years.index'),
    'label' => 'Buka Tahun Ajaran',
],

Tahap 12.2J: Test
Buat test:
php artisan make:test Admin/AcademicYearTest
Buka:
nano tests/Feature/Admin/AcademicYearTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_academic_years(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.view');

        AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/academic-years');

        $response
            ->assertStatus(200)
            ->assertSee('Tahun Ajaran dan Semester')
            ->assertSee('2026/2027');
    }

    public function test_user_with_permission_can_create_academic_year_with_semesters(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.view');
        $this->grantPermissionToUser($user, 'academic_years.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/academic-years', [
                'code' => '2027-2028',
                'name' => '2027/2028',
                'start_date' => '2027-07-01',
                'end_date' => '2028-06-30',
                'ganjil_start_date' => '2027-07-01',
                'ganjil_end_date' => '2027-12-31',
                'genap_start_date' => '2028-01-01',
                'genap_end_date' => '2028-06-30',
            ]);

        $response->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'code' => '2027-2028',
            'name' => '2027/2028',
        ]);

        $this->assertDatabaseHas('semesters', [
            'code' => '2027-2028-ganjil',
            'semester_type' => 'ganjil',
        ]);

        $this->assertDatabaseHas('semesters', [
            'code' => '2027-2028-genap',
            'semester_type' => 'genap',
        ]);
    }

    public function test_user_can_activate_one_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.activate');

        $academicYear = AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-ganjil',
            'name' => 'Semester Ganjil 2026/2027',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'draft',
            'is_active' => false,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.semesters.activate', $semester));

        $response->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('academic_years', [
            'id' => $academicYear->id,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    public function test_user_can_lock_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'academic_years.lock');

        $academicYear = AcademicYear::create([
            'code' => '2026-2027',
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $semester = Semester::create([
            'academic_year_id' => $academicYear->id,
            'code' => '2026-2027-ganjil',
            'name' => 'Semester Ganjil 2026/2027',
            'semester_type' => 'ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'is_active' => true,
            'is_locked' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.semesters.lock', $semester));

        $response->assertRedirect(route('admin.academic-years.index'));

        $this->assertDatabaseHas('semesters', [
            'id' => $semester->id,
            'is_locked' => true,
            'status' => 'locked',
            'locked_by' => $user->id,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_academic_year_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Academic Year Role',
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

Tahap 12.2K: Jalankan Migration, Seeder, Test, Build
Jalankan:
php artisan migrate
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Periksa route:
php artisan route:list --path=academic-years
php artisan route:list --path=semesters
Target:
GET|HEAD  admin/academic-years
GET|HEAD  admin/academic-years/create
POST      admin/academic-years
PUT       admin/semesters/{semester}/activate
PUT       admin/semesters/{semester}/lock

Tahap 12.2L: Uji Manual
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/academic-years
Pastikan tampil:
Tahun Ajaran dan Semester
2026/2027
Semester Ganjil
Semester Genap
Aktifkan
Kunci
Buat tahun ajaran baru:
Kode: 2027-2028
Nama: 2027/2028
Mulai: 2027-07-01
Selesai: 2028-06-30
Ganjil mulai: 2027-07-01
Ganjil selesai: 2027-12-31
Genap mulai: 2028-01-01
Genap selesai: 2028-06-30

Tahap 12.2M: Commit
Jika semua berhasil:
git status
Lalu:
git add app/Models/AcademicYear.php \
        app/Models/Semester.php \
        app/Http/Controllers/Admin/AcademicYearController.php \
        database/migrations/ \
        database/seeders/AcademicYearSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/academic-years/index.blade.php \
        resources/views/admin/academic-years/create.blade.php \
        resources/views/layouts/sidebar.blade.php \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/AcademicYearTest.php
Commit:
git commit -m "feat: add academic year and semester module"
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
Setelah berhasil, kita lanjut ke Tahap 12.3: Data Ruangan dan Tingkat Kelas.













