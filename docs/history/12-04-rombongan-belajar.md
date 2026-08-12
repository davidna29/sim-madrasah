# Tahap 12.4 — Rombongan Belajar / Class Groups

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 25381–26595.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.4: Rombongan Belajar / Class Groups
Rombongan belajar adalah kelas aktif dalam tahun ajaran tertentu.
Contoh:
Tahun Ajaran 2026/2027
- VII A
- VII B
- VIII A
- IX A
Tingkat kelas hanya menyimpan level seperti Kelas VII. Ruangan hanya menyimpan tempat seperti Ruang VII A. Rombongan belajar menghubungkan keduanya dalam satu tahun ajaran.
Prinsip penting:
Jangan menyimpan kelas langsung di tabel siswa.
Jangan update histori kelas lama.
Saat naik kelas, nanti siswa akan dibuatkan riwayat kelas baru.
Pada tahap ini kita buat:
class_groups

Tahap 12.4A: Model dan Migration
1. Buat model dan migration
php artisan make:model ClassGroup -m
2. Isi migration class_groups
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_class_groups_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel rombongan belajar.
     */
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_year_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('grade_level_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('homeroom_teacher_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('code', 50);
            $table->string('name', 100);
            $table->string('parallel_name', 30)->nullable();

            $table->unsignedSmallInteger('capacity')->nullable();

            $table->string('status', 30)->default('active');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['academic_year_id', 'code']);

            $table->index('grade_level_id');
            $table->index('room_id');
            $table->index('homeroom_teacher_user_id');
            $table->index('status');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel rombongan belajar.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_groups');
    }
};
3. Isi model ClassGroup
Buka:
nano app/Models/ClassGroup.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'grade_level_id',
        'room_id',
        'homeroom_teacher_user_id',
        'code',
        'name',
        'parallel_name',
        'capacity',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'homeroom_teacher_user_id'
        );
    }
}

Tahap 12.4B: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'class_groups.view',
    'module' => 'class_groups',
    'action' => 'view',
    'display_name' => 'Melihat Rombongan Belajar',
    'description' => 'Melihat data rombongan belajar.',
],
[
    'name' => 'class_groups.create',
    'module' => 'class_groups',
    'action' => 'create',
    'display_name' => 'Membuat Rombongan Belajar',
    'description' => 'Menambahkan rombongan belajar.',
],
[
    'name' => 'class_groups.update',
    'module' => 'class_groups',
    'action' => 'update',
    'display_name' => 'Mengubah Rombongan Belajar',
    'description' => 'Mengubah data rombongan belajar.',
],

