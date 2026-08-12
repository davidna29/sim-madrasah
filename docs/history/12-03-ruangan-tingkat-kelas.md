# Tahap 12.3 — Data Ruangan dan Tingkat Kelas

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 23660–25380.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.3: Data Ruangan dan Tingkat Kelas
Pada desain ERD, grade_levels, class_groups, dan rooms berada dalam kelompok periode dan data akademik. grade_levels menyimpan tingkat kelas, rooms menyimpan ruangan, dan class_groups nanti akan menghubungkan tingkat kelas dengan rombongan belajar.
Agar tidak terlalu banyak perubahan sekaligus, tahap ini kita fokus pada:
grade_levels
rooms
class_groups kita buat pada Tahap 12.4.

Tahap 12.3A: Migration dan Model
1. Buat model dan migration
Jalankan:
php artisan make:model GradeLevel -m
php artisan make:model Room -m

2. Migration grade_levels
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_grade_levels_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel tingkat kelas.
     */
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->unsignedTinyInteger('level_number');

            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('level_number');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel tingkat kelas.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};

3. Migration rooms
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_rooms_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel ruangan.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 100);

            $table->string('room_type', 50)->default('classroom');
            $table->unsignedSmallInteger('capacity')->nullable();

            $table->string('location', 150)->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('room_type');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel ruangan.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

Tahap 12.3B: Model
1. Model GradeLevel
Buka:
nano app/Models/GradeLevel.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'level_number',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

2. Model Room
Buka:
nano app/Models/Room.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'room_type',
        'capacity',
        'location',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

Tahap 12.3C: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'grade_levels.view',
    'module' => 'grade_levels',
    'action' => 'view',
    'display_name' => 'Melihat Tingkat Kelas',
    'description' => 'Melihat data tingkat kelas.',
],
[
    'name' => 'grade_levels.create',
    'module' => 'grade_levels',
    'action' => 'create',
    'display_name' => 'Membuat Tingkat Kelas',
    'description' => 'Menambahkan data tingkat kelas.',
],
[
    'name' => 'grade_levels.update',
    'module' => 'grade_levels',
    'action' => 'update',
    'display_name' => 'Mengubah Tingkat Kelas',
    'description' => 'Mengubah data tingkat kelas.',
],
[
    'name' => 'rooms.view',
    'module' => 'rooms',
    'action' => 'view',
    'display_name' => 'Melihat Ruangan',
    'description' => 'Melihat data ruangan.',
],
[
    'name' => 'rooms.create',
    'module' => 'rooms',
    'action' => 'create',
    'display_name' => 'Membuat Ruangan',
    'description' => 'Menambahkan data ruangan.',
],
[
    'name' => 'rooms.update',
    'module' => 'rooms',
    'action' => 'update',
    'display_name' => 'Mengubah Ruangan',
    'description' => 'Mengubah data ruangan.',
],

