# Tahap 12.5 — Mata Pelajaran

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 26596–27624.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.5: Mata Pelajaran
Mata pelajaran adalah data master. Artinya, data ini tidak langsung melekat ke tahun ajaran tertentu.
Contoh:
Matematika
Bahasa Indonesia
IPA
IPS
Akidah Akhlak
Al-Qur'an Hadis
Fiqih
SKI
Bahasa Arab
Nanti pada tahap berikutnya, mata pelajaran akan dihubungkan ke:
kurikulum
tahun ajaran
kelas
guru pengampu
jadwal
nilai
rapor
Pada tahap ini kita buat fondasinya dulu:
subjects

Tahap 12.5A: Model dan Migration
1. Buat model dan migration
Jalankan:
php artisan make:model Subject -m
2. Isi migration subjects
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_subjects_table.php | head -n 1)
Isi:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel mata pelajaran.
     */
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);

            $table->string('subject_group', 50)->default('general');

            $table->boolean('is_local_content')->default(false);
            $table->boolean('is_religious')->default(false);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('subject_group');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel mata pelajaran.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
Mengapa subjects tidak punya academic_year_id?
Karena Matematika tetap sama sebagai data master.
Yang berubah per tahun ajaran nanti adalah:
Matematika dipakai di kelas berapa
guru pengampunya siapa
jumlah jamnya berapa
masuk rapor atau tidak
Itu akan dibuat pada modul kurikulum atau penugasan mengajar, bukan di tabel master mata pelajaran.

Tahap 12.5B: Model Subject
Buka:
nano app/Models/Subject.php
Isi:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'subject_group',
        'is_local_content',
        'is_religious',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_local_content' => 'boolean',
            'is_religious' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}

Tahap 12.5C: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'subjects.view',
    'module' => 'subjects',
    'action' => 'view',
    'display_name' => 'Melihat Mata Pelajaran',
    'description' => 'Melihat data mata pelajaran.',
],
[
    'name' => 'subjects.create',
    'module' => 'subjects',
    'action' => 'create',
    'display_name' => 'Membuat Mata Pelajaran',
    'description' => 'Menambahkan data mata pelajaran.',
],
[
    'name' => 'subjects.update',
    'module' => 'subjects',
    'action' => 'update',
    'display_name' => 'Mengubah Mata Pelajaran',
    'description' => 'Mengubah data mata pelajaran.',
],

Tahap 12.5D: Seeder Mata Pelajaran
Buat seeder:
php artisan make:seeder SubjectSeeder
Buka:
nano database/seeders/SubjectSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Membuat data mata pelajaran awal.
     */
    public function run(): void
    {
        $subjects = [
            [
                'code' => 'PAI-QH',
                'name' => "Al-Qur'an Hadis",
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'PAI-AA',
                'name' => 'Akidah Akhlak',
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'PAI-FQ',
                'name' => 'Fiqih',
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'PAI-SKI',
                'name' => 'Sejarah Kebudayaan Islam',
                'subject_group' => 'religious',
                'is_religious' => true,
            ],
            [
                'code' => 'BIN',
                'name' => 'Bahasa Indonesia',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'BIG',
                'name' => 'Bahasa Inggris',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'MTK',
                'name' => 'Matematika',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'IPA',
                'name' => 'Ilmu Pengetahuan Alam',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'IPS',
                'name' => 'Ilmu Pengetahuan Sosial',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'PJOK',
                'name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'SBK',
                'name' => 'Seni Budaya',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'INF',
                'name' => 'Informatika',
                'subject_group' => 'general',
                'is_religious' => false,
            ],
            [
                'code' => 'BAR',
                'name' => 'Bahasa Arab',
                'subject_group' => 'language',
                'is_religious' => false,
            ],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(
                [
                    'code' => $subject['code'],
                ],
                [
                    'name' => $subject['name'],
                    'subject_group' => $subject['subject_group'],
                    'is_local_content' => $subject['is_local_content'] ?? false,
                    'is_religious' => $subject['is_religious'],
                    'is_active' => true,
                    'description' => null,
                ]
            );
        }
    }
}