Tahap 12.4C: Seeder Rombongan Belajar
Buat seeder:
php artisan make:seeder ClassGroupSeeder
Buka:
nano database/seeders/ClassGroupSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class ClassGroupSeeder extends Seeder
{
    /**
     * Membuat data rombongan belajar awal.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::query()
            ->where('code', '2026-2027')
            ->first();

        if ($academicYear === null) {
            $academicYear = AcademicYear::query()->first();
        }

        if ($academicYear === null) {
            return;
        }

        $gradeLevels = GradeLevel::query()
            ->whereIn('code', ['VII', 'VIII', 'IX'])
            ->get()
            ->keyBy('code');

        $rooms = Room::query()
            ->whereIn('code', [
                'R-VII-A',
                'R-VIII-A',
                'R-IX-A',
            ])
            ->get()
            ->keyBy('code');

        $classGroups = [
            [
                'code' => 'VII-A',
                'name' => 'Kelas VII A',
                'parallel_name' => 'A',
                'grade_level_code' => 'VII',
                'room_code' => 'R-VII-A',
                'capacity' => 32,
            ],
            [
                'code' => 'VIII-A',
                'name' => 'Kelas VIII A',
                'parallel_name' => 'A',
                'grade_level_code' => 'VIII',
                'room_code' => 'R-VIII-A',
                'capacity' => 32,
            ],
            [
                'code' => 'IX-A',
                'name' => 'Kelas IX A',
                'parallel_name' => 'A',
                'grade_level_code' => 'IX',
                'room_code' => 'R-IX-A',
                'capacity' => 32,
            ],
        ];

        foreach ($classGroups as $classGroup) {
            $gradeLevel = $gradeLevels->get(
                $classGroup['grade_level_code']
            );

            if ($gradeLevel === null) {
                continue;
            }

            ClassGroup::query()->updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'code' => $classGroup['code'],
                ],
                [
                    'grade_level_id' => $gradeLevel->id,
                    'room_id' => $rooms
                        ->get($classGroup['room_code'])
                        ?->id,
                    'homeroom_teacher_user_id' => null,
                    'name' => $classGroup['name'],
                    'parallel_name' => $classGroup['parallel_name'],
                    'capacity' => $classGroup['capacity'],
                    'status' => 'active',
                    'is_active' => true,
                ]
            );
        }
    }
}

Tahap 12.4D: Controller
Buat controller:
php artisan make:controller Admin/ClassGroupController
Buka:
nano app/Http/Controllers/Admin/ClassGroupController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassGroupController extends Controller
{
    public function index(): View
    {
        $classGroups = ClassGroup::query()
            ->with([
                'academicYear',
                'gradeLevel',
                'room',
                'homeroomTeacher',
            ])
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.class-groups.index', [
            'classGroups' => $classGroups,
        ]);
    }

    public function create(): View
    {
        return view('admin.class-groups.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateClassGroup($request);

        ClassGroup::create($validated);

        return redirect()
            ->route('admin.class-groups.index')
            ->with('success', 'Rombongan belajar berhasil dibuat.');
    }

    public function edit(ClassGroup $classGroup): View
    {
        return view('admin.class-groups.edit', [
            'classGroup' => $classGroup,
        ] + $this->formData());
    }

    public function update(
        Request $request,
        ClassGroup $classGroup
    ): RedirectResponse {
        $validated = $this->validateClassGroup(
            $request,
            $classGroup
        );

        $classGroup->update($validated);

        return redirect()
            ->route('admin.class-groups.index')
            ->with('success', 'Rombongan belajar berhasil diperbarui.');
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
            'gradeLevels' => GradeLevel::query()
                ->where('is_active', true)
                ->orderBy('level_number')
                ->get(),
            'rooms' => Room::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'homeroomTeachers' => User::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Validasi rombongan belajar.
     *
     * @return array<string, mixed>
     */
    private function validateClassGroup(
        Request $request,
        ?ClassGroup $classGroup = null
    ): array {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
            'grade_level_id' => [
                'required',
                'exists:grade_levels,id',
            ],
            'room_id' => [
                'nullable',
                'exists:rooms,id',
            ],
            'homeroom_teacher_user_id' => [
                'nullable',
                'exists:users,id',
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('class_groups', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'academic_year_id',
                            $request->input('academic_year_id')
                        )
                    )
                    ->ignore($classGroup),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'parallel_name' => [
                'nullable',
                'string',
                'max:30',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'status' => [
                'required',
                'string',
                'max:30',
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

Tahap 12.4E: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\ClassGroupController;
Di dalam group admin, tambahkan:
Route::get('/class-groups', [ClassGroupController::class, 'index'])
    ->middleware('permission:class_groups.view')
    ->name('class-groups.index');

Route::get('/class-groups/create', [ClassGroupController::class, 'create'])
    ->middleware('permission:class_groups.create')
    ->name('class-groups.create');

Route::post('/class-groups', [ClassGroupController::class, 'store'])
    ->middleware('permission:class_groups.create')
    ->name('class-groups.store');

Route::get('/class-groups/{classGroup}/edit', [ClassGroupController::class, 'edit'])
    ->middleware('permission:class_groups.update')
    ->name('class-groups.edit');

Route::put('/class-groups/{classGroup}', [ClassGroupController::class, 'update'])
    ->middleware('permission:class_groups.update')
    ->name('class-groups.update');

Tahap 12.4F: View
Buat folder:
mkdir -p resources/views/admin/class-groups/partials
1. View index
Buat file:
nano resources/views/admin/class-groups/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Rombongan Belajar
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola kelas aktif berdasarkan tahun ajaran.
                </p>
            </div>

            @can('permission', 'class_groups.create')
                <a
                    href="{{ route('admin.class-groups.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Rombel
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Kode</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nama Rombel</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Ajaran</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Tingkat</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Ruangan</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Wali Kelas</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($classGroups as $classGroup)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $classGroup->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $classGroup->name }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            Kapasitas: {{ $classGroup->capacity ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->academicYear?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->gradeLevel?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->room?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $classGroup->homeroomTeacher?->name ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($classGroup->is_active)
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
                                        @can('permission', 'class_groups.update')
                                            <a
                                                href="{{ route('admin.class-groups.edit', $classGroup) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada rombongan belajar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $classGroups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
2. View create
nano resources/views/admin/class-groups/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Rombongan Belajar
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat rombongan belajar baru untuk tahun ajaran tertentu.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.class-groups.partials.form', [
                'classGroup' => new \App\Models\ClassGroup(),
                'action' => route('admin.class-groups.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Rombel',
            ])
        </div>
    </div>
</x-app-layout>
3. View edit
nano resources/views/admin/class-groups/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Rombongan Belajar
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data rombongan belajar.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.class-groups.partials.form', [
                'classGroup' => $classGroup,
                'action' => route('admin.class-groups.update', $classGroup),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Rombel',
            ])
        </div>
    </div>
</x-app-layout>
4. Form partial
nano resources/views/admin/class-groups/partials/form.blade.php
Isi:
<form
    method="POST"
    action="{{ $action }}"
    class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
>
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

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
                        @selected((string) old('academic_year_id', $classGroup->academic_year_id) === (string) $academicYear->id)
                    >
                        {{ $academicYear->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('academic_year_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="grade_level_id" value="Tingkat Kelas" />

            <select
                id="grade_level_id"
                name="grade_level_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                <option value="">Pilih Tingkat Kelas</option>

                @foreach ($gradeLevels as $gradeLevel)
                    <option
                        value="{{ $gradeLevel->id }}"
                        @selected((string) old('grade_level_id', $classGroup->grade_level_id) === (string) $gradeLevel->id)
                    >
                        {{ $gradeLevel->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('grade_level_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="room_id" value="Ruangan" />

            <select
                id="room_id"
                name="room_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
                <option value="">Tanpa Ruangan</option>

                @foreach ($rooms as $room)
                    <option
                        value="{{ $room->id }}"
                        @selected((string) old('room_id', $classGroup->room_id) === (string) $room->id)
                    >
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="homeroom_teacher_user_id" value="Wali Kelas" />

            <select
                id="homeroom_teacher_user_id"
                name="homeroom_teacher_user_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >
                <option value="">Belum ditentukan</option>

                @foreach ($homeroomTeachers as $teacher)
                    <option
                        value="{{ $teacher->id }}"
                        @selected((string) old('homeroom_teacher_user_id', $classGroup->homeroom_teacher_user_id) === (string) $teacher->id)
                    >
                        {{ $teacher->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('homeroom_teacher_user_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="code" value="Kode Rombel" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $classGroup->code)"
                placeholder="VII-A"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Rombel" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $classGroup->name)"
                placeholder="Kelas VII A"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="parallel_name" value="Nama Paralel" />

            <x-text-input
                id="parallel_name"
                name="parallel_name"
                type="text"
                class="mt-1 block w-full"
                :value="old('parallel_name', $classGroup->parallel_name)"
                placeholder="A"
            />

            <x-input-error :messages="$errors->get('parallel_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="capacity" value="Kapasitas" />

            <x-text-input
                id="capacity"
                name="capacity"
                type="number"
                min="1"
                class="mt-1 block w-full"
                :value="old('capacity', $classGroup->capacity)"
            />

            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />

            <select
                id="status"
                name="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ([
                    'active' => 'Aktif',
                    'inactive' => 'Nonaktif',
                    'archived' => 'Arsip',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('status', $classGroup->status ?: 'active') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                @checked(old('is_active', $classGroup->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Aktif
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.class-groups.index') }}"
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

Tahap 12.4G: Sidebar dan Dashboard Shortcut
1. Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Tambahkan link:
@can('permission', 'class_groups.view')
    <x-ui.sidebar-link
        :href="route('admin.class-groups.index')"
        :active="request()->routeIs('admin.class-groups.*')"
    >
        Rombongan Belajar
    </x-ui.sidebar-link>
@endcan
Jika kondisi section belum memeriksa permission ini, tambahkan:
auth()->user()?->can('permission', 'class_groups.view') ||
2. Dashboard shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Rombongan Belajar',
    'description' => 'Kelola kelas aktif berdasarkan tahun ajaran, tingkat kelas, ruangan, dan wali kelas.',
    'href' => route('admin.class-groups.index'),
    'label' => 'Buka Rombel',
],

Tahap 12.4H: Test
Buat test:
php artisan make:test Admin/ClassGroupTest
Buka:
nano tests/Feature/Admin/ClassGroupTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassGroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_class_group_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.view');

        $academicYear = $this->createAcademicYear();
        $gradeLevel = $this->createGradeLevel();
        $room = $this->createRoom();

        ClassGroup::create([
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

        $response = $this
            ->actingAs($user)
            ->get('/admin/class-groups');

        $response
            ->assertStatus(200)
            ->assertSee('Rombongan Belajar')
            ->assertSee('Kelas VII A');
    }

    public function test_user_can_create_class_group(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.create');

        $academicYear = $this->createAcademicYear();
        $gradeLevel = $this->createGradeLevel();
        $room = $this->createRoom();

        $response = $this
            ->actingAs($user)
            ->post('/admin/class-groups', [
                'academic_year_id' => $academicYear->id,
                'grade_level_id' => $gradeLevel->id,
                'room_id' => $room->id,
                'homeroom_teacher_user_id' => null,
                'code' => 'VII-A',
                'name' => 'Kelas VII A',
                'parallel_name' => 'A',
                'capacity' => 32,
                'status' => 'active',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.class-groups.index'));

        $this->assertDatabaseHas('class_groups', [
            'academic_year_id' => $academicYear->id,
            'grade_level_id' => $gradeLevel->id,
            'room_id' => $room->id,
            'code' => 'VII-A',
            'name' => 'Kelas VII A',
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_class_group(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.update');

        $academicYear = $this->createAcademicYear();
        $gradeLevel = $this->createGradeLevel();
        $room = $this->createRoom();

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

        $response = $this
            ->actingAs($user)
            ->put(route('admin.class-groups.update', $classGroup), [
                'academic_year_id' => $academicYear->id,
                'grade_level_id' => $gradeLevel->id,
                'room_id' => $room->id,
                'homeroom_teacher_user_id' => null,
                'code' => 'VII-A',
                'name' => 'Kelas VII A Revisi',
                'parallel_name' => 'A',
                'capacity' => 34,
                'status' => 'active',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.class-groups.index'));

        $this->assertDatabaseHas('class_groups', [
            'id' => $classGroup->id,
            'name' => 'Kelas VII A Revisi',
            'capacity' => 34,
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

    private function createGradeLevel(): GradeLevel
    {
        return GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);
    }

    private function createRoom(): Room
    {
        return Room::create([
            'code' => 'R-VII-A',
            'name' => 'Ruang VII A',
            'room_type' => 'classroom',
            'capacity' => 32,
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_class_group_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Class Group Role',
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

Tahap 12.4I: Jalankan Migration, Seeder, Test, Build
php artisan migrate
php artisan db:seed --class=ClassGroupSeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=class-groups
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/class-groups
GET|HEAD  admin/class-groups/create
POST      admin/class-groups
GET|HEAD  admin/class-groups/{classGroup}/edit
PUT       admin/class-groups/{classGroup}
Target test:
80+ tests passed
build berhasil

Tahap 12.4J: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/ClassGroup.php \
        app/Http/Controllers/Admin/ClassGroupController.php \
        database/migrations/ \
        database/seeders/ClassGroupSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/class-groups/ \
        resources/views/layouts/sidebar.blade.php \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/ClassGroupTest.php
Commit:
git commit -m "feat: add class group module"
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
Setelah berhasil, kita lanjut ke Tahap 12.5: Mata Pelajaran.