Tahap 12.3D: Seeder Awal
1. Seeder GradeLevelSeeder
Jalankan:
php artisan make:seeder GradeLevelSeeder
Buka:
nano database/seeders/GradeLevelSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    /**
     * Membuat data tingkat kelas awal.
     */
    public function run(): void
    {
        $gradeLevels = [
            [
                'code' => 'VII',
                'name' => 'Kelas VII',
                'level_number' => 7,
            ],
            [
                'code' => 'VIII',
                'name' => 'Kelas VIII',
                'level_number' => 8,
            ],
            [
                'code' => 'IX',
                'name' => 'Kelas IX',
                'level_number' => 9,
            ],
        ];

        foreach ($gradeLevels as $gradeLevel) {
            GradeLevel::query()->updateOrCreate(
                [
                    'code' => $gradeLevel['code'],
                ],
                [
                    'name' => $gradeLevel['name'],
                    'level_number' => $gradeLevel['level_number'],
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}

2. Seeder RoomSeeder
Jalankan:
php artisan make:seeder RoomSeeder
Buka:
nano database/seeders/RoomSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Membuat data ruangan awal.
     */
    public function run(): void
    {
        $rooms = [
            [
                'code' => 'R-VII-A',
                'name' => 'Ruang VII A',
                'room_type' => 'classroom',
                'capacity' => 32,
            ],
            [
                'code' => 'R-VIII-A',
                'name' => 'Ruang VIII A',
                'room_type' => 'classroom',
                'capacity' => 32,
            ],
            [
                'code' => 'R-IX-A',
                'name' => 'Ruang IX A',
                'room_type' => 'classroom',
                'capacity' => 32,
            ],
            [
                'code' => 'LAB-KOM',
                'name' => 'Laboratorium Komputer',
                'room_type' => 'laboratory',
                'capacity' => 30,
            ],
        ];

        foreach ($rooms as $room) {
            Room::query()->updateOrCreate(
                [
                    'code' => $room['code'],
                ],
                [
                    'name' => $room['name'],
                    'room_type' => $room['room_type'],
                    'capacity' => $room['capacity'],
                    'location' => null,
                    'description' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}

Tahap 12.3E: Jalankan Migration dan Seeder
Jalankan:
php artisan migrate
php artisan db:seed --class=GradeLevelSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=RbacSeeder
Periksa tabel:
php artisan db:table grade_levels
php artisan db:table rooms
Target:
grade_levels tersedia
rooms tersedia

Tahap 12.3F: Test Database dan Model
Buat test:
php artisan make:test Admin/GradeLevelRoomTest
Buka:
nano tests/Feature/Admin/GradeLevelRoomTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\GradeLevel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLevelRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_grade_level_can_be_created(): void
    {
        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'description' => 'Tingkat awal MTs.',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('grade_levels', [
            'id' => $gradeLevel->id,
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);
    }

    public function test_room_can_be_created(): void
    {
        $room = Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'location' => 'Gedung Utama',
            'description' => 'Ruang praktik TIK.',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);
    }
}

Tahap 12.3G: Pemeriksaan
Jalankan:
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 69 menjadi sekitar 71.

Tahap 12.3H: Commit ke GitHub
Jika semua berhasil:
git status
Lalu:
git add app/Models/GradeLevel.php \
        app/Models/Room.php \
        database/migrations/ \
        database/seeders/GradeLevelSeeder.php \
        database/seeders/RoomSeeder.php \
        database/seeders/RbacSeeder.php \
        tests/Feature/Admin/GradeLevelRoomTest.php
Commit:
git commit -m "feat: add grade level and room foundations"
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
Jika berhasil, kita lanjut ke Tahap 12.3B lanjutan: Halaman CRUD Tingkat Kelas dan Ruangan.











Tahap 12.3B: Halaman CRUD Tingkat Kelas dan Ruangan.
CRUD berarti:
Create  = tambah data
Read    = lihat data
Update  = ubah data
Delete  = hapus data
Untuk tahap ini, kita belum membuat delete. Kita gunakan is_active untuk menonaktifkan data. Ini lebih aman karena data ruangan dan tingkat kelas nanti bisa terhubung dengan kelas, jadwal, absensi, dan histori akademik.

Tahap 12.3B: CRUD Tingkat Kelas dan Ruangan
1. Buat controller
Jalankan:
php artisan make:controller Admin/GradeLevelController
php artisan make:controller Admin/RoomController

2. Isi GradeLevelController
Buka:
nano app/Http/Controllers/Admin/GradeLevelController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeLevelController extends Controller
{
    public function index(): View
    {
        $gradeLevels = GradeLevel::query()
            ->orderBy('level_number')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.grade-levels.index', [
            'gradeLevels' => $gradeLevels,
        ]);
    }

    public function create(): View
    {
        return view('admin.grade-levels.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGradeLevel($request);

        GradeLevel::create($validated);

        return redirect()
            ->route('admin.grade-levels.index')
            ->with('success', 'Tingkat kelas berhasil dibuat.');
    }

    public function edit(GradeLevel $gradeLevel): View
    {
        return view('admin.grade-levels.edit', [
            'gradeLevel' => $gradeLevel,
        ]);
    }

    public function update(
        Request $request,
        GradeLevel $gradeLevel
    ): RedirectResponse {
        $validated = $this->validateGradeLevel(
            $request,
            $gradeLevel
        );

        $gradeLevel->update($validated);

        return redirect()
            ->route('admin.grade-levels.index')
            ->with('success', 'Tingkat kelas berhasil diperbarui.');
    }

    /**
     * Validasi tingkat kelas.
     *
     * @return array<string, mixed>
     */
    private function validateGradeLevel(
        Request $request,
        ?GradeLevel $gradeLevel = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('grade_levels', 'code')
                    ->ignore($gradeLevel),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'level_number' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

3. Isi RoomController
Buka:
nano app/Http/Controllers/Admin/RoomController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::query()
            ->orderBy('room_type')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): View
    {
        return view('admin.rooms.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRoom($request);

        Room::create($validated);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dibuat.');
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.edit', [
            'room' => $room,
        ]);
    }

    public function update(
        Request $request,
        Room $room
    ): RedirectResponse {
        $validated = $this->validateRoom(
            $request,
            $room
        );

        $room->update($validated);

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui.');
    }

    /**
     * Validasi ruangan.
     *
     * @return array<string, mixed>
     */
    private function validateRoom(
        Request $request,
        ?Room $room = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('rooms', 'code')
                    ->ignore($room),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'room_type' => [
                'required',
                'string',
                'max:50',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
            'location' => [
                'nullable',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]) + [
            'is_active' => $request->boolean('is_active'),
        ];
    }
}

4. Tambahkan route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\RoomController;
Di dalam group admin, tambahkan:
Route::get('/grade-levels', [GradeLevelController::class, 'index'])
    ->middleware('permission:grade_levels.view')
    ->name('grade-levels.index');

Route::get('/grade-levels/create', [GradeLevelController::class, 'create'])
    ->middleware('permission:grade_levels.create')
    ->name('grade-levels.create');

Route::post('/grade-levels', [GradeLevelController::class, 'store'])
    ->middleware('permission:grade_levels.create')
    ->name('grade-levels.store');

Route::get('/grade-levels/{gradeLevel}/edit', [GradeLevelController::class, 'edit'])
    ->middleware('permission:grade_levels.update')
    ->name('grade-levels.edit');

Route::put('/grade-levels/{gradeLevel}', [GradeLevelController::class, 'update'])
    ->middleware('permission:grade_levels.update')
    ->name('grade-levels.update');

Route::get('/rooms', [RoomController::class, 'index'])
    ->middleware('permission:rooms.view')
    ->name('rooms.index');

Route::get('/rooms/create', [RoomController::class, 'create'])
    ->middleware('permission:rooms.create')
    ->name('rooms.create');

Route::post('/rooms', [RoomController::class, 'store'])
    ->middleware('permission:rooms.create')
    ->name('rooms.store');

Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])
    ->middleware('permission:rooms.update')
    ->name('rooms.edit');

Route::put('/rooms/{room}', [RoomController::class, 'update'])
    ->middleware('permission:rooms.update')
    ->name('rooms.update');

5. View daftar tingkat kelas
Buat folder:
mkdir -p resources/views/admin/grade-levels
Buat file:
nano resources/views/admin/grade-levels/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tingkat Kelas
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola tingkat kelas seperti VII, VIII, IX, X, XI, dan XII.
                </p>
            </div>

            @can('permission', 'grade_levels.create')
                <a
                    href="{{ route('admin.grade-levels.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Tingkat Kelas
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kode
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Nama
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Nomor Tingkat
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
                            @forelse ($gradeLevels as $gradeLevel)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $gradeLevel->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $gradeLevel->name }}
                                        </div>

                                        @if ($gradeLevel->description)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $gradeLevel->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $gradeLevel->level_number }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($gradeLevel->is_active)
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
                                        @can('permission', 'grade_levels.update')
                                            <a
                                                href="{{ route('admin.grade-levels.edit', $gradeLevel) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="5"
                                        class="px-4 py-6 text-center text-gray-500"
                                    >
                                        Belum ada tingkat kelas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $gradeLevels->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

