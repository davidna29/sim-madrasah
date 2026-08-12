# Tahap 11 — Dashboard SIM Madrasah

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 18701–21418.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 11 — Dashboard SIM Madrasah
Pada tahap 11 kita mulai membangun tampilan utama berdasarkan role:
Super Admin
Kepala Madrasah
Guru
Wali Kelas
Bendahara
Orang Tua
Siswa
Setiap dashboard memiliki widget dan informasi berbeda.
Silakan kerjakan tahap 10.7 ini, lalu kirim:
php artisan route:list --path=roles
php artisan test
npm run build
git status
Saya cek dulu sebelum masuk Dashboard.
Ini adalah tahap terakhir dari modul RBAC (Role Based Access Control)





=========================================================





Tahap 11: Dashboard SIM Madrasah
Kita mulai dari Tahap 11.1: Dashboard dasar berbasis role.
Pada tahap ini kita belum mengambil data akademik, siswa, SPP, absensi, atau rapor, karena modulnya belum dibuat. Jadi dashboard tahap awal hanya menampilkan struktur yang benar:
User login
Role aktif
Jumlah permission
Status akun
Widget dashboard sesuai role
Ini penting agar nanti saat modul akademik, keuangan, dan kesiswaan dibuat, kita tinggal mengisi widget dengan data asli.

Tahap 11.1: Dashboard Dasar Berbasis Role
1. Konsep sederhana
Dashboard adalah halaman pertama setelah pengguna login.
Dashboard harus menjawab:
Siapa pengguna ini?
Apa role pengguna ini?
Apa akses yang dimiliki?
Informasi apa yang paling relevan untuk role tersebut?
Contoh:
Super Admin melihat pengaturan sistem.
Bendahara melihat pembayaran.
Guru melihat jurnal dan nilai.
Wali kelas melihat siswa dan rapor.
Siswa melihat jadwal dan portofolio.
Orang tua melihat perkembangan anak.

2. Analogi sederhana
Dashboard seperti meja kerja.
Meja kerja kepala madrasah berbeda dengan meja kerja bendahara.
Meja kerja guru juga berbeda dengan meja kerja siswa.
Karena itu, dashboard tidak boleh satu bentuk untuk semua pengguna.

3. Buat DashboardController
Jalankan:
php artisan make:controller DashboardController
Buka:
nano app/Http/Controllers/DashboardController.php
Isi dengan:
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan role pengguna.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $roles = $user->roles()
            ->with([
                'permissions' => function ($query): void {
                    $query
                        ->where('permissions.is_active', true)
                        ->orderBy('permissions.module')
                        ->orderBy('permissions.action');
                },
            ])
            ->where('roles.is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('user_roles.expires_at')
                    ->orWhere('user_roles.expires_at', '>', now());
            })
            ->orderBy('roles.display_name')
            ->get();

        $roleNames = $roles
            ->pluck('name')
            ->values();

        $permissionCount = $roles
            ->flatMap(fn ($role): Collection => $role->permissions)
            ->pluck('name')
            ->unique()
            ->count();

        return view('dashboard', [
            'user' => $user,
            'roles' => $roles,
            'dashboardTitle' => $this->resolveDashboardTitle($roleNames),
            'widgets' => $this->resolveWidgets($roleNames),
            'permissionCount' => $permissionCount,
        ]);
    }

    /**
     * Menentukan judul dashboard utama.
     */
    private function resolveDashboardTitle(Collection $roleNames): string
    {
        if ($roleNames->contains('super_admin')) {
            return 'Dashboard Super Admin';
        }

        if ($roleNames->contains('kepala_madrasah')) {
            return 'Dashboard Kepala Madrasah';
        }

        if ($roleNames->contains('wakamad_kurikulum')) {
            return 'Dashboard Wakamad Kurikulum';
        }

        if ($roleNames->contains('wakamad_kesiswaan')) {
            return 'Dashboard Wakamad Kesiswaan';
        }

        if ($roleNames->contains('bendahara')) {
            return 'Dashboard Bendahara';
        }

        if ($roleNames->contains('wali_kelas')) {
            return 'Dashboard Wali Kelas';
        }

        if ($roleNames->contains('guru_mata_pelajaran')) {
            return 'Dashboard Guru Mata Pelajaran';
        }

        if ($roleNames->contains('orang_tua')) {
            return 'Dashboard Orang Tua';
        }

        if ($roleNames->contains('siswa')) {
            return 'Dashboard Siswa';
        }

        return 'Dashboard SIM Madrasah';
    }

    /**
     * Menentukan widget awal berdasarkan role.
     *
     * Widget ini masih berupa fondasi tampilan.
     * Data asli akan dihubungkan setelah modul terkait dibuat.
     *
     * @return array<int, array<string, string>>
     */
    private function resolveWidgets(Collection $roleNames): array
    {
        if ($roleNames->contains('super_admin')) {
            return [
                [
                    'title' => 'Pengguna dan Hak Akses',
                    'description' => 'Kelola akun, role, permission, dan keamanan akses sistem.',
                ],
                [
                    'title' => 'Konfigurasi Sistem',
                    'description' => 'Atur identitas madrasah, tahun ajaran aktif, dan pengaturan aplikasi.',
                ],
                [
                    'title' => 'Audit dan Aktivitas',
                    'description' => 'Pantau aktivitas penting dan perubahan data sensitif.',
                ],
            ];
        }

        if ($roleNames->contains('kepala_madrasah')) {
            return [
                [
                    'title' => 'Monitoring Akademik',
                    'description' => 'Pantau perkembangan nilai, kehadiran, dan proses rapor.',
                ],
                [
                    'title' => 'Monitoring Keuangan',
                    'description' => 'Lihat ringkasan tagihan, pembayaran, dan tunggakan siswa.',
                ],
                [
                    'title' => 'PKKM dan Akreditasi',
                    'description' => 'Pantau kelengkapan eviden mutu madrasah.',
                ],
            ];
        }

        if ($roleNames->contains('bendahara')) {
            return [
                [
                    'title' => 'Tagihan Siswa',
                    'description' => 'Kelola jenis tagihan dan status pembayaran siswa.',
                ],
                [
                    'title' => 'Input Pembayaran',
                    'description' => 'Catat pembayaran, cicilan, dan koreksi transaksi.',
                ],
                [
                    'title' => 'Rekap SPP',
                    'description' => 'Lihat rekap pembayaran bulanan, tahunan, kelas, dan siswa.',
                ],
            ];
        }

        if ($roleNames->contains('wali_kelas')) {
            return [
                [
                    'title' => 'Siswa Kelas',
                    'description' => 'Pantau daftar siswa yang menjadi tanggung jawab wali kelas.',
                ],
                [
                    'title' => 'Kehadiran dan Nilai',
                    'description' => 'Lihat ringkasan kehadiran dan perkembangan nilai siswa.',
                ],
                [
                    'title' => 'Rapor',
                    'description' => 'Siapkan catatan wali kelas dan proses rapor.',
                ],
            ];
        }

        if ($roleNames->contains('guru_mata_pelajaran')) {
            return [
                [
                    'title' => 'Jadwal Mengajar',
                    'description' => 'Lihat jadwal mengajar berdasarkan penugasan aktif.',
                ],
                [
                    'title' => 'Jurnal Mengajar',
                    'description' => 'Catat pelaksanaan pembelajaran dan tindak lanjut.',
                ],
                [
                    'title' => 'Input Nilai',
                    'description' => 'Kelola nilai sesuai kelas dan mata pelajaran yang diajar.',
                ],
            ];
        }

        if ($roleNames->contains('orang_tua')) {
            return [
                [
                    'title' => 'Perkembangan Anak',
                    'description' => 'Lihat kehadiran, nilai, prestasi, dan rapor anak.',
                ],
                [
                    'title' => 'Tagihan',
                    'description' => 'Pantau tagihan dan riwayat pembayaran anak.',
                ],
                [
                    'title' => 'Dokumen',
                    'description' => 'Unduh dokumen yang diizinkan oleh madrasah.',
                ],
            ];
        }

        if ($roleNames->contains('siswa')) {
            return [
                [
                    'title' => 'Jadwal',
                    'description' => 'Lihat jadwal pelajaran dan kegiatan siswa.',
                ],
                [
                    'title' => 'Nilai dan Rapor',
                    'description' => 'Lihat nilai yang sudah dipublikasikan.',
                ],
                [
                    'title' => 'Portofolio Digital',
                    'description' => 'Lihat riwayat prestasi, tahfidz, karya, dan dokumen pribadi.',
                ],
            ];
        }

        return [
            [
                'title' => 'Profil Akun',
                'description' => 'Lihat dan perbarui informasi dasar akun pengguna.',
            ],
            [
                'title' => 'Notifikasi',
                'description' => 'Lihat informasi terbaru yang memerlukan perhatian.',
            ],
            [
                'title' => 'Bantuan',
                'description' => 'Hubungi administrator jika akses belum sesuai.',
            ],
        ];
    }
}