Tahap 12.5E: Controller Mata Pelajaran
Buat controller:
php artisan make:controller Admin/SubjectController
Buka:
nano app/Http/Controllers/Admin/SubjectController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->orderBy('subject_group')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.subjects.index', [
            'subjects' => $subjects,
        ]);
    }

    public function create(): View
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateSubject($request);

        Subject::create($validated);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil dibuat.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subjects.edit', [
            'subject' => $subject,
        ]);
    }

    public function update(
        Request $request,
        Subject $subject
    ): RedirectResponse {
        $validated = $this->validateSubject($request, $subject);

        $subject->update($validated);

        return redirect()
            ->route('admin.subjects.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Validasi mata pelajaran.
     *
     * @return array<string, mixed>
     */
    private function validateSubject(
        Request $request,
        ?Subject $subject = null
    ): array {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('subjects', 'code')->ignore($subject),
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'subject_group' => [
                'required',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_local_content' => [
                'nullable',
                'boolean',
            ],
            'is_religious' => [
                'nullable',
                'boolean',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['is_local_content'] = $request->boolean('is_local_content');
        $validated['is_religious'] = $request->boolean('is_religious');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}

Tahap 12.5F: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\SubjectController;
Di dalam group admin, tambahkan:
Route::get('/subjects', [SubjectController::class, 'index'])
    ->middleware('permission:subjects.view')
    ->name('subjects.index');

Route::get('/subjects/create', [SubjectController::class, 'create'])
    ->middleware('permission:subjects.create')
    ->name('subjects.create');

Route::post('/subjects', [SubjectController::class, 'store'])
    ->middleware('permission:subjects.create')
    ->name('subjects.store');

Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])
    ->middleware('permission:subjects.update')
    ->name('subjects.edit');

Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
    ->middleware('permission:subjects.update')
    ->name('subjects.update');

Tahap 12.5G: View Mata Pelajaran
Buat folder:
mkdir -p resources/views/admin/subjects/partials
1. Index
Buat file:
nano resources/views/admin/subjects/index.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mata Pelajaran
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Kelola data master mata pelajaran.
                </p>
            </div>

            @can('permission', 'subjects.create')
                <a
                    href="{{ route('admin.subjects.create') }}"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Tambah Mata Pelajaran
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
                                    Mata Pelajaran
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Kelompok
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                    Jenis
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
                            @forelse ($subjects as $subject)
                                <tr>
                                    <td class="px-4 py-3 font-mono">
                                        {{ $subject->code }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ $subject->name }}
                                        </div>

                                        @if ($subject->description)
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ $subject->description }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $subject->subject_group }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            @if ($subject->is_religious)
                                                <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                                    Keagamaan
                                                </span>
                                            @endif

                                            @if ($subject->is_local_content)
                                                <span class="rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700">
                                                    Muatan Lokal
                                                </span>
                                            @endif

                                            @unless ($subject->is_religious || $subject->is_local_content)
                                                <span class="text-gray-500">
                                                    Umum
                                                </span>
                                            @endunless
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if ($subject->is_active)
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
                                        @can('permission', 'subjects.update')
                                            <a
                                                href="{{ route('admin.subjects.edit', $subject) }}"
                                                class="text-sm font-medium text-green-700 hover:text-green-900"
                                            >
                                                Edit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        Belum ada mata pelajaran.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $subjects->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
2. Create
Buat file:
nano resources/views/admin/subjects/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tambah Mata Pelajaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Tambahkan mata pelajaran baru.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.subjects.partials.form', [
                'subject' => new \App\Models\Subject(),
                'action' => route('admin.subjects.store'),
                'method' => 'POST',
                'buttonLabel' => 'Simpan Mata Pelajaran',
            ])
        </div>
    </div>
</x-app-layout>
3. Edit
Buat file:
nano resources/views/admin/subjects/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Mata Pelajaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data mata pelajaran.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @include('admin.subjects.partials.form', [
                'subject' => $subject,
                'action' => route('admin.subjects.update', $subject),
                'method' => 'PUT',
                'buttonLabel' => 'Perbarui Mata Pelajaran',
            ])
        </div>
    </div>
