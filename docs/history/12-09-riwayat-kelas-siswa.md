# Tahap 12.9 — Riwayat Kelas Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 31149–32421.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.9: Riwayat Kelas Siswa
Ini tahap penting karena sesuai prinsip awal SIM Madrasah:
Jangan simpan kelas siswa langsung di tabel students.
Jangan update kelas lama saat siswa naik kelas.
Buat record riwayat baru untuk setiap semester atau tahun ajaran.
Struktur datanya:
students
  ↓
student_class_histories
  ↓
academic_years
semesters
class_groups
Contoh:
Ahmad Siswa
- 2026/2027 Ganjil  → VII A
- 2026/2027 Genap   → VII A
- 2027/2028 Ganjil  → VIII A
Dengan cara ini, histori kelas siswa tetap aman.

Tahap 12.9A: Model dan Migration
1. Buat model dan migration
Jalankan:
php artisan make:model StudentClassHistory -m
2. Isi migration student_class_histories
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_student_class_histories_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel riwayat kelas siswa.
     */
    public function up(): void
    {
        Schema::create('student_class_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('semester_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('class_group_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('status', 30)->default('active');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->boolean('is_current')->default(false);

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'semester_id']);

            $table->index('academic_year_id');
            $table->index('semester_id');
            $table->index('class_group_id');
            $table->index('status');
            $table->index('is_current');
        });
    }

    /**
     * Menghapus tabel riwayat kelas siswa.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_histories');
    }
};
Mengapa ada unique student_id dan semester_id?
Karena satu siswa hanya boleh berada pada satu rombongan belajar dalam satu semester.
Contoh yang benar:
Ahmad - Semester Ganjil 2026/2027 - VII A
Contoh yang salah:
Ahmad - Semester Ganjil 2026/2027 - VII A
Ahmad - Semester Ganjil 2026/2027 - VII B
Sistem harus mencegah data ganda seperti itu.

Tahap 12.9B: Model StudentClassHistory
Buka:
nano app/Models/StudentClassHistory.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'class_group_id',
        'status',
        'start_date',
        'end_date',
        'is_current',
        'assigned_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

Tahap 12.9C: Tambahkan Relasi ke Model Student
Buka:
nano app/Models/Student.php
Tambahkan import:
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
Tambahkan method ini di dalam class Student:
public function classHistories(): HasMany
{
    return $this->hasMany(StudentClassHistory::class);
}

public function currentClassHistory(): HasOne
{
    return $this->hasOne(StudentClassHistory::class)
        ->where('is_current', true);
}

Tahap 12.9D: Tambahkan Relasi ke Model ClassGroup
Buka:
nano app/Models/ClassGroup.php
Tambahkan import:
use Illuminate\Database\Eloquent\Relations\HasMany;
Tambahkan method:
public function studentClassHistories(): HasMany
{
    return $this->hasMany(StudentClassHistory::class);
}

Tahap 12.9E: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'student_class_histories.view',
    'module' => 'student_class_histories',
    'action' => 'view',
    'display_name' => 'Melihat Riwayat Kelas Siswa',
    'description' => 'Melihat riwayat penempatan kelas siswa.',
],
[
    'name' => 'student_class_histories.create',
    'module' => 'student_class_histories',
    'action' => 'create',
    'display_name' => 'Membuat Riwayat Kelas Siswa',
    'description' => 'Menambahkan riwayat penempatan kelas siswa.',
],

Tahap 12.9F: Seeder Riwayat Kelas Siswa
Buat seeder:
php artisan make:seeder StudentClassHistorySeeder
Buka:
nano database/seeders/StudentClassHistorySeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentClassHistorySeeder extends Seeder
{
    /**
     * Membuat data awal riwayat kelas siswa.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $academicYear = AcademicYear::query()
                ->where('code', '2026-2027')
                ->first();

            if ($academicYear === null) {
                return;
            }

            $semester = Semester::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('semester_type', 'ganjil')
                ->first();

            $classGroup = ClassGroup::query()
                ->where('academic_year_id', $academicYear->id)
                ->where('code', 'VII-A')
                ->first();

            if ($semester === null || $classGroup === null) {
                return;
            }

            $students = Student::query()
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                StudentClassHistory::query()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'academic_year_id' => $academicYear->id,
                        'class_group_id' => $classGroup->id,
                        'status' => 'active',
                        'start_date' => $semester->start_date,
                        'end_date' => null,
                        'is_current' => true,
                        'assigned_by' => null,
                        'notes' => null,
                    ]
                );
            }
        });
    }
}

Tahap 12.9G: Controller Riwayat Kelas Siswa
Buat controller:
php artisan make:controller Admin/StudentClassHistoryController
Buka:
nano app/Http/Controllers/Admin/StudentClassHistoryController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentClassHistoryController extends Controller
{
    public function index(Student $student): View
    {
        $student->load('person');

        $histories = $student->classHistories()
            ->with([
                'academicYear',
                'semester',
                'classGroup.gradeLevel',
                'classGroup.room',
                'assignedBy',
            ])
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->paginate(15);

        return view('admin.students.class-histories.index', [
            'student' => $student,
            'histories' => $histories,
        ]);
    }

    public function create(Student $student): View
    {
        $student->load('person');

        return view('admin.students.class-histories.create', [
            'student' => $student,
        ] + $this->formData());
    }

    public function store(
        Request $request,
        Student $student
    ): RedirectResponse {
        $validated = $this->validateClassHistory($request, $student);

        DB::transaction(function () use ($validated, $student, $request): void {
            if ($validated['is_current']) {
                $student->classHistories()
                    ->update([
                        'is_current' => false,
                    ]);
            }

            StudentClassHistory::create([
                'student_id' => $student->id,
                'academic_year_id' => $validated['academic_year_id'],
                'semester_id' => $validated['semester_id'],
                'class_group_id' => $validated['class_group_id'],
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'is_current' => $validated['is_current'],
                'assigned_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.students.class-histories.index', $student)
            ->with('success', 'Riwayat kelas siswa berhasil ditambahkan.');
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
            'semesters' => Semester::query()
                ->with('academicYear')
                ->orderByDesc('academic_year_id')
                ->orderBy('semester_type')
                ->get(),
            'classGroups' => ClassGroup::query()
                ->with([
                    'academicYear',
                    'gradeLevel',
                    'room',
                ])
                ->where('is_active', true)
                ->orderByDesc('academic_year_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Validasi riwayat kelas siswa.
     *
     * @return array<string, mixed>
     */
    private function validateClassHistory(
        Request $request,
        Student $student
    ): array {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
            'semester_id' => [
                'required',
                'exists:semesters,id',
                Rule::unique('student_class_histories', 'semester_id')
                    ->where(
                        fn ($query) => $query->where(
                            'student_id',
                            $student->id
                        )
                    ),
            ],
            'class_group_id' => [
                'required',
                'exists:class_groups,id',
            ],
            'status' => [
                'required',
                'in:active,completed,transferred,graduated',
            ],
            'start_date' => [
                'nullable',
                'date',
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],
            'is_current' => [
                'nullable',
                'boolean',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        $semester = Semester::query()
            ->findOrFail($validated['semester_id']);

        if ((int) $semester->academic_year_id !== (int) $validated['academic_year_id']) {
            throw ValidationException::withMessages([
                'semester_id' => 'Semester tidak sesuai dengan tahun ajaran yang dipilih.',
            ]);
        }

        $classGroup = ClassGroup::query()
            ->findOrFail($validated['class_group_id']);

        if ((int) $classGroup->academic_year_id !== (int) $validated['academic_year_id']) {
            throw ValidationException::withMessages([
                'class_group_id' => 'Rombongan belajar tidak sesuai dengan tahun ajaran yang dipilih.',
            ]);
        }

        $validated['is_current'] = $request->boolean('is_current');

        return $validated;
    }
}