4. Ubah route dashboard
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\DashboardController;
Cari route dashboard lama:
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware([
    'auth',
    'verified',
    'active.account',
    'permission:dashboard.view',
])->name('dashboard');
Ganti menjadi:
Route::get('/dashboard', DashboardController::class)
    ->middleware([
        'auth',
        'verified',
        'active.account',
        'permission:dashboard.view',
    ])
    ->name('dashboard');

5. Ubah tampilan dashboard
Buka:
nano resources/views/dashboard.blade.php
Ganti seluruh isinya:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $dashboardTitle }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Selamat datang, {{ $user->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Status Akun
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ ucfirst($user->status) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Jenis Akun
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ ucfirst($user->account_type) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Role Aktif
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ $roles->count() }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            Permission Aktif
                        </div>

                        <div class="mt-2 text-lg font-semibold text-gray-900">
                            {{ $permissionCount }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Role Pengguna
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Role menentukan kelompok hak akses pengguna di dalam SIM Madrasah.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($roles as $role)
                            <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
                                {{ $role->display_name }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">
                                Belum memiliki role aktif.
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Menu Kerja Utama
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Widget berikut disesuaikan dengan role pengguna. Data asli akan ditampilkan setelah modul terkait tersedia.
                </p>

                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-3">
                    @foreach ($widgets as $widget)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="font-semibold text-gray-900">
                                    {{ $widget['title'] }}
                                </h4>

                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $widget['description'] }}
                                </p>

                                <div class="mt-4 text-xs font-medium text-gray-400">
                                    Menunggu modul terkait
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Informasi Keamanan Akun
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                        <div>
                            <div class="text-gray-500">
                                Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">
                                IP Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_ip ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500">
                                Password Diubah
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->password_changed_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

6. Buat test dashboard
Jalankan:
php artisan make:test Dashboard/DashboardPageTest
Buka:
nano tests/Feature/Dashboard/DashboardPageTest.php
Isi:
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_super_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Administrator SIM Madrasah',
        ]);

        $role = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Dashboard Super Admin')
            ->assertSee('Pengguna dan Hak Akses');
    }

    public function test_bendahara_can_view_bendahara_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Bendahara Madrasah',
        ]);

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Dashboard Bendahara')
            ->assertSee('Tagihan Siswa')
            ->assertSee('Rekap SPP');
    }

    public function test_user_without_dashboard_permission_cannot_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertForbidden();
    }
}

7. Jalankan pemeriksaan
php artisan optimize:clear
Lalu:
php artisan route:list --path=dashboard
Jalankan format:
./vendor/bin/pint
Lalu test:
php artisan test
Build:
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 48 menjadi sekitar 51.

8. Uji manual
Pastikan RBAC seeder sudah dijalankan:
php artisan db:seed --class=RbacSeeder
Login sebagai:
superadmin
Buka:
open http://sim-madrasah.test/dashboard
Dashboard harus menampilkan:
Dashboard Super Admin
Role Aktif
Permission Aktif
Pengguna dan Hak Akses
Konfigurasi Sistem
Audit dan Aktivitas

9. Commit ke GitHub
Jika semua aman:
git status
Kemudian:
git add app/Http/Controllers/DashboardController.php \
        routes/web.php \
        resources/views/dashboard.blade.php \
        tests/Feature/Dashboard/DashboardPageTest.php
Commit:
git commit -m "feat: add role based dashboard foundation"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean

Kirim hasil dari:
php artisan test
npm run build
git status
Jika berhasil, kita lanjut ke Tahap 11.2: Layout Dashboard Admin yang Lebih Rapi dengan Card Ringkasan dan Navigasi Modul.










Tahap 11.2: Layout Dashboard Admin yang Lebih Rapi dengan Card Ringkasan dan Navigasi Modul.
Pada tahap ini kita tidak membuat modul baru. Kita hanya merapikan dashboard agar siap menjadi halaman utama SIM Madrasah.
Tahap 11.2: Layout Dashboard dengan Card dan Navigasi Modul
Tujuan
Dashboard akan memiliki:
1. Card ringkasan akun
2. Daftar role aktif
3. Navigasi modul sesuai role
4. Informasi keamanan akun
5. Komponen UI yang bisa dipakai ulang
Kita akan membuat dua komponen Blade:
resources/views/components/ui/stat-card.blade.php
resources/views/components/ui/module-card.blade.php
Tujuannya agar tampilan dashboard tidak penuh kode berulang.

1. Buat folder komponen UI
Jalankan:
mkdir -p resources/views/components/ui

2. Buat komponen stat card
Buat file:
nano resources/views/components/ui/stat-card.blade.php
Isi:
@props([
    'label',
    'value',
    'description' => null,
])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6">
        <div class="text-sm font-medium text-gray-500">
            {{ $label }}
        </div>

        <div class="mt-2 text-2xl font-semibold text-gray-900">
            {{ $value }}
        </div>

        @if ($description)
            <p class="mt-2 text-sm text-gray-500">
                {{ $description }}
            </p>
        @endif
    </div>
</div>
Penjelasan
Komponen ini dipakai untuk card kecil seperti:
Status Akun
Role Aktif
Permission Aktif
Login Terakhir
Jadi nanti kita tidak menulis struktur card berulang kali.