6. View create tingkat kelas
Buat file:
nano resources/views/admin/grade-levels/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Tingkat Kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan tingkat kelas baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.grade-levels.partials.form', [
                'gradeLevel' => new \App\Models\GradeLevel(),
                'action' => route('admin.grade-levels.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Tingkat Kelas',
            ])
        </div>
    </div>
</x-app-layout>

7. View edit tingkat kelas
Buat file:
nano resources/views/admin/grade-levels/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Tingkat Kelas
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data tingkat kelas.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.grade-levels.partials.form', [
                'gradeLevel' => $gradeLevel,
                'action' => route('admin.grade-levels.update', $gradeLevel),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Tingkat Kelas',
            ])
        </div>
    </div>
</x-app-layout>

8. Form partial tingkat kelas
Buat folder:
mkdir -p resources/views/admin/grade-levels/partials
Buat file:
nano resources/views/admin/grade-levels/partials/form.blade.php
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
            <x-input-label for="code" value="Kode" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $gradeLevel->code)"
                placeholder="VII"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Tingkat Kelas" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $gradeLevel->name)"
                placeholder="Kelas VII"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="level_number" value="Nomor Tingkat" />

            <x-text-input
                id="level_number"
                name="level_number"
                type="number"
                min="1"
                max="12"
                class="mt-1 block w-full"
                :value="old('level_number', $gradeLevel->level_number)"
                required
            />

            <x-input-error :messages="$errors->get('level_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />

            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >{{ old('description', $gradeLevel->description) }}</textarea>

            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                @checked(old('is_active', $gradeLevel->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Aktif
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.grade-levels.index') }}"
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

9. View daftar ruangan
Buat folder:
mkdir -p resources/views/admin/rooms
Buat file:
nano resources/views/admin/rooms/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ruangan
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola ruang kelas, laboratorium, perpustakaan, dan ruangan lain.
                </p>
            </div>

            @can('permission', 'rooms.create')
                <a
                    href="{{ route('admin.rooms.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Ruangan
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
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kode
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Nama Ruangan
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Jenis
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kapasitas
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
                            @forelse ($rooms as $room)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $room->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $room->name }}
                                        </div>

                                        @if ($room->location)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $room->location }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $room->room_type }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $room->capacity ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($room->is_active)
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
                                        @can('permission', 'rooms.update')
                                            <a
                                                href="{{ route('admin.rooms.edit', $room) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="6"
                                        class="px-4 py-6 text-center text-gray-500"
                                    >
                                        Belum ada ruangan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $rooms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