Tahap 12.9H: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\StudentClassHistoryController;
Di dalam group admin, tambahkan route berikut setelah route students:
Route::get('/students/{student}/class-histories', [StudentClassHistoryController::class, 'index'])
    ->middleware('permission:student_class_histories.view')
    ->name('students.class-histories.index');

Route::get('/students/{student}/class-histories/create', [StudentClassHistoryController::class, 'create'])
    ->middleware('permission:student_class_histories.create')
    ->name('students.class-histories.create');

Route::post('/students/{student}/class-histories', [StudentClassHistoryController::class, 'store'])
    ->middleware('permission:student_class_histories.create')
    ->name('students.class-histories.store');

Tahap 12.9I: Update View Daftar Siswa
Buka:
nano resources/views/admin/students/index.blade.php
Cari kolom aksi:
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
Ganti menjadi:
<td class="px-4 py-3">
    <div class="flex flex-wrap gap-3">
        @can('permission', 'students.update')
            <a
                href="{{ route('admin.students.edit', $student) }}"
                class="text-sm font-medium text-green-700 hover:text-green-900"
            >
                Edit
            </a>
        @endcan

        @can('permission', 'student_class_histories.view')
            <a
                href="{{ route('admin.students.class-histories.index', $student) }}"
                class="text-sm font-medium text-blue-700 hover:text-blue-900"
            >
                Riwayat Kelas
            </a>
        @endcan
    </div>