3. Buat komponen module card
Buat file:
nano resources/views/components/ui/module-card.blade.php
Isi:
@props([
    'title',
    'description',
    'href' => null,
    'status' => 'Menunggu modul',
])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6 flex h-full flex-col justify-between">
        <div>
            <div class="flex items-start justify-between gap-4">
                <h4 class="font-semibold text-gray-900">
                    {{ $title }}
                </h4>

                @if ($href)
                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                        Tersedia
                    </span>
                @else
                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                        {{ $status }}
                    </span>
                @endif
            </div>

            <p class="mt-2 text-sm text-gray-500">
                {{ $description }}
            </p>
        </div>

        <div class="mt-5">
            @if ($href)
                <a
                    href="{{ $href }}"
                    class="inline-flex items-center text-sm font-medium text-green-700 hover:text-green-900"
                >
                    Buka Modul
                </a>
            @else
                <span class="text-xs font-medium text-gray-400">
                    Akan aktif setelah modul dibuat
                </span>
            @endif
        </div>
    </div>
</div>
Penjelasan
Komponen ini dipakai untuk navigasi modul.
Jika modul sudah punya route, maka muncul tombol Buka Modul.
Jika belum ada route, card tetap tampil sebagai rencana modul, tetapi belum bisa dibuka.

4. Perbarui DashboardController
Buka:
nano app/Http/Controllers/DashboardController.php
Ganti seluruh isinya:
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan role pengguna.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $roles = $user->roles()
            ->with([
                'permissions' => function ($query): void {
                    $query
                        ->where('permissions.is_active', true)
                        ->orderBy('permissions.module')
                        ->orderBy('permissions.action');
                },
            ])
            ->where('roles.is_active', true)
            ->where(function ($query): void {
                $query
                    ->whereNull('user_roles.expires_at')
                    ->orWhere('user_roles.expires_at', '>', now());
            })
            ->orderBy('roles.display_name')
            ->get();

        $roleNames = $roles
            ->pluck('name')
            ->values();

        $permissionCount = $roles
            ->flatMap(fn ($role): Collection => $role->permissions)
            ->pluck('name')
            ->unique()
            ->count();

        return view('dashboard', [
            'user' => $user,
            'roles' => $roles,
            'dashboardTitle' => $this->resolveDashboardTitle($roleNames),
            'summaryCards' => $this->resolveSummaryCards(
                $user,
                $roles,
                $permissionCount
            ),
            'moduleCards' => $this->resolveModuleCards($roleNames),
        ]);
    }

    /**
     * Menentukan judul dashboard utama.
     */
    private function resolveDashboardTitle(Collection $roleNames): string
    {
        if ($roleNames->contains('super_admin')) {
            return 'Dashboard Super Admin';
        }

        if ($roleNames->contains('kepala_madrasah')) {
            return 'Dashboard Kepala Madrasah';
        }

        if ($roleNames->contains('wakamad_kurikulum')) {
            return 'Dashboard Wakamad Kurikulum';
        }

        if ($roleNames->contains('wakamad_kesiswaan')) {
            return 'Dashboard Wakamad Kesiswaan';
        }

        if ($roleNames->contains('wakamad_sarpras')) {
            return 'Dashboard Wakamad Sarpras';
        }

        if ($roleNames->contains('wakamad_humas')) {
            return 'Dashboard Wakamad Humas';
        }

        if ($roleNames->contains('bendahara')) {
            return 'Dashboard Bendahara';
        }

        if ($roleNames->contains('wali_kelas')) {
            return 'Dashboard Wali Kelas';
        }

        if ($roleNames->contains('guru_mata_pelajaran')) {
            return 'Dashboard Guru Mata Pelajaran';
        }

        if ($roleNames->contains('guru_bk')) {
            return 'Dashboard Guru BK';
        }

        if ($roleNames->contains('orang_tua')) {
            return 'Dashboard Orang Tua';
        }

        if ($roleNames->contains('siswa')) {
            return 'Dashboard Siswa';
        }

        return 'Dashboard SIM Madrasah';
    }

    /**
     * Card ringkasan akun.
     *
     * @return array<int, array<string, string|int>>
     */
    private function resolveSummaryCards(
        User $user,
        Collection $roles,
        int $permissionCount
    ): array {
        return [
            [
                'label' => 'Status Akun',
                'value' => ucfirst($user->status),
                'description' => 'Status akses pengguna saat ini.',
            ],
            [
                'label' => 'Jenis Akun',
                'value' => ucfirst($user->account_type),
                'description' => 'Kategori akun dalam sistem.',
            ],
            [
                'label' => 'Role Aktif',
                'value' => $roles->count(),
                'description' => 'Role aktif yang melekat pada akun.',
            ],
            [
                'label' => 'Permission Aktif',
                'value' => $permissionCount,
                'description' => 'Jumlah permission dari role aktif.',
            ],
        ];
    }

    /**
     * Navigasi modul berdasarkan role.
     *
     * @return array<int, array<string, string|null>>
     */
    private function resolveModuleCards(Collection $roleNames): array
    {
        if ($roleNames->contains('super_admin')) {
            return [
                [
                    'title' => 'Pengguna dan Hak Akses',
                    'description' => 'Kelola akun, role, permission, dan keamanan akses sistem.',
                    'href' => route('admin.roles.index'),
                ],
                [
                    'title' => 'Manajemen Permission',
                    'description' => 'Lihat dan periksa daftar permission yang tersedia.',
                    'href' => route('admin.permissions.index'),
                ],
                [
                    'title' => 'Konfigurasi Sistem',
                    'description' => 'Atur identitas madrasah, tahun ajaran aktif, dan pengaturan aplikasi.',
                    'href' => null,
                ],
                [
                    'title' => 'Audit dan Aktivitas',
                    'description' => 'Pantau aktivitas penting dan perubahan data sensitif.',
                    'href' => null,
                ],
            ];
        }

        if ($roleNames->contains('kepala_madrasah')) {
            return [
                [
                    'title' => 'Monitoring Akademik',
                    'description' => 'Pantau perkembangan nilai, kehadiran, dan proses rapor.',
                    'href' => null,
                ],
                [
                    'title' => 'Monitoring Keuangan',
                    'description' => 'Lihat ringkasan tagihan, pembayaran, dan tunggakan siswa.',
                    'href' => null,
                ],
                [
                    'title' => 'PKKM dan Akreditasi',
                    'description' => 'Pantau kelengkapan eviden mutu madrasah.',
                    'href' => null,
                ],
            ];
        }

        if ($roleNames->contains('bendahara')) {
            return [
                [
                    'title' => 'Tagihan Siswa',
                    'description' => 'Kelola jenis tagihan dan status pembayaran siswa.',
                    'href' => null,
                ],
                [
                    'title' => 'Input Pembayaran',
                    'description' => 'Catat pembayaran, cicilan, dan koreksi transaksi.',
                    'href' => null,
                ],
                [
                    'title' => 'Rekap SPP',
                    'description' => 'Lihat rekap pembayaran bulanan, tahunan, kelas, dan siswa.',
                    'href' => null,
                ],
            ];
        }

        if ($roleNames->contains('wali_kelas')) {
            return [
                [
                    'title' => 'Siswa Kelas',
                    'description' => 'Pantau daftar siswa yang menjadi tanggung jawab wali kelas.',
                    'href' => null,
                ],
                [
                    'title' => 'Kehadiran dan Nilai',
                    'description' => 'Lihat ringkasan kehadiran dan perkembangan nilai siswa.',
                    'href' => null,
                ],
                [
                    'title' => 'Rapor',
                    'description' => 'Siapkan catatan wali kelas dan proses rapor.',
                    'href' => null,
                ],
            ];
        }

        if ($roleNames->contains('guru_mata_pelajaran')) {
            return [
                [
                    'title' => 'Jadwal Mengajar',
                    'description' => 'Lihat jadwal mengajar berdasarkan penugasan aktif.',
                    'href' => null,
                ],
                [
                    'title' => 'Jurnal Mengajar',
                    'description' => 'Catat pelaksanaan pembelajaran dan tindak lanjut.',
                    'href' => null,
                ],
                [
                    'title' => 'Input Nilai',
                    'description' => 'Kelola nilai sesuai kelas dan mata pelajaran yang diajar.',
                    'href' => null,
                ],
            ];
        }

        if ($roleNames->contains('orang_tua')) {
            return [
                [
                    'title' => 'Perkembangan Anak',
                    'description' => 'Lihat kehadiran, nilai, prestasi, dan rapor anak.',
                    'href' => null,
                ],
                [
                    'title' => 'Tagihan',
                    'description' => 'Pantau tagihan dan riwayat pembayaran anak.',
                    'href' => null,
                ],
                [
                    'title' => 'Dokumen',
                    'description' => 'Unduh dokumen yang diizinkan oleh madrasah.',
                    'href' => null,
                ],
            ];
        }

        if ($roleNames->contains('siswa')) {
            return [
                [
                    'title' => 'Jadwal',
                    'description' => 'Lihat jadwal pelajaran dan kegiatan siswa.',
                    'href' => null,
                ],
                [
                    'title' => 'Nilai dan Rapor',
                    'description' => 'Lihat nilai yang sudah dipublikasikan.',
                    'href' => null,
                ],
                [
                    'title' => 'Portofolio Digital',
                    'description' => 'Lihat riwayat prestasi, tahfidz, karya, dan dokumen pribadi.',
                    'href' => null,
                ],
            ];
        }

        return [
            [
                'title' => 'Profil Akun',
                'description' => 'Lihat dan perbarui informasi dasar akun pengguna.',
                'href' => route('profile.edit'),
            ],
            [
                'title' => 'Notifikasi',
                'description' => 'Lihat informasi terbaru yang memerlukan perhatian.',
                'href' => null,
            ],
            [
                'title' => 'Bantuan',
                'description' => 'Hubungi administrator jika akses belum sesuai.',
                'href' => null,
            ],
        ];
    }
}