</x-app-layout>
4. Form partial
Buat file:
nano resources/views/admin/subjects/partials/form.blade.php
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
            <x-input-label for="code" value="Kode Mata Pelajaran" />

            <x-text-input
                id="code"
                name="code"
                type="text"
                class="mt-1 block w-full"
                :value="old('code', $subject->code)"
                placeholder="MTK"
                required
            />

            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Nama Mata Pelajaran" />

            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $subject->name)"
                placeholder="Matematika"
                required
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="subject_group" value="Kelompok Mata Pelajaran" />

            <select
                id="subject_group"
                name="subject_group"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                required
            >
                @foreach ([
                    'general' => 'Umum',
                    'religious' => 'Keagamaan',
                    'language' => 'Bahasa',
                    'local_content' => 'Muatan Lokal',
                    'extracurricular' => 'Ekstrakurikuler',
                ] as $value => $label)
                    <option
                        value="{{ $value }}"
                        @selected(old('subject_group', $subject->subject_group ?: 'general') === $value)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('subject_group')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" value="Deskripsi" />

            <textarea
                id="description"
                name="description"
                rows="3"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            >{{ old('description', $subject->description) }}</textarea>

            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="space-y-3">
            <div class="flex items-center gap-2">
                <input
                    id="is_religious"
                    name="is_religious"
                    type="checkbox"
                    value="1"
                    class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                    @checked(old('is_religious', $subject->is_religious))
                >

                <label for="is_religious" class="text-sm text-gray-700">
                    Mata pelajaran keagamaan
                </label>
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_local_content"
                    name="is_local_content"
                    type="checkbox"
                    value="1"
                    class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                    @checked(old('is_local_content', $subject->is_local_content))
                >

                <label for="is_local_content" class="text-sm text-gray-700">
                    Muatan lokal
                </label>
            </div>

            <div class="flex items-center gap-2">
                <input
                    id="is_active"
                    name="is_active"
                    type="checkbox"
                    value="1"
                    class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                    @checked(old('is_active', $subject->is_active ?? true))
                >

                <label for="is_active" class="text-sm text-gray-700">
                    Aktif
                </label>
            </div>
        </div>
    </div>

    <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
        <a
            href="{{ route('admin.subjects.index') }}"
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

Tahap 12.5H: Sidebar dan Dashboard Shortcut
1. Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Tambahkan link:
@can('permission', 'subjects.view')
    <x-ui.sidebar-link
        :href="route('admin.subjects.index')"
        :active="request()->routeIs('admin.subjects.*')"
    >
        Mata Pelajaran
    </x-ui.sidebar-link>
@endcan
Jika kondisi section belum memeriksa permission ini, tambahkan:
auth()->user()?->can('permission', 'subjects.view') ||
2. Dashboard shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Mata Pelajaran',
    'description' => 'Kelola data master mata pelajaran untuk kurikulum, jadwal, nilai, dan rapor.',
    'href' => route('admin.subjects.index'),
    'label' => 'Buka Mapel',
],

Tahap 12.5I: Test
Buat test:
php artisan make:test Admin/SubjectTest
Buka:
nano tests/Feature/Admin/SubjectTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_subject_page(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'subjects.view');

        Subject::create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'subject_group' => 'general',
            'is_local_content' => false,
            'is_religious' => false,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/subjects');

        $response
            ->assertStatus(200)
            ->assertSee('Mata Pelajaran')
            ->assertSee('Matematika');
    }

    public function test_user_can_create_subject(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'subjects.create');

        $response = $this
            ->actingAs($user)
            ->post('/admin/subjects', [
                'code' => 'IPA',
                'name' => 'Ilmu Pengetahuan Alam',
                'subject_group' => 'general',
                'description' => 'Mata pelajaran sains.',
                'is_local_content' => '0',
                'is_religious' => '0',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'code' => 'IPA',
            'name' => 'Ilmu Pengetahuan Alam',
            'subject_group' => 'general',
            'is_active' => true,
        ]);
    }

    public function test_user_can_update_subject(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'subjects.update');

        $subject = Subject::create([
            'code' => 'MTK',
            'name' => 'Matematika',
            'subject_group' => 'general',
            'is_local_content' => false,
            'is_religious' => false,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('admin.subjects.update', $subject), [
                'code' => 'MTK',
                'name' => 'Matematika Revisi',
                'subject_group' => 'general',
                'description' => 'Revisi.',
                'is_local_content' => '0',
                'is_religious' => '0',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.subjects.index'));

        $this->assertDatabaseHas('subjects', [
            'id' => $subject->id,
            'name' => 'Matematika Revisi',
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_subject_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Subject Role',
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

Tahap 12.5J: Jalankan Migration, Seeder, Test, Build
Jalankan:
php artisan migrate
php artisan db:seed --class=SubjectSeeder
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=subjects
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/subjects
GET|HEAD  admin/subjects/create
POST      admin/subjects
GET|HEAD  admin/subjects/{subject}/edit
PUT       admin/subjects/{subject}
Target test:
83+ tests passed
build berhasil

Tahap 12.5K: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/Subject.php \
        app/Http/Controllers/Admin/SubjectController.php \
        database/migrations/ \
        database/seeders/SubjectSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/subjects/ \
        resources/views/layouts/sidebar.blade.php \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/SubjectTest.php
Commit:
git commit -m "feat: add subject module"
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
Setelah berhasil, kita lanjut ke Tahap 12.6: Data Guru dan Pegawai.




















