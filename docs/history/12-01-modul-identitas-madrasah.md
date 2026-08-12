# Tahap 12.1 — Modul Identitas Madrasah

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 21419–22397.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

=========================================================










Tahap 12.1: Modul Identitas Madrasah
Modul pertama yang kita buat adalah Identitas Madrasah.
Modul ini penting karena menjadi sumber data resmi untuk:
nama madrasah
NSM
NPSN
alamat
email
telepon
zona waktu
status madrasah
kop surat
kwitansi
rapor
website publik
PPDB
PKKM
akreditasi
Desain database Tahap 6 sudah menetapkan tabel madrasahs sebagai penyimpan identitas madrasah, dengan kolom seperti code, name, nsm, npsn, email, phone, address, province, timezone, dan is_active.
Pada tahap ini kita belum membuat upload logo. Kolom logo_file_id tetap disiapkan, tetapi fitur upload file akan dibuat setelah modul file management tersedia.

Tahap 12.1A: Migration dan Model Madrasah
1. Buat model dan migration
Jalankan:
php artisan make:model Madrasah -m
File yang dibuat:
app/Models/Madrasah.php
database/migrations/xxxx_xx_xx_xxxxxx_create_madrasahs_table.php

2. Isi migration madrasahs
Buka migration terbaru:
nano $(ls -t database/migrations/*_create_madrasahs_table.php | head -n 1)
Ganti seluruh isinya:
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel identitas madrasah.
     */
    public function up(): void
    {
        Schema::create('madrasahs', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();
            $table->string('name', 150);

            $table->string('nsm', 30)
                ->nullable()
                ->unique();

            $table->string('npsn', 30)
                ->nullable()
                ->unique();

            $table->string('email', 191)
                ->nullable();

            $table->string('phone', 30)
                ->nullable();

            $table->text('address')
                ->nullable();

            $table->string('village', 100)
                ->nullable();

            $table->string('district', 100)
                ->nullable();

            $table->string('city', 100)
                ->nullable();

            $table->string('province', 100)
                ->nullable();

            $table->string('postal_code', 10)
                ->nullable();

            /*
             * Foreign key ke tabel files belum dibuat karena
             * modul file management belum tersedia.
             */
            $table->unsignedBigInteger('logo_file_id')
                ->nullable();

            $table->string('timezone', 50)
                ->default('Asia/Jakarta');

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Menghapus tabel madrasahs.
     */
    public function down(): void
    {
        Schema::dropIfExists('madrasahs');
    }
};

3. Isi model Madrasah
Buka:
nano app/Models/Madrasah.php
Ganti seluruh isinya:
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madrasah extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'code',
        'name',
        'nsm',
        'npsn',
        'email',
        'phone',
        'address',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'logo_file_id',
        'timezone',
        'is_active',
    ];

    /**
     * Konversi tipe data database.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}

Tahap 12.1B: Permission Modul Identitas Madrasah
Kita butuh permission baru:
madrasah.view
madrasah.update
Artinya:
madrasah.view   = boleh melihat identitas madrasah
madrasah.update = boleh mengubah identitas madrasah

1. Update RbacSeeder
Buka:
nano database/seeders/RbacSeeder.php
Cari array $permissions.
Tambahkan dua item berikut:
[
    'name' => 'madrasah.view',
    'module' => 'madrasah',
    'action' => 'view',
    'display_name' => 'Melihat Identitas Madrasah',
    'description' => 'Melihat profil dan identitas dasar madrasah.',
],
[
    'name' => 'madrasah.update',
    'module' => 'madrasah',
    'action' => 'update',
    'display_name' => 'Mengubah Identitas Madrasah',
    'description' => 'Mengubah profil dan identitas dasar madrasah.',
],
Letakkan di dekat permission settings.view dan settings.update.
Karena Super Admin menerima semua permission melalui bagian:
$allPermissionIds = Permission::query()
    ->pluck('id')
    ->all();

$superAdminRole->permissions()->sync($allPermissionIds);
maka permission baru otomatis masuk ke role super_admin setelah seeder dijalankan.

Tahap 12.1C: Seeder Identitas Madrasah Awal
1. Buat seeder
php artisan make:seeder MadrasahSeeder
Buka:
nano database/seeders/MadrasahSeeder.php
Isi:
<?php

namespace Database\Seeders;

use App\Models\Madrasah;
use Illuminate\Database\Seeder;

class MadrasahSeeder extends Seeder
{
    /**
     * Membuat data identitas madrasah awal.
     */
    public function run(): void
    {
        Madrasah::query()->updateOrCreate(
            [
                'code' => 'default',
            ],
            [
                'name' => 'SIM Madrasah',
                'nsm' => null,
                'npsn' => null,
                'email' => null,
                'phone' => null,
                'address' => null,
                'village' => null,
                'district' => null,
                'city' => null,
                'province' => null,
                'postal_code' => null,
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );
    }
}
Seeder ini membuat satu data madrasah awal dengan kode:
default
Nanti data ini dapat diubah melalui halaman admin.

Tahap 12.1D: Controller Identitas Madrasah
1. Buat controller
php artisan make:controller Admin/MadrasahProfileController
Buka:
nano app/Http/Controllers/Admin/MadrasahProfileController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Madrasah;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MadrasahProfileController extends Controller
{
    /**
     * Menampilkan form identitas madrasah.
     */
    public function edit(): View
    {
        $madrasah = Madrasah::query()
            ->where('code', 'default')
            ->firstOrFail();

        return view('admin.madrasah.edit', [
            'madrasah' => $madrasah,
        ]);
    }

    /**
     * Menyimpan perubahan identitas madrasah.
     */
    public function update(Request $request): RedirectResponse
    {
        $madrasah = Madrasah::query()
            ->where('code', 'default')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'nsm' => [
                'nullable',
                'string',
                'max:30',
            ],
            'npsn' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:191',
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
            'village' => [
                'nullable',
                'string',
                'max:100',
            ],
            'district' => [
                'nullable',
                'string',
                'max:100',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'province' => [
                'nullable',
                'string',
                'max:100',
            ],
            'postal_code' => [
                'nullable',
                'string',
                'max:10',
            ],
            'timezone' => [
                'required',
                'string',
                'max:50',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $madrasah->update($validated);

        return redirect()
            ->route('admin.madrasah.edit')
            ->with('success', 'Identitas madrasah berhasil diperbarui.');
    }
}

Tahap 12.1E: Route Identitas Madrasah
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\MadrasahProfileController;
Di dalam group admin, tambahkan route berikut:
Route::get('/madrasah', [MadrasahProfileController::class, 'edit'])
    ->middleware('permission:madrasah.view')
    ->name('madrasah.edit');

Route::put('/madrasah', [MadrasahProfileController::class, 'update'])
    ->middleware('permission:madrasah.update')
    ->name('madrasah.update');
Alamatnya menjadi:
/admin/madrasah

Tahap 12.1F: View Form Identitas Madrasah
Buat folder:
mkdir -p resources/views/admin/madrasah
Buat file:
nano resources/views/admin/madrasah/edit.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Identitas Madrasah
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data dasar madrasah yang digunakan di seluruh sistem.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('admin.madrasah.update') }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label
                                for="name"
                                value="Nama Madrasah"
                            />

                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name', $madrasah->name)"
                                required
                            />

                            <x-input-error
                                :messages="$errors->get('name')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="timezone"
                                value="Zona Waktu"
                            />

                            <x-text-input
                                id="timezone"
                                name="timezone"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('timezone', $madrasah->timezone)"
                                required
                            />

                            <x-input-error
                                :messages="$errors->get('timezone')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="nsm"
                                value="NSM"
                            />

                            <x-text-input
                                id="nsm"
                                name="nsm"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('nsm', $madrasah->nsm)"
                            />

                            <x-input-error
                                :messages="$errors->get('nsm')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="npsn"
                                value="NPSN"
                            />

                            <x-text-input
                                id="npsn"
                                name="npsn"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('npsn', $madrasah->npsn)"
                            />

                            <x-input-error
                                :messages="$errors->get('npsn')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="email"
                                value="Email"
                            />

                            <x-text-input
                                id="email"
                                name="email"
                                type="email"
                                class="mt-1 block w-full"
                                :value="old('email', $madrasah->email)"
                            />

                            <x-input-error
                                :messages="$errors->get('email')"
                                class="mt-2"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="phone"
                                value="Telepon"
                            />

                            <x-text-input
                                id="phone"
                                name="phone"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('phone', $madrasah->phone)"
                            />

                            <x-input-error
                                :messages="$errors->get('phone')"
                                class="mt-2"
                            />
                        </div>
                    </div>

                    <div>
                        <x-input-label
                            for="address"
                            value="Alamat"
                        />

                        <textarea
                            id="address"
                            name="address"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        >{{ old('address', $madrasah->address) }}</textarea>

                        <x-input-error
                            :messages="$errors->get('address')"
                            class="mt-2"
                        />
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-input-label
                                for="village"
                                value="Desa/Kelurahan"
                            />

                            <x-text-input
                                id="village"
                                name="village"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('village', $madrasah->village)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="district"
                                value="Kecamatan"
                            />

                            <x-text-input
                                id="district"
                                name="district"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('district', $madrasah->district)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="city"
                                value="Kabupaten/Kota"
                            />

                            <x-text-input
                                id="city"
                                name="city"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('city', $madrasah->city)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="province"
                                value="Provinsi"
                            />

                            <x-text-input
                                id="province"
                                name="province"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('province', $madrasah->province)"
                            />
                        </div>

                        <div>
                            <x-input-label
                                for="postal_code"
                                value="Kode Pos"
                            />

                            <x-text-input
                                id="postal_code"
                                name="postal_code"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('postal_code', $madrasah->postal_code)"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            name="is_active"
                            type="checkbox"
                            value="1"
                            class="rounded border-gray-300 text-green-700 shadow-sm focus:ring-green-500"
                            @checked(old('is_active', $madrasah->is_active))
                        >

                        <label
                            for="is_active"
                            class="text-sm text-gray-700"
                        >
                            Madrasah aktif
                        </label>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 bg-gray-50 px-6 py-4">
                    @can('permission', 'madrasah.update')
                        <button
                            type="submit"
                            class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                        >
                            Simpan Identitas
                        </button>
                    @endcan
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

Tahap 12.1G: Tambahkan Menu Sidebar
Buka:
nano resources/views/layouts/sidebar.blade.php
Di bagian Administrasi Sistem, tambahkan link ini:
@can('permission', 'madrasah.view')
    <x-ui.sidebar-link
        :href="route('admin.madrasah.edit')"
        :active="request()->routeIs('admin.madrasah.*')"
    >
        Identitas Madrasah
    </x-ui.sidebar-link>
@endcan
Letakkan sebelum Role.
Jika kondisi @if section Administrasi Sistem masih hanya memeriksa roles.view atau permissions.view, ubah menjadi:
@if (
    auth()->user()?->can('permission', 'madrasah.view') ||
    auth()->user()?->can('permission', 'roles.view') ||
    auth()->user()?->can('permission', 'permissions.view')
)

Tahap 12.1H: Update Dashboard Shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada method resolveShortcutCards, tambahkan shortcut berikut di awal array:
[
    'title' => 'Identitas Madrasah',
    'description' => 'Kelola nama madrasah, NSM, NPSN, alamat, kontak, dan zona waktu.',
    'href' => route('admin.madrasah.edit'),
    'label' => 'Buka Identitas',
],
Sehingga Super Admin punya shortcut cepat ke identitas madrasah.

Tahap 12.1I: Test Modul Identitas Madrasah
1. Buat test
php artisan make:test Admin/MadrasahProfileTest
Buka:
nano tests/Feature/Admin/MadrasahProfileTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Madrasah;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MadrasahProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_permission_can_view_madrasah_profile_form(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'madrasah.view');

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Test',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/madrasah');

        $response
            ->assertStatus(200)
            ->assertSee('Identitas Madrasah')
            ->assertSee('Madrasah Test');
    }

    public function test_user_without_permission_cannot_view_madrasah_profile_form(): void
    {
        $user = User::factory()->create();

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Test',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin/madrasah');

        $response->assertForbidden();
    }

    public function test_user_with_update_permission_can_update_madrasah_profile(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'madrasah.view');
        $this->grantPermissionToUser($user, 'madrasah.update');

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Lama',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put('/admin/madrasah', [
                'name' => 'Madrasah Baru',
                'nsm' => '1234567890',
                'npsn' => '9876543210',
                'email' => 'info@madrasah.test',
                'phone' => '08123456789',
                'address' => 'Jl. Pendidikan No. 1',
                'village' => 'Desa Ilmu',
                'district' => 'Kecamatan Belajar',
                'city' => 'Kabupaten Cerdas',
                'province' => 'Jawa Timur',
                'postal_code' => '12345',
                'timezone' => 'Asia/Jakarta',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.madrasah.edit'));

        $this->assertDatabaseHas('madrasahs', [
            'code' => 'default',
            'name' => 'Madrasah Baru',
            'nsm' => '1234567890',
            'npsn' => '9876543210',
            'email' => 'info@madrasah.test',
            'city' => 'Kabupaten Cerdas',
            'is_active' => true,
        ]);
    }

    public function test_user_without_update_permission_cannot_update_madrasah_profile(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'madrasah.view');

        Madrasah::create([
            'code' => 'default',
            'name' => 'Madrasah Lama',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->put('/admin/madrasah', [
                'name' => 'Madrasah Baru',
                'timezone' => 'Asia/Jakarta',
            ]);

        $response->assertForbidden();
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_madrasah_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Madrasah Role',
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

Tahap 12.1J: Jalankan Migration dan Seeder
Jalankan:
php artisan migrate
php artisan db:seed --class=MadrasahSeeder
php artisan db:seed --class=RbacSeeder
Periksa tabel:
php artisan db:table madrasahs
Periksa route:
php artisan route:list --path=madrasah
Target route:
GET|HEAD  admin/madrasah
PUT       admin/madrasah

Tahap 12.1K: Test dan Build
Jalankan:
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil

Tahap 12.1L: Uji Manual
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/madrasah
Isi data madrasah sesuai kebutuhan.
Contoh:
Nama Madrasah: MA Nurul Ilmi
NSM: isi sesuai data asli
NPSN: isi sesuai data asli
Email: info@madrasah.test
Telepon: 08123456789
Alamat: Jl. Pendidikan No. 1
Zona Waktu: Asia/Jakarta
Simpan.
Pastikan muncul pesan:
Identitas madrasah berhasil diperbarui.

Tahap 12.1M: Commit ke GitHub
Jika semua berhasil:
git status
Lalu:
git add app/Models/Madrasah.php \
        app/Http/Controllers/Admin/MadrasahProfileController.php \
        database/migrations/ \
        database/seeders/MadrasahSeeder.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/madrasah/edit.blade.php \
        resources/views/layouts/sidebar.blade.php \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/MadrasahProfileTest.php
Commit:
git commit -m "feat: add madrasah identity module"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean

Kirim hasil:
php artisan migrate
php artisan db:seed --class=MadrasahSeeder
php artisan db:seed --class=RbacSeeder
php artisan test
npm run build
git status
Jika berhasil, kita lanjut ke Tahap 12.2: Tahun Ajaran dan Semester.