5. Perbarui dashboard view
Buka:
nano resources/views/dashboard.blade.php
Ganti seluruh isinya:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $dashboardTitle }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Selamat datang, {{ $user->name }}.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <x-ui.stat-card
                        :label="$card['label']"
                        :value="$card['value']"
                        :description="$card['description']"
                    />
                @endforeach
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Role Pengguna
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Role menentukan kelompok hak akses pengguna di dalam SIM Madrasah.
                            </p>
                        </div>

                        <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-medium text-green-700">
                            {{ $roles->count() }} role aktif
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($roles as $role)
                            <span class="rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700">
                                {{ $role->display_name }}
                            </span>
                        @empty
                            <span class="text-sm text-gray-500">
                                Belum memiliki role aktif.
                            </span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Navigasi Modul
                        </h3>

                        <p class="mt-1 text-sm text-gray-500">
                            Menu kerja utama disesuaikan dengan role pengguna.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                    @foreach ($moduleCards as $module)
                        <x-ui.module-card
                            :title="$module['title']"
                            :description="$module['description']"
                            :href="$module['href']"
                        />
                    @endforeach
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Keamanan Akun
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Informasi ini membantu memantau keamanan akun pengguna.
                    </p>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3 text-sm">
                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <div class="text-gray-500">
                                Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <div class="text-gray-500">
                                IP Login Terakhir
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->last_login_ip ?? '-' }}
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                            <div class="text-gray-500">
                                Password Diubah
                            </div>

                            <div class="mt-1 font-medium text-gray-900">
                                {{ $user->password_changed_at?->format('d M Y H:i') ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-dashed border-green-200 bg-green-50 p-5">
                <h3 class="font-semibold text-green-900">
                    Catatan Pengembangan
                </h3>

                <p class="mt-1 text-sm text-green-800">
                    Dashboard ini masih menggunakan data fondasi. Widget akademik, keuangan, kesiswaan, PPDB, PKKM, dan portofolio akan dihubungkan setelah modul terkait selesai dibuat.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>

6. Tambahkan test layout dashboard
Jalankan:
php artisan make:test Dashboard/DashboardLayoutTest
Buka:
nano tests/Feature/Dashboard/DashboardLayoutTest.php
Isi:
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_admin_module_navigation(): void
    {
        $user = User::factory()->create([
            'name' => 'Administrator SIM Madrasah',
        ]);

        $role = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Navigasi Modul')
            ->assertSee('Pengguna dan Hak Akses')
            ->assertSee('Manajemen Permission')
            ->assertSee('Keamanan Akun');
    }

    public function test_bendahara_sees_bendahara_module_navigation(): void
    {
        $user = User::factory()->create([
            'name' => 'Bendahara Madrasah',
        ]);

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Dashboard Bendahara')
            ->assertSee('Tagihan Siswa')
            ->assertSee('Input Pembayaran')
            ->assertSee('Rekap SPP');
    }
}

7. Jalankan pemeriksaan
php artisan optimize:clear
Lalu:
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 51 menjadi sekitar 53.

8. Uji manual
Jalankan ulang RBAC seeder bila perlu:
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin, lalu buka:
open http://sim-madrasah.test/dashboard
Pastikan terlihat:
Dashboard Super Admin
Status Akun
Jenis Akun
Role Aktif
Permission Aktif
Role Pengguna
Navigasi Modul
Pengguna dan Hak Akses
Manajemen Permission
Keamanan Akun
Catatan Pengembangan

9. Commit ke GitHub
Jika semua aman:
git status
Kemudian:
git add app/Http/Controllers/DashboardController.php \
        resources/views/dashboard.blade.php \
        resources/views/components/ui/stat-card.blade.php \
        resources/views/components/ui/module-card.blade.php \
        tests/Feature/Dashboard/DashboardLayoutTest.php
Commit:
git commit -m "feat: improve role based dashboard layout"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean
Kirim hasil dari:
php artisan test
npm run build
git status
Setelah berhasil, kita lanjut ke Tahap 11.3: Sidebar dan Navigasi Modul SIM Madrasah.








Tahap 11.3: Sidebar dan Navigasi Modul SIM Madrasah.
Pada tahap ini kita membuat navigasi aplikasi lebih rapi. Saat ini menu masih berada di navigation bar atas. Untuk SIM Madrasah yang modulnya banyak, kita butuh sidebar agar menu lebih mudah dibaca.
Tahap 11.3: Sidebar dan Navigasi Modul
Tujuan
Kita akan membuat:
1. Sidebar desktop
2. Menu berdasarkan permission
3. Komponen sidebar link
4. Layout aplikasi dengan sidebar
5. Test untuk memastikan menu hanya tampil sesuai akses
Sidebar tahap awal hanya menampilkan modul yang sudah ada:
Dashboard
Role
Permission
Profil
Modul lain seperti Siswa, Guru, Akademik, Keuangan, PPDB, PKKM, dan Akreditasi belum kita tampilkan sebagai link aktif karena routenya belum dibuat.

1. Buat komponen sidebar link
Jalankan:
mkdir -p resources/views/components/ui
Buat file:
nano resources/views/components/ui/sidebar-link.blade.php
Isi:
@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => $active
            ? 'flex items-center rounded-lg bg-green-700 px-3 py-2 text-sm font-medium text-white'
            : 'flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-green-50 hover:text-green-700',
    ]) }}
