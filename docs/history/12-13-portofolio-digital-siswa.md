# Tahap 12.13 — Ringkasan Portofolio Digital Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 35431–36203.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.13: Ringkasan Portofolio Digital Siswa
Tahap ini belum membuat seluruh isi portofolio lengkap. Kita mulai dari halaman ringkasan portofolio siswa.
Tujuannya:
1. Menampilkan identitas siswa.
2. Menampilkan akun siswa.
3. Menampilkan kelas aktif siswa.
4. Menampilkan riwayat kelas.
5. Menampilkan orang tua/wali.
6. Menjadi pintu masuk fitur portofolio lanjutan.
Nanti halaman ini akan berkembang untuk menampilkan:
kehadiran
nilai
prestasi
pelanggaran
konseling
tahfidz
pembiasaan
pembayaran
dokumen
QR Code
rapor

Tahap 12.13A: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'student_portfolios.view',
    'module' => 'student_portfolios',
    'action' => 'view',
    'display_name' => 'Melihat Portofolio Digital Siswa',
    'description' => 'Melihat ringkasan portofolio digital siswa.',
],
Kenapa permission baru?
Karena portofolio siswa nanti berisi data sensitif. Tidak semua role boleh melihat seluruh riwayat siswa.

Tahap 12.13B: Buat Controller Portofolio Siswa
Jalankan:
php artisan make:controller Admin/StudentPortfolioController
Buka:
nano app/Http/Controllers/Admin/StudentPortfolioController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Contracts\View\View;

class StudentPortfolioController extends Controller
{
    /**
     * Menampilkan ringkasan portofolio digital siswa.
     */
    public function show(Student $student): View
    {
        $student->load([
            'person.user',
            'admissionAcademicYear',
            'currentClassHistory.academicYear',
            'currentClassHistory.semester',
            'currentClassHistory.classGroup.gradeLevel',
            'currentClassHistory.classGroup.room',
        ]);

        $classHistories = $student->classHistories()
            ->with([
                'academicYear',
                'semester',
                'classGroup.gradeLevel',
                'classGroup.room',
            ])
            ->orderByDesc('academic_year_id')
            ->orderByDesc('semester_id')
            ->limit(10)
            ->get();

        $guardians = $student->guardians()
            ->with('person.user')
            ->orderByDesc('is_primary_contact')
            ->orderBy('relationship')
            ->get();

        return view('admin.students.portfolio.show', [
            'student' => $student,
            'classHistories' => $classHistories,
            'guardians' => $guardians,
        ]);
    }
}

Tahap 12.13C: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\StudentPortfolioController;
Di dalam group admin, tambahkan setelah route students:
Route::get('/students/{student}/portfolio', [StudentPortfolioController::class, 'show'])
    ->middleware('permission:student_portfolios.view')
    ->name('students.portfolio.show');
Route ini menghasilkan alamat:
/admin/students/{student}/portfolio

Tahap 12.13D: Update View Daftar Siswa
Buka:
nano resources/views/admin/students/index.blade.php
Pada bagian aksi siswa, tambahkan link ini di dalam:
<div class="flex flex-wrap gap-3">
Tambahkan:
@can('permission', 'student_portfolios.view')
    <a
        href="{{ route('admin.students.portfolio.show', $student) }}"
        class="text-sm font-medium text-indigo-700 hover:text-indigo-900"
    >
        Portofolio
    </a>
@endcan
Aksi siswa sekarang menjadi:
Edit
Riwayat Kelas
Wali
Portofolio