</td>

Tahap 12.9J: View Riwayat Kelas Siswa
Buat folder:
mkdir -p resources/views/admin/students/class-histories
1. Index
Buat file:
nano resources/views/admin/students/class-histories/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Riwayat Kelas Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->person?->full_name }}
                </p>
            </div>

            @can('permission', 'student_class_histories.create')
                <a
                    href="{{ route('admin.students.class-histories.create', $student) }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Riwayat
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Tahun Ajaran
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Semester
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Rombel
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Ruangan
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Periode
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($histories as $history)
                                <tr>
                                    <td class="px-4 py-3">
                                        {{ $history->academicYear?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $history->semester?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $history->classGroup?->name ?? '-' }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $history->classGroup?->gradeLevel?->name ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $history->classGroup?->room?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $history->start_date?->format('d M Y') ?? '-' }}
                                        sampai
                                        {{ $history->end_date?->format('d M Y') ?? 'sekarang' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                {{ $history->status }}
                                            </span>

                                            @if ($history->is_current)
                                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                    Saat Ini
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada riwayat kelas siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $histories->links() }}
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
nano resources/views/admin/students/class-histories/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Riwayat Kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ $student->person?->full_name }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ route('admin.students.class-histories.store', $student) }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf

                <div class="p-6 space-y-6">
                    <div>
                        <x-input-label for="academic_year_id" value="Tahun Ajaran" />

                        <select
                            id="academic_year_id"
                            name="academic_year_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="">Pilih Tahun Ajaran</option>

                            @foreach ($academicYears as $academicYear)
                                <option
                                    value="{{ $academicYear->id }}"
                                    @selected((string) old('academic_year_id') === (string) $academicYear->id)
                                >
                                    {{ $academicYear->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="semester_id" value="Semester" />

                        <select
                            id="semester_id"
                            name="semester_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="">Pilih Semester</option>

                            @foreach ($semesters as $semester)
                                <option
                                    value="{{ $semester->id }}"
                                    @selected((string) old('semester_id') === (string) $semester->id)
                                >
                                    {{ $semester->academicYear?->name }}
                                    -
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('semester_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="class_group_id" value="Rombongan Belajar" />

                        <select
                            id="class_group_id"
                            name="class_group_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="">Pilih Rombel</option>

                            @foreach ($classGroups as $classGroup)
                                <option
                                    value="{{ $classGroup->id }}"
                                    @selected((string) old('class_group_id') === (string) $classGroup->id)
                                >
                                    {{ $classGroup->academicYear?->name }}
                                    -
                                    {{ $classGroup->name }}
                                    -
                                    {{ $classGroup->gradeLevel?->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('class_group_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status Penempatan" />

                        <select
                            id="status"
                            name="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            @foreach ([
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'transferred' => 'Pindah',
                                'graduated' => 'Lulus',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('status', 'active') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label for="start_date" value="Tanggal Mulai" />

                            <x-text-input
                                id="start_date"
                                name="start_date"
                                type="date"
                                class="mt-1 block w-full"
                                :value="old('start_date')"
                            />

                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="end_date" value="Tanggal Selesai" />

                            <x-text-input
                                id="end_date"
                                name="end_date"
                                type="date"
                                class="mt-1 block w-full"
                                :value="old('end_date')"
                            />

                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" value="Catatan" />

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >{{ old('notes') }}</textarea>

                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_current"
                            name="is_current"
                            type="checkbox"
                            value="1"
                            class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                            @checked(old('is_current', true))
                        >

                        <label for="is_current" class="text-sm text-gray-700">
                            Jadikan kelas aktif saat ini
                        </label>
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <a
                        href="{{ route('admin.students.class-histories.index', $student) }}"
                        class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                        Simpan Riwayat
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

Tahap 12.9K: Dashboard Shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Riwayat Kelas Siswa',
    'description' => 'Kelola histori penempatan siswa per tahun ajaran, semester, dan rombongan belajar.',
    'href' => route('admin.students.index'),
    'label' => 'Buka Siswa',
],
Kita arahkan ke halaman siswa karena riwayat kelas melekat pada masing-masing siswa.

Tahap 12.9L: Test Riwayat Kelas Siswa
Buat test:
php artisan make:test Admin/StudentClassHistoryTest
Buka:
nano tests/Feature/Admin/StudentClassHistoryTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Room;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentClassHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_class_history_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.view');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.class-histories.index', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Riwayat Kelas Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee('Kelas VII A');
    }

    public function test_user_can_create_student_class_history(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.class-histories.store', $student), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2026-07-01',
                'end_date' => null,
                'is_current' => '1',
                'notes' => 'Penempatan awal.',
            ]);

        $response->assertRedirect(
            route('admin.students.class-histories.index', $student)
        );

        $this->assertDatabaseHas('student_class_histories', [
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'is_current' => true,
        ]);
    }

    public function test_student_cannot_have_duplicate_class_history_in_same_semester(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_class_histories.create');

        [$student, $academicYear, $semester, $classGroup] = $this->createStudentClassData();

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('admin.students.class-histories.store', $student), [
                'academic_year_id' => $academicYear->id,
                'semester_id' => $semester->id,
                'class_group_id' => $classGroup->id,
                'status' => 'active',
                'start_date' => '2026-07-01',
                'is_current' => '1',
            ]);

        $response->assertSessionHasErrors('semester_id');
    }

    /**
     * @return array{0: Student, 1: AcademicYear, 2: Semester, 3: ClassGroup}
     */
    private function createStudentClassData(): array
    {
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

        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $room = Room::create([
            'code' => 'R-VII-A',
            'name' => 'Ruang VII A',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);

        $classGroup = ClassGroup::create([
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'parallel_name' => 'A',
            'capacity' => 32,
            'status' => 'active',
            'is_active' => true,
        ]);

        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad@test.local',
        ]);

        $student = Student::create([
            'person_id' => $person->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        return [
            $student,
            $academicYear,
            $semester,
            $classGroup,
        ];
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_class_history_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Class History Role',
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

Tahap 12.9M: Jalankan Migration, Seeder, Test, Build
Jalankan:
php artisan migrate
php artisan db:seed --class=StudentClassHistorySeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=class-histories
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/students/{student}/class-histories
GET|HEAD  admin/students/{student}/class-histories/create
POST      admin/students/{student}/class-histories
Target test:
96+ tests passed
build berhasil

Tahap 12.9N: Uji Manual
Jalankan seeder pendukung bila data belum lengkap:
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=GradeLevelSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=ClassGroupSeeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentClassHistorySeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Klik:
Riwayat Kelas
Pada salah satu siswa.
Coba tambah riwayat kelas:
Tahun Ajaran: 2026/2027
Semester: Semester Ganjil 2026/2027
Rombongan Belajar: Kelas VII A
Status: Aktif
Tanggal Mulai: 2026-07-01
Jadikan kelas aktif saat ini: centang

Tahap 12.9O: Commit
Setelah semua aman:
git status
Lalu:
git add app/Models/StudentClassHistory.php \
        app/Models/Student.php \
        app/Models/ClassGroup.php \
        app/Http/Controllers/Admin/StudentClassHistoryController.php \
        database/migrations/ \
        database/seeders/StudentClassHistorySeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/index.blade.php \
        resources/views/admin/students/class-histories/ \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/StudentClassHistoryTest.php
Commit:
git commit -m "feat: add student class history module"
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
Setelah berhasil, kita lanjut ke Tahap 12.10: Akun Siswa dari Data Student.