>
    {{ $slot }}
</a>

2. Buat komponen judul section sidebar
Buat file:
nano resources/views/components/ui/sidebar-section.blade.php
Isi:
@props([
    'title',
])

<div class="mt-6 first:mt-0">
    <div class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
        {{ $title }}
    </div>

    <div class="mt-2 space-y-1">
        {{ $slot }}
    </div>
</div>

3. Buat file sidebar
Buat file:
nano resources/views/layouts/sidebar.blade.php
Isi:
<aside class="hidden lg:flex lg:w-72 lg:flex-col lg:border-r lg:border-gray-200 lg:bg-white">
    <div class="flex min-h-0 flex-1 flex-col">
        <div class="flex h-16 items-center border-b border-gray-200 px-6">
            <div>
                <div class="text-base font-bold text-green-700">
                    SIM Madrasah
                </div>

                <div class="text-xs text-gray-500">
                    Sistem Informasi Manajemen
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-6">
            <x-ui.sidebar-section title="Utama">
                <x-ui.sidebar-link
                    :href="route('dashboard')"
                    :active="request()->routeIs('dashboard')"
                >
                    Dashboard
                </x-ui.sidebar-link>
            </x-ui.sidebar-section>

            @canany([
                'permission',
                'roles.view'
            ])
                <x-ui.sidebar-section title="Administrasi Sistem">
                    @can('permission', 'roles.view')
                        <x-ui.sidebar-link
                            :href="route('admin.roles.index')"
                            :active="request()->routeIs('admin.roles.*')"
                        >
                            Role
                        </x-ui.sidebar-link>
                    @endcan

                    @can('permission', 'permissions.view')
                        <x-ui.sidebar-link
                            :href="route('admin.permissions.index')"
                            :active="request()->routeIs('admin.permissions.*')"
                        >
                            Permission
                        </x-ui.sidebar-link>
                    @endcan
                </x-ui.sidebar-section>
            @endcanany

            <x-ui.sidebar-section title="Akun">
                <x-ui.sidebar-link
                    :href="route('profile.edit')"
                    :active="request()->routeIs('profile.edit')"
                >
                    Profil Saya
                </x-ui.sidebar-link>
            </x-ui.sidebar-section>

            <x-ui.sidebar-section title="Modul Berikutnya">
                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-3 py-3 text-xs text-gray-500">
                    Modul siswa, guru, akademik, keuangan, PPDB, PKKM, dan akreditasi akan aktif setelah route dan database modul dibuat.
                </div>
            </x-ui.sidebar-section>
        </nav>
    </div>
</aside>
Catatan penting
Bagian ini:
@can('permission', 'roles.view')
membuat menu Role hanya muncul jika user memiliki permission roles.view.
Namun keamanan utama tetap ada pada route middleware:
permission:roles.view
Jadi menu disembunyikan di UI, tetapi akses tetap dikunci di route.

4. Perbaiki bagian @canany
Blade @canany tidak cocok untuk bentuk permission yang memakai parameter seperti @can('permission', 'roles.view').
Agar aman, ganti bagian ini:
@canany([
    'permission',
    'roles.view'
])
menjadi:
@if (
    auth()->user()?->can('permission', 'roles.view') ||
    auth()->user()?->can('permission', 'permissions.view')
)
Lalu penutupnya:
@endif
Jadi bagian final untuk Administrasi Sistem menjadi:
@if (
    auth()->user()?->can('permission', 'roles.view') ||
    auth()->user()?->can('permission', 'permissions.view')
)
    <x-ui.sidebar-section title="Administrasi Sistem">
        @can('permission', 'roles.view')
            <x-ui.sidebar-link
                :href="route('admin.roles.index')"
                :active="request()->routeIs('admin.roles.*')"
            >
                Role
            </x-ui.sidebar-link>
        @endcan

        @can('permission', 'permissions.view')
            <x-ui.sidebar-link
                :href="route('admin.permissions.index')"
                :active="request()->routeIs('admin.permissions.*')"
            >
                Permission
            </x-ui.sidebar-link>
        @endcan
    </x-ui.sidebar-section>
@endif
Gunakan versi ini agar tidak terjadi error Blade.

5. Ubah layout aplikasi agar memakai sidebar
Buka:
nano resources/views/layouts/app.blade.php
Biasanya file ini berisi:
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        @include('layouts.navigation')

        @isset($header)
            ...
        @endisset

        <main>
            {{ $slot }}
        </main>
    </div>
</body>
Ganti seluruh isinya dengan:
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIM Madrasah') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
            rel="stylesheet"
        />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <div class="flex min-h-[calc(100vh-4rem)]">
                @include('layouts.sidebar')

                <div class="min-w-0 flex-1">
                    @isset($header)
                        <header class="bg-white shadow">
                            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset

                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>

6. Sederhanakan navigation atas
Sekarang sidebar menjadi navigasi utama untuk desktop. Navigation atas tetap dipakai untuk:
logo kecil
dropdown user
menu responsive mobile
Buka:
nano resources/views/layouts/navigation.blade.php
Biarkan struktur Breeze tetap ada, tetapi pada menu desktop bagian kiri, cukup sisakan Dashboard. Jika Anda sudah menambahkan Role dan Permission di navigation atas sebelumnya, boleh dihapus agar tidak dobel.
Cari bagian seperti ini:
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    {{ __('Dashboard') }}
</x-nav-link>
Biarkan bagian itu.
Hapus link Role dan Permission dari navigation atas jika ada. Sidebar yang akan menampilkannya.
Pada menu responsive mobile, Role dan Permission tetap boleh dipertahankan karena sidebar disembunyikan pada layar kecil. Jadi bagian mobile tidak perlu dihapus.