Tahap 12.13E: Buat View Portofolio Siswa
Buat folder:
mkdir -p resources/views/admin/students/portfolio
Buat file:
nano resources/views/admin/students/portfolio/show.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Portofolio Digital Siswa
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    {{ $student->person?->full_name }}
                </p>
            </div>

            <a
                href="{{ route('admin.students.index') }}"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Identitas Siswa
                        </h3>

                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                            <div>
                                <div class="text-gray-500">Nama Lengkap</div>
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

                            <div>
                                <div class="text-gray-500">Nomor Registrasi</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->registration_number ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Tahun Ajaran Masuk</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->admissionAcademicYear?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Tanggal Masuk</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->admission_date?->format('d M Y') ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Email</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->person?->email ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Telepon</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->person?->phone ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Status Ringkas
                        </h3>

                        <div class="mt-4 space-y-4 text-sm">
                            <div>
                                <div class="text-gray-500">Status Siswa</div>
                                <div class="mt-1">
                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        {{ $student->status }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Status Akun</div>

                                @if ($student->person?->user)
                                    <div class="mt-1 font-mono text-xs text-gray-900">
                                        {{ $student->person->user->username }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $student->person->user->status }}
                                    </div>
                                @else
                                    <div class="mt-1 text-gray-500">
                                        Belum memiliki akun.
                                    </div>
                                @endif
                            </div>

                            <div>
                                <div class="text-gray-500">Kelas Saat Ini</div>

                                @if ($student->currentClassHistory)
                                    <div class="mt-1 font-medium text-gray-900">
                                        {{ $student->currentClassHistory->classGroup?->name ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $student->currentClassHistory->semester?->name ?? '-' }}
                                    </div>
                                @else
                                    <div class="mt-1 text-gray-500">
                                        Belum ada kelas aktif.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900">
                        Kelas Aktif
                    </h3>

                    @if ($student->currentClassHistory)
                        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-4 text-sm">
                            <div>
                                <div class="text-gray-500">Tahun Ajaran</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->academicYear?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Semester</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->semester?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Rombel</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->classGroup?->name ?? '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-gray-500">Ruangan</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->currentClassHistory->classGroup?->room?->name ?? '-' }}
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">
                            Siswa belum memiliki kelas aktif.
                        </p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Riwayat Kelas Terbaru
                        </h3>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Tahun
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Semester
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Rombel
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">
                                            Status
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-100">
                                    @forelse ($classHistories as $history)
                                        <tr>
                                            <td class="px-4 py-3">
                                                {{ $history->academicYear?->name ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                {{ $history->semester?->name ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                {{ $history->classGroup?->name ?? '-' }}
                                            </td>

                                            <td class="px-4 py-3">
                                                @if ($history->is_current)
                                                    <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                        Saat Ini
                                                    </span>
                                                @else
                                                    <span class="rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                                        {{ $history->status }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                                Belum ada riwayat kelas.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="font-semibold text-gray-900">
                            Orang Tua/Wali
                        </h3>

                        <div class="mt-4 space-y-4">
                            @forelse ($guardians as $guardian)
                                <div class="rounded-lg border border-gray-100 p-4 text-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="font-medium text-gray-900">
                                                {{ $guardian->person?->full_name }}
                                            </div>

                                            <div class="text-xs text-gray-500">
                                                {{ $guardian->relationship }}
                                            </div>
                                        </div>

                                        @if ($guardian->is_primary_contact)
                                            <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                                Utama
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 text-gray-600">
                                        {{ $guardian->person?->phone ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500">
                                        {{ $guardian->person?->email ?? '-' }}
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">
                                    Belum ada data orang tua atau wali.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900">
                        Modul Portofolio Berikutnya
                    </h3>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        @foreach ([
                            'Kehadiran',
                            'Nilai',
                            'Prestasi',
                            'Pelanggaran',
                            'Konseling',
                            'Tahfidz',
                            'Pembiasaan',
                            'Pembayaran',
                            'Dokumen',
                        ] as $module)
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                                <div class="font-medium text-gray-900">
                                    {{ $module }}
                                </div>

                                <div class="mt-1 text-xs text-gray-500">
                                    Belum aktif. Akan dibuat pada tahap modul lanjutan.
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

Tahap 12.13F: Dashboard Shortcut
Buka:
nano app/Http/Controllers/DashboardController.php
Pada resolveShortcutCards, tambahkan:
[
    'title' => 'Portofolio Digital Siswa',
    'description' => 'Lihat ringkasan identitas, kelas aktif, riwayat kelas, dan wali siswa.',
    'href' => route('admin.students.index'),
    'label' => 'Buka Portofolio',
],
Kita arahkan ke halaman siswa karena portofolio melekat pada masing-masing siswa.

Tahap 12.13G: Test Portofolio Siswa
Buat test:
php artisan make:test Admin/StudentPortfolioTest
Buka:
nano tests/Feature/Admin/StudentPortfolioTest.php
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
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_student_portfolio(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'student_portfolios.view');

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.show', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Portofolio Digital Siswa')
            ->assertSee('Ahmad Siswa')
            ->assertSee('Kelas VII A')
            ->assertSee('Wali Ahmad')
            ->assertSee('Modul Portofolio Berikutnya');
    }

    public function test_user_without_permission_cannot_view_student_portfolio(): void
    {
        $user = User::factory()->create();

        [$student] = $this->createPortfolioData();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.students.portfolio.show', $student));

        $response->assertForbidden();
    }

    /**
     * @return array{0: Student}
     */
    private function createPortfolioData(): array
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

        $studentPerson = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.siswa@test.local',
        ]);

        $student = Student::create([
            'person_id' => $studentPerson->id,
            'admission_academic_year_id' => $academicYear->id,
            'student_number' => 'SIS-001',
            'nisn' => '1000000001',
            'registration_number' => 'REG-001',
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        $guardianPerson = Person::create([
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
            'phone' => '08123456789',
        ]);

        StudentGuardian::create([
            'student_id' => $student->id,
            'person_id' => $guardianPerson->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);

        return [$student];
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_student_portfolio_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Portfolio Role',
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

Tahap 12.13H: Jalankan Pemeriksaan
Jalankan:
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=portfolio
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route:
GET|HEAD  admin/students/{student}/portfolio
Target test:
108+ tests passed
build berhasil

Tahap 12.13I: Uji Manual
Jalankan seeder pendukung:
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=GradeLevelSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=ClassGroupSeeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentClassHistorySeeder
php artisan db:seed --class=StudentGuardianSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Klik:
Portofolio
Pastikan halaman menampilkan:
Identitas Siswa
Status Ringkas
Kelas Saat Ini
Riwayat Kelas Terbaru
Orang Tua/Wali
Modul Portofolio Berikutnya

Tahap 12.13J: Commit
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/Admin/StudentPortfolioController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/index.blade.php \
        resources/views/admin/students/portfolio/ \
        app/Http/Controllers/DashboardController.php \
        tests/Feature/Admin/StudentPortfolioTest.php
Commit:
git commit -m "feat: add student portfolio summary"
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
Setelah berhasil, kita lanjut ke Tahap 12.14: QR Code Portofolio Siswa.