10. View create dan edit ruangan
Buat file:
nano resources/views/admin/rooms/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Ruangan
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan ruangan baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.rooms.partials.form', [
                'room' => new \App\Models\Room(),
                'action' => route('admin.rooms.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Ruangan',
            ])
        </div>
    </div>
</x-app-layout>
Buat file:
nano resources/views/admin/rooms/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Ruangan
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data ruangan.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.rooms.partials.form', [
                'room' => $room,
                'action' => route('admin.rooms.update', $room),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Ruangan',
            ])
        </div>
    </div>
</x-app-layout>

11. Form partial ruangan
Buat folder:
mkdir -p resources/views/admin/rooms/partials
Buat file:
nano resources/views/admin/rooms/partials/form.blade.php
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
            <x-input-label for="code" value="Kode Ruangan" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $room->code)"
                placeholder="LAB-KOM"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Ruangan" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $room->name)"
                placeholder="Laboratorium Komputer"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="room_type" value="Jenis Ruangan" />

            <select
                id="room_type"
                name="room_type"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ([
                    'classroom' => 'Ruang Kelas',
                    'laboratory' => 'Laboratorium',
                    'library' => 'Perpustakaan',
                    'office' => 'Kantor',
                    'hall' => 'Aula',
                    'other' => 'Lainnya',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('room_type', $room->room_type ?: 'classroom') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('room_type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="capacity" value="Kapasitas" />

            <x-text-input
                id="capacity"
                name="capacity"
                type="number"
                min="1"
                class="mt-1 block w-full"
                :value="old('capacity', $room->capacity)"
            />

            <x-input-error :messages="$errors->get('capacity')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="location" value="Lokasi" />

            <x-text-input
                id="location"
                name="location"
                type="text"
                class="mt-1 block w-full"
                :value="old('location', $room->location)"
                placeholder="Gedung Utama Lantai 1"
            />

            <x-input-error :messages="$errors->get('location')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />

            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >{{ old('description', $room->description) }}</textarea>

            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2">
            <input
                id="is_active"
                name="is_active"
                type="checkbox"
                value="1"
                class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                @checked(old('is_active', $room->is_active ?? true))
            >

            <label for="is_active" class="text-sm text-gray-700">
                Aktif
            </label>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.rooms.index') }}"
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

12. Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Di section Data Master atau Administrasi Sistem, tambahkan:
@can('permission', 'grade_levels.view')
    <x-ui.sidebar-link
        :href="route('admin.grade-levels.index')"
        :active="request()->routeIs('admin.grade-levels.*')"
    >
        Tingkat Kelas
    </x-ui.sidebar-link>
@endcan

@can('permission', 'rooms.view')
    <x-ui.sidebar-link
        :href="route('admin.rooms.index')"
        :active="request()->routeIs('admin.rooms.*')"
    >
        Ruangan
    </x-ui.sidebar-link>
@endcan
Jika kondisi section belum memeriksa permission ini, tambahkan:
auth()->user()?->can('permission', 'grade_levels.view') ||
auth()->user()?->can('permission', 'rooms.view') ||

13. Test CRUD
Buat test:
php artisan make:test Admin/GradeLevelRoomCrudTest
Buka:
nano tests/Feature/Admin/GradeLevelRoomCrudTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\GradeLevel;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeLevelRoomCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_grade_level_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.view');

        GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/grade-levels');

        $response
            ->assertStatus(200)
            ->assertSee('Tingkat Kelas')
            ->assertSee('Kelas VII');
    }

    public function test_user_can_create_grade_level(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/grade-levels', [
                'code' => 'X',
                'name' => 'Kelas X',
                'level_number' => 10,
                'description' => 'Tingkat kelas MA.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.grade-levels.index'));

        $this->assertDatabaseHas('grade_levels', [
            'code' => 'X',
            'name' => 'Kelas X',
            'level_number' => 10,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_grade_level(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'grade_levels.update');

        $gradeLevel = GradeLevel::create([
            'code' => 'VII',
            'name' => 'Kelas VII',
            'level_number' => 7,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.grade-levels.update', $gradeLevel), [
                'code' => 'VII',
                'name' => 'Kelas VII Revisi',
                'level_number' => 7,
                'description' => 'Revisi.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.grade-levels.index'));

        $this->assertDatabaseHas('grade_levels', [
            'id' => $gradeLevel->id,
            'name' => 'Kelas VII Revisi',
        ]);
    }

    public function test_user_can_view_room_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.view');

        Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/rooms');

        $response
            ->assertStatus(200)
            ->assertSee('Ruangan')
            ->assertSee('Laboratorium Komputer');
    }

    public function test_user_can_create_room(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/rooms', [
                'code' => 'R-AULA',
                'name' => 'Aula Madrasah',
                'room_type' => 'hall',
                'capacity' => 200,
                'location' => 'Gedung Utama',
                'description' => 'Aula kegiatan.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'code' => 'R-AULA',
            'name' => 'Aula Madrasah',
            'room_type' => 'hall',
            'capacity' => 200,
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_room(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'rooms.update');

        $room = Room::create([
            'code' => 'LAB-KOM',
            'name' => 'Laboratorium Komputer',
            'room_type' => 'laboratory',
            'capacity' => 30,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.rooms.update', $room), [
                'code' => 'LAB-KOM',
                'name' => 'Laboratorium Komputer Revisi',
                'room_type' => 'laboratory',
                'capacity' => 35,
                'location' => 'Gedung B',
                'description' => 'Revisi.',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.rooms.index'));

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Laboratorium Komputer Revisi',
            'capacity' => 35,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_grade_room_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Grade Room Role',
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

14. Jalankan pemeriksaan
php artisan optimize:clear
php artisan route:list --path=grade-levels
php artisan route:list --path=rooms
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 71 menjadi sekitar 77.

15. Commit
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/Admin/GradeLevelController.php \
        app/Http/Controllers/Admin/RoomController.php \
        routes/web.php \
        resources/views/admin/grade-levels/ \
        resources/views/admin/rooms/ \
        resources/views/layouts/sidebar.blade.php \
        tests/Feature/Admin/GradeLevelRoomCrudTest.php
Commit:
git commit -m "feat: add grade level and room CRUD pages"
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
Setelah berhasil, kita lanjut ke Tahap 12.4: Rombongan Belajar atau Class Groups.














