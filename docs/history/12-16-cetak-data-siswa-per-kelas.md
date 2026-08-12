# Tahap 12.16 — Cetak Data Siswa Per Kelas

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 36939–37586.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.16: Cetak Data Siswa Per Kelas
Pada tahap ini kita membuat fitur cetak daftar siswa berdasarkan rombongan belajar.
Data yang dicetak berasal dari:
class_groups
  ↓
student_class_histories
  ↓
students
  ↓
people
Artinya, sistem tidak mengambil kelas dari tabel students, tetapi dari riwayat kelas siswa. Ini sesuai prinsip utama SIM Madrasah: histori kelas tidak boleh ditimpa.

Tahap 12.16A: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'class_groups.print_students',
    'module' => 'class_groups',
    'action' => 'print_students',
    'display_name' => 'Mencetak Data Siswa Per Kelas',
    'description' => 'Mencetak daftar siswa berdasarkan rombongan belajar.',
],
Permission ini dipisahkan dari class_groups.view karena melihat data kelas dan mencetak dokumen adalah hak akses yang berbeda.

Tahap 12.16B: Buat Controller Cetak Siswa Per Kelas
Jalankan:
php artisan make:controller Admin/ClassGroupStudentPrintController
Buka:
nano app/Http/Controllers/Admin/ClassGroupStudentPrintController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\StudentClassHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ClassGroupStudentPrintController extends Controller
{
    /**
     * Mencetak daftar siswa per rombongan belajar.
     */
    public function __invoke(
        Request $request,
        ClassGroup $classGroup
    ): Response {
        $classGroup->load([
            'academicYear',
            'gradeLevel',
            'room',
            'homeroomTeacher',
        ]);

        $semester = $this->resolveSemester($request, $classGroup);

        $histories = StudentClassHistory::query()
            ->with([
                'student.person',
                'semester',
                'academicYear',
                'classGroup',
            ])
            ->where('class_group_id', $classGroup->id)
            ->when(
                $semester !== null,
                fn ($query) => $query->where('semester_id', $semester->id)
            )
            ->get()
            ->sortBy(fn ($history) => $history->student?->person?->full_name)
            ->values();

        $filename = 'daftar-siswa-'.Str::slug($classGroup->code).'.pdf';

        $pdf = Pdf::loadView('admin.class-groups.students-pdf', [
            'classGroup' => $classGroup,
            'semester' => $semester,
            'histories' => $histories,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }

    private function resolveSemester(
        Request $request,
        ClassGroup $classGroup
    ): ?Semester {
        if ($request->filled('semester_id')) {
            return Semester::query()
                ->where('academic_year_id', $classGroup->academic_year_id)
                ->whereKey($request->integer('semester_id'))
                ->first();
        }

        return Semester::query()
            ->where('academic_year_id', $classGroup->academic_year_id)
            ->where('status', 'active')
            ->first()
            ?? Semester::query()
                ->where('academic_year_id', $classGroup->academic_year_id)
                ->orderBy('start_date')
                ->first();
    }
}
Kenapa controller ini memakai __invoke()?
Karena fitur ini hanya punya satu tugas, yaitu mencetak daftar siswa. Controller seperti ini lebih ringkas dan mudah dibaca.

Tahap 12.16C: Tambahkan Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\ClassGroupStudentPrintController;
Di dalam group admin, tambahkan setelah route class-groups:
Route::get('/class-groups/{classGroup}/students/pdf', ClassGroupStudentPrintController::class)
    ->middleware('permission:class_groups.print_students')
    ->name('class-groups.students.pdf');
Target route:
GET admin/class-groups/{classGroup}/students/pdf

Tahap 12.16D: Tambahkan Tombol Cetak di Halaman Rombongan Belajar
Buka:
nano resources/views/admin/class-groups/index.blade.php
Cari bagian aksi:
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
Ganti menjadi:
<td class="px-4 py-3">
    <div class="flex flex-wrap gap-3">
        @can('permission', 'class_groups.update')
            <a
                href="{{ route('admin.class-groups.edit', $classGroup) }}"
                class="text-sm font-medium text-green-700 hover:text-green-900"
            >
                Edit
            </a>
        @endcan

        @can('permission', 'class_groups.print_students')
            <a
                href="{{ route('admin.class-groups.students.pdf', $classGroup) }}"
                target="_blank"
                class="text-sm font-medium text-blue-700 hover:text-blue-900"
            >
                Cetak Siswa
            </a>
        @endcan
    </div>
</td>

Tahap 12.16E: Buat View PDF Data Siswa Per Kelas
Buat file:
nano resources/views/admin/class-groups/students-pdf.blade.php
Isi:
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Siswa Per Kelas</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 11px;
            margin: 24px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #166534;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .school {
            font-size: 18px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 4px;
        }

        .title {
            font-size: 14px;
            font-weight: bold;
        }

        .meta {
            width: 100%;
            margin-bottom: 16px;
            font-size: 11px;
        }

        .meta td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            width: 120px;
            color: #6b7280;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #9ca3af;
            padding: 7px;
            text-align: left;
            font-weight: bold;
        }

        table.data td {
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #6b7280;
        }

        .signature {
            width: 100%;
            margin-top: 36px;
            font-size: 11px;
        }

        .signature td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }

        .name-line {
            margin-top: 56px;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school">SIM Madrasah</div>
        <div class="title">Daftar Siswa Per Kelas</div>
    </div>

    <table class="meta">
        <tr>
            <td class="label">Tahun Ajaran</td>
            <td>: {{ $classGroup->academicYear?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td>: {{ $semester?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Rombel</td>
            <td>: {{ $classGroup->name }}</td>
        </tr>
        <tr>
            <td class="label">Tingkat</td>
            <td>: {{ $classGroup->gradeLevel?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Ruangan</td>
            <td>: {{ $classGroup->room?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Wali Kelas</td>
            <td>: {{ $classGroup->homeroomTeacher?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Siswa</td>
            <td>: {{ $histories->count() }} siswa</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="center" style="width: 32px;">No</th>
                <th>Nama Siswa</th>
                <th style="width: 90px;">NIS</th>
                <th style="width: 100px;">NISN</th>
                <th style="width: 80px;">Status</th>
                <th style="width: 120px;">Keterangan</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($histories as $history)
                <tr>
                    <td class="center">{{ $loop->iteration }}</td>
                    <td>{{ $history->student?->person?->full_name ?? '-' }}</td>
                    <td>{{ $history->student?->student_number ?? '-' }}</td>
                    <td>{{ $history->student?->nisn ?? '-' }}</td>
                    <td>{{ $history->status }}</td>
                    <td>{{ $history->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">
                        Belum ada siswa pada rombongan belajar ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Madrasah

                <div class="name-line">
                    ................................
                </div>
            </td>

            <td>
                Wali Kelas

                <div class="name-line">
                    {{ $classGroup->homeroomTeacher?->name ?? '................................' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis dari Sistem Informasi Manajemen Madrasah.
    </div>
</body>
</html>

Tahap 12.16F: Test Cetak Data Siswa Per Kelas
Buat test:
php artisan make:test Admin/ClassGroupStudentPrintTest
Buka:
nano tests/Feature/Admin/ClassGroupStudentPrintTest.php
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

class ClassGroupStudentPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_print_students_by_class_group_pdf(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.print_students');

        [$classGroup] = $this->createClassGroupWithStudent();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.class-groups.students.pdf', $classGroup));

        $response->assertStatus(200);

        $this->assertStringContainsString(
            'application/pdf',
            (string) $response->headers->get('content-type')
        );

        $this->assertStringStartsWith(
            '%PDF',
            $response->getContent()
        );
    }

    public function test_user_without_permission_cannot_print_students_by_class_group_pdf(): void
    {
        $user = User::factory()->create();

        [$classGroup] = $this->createClassGroupWithStudent();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.class-groups.students.pdf', $classGroup));

        $response->assertForbidden();
    }

    /**
     * @return array{0: ClassGroup}
     */
    private function createClassGroupWithStudent(): array
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
            'email' => 'ahmad.siswa@test.local',
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

        StudentClassHistory::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'class_group_id' => $classGroup->id,
            'status' => 'active',
            'start_date' => '2026-07-01',
            'is_current' => true,
        ]);

        return [$classGroup];
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_class_group_print_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Class Group Print Role',
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

Tahap 12.16G: Jalankan Pemeriksaan
Jalankan:
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=class-groups
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route tambahan:
GET|HEAD  admin/class-groups/{classGroup}/students/pdf
Target test:
113+ tests passed
build berhasil

Tahap 12.16H: Uji Manual
Jalankan seeder pendukung jika perlu:
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=GradeLevelSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=ClassGroupSeeder
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentClassHistorySeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/class-groups
Klik:
Cetak Siswa
Pastikan PDF menampilkan:
Daftar Siswa Per Kelas
Tahun Ajaran
Semester
Rombel
Tingkat
Ruangan
Wali Kelas
Jumlah Siswa
Tabel siswa
Kolom tanda tangan

Tahap 12.16I: Commit
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/Admin/ClassGroupStudentPrintController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/class-groups/index.blade.php \
        resources/views/admin/class-groups/students-pdf.blade.php \
        tests/Feature/Admin/ClassGroupStudentPrintTest.php
Commit:
git commit -m "feat: add class group student print pdf"
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
Setelah berhasil, kita lanjut ke Tahap 12.17: Export Data Siswa Per Kelas ke Excel.

