7. Buat test sidebar
Jalankan:
php artisan make:test Dashboard/SidebarNavigationTest
Buka:
nano tests/Feature/Dashboard/SidebarNavigationTest.php
Isi:
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_roles_permission_can_see_role_menu(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'roles.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Role')
            ->assertSee('Administrasi Sistem');
    }

    public function test_user_without_roles_permission_cannot_see_role_menu(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'dashboard.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertDontSee('Administrasi Sistem')
            ->assertDontSee('Permission');
    }

    public function test_user_with_permissions_permission_can_see_permission_menu(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'permissions.view');

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Permission')
            ->assertSee('Administrasi Sistem');
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::create([
            'name' => 'test_sidebar_role_'.str_replace('.', '_', $permissionName),
            'display_name' => 'Test Sidebar Role',
            'is_system' => false,
            'is_active' => true,
        ]);

        [$module, $action] = explode('.', $permissionName);

        $permission = Permission::create([
            'name' => $permissionName,
            'module' => $module,
            'action' => $action,
            'display_name' => $permissionName,
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);
    }
}

8. Jalankan pemeriksaan
php artisan optimize:clear
Lalu:
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 53 menjadi sekitar 56.

9. Uji manual
Jalankan:
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/dashboard
Pastikan terlihat sidebar di sisi kiri:
SIM Madrasah
Utama
- Dashboard

Administrasi Sistem
- Role
- Permission

Akun
- Profil Saya

Modul Berikutnya
Buka juga:
open http://sim-madrasah.test/admin/roles
open http://sim-madrasah.test/admin/permissions
Sidebar harus tetap muncul.

10. Commit ke GitHub
Jika semua aman:
git status
Kemudian:
git add resources/views/components/ui/sidebar-link.blade.php \
        resources/views/components/ui/sidebar-section.blade.php \
        resources/views/layouts/sidebar.blade.php \
        resources/views/layouts/app.blade.php \
        resources/views/layouts/navigation.blade.php \
        tests/Feature/Dashboard/SidebarNavigationTest.php
Commit:
git commit -m "feat: add sidebar navigation layout"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean
Kirim hasil dari:
php artisan test
npm run build
git status
Setelah berhasil, kita lanjut ke Tahap 11.4: Dashboard Super Admin dengan Ringkasan Role, Permission, dan User.








Tahap 11.4: Dashboard Super Admin dengan Ringkasan User, Role, dan Permission.

Tahap 11.4: Ringkasan Sistem untuk Super Admin
Tujuan
Pada tahap ini, dashboard Super Admin akan menampilkan ringkasan awal sistem:
Total User
User Aktif
Total Role
Total Permission
Data ini sudah bisa dihitung karena tabel users, roles, dan permissions sudah tersedia.

1. Perbarui DashboardController
Buka:
nano app/Http/Controllers/DashboardController.php
Tambahkan import berikut di bagian atas:
use App\Models\Permission;
use App\Models\Role;
Kemudian di method __invoke, ubah bagian return view(...) menjadi seperti ini:
return view('dashboard', [
    'user' => $user,
    'roles' => $roles,
    'dashboardTitle' => $this->resolveDashboardTitle($roleNames),
    'summaryCards' => $this->resolveSummaryCards(
        $user,
        $roles,
        $permissionCount
    ),
    'systemCards' => $this->resolveSystemCards($roleNames),
    'moduleCards' => $this->resolveModuleCards($roleNames),
]);
Lalu tambahkan method berikut sebelum resolveModuleCards():
/**
 * Ringkasan sistem untuk Super Admin.
 *
 * @return array<int, array<string, string|int>>
 */
private function resolveSystemCards(Collection $roleNames): array
{
    if (! $roleNames->contains('super_admin')) {
        return [];
    }

    return [
        [
            'label' => 'Total User',
            'value' => User::query()->count(),
            'description' => 'Jumlah akun yang terdaftar dalam sistem.',
        ],
        [
            'label' => 'User Aktif',
            'value' => User::query()
                ->where('status', 'active')
                ->count(),
            'description' => 'Jumlah akun yang sedang aktif.',
        ],
        [
            'label' => 'Total Role',
            'value' => Role::query()->count(),
            'description' => 'Jumlah role yang tersedia.',
        ],
        [
            'label' => 'Total Permission',
            'value' => Permission::query()->count(),
            'description' => 'Jumlah permission yang tersedia.',
        ],
    ];
}

2. Perbarui dashboard.blade.php
Buka:
nano resources/views/dashboard.blade.php
Cari bagian setelah card ringkasan akun:
<div class="grid grid-cols-1 gap-6 md:grid-cols-4">
    @foreach ($summaryCards as $card)
        <x-ui.stat-card
            :label="$card['label']"
            :value="$card['value']"
            :description="$card['description']"
        />
    @endforeach
</div>
Tambahkan kode berikut setelah blok tersebut:
@if (! empty($systemCards))
    <div>
        <div class="flex items-end justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Ringkasan Sistem
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Data umum sistem yang hanya ditampilkan untuk Super Admin.
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-4">
            @foreach ($systemCards as $card)
                <x-ui.stat-card
                    :label="$card['label']"
                    :value="$card['value']"
                    :description="$card['description']"
                />
            @endforeach
        </div>
    </div>
@endif

3. Buat test
Jalankan:
php artisan make:test Dashboard/SuperAdminDashboardSummaryTest
Buka:
nano tests/Feature/Dashboard/SuperAdminDashboardSummaryTest.php
Isi dengan:
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminDashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_see_system_summary_cards(): void
    {
        $user = User::factory()->create([
            'name' => 'Administrator SIM Madrasah',
            'status' => 'active',
        ]);

        User::factory()->count(2)->create([
            'status' => 'active',
        ]);

        User::factory()->create([
            'status' => 'inactive',
        ]);

        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        Permission::create([
            'name' => 'roles.view',
            'module' => 'roles',
            'action' => 'view',
            'display_name' => 'Melihat Role',
            'is_active' => true,
        ]);

        $user->roles()->attach($superAdminRole->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Ringkasan Sistem')
            ->assertSee('Total User')
            ->assertSee('User Aktif')
            ->assertSee('Total Role')
            ->assertSee('Total Permission');
    }

    public function test_non_super_admin_cannot_see_system_summary_cards(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertDontSee('Ringkasan Sistem')
            ->assertDontSee('Total User')
            ->assertDontSee('Total Role');
    }
}

4. Jalankan pemeriksaan
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 56 menjadi sekitar 58.

5. Uji manual
Pastikan Super Admin punya role dan permission:
php artisan db:seed --class=RbacSeeder
Buka dashboard:
open http://sim-madrasah.test/dashboard
Pastikan tampil bagian:
Ringkasan Sistem
Total User
User Aktif
Total Role
Total Permission

6. Commit ke GitHub
Jika aman:
git status
Lalu:
git add app/Http/Controllers/DashboardController.php \
        resources/views/dashboard.blade.php \
        tests/Feature/Dashboard/SuperAdminDashboardSummaryTest.php
Commit:
git commit -m "feat: add super admin system summary dashboard"
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
Setelah berhasil, kita lanjut ke Tahap 11.5: Shortcut Dashboard Super Admin ke Role dan Permission.










Tahap 11.5: Shortcut Dashboard Super Admin ke Role dan Permission.
Pada tahap ini kita membuat shortcut khusus di dashboard Super Admin agar akses ke halaman RBAC lebih cepat.
Tahap 11.5: Shortcut Dashboard Super Admin
Tujuan
Dashboard Super Admin akan memiliki bagian baru:
Shortcut Administrasi Sistem
Isinya:
Kelola Role
Kelola Permission
Detail Role Super Admin
Shortcut ini hanya tampil untuk user dengan role:
super_admin

1. Perbarui DashboardController
Buka:
nano app/Http/Controllers/DashboardController.php
Pastikan bagian import atas sudah memiliki:
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
Pada method __invoke, cari bagian:
return view('dashboard', [
Ubah menjadi:
return view('dashboard', [
    'user' => $user,
    'roles' => $roles,
    'dashboardTitle' => $this->resolveDashboardTitle($roleNames),
    'summaryCards' => $this->resolveSummaryCards(
        $user,
        $roles,
        $permissionCount
    ),
    'systemCards' => $this->resolveSystemCards($roleNames),
    'shortcutCards' => $this->resolveShortcutCards($roleNames),
    'moduleCards' => $this->resolveModuleCards($roleNames),
]);
Lalu tambahkan method berikut sebelum resolveModuleCards():
/**
 * Shortcut cepat untuk Super Admin.
 *
 * @return array<int, array<string, string|null>>
 */
private function resolveShortcutCards(Collection $roleNames): array
{
    if (! $roleNames->contains('super_admin')) {
        return [];
    }

    $superAdminRole = Role::query()
        ->where('name', 'super_admin')
        ->first();

    return [
        [
            'title' => 'Kelola Role',
            'description' => 'Lihat daftar role, jumlah user, dan jumlah permission pada setiap role.',
            'href' => route('admin.roles.index'),
            'label' => 'Buka Role',
        ],
        [
            'title' => 'Kelola Permission',
            'description' => 'Lihat seluruh permission yang digunakan untuk membatasi akses fitur.',
            'href' => route('admin.permissions.index'),
            'label' => 'Buka Permission',
        ],
        [
            'title' => 'Detail Role Super Admin',
            'description' => 'Periksa permission yang dimiliki oleh role Super Admin.',
            'href' => $superAdminRole
                ? route('admin.roles.show', $superAdminRole)
                : null,
            'label' => 'Lihat Detail',
        ],
    ];
}
Mengapa shortcut dibuat terpisah?
moduleCards berisi gambaran modul kerja berdasarkan role.
shortcutCards berisi akses cepat untuk tugas administrasi sistem.
Pemisahan ini membuat dashboard lebih mudah dikembangkan. Nanti kita bisa menambahkan shortcut lain seperti:
Tambah User
Backup Database
Activity Log
Audit Log
Pengaturan Sistem

2. Buat komponen shortcut card
Buat file:
nano resources/views/components/ui/shortcut-card.blade.php
Isi:
@props([
    'title',
    'description',
    'href' => null,
    'label' => 'Buka',
])

<div class="rounded-lg border border-green-100 bg-white p-5 shadow-sm">
    <div class="flex h-full flex-col justify-between">
        <div>
            <h4 class="font-semibold text-gray-900">
                {{ $title }}
            </h4>

            <p class="mt-2 text-sm text-gray-500">
                {{ $description }}
            </p>
        </div>

        <div class="mt-5">
            @if ($href)
                <a
                    href="{{ $href }}"
                    class="inline-flex items-center rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    {{ $label }}
                </a>
            @else
                <span class="inline-flex items-center rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-500">
                    Belum tersedia
                </span>
            @endif
        </div>
    </div>
</div>

3. Perbarui dashboard view
Buka:
nano resources/views/dashboard.blade.php
Cari bagian setelah Ringkasan Sistem. Tambahkan blok berikut setelah blok @if (! empty($systemCards)) ... @endif:
@if (! empty($shortcutCards))
    <div>
        <div class="flex items-end justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Shortcut Administrasi Sistem
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Akses cepat untuk mengelola fondasi hak akses SIM Madrasah.
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ($shortcutCards as $shortcut)
                <x-ui.shortcut-card
                    :title="$shortcut['title']"
                    :description="$shortcut['description']"
                    :href="$shortcut['href']"
                    :label="$shortcut['label']"
                />
            @endforeach
        </div>
    </div>
@endif
Letakkan sebelum bagian:
Role Pengguna
Urutan dashboard menjadi lebih rapi:
Card ringkasan akun
Ringkasan Sistem
Shortcut Administrasi Sistem
Role Pengguna
Navigasi Modul
Keamanan Akun
Catatan Pengembangan

4. Buat test shortcut dashboard
Jalankan:
php artisan make:test Dashboard/SuperAdminShortcutTest
Buka:
nano tests/Feature/Dashboard/SuperAdminShortcutTest.php
Isi:
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminShortcutTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_see_rbac_shortcuts(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $user->roles()->attach($superAdminRole->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Shortcut Administrasi Sistem')
            ->assertSee('Kelola Role')
            ->assertSee('Kelola Permission')
            ->assertSee('Detail Role Super Admin');
    }

    public function test_non_super_admin_cannot_see_rbac_shortcuts(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertDontSee('Shortcut Administrasi Sistem')
            ->assertDontSee('Kelola Role')
            ->assertDontSee('Kelola Permission')
            ->assertDontSee('Detail Role Super Admin');
    }
}

5. Jalankan pemeriksaan
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test akan bertambah dari 57 menjadi sekitar 59.

6. Uji manual
Jalankan:
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin, lalu buka:
open http://sim-madrasah.test/dashboard
Pastikan muncul bagian:
Shortcut Administrasi Sistem
Kelola Role
Kelola Permission
Detail Role Super Admin
Klik masing-masing shortcut:
Kelola Role            → /admin/roles
Kelola Permission      → /admin/permissions
Detail Role Super Admin → /admin/roles/{id}

7. Commit ke GitHub
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/DashboardController.php \
        resources/views/dashboard.blade.php \
        resources/views/components/ui/shortcut-card.blade.php \
        tests/Feature/Dashboard/SuperAdminShortcutTest.php
Commit:
git commit -m "feat: add super admin dashboard shortcuts"
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
Setelah berhasil, kita lanjut ke Tahap 11.6: Finalisasi Dashboard Dasar dan Persiapan Masuk Modul Data Master.








Tahap 11.6: Finalisasi Dashboard Dasar dan Persiapan Masuk Modul Data Master.
Tahap ini menjadi penutup Tahap 11 Dashboard. Kita tidak membuat modul data master dulu. Kita hanya menyiapkan dashboard agar jelas bahwa pengembangan berikutnya adalah modul Data Master.
Target tahap ini:
1. Menambahkan section “Tahap Pengembangan Berikutnya”
2. Menampilkan daftar modul Data Master yang akan dibuat
3. Menjaga dashboard tetap berbasis role
4. Menambah test agar dashboard tidak mudah rusak
5. Menutup Tahap 11 dengan kondisi stabil

Tahap 11.6A: Tambahkan Roadmap Modul Berikutnya
1. Perbarui DashboardController
Buka:
nano app/Http/Controllers/DashboardController.php
Pada method __invoke, cari bagian:
return view('dashboard', [
Pastikan menjadi seperti ini:
return view('dashboard', [
    'user' => $user,
    'roles' => $roles,
    'dashboardTitle' => $this->resolveDashboardTitle($roleNames),
    'summaryCards' => $this->resolveSummaryCards(
        $user,
        $roles,
        $permissionCount
    ),
    'systemCards' => $this->resolveSystemCards($roleNames),
    'shortcutCards' => $this->resolveShortcutCards($roleNames),
    'moduleCards' => $this->resolveModuleCards($roleNames),
    'nextDevelopmentCards' => $this->resolveNextDevelopmentCards($roleNames),
]);
Lalu tambahkan method ini sebelum resolveModuleCards():
/**
 * Menampilkan rencana modul berikutnya setelah dashboard.
 *
 * @return array<int, array<string, string>>
 */
private function resolveNextDevelopmentCards(Collection $roleNames): array
{
    if (! $roleNames->contains('super_admin')) {
        return [];
    }

    return [
        [
            'title' => 'Identitas Madrasah',
            'description' => 'Menyimpan nama madrasah, NSM, NPSN, alamat, logo, kontak, dan konfigurasi identitas dasar.',
            'stage' => 'Data Master',
        ],
        [
            'title' => 'Tahun Ajaran dan Semester',
            'description' => 'Menjadi pusat histori untuk penempatan siswa, penugasan guru, jadwal, nilai, rapor, dan absensi.',
            'stage' => 'Data Master',
        ],
        [
            'title' => 'Data Guru dan Pegawai',
            'description' => 'Menyimpan identitas guru, tenaga kependidikan, status pegawai, dan relasi dengan akun pengguna.',
            'stage' => 'Data Master',
        ],
        [
            'title' => 'Data Siswa',
            'description' => 'Menyimpan identitas siswa secara permanen tanpa menyimpan kelas sebagai data tunggal yang ditimpa.',
            'stage' => 'Data Master',
        ],
        [
            'title' => 'Kelas dan Ruangan',
            'description' => 'Menjadi dasar rombongan belajar, penempatan siswa, jadwal pelajaran, dan manajemen ruang.',
            'stage' => 'Data Master',
        ],
        [
            'title' => 'Mata Pelajaran',
            'description' => 'Menjadi dasar kurikulum, penugasan mengajar, jadwal, nilai, dan rapor.',
            'stage' => 'Data Master',
        ],
    ];
}
2. Mengapa hanya Super Admin?
Karena bagian ini adalah roadmap pengembangan sistem, bukan menu operasional harian.
Guru, siswa, orang tua, bendahara, dan wali kelas tidak perlu melihat roadmap teknis pengembangan aplikasi.

Tahap 11.6B: Perbarui Tampilan Dashboard
Buka:
nano resources/views/dashboard.blade.php
Cari bagian sebelum Catatan Pengembangan.
Tambahkan blok ini sebelum blok catatan pengembangan:
@if (! empty($nextDevelopmentCards))
    <div>
        <div class="flex items-end justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">
                    Tahap Pengembangan Berikutnya
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Modul Data Master akan menjadi fondasi untuk akademik, keuangan, kesiswaan, dan portofolio siswa.
                </p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($nextDevelopmentCards as $card)
                <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <h4 class="font-semibold text-gray-900">
                            {{ $card['title'] }}
                        </h4>

                        <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                            {{ $card['stage'] }}
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        {{ $card['description'] }}
                    </p>

                    <div class="mt-4 text-xs font-medium text-gray-400">
                        Belum aktif. Menunggu pembuatan migration, model, route, dan halaman modul.
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
Urutan dashboard sekarang menjadi:
1. Ringkasan akun
2. Ringkasan sistem
3. Shortcut administrasi sistem
4. Role pengguna
5. Navigasi modul
6. Keamanan akun
7. Tahap pengembangan berikutnya
8. Catatan pengembangan

Tahap 11.6C: Tambahkan Test Roadmap Dashboard
Jalankan:
php artisan make:test Dashboard/DashboardNextDevelopmentTest
Buka:
nano tests/Feature/Dashboard/DashboardNextDevelopmentTest.php
Isi:
<?php

namespace Tests\Feature\Dashboard;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNextDevelopmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_see_next_development_section(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $role = Role::create([
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
            'is_system' => true,
            'is_active' => true,
        ]);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertSee('Tahap Pengembangan Berikutnya')
            ->assertSee('Identitas Madrasah')
            ->assertSee('Tahun Ajaran dan Semester')
            ->assertSee('Data Guru dan Pegawai')
            ->assertSee('Data Siswa')
            ->assertSee('Kelas dan Ruangan')
            ->assertSee('Mata Pelajaran');
    }

    public function test_non_super_admin_cannot_see_next_development_section(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $role = Role::create([
            'name' => 'bendahara',
            'display_name' => 'Bendahara',
            'is_system' => true,
            'is_active' => true,
        ]);

        $permission = Permission::create([
            'name' => 'dashboard.view',
            'module' => 'dashboard',
            'action' => 'view',
            'display_name' => 'Melihat Dashboard',
            'is_active' => true,
        ]);

        $role->permissions()->attach($permission->id);

        $user->roles()->attach($role->id, [
            'assigned_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response
            ->assertStatus(200)
            ->assertDontSee('Tahap Pengembangan Berikutnya')
            ->assertDontSee('Identitas Madrasah')
            ->assertDontSee('Tahun Ajaran dan Semester');
    }
}

Tahap 11.6D: Jalankan Pemeriksaan
Jalankan:
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
semua test PASS
build berhasil
Jumlah test kemungkinan bertambah dari 57 menjadi sekitar 59.

Tahap 11.6E: Uji Manual
Jalankan:
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin, lalu buka:
open http://sim-madrasah.test/dashboard
Pastikan tampil bagian:
Tahap Pengembangan Berikutnya
Identitas Madrasah
Tahun Ajaran dan Semester
Data Guru dan Pegawai
Data Siswa
Kelas dan Ruangan
Mata Pelajaran

Tahap 11.6F: Commit ke GitHub
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/DashboardController.php \
        resources/views/dashboard.blade.php \
        tests/Feature/Dashboard/DashboardNextDevelopmentTest.php
Commit:
git commit -m "feat: add dashboard next development roadmap"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean

Penutupan Tahap 11
Jika semua berhasil, maka Tahap 11 Dashboard selesai.
Fondasi dashboard yang sudah selesai:
DashboardController tersedia
Dashboard berbasis role aktif
Ringkasan akun tersedia
Ringkasan sistem Super Admin tersedia
Shortcut administrasi sistem tersedia
Navigasi modul tersedia
Sidebar layout tersedia
Keamanan akun tampil
Roadmap modul Data Master tampil
Test dashboard stabil
Build frontend berhasil
Setelah ini kita masuk ke Tahap 12: Coding Modul Satu per Satu.
Modul pertama yang paling tepat adalah:
Modul Data Master 1: Identitas Madrasah
Kenapa mulai dari identitas madrasah?
Karena data ini menjadi dasar untuk:
kop surat
rapor
kwitansi
website publik
PPDB
PKKM
akreditasi
profil sistem
pengaturan aplikasi
Kirim hasil dari:
php artisan test
npm run build
git status
Setelah bersih, kita lanjut ke Tahap 12.1: Modul Identitas Madrasah.










