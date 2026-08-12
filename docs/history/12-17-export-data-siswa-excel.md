# Tahap 12.17 — Export Data Siswa Per Kelas ke Excel

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 37587–38166.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.17: Export Data Siswa Per Kelas ke Excel
Pada tahap ini kita membuat fitur export daftar siswa per rombongan belajar ke Excel.
Data tetap diambil dari:
class_groups
  ↓
student_class_histories
  ↓
students
  ↓
people
Jadi sistem tetap menjaga prinsip histori. Kita tidak mengambil kelas dari tabel students.
Untuk export Excel, kita gunakan package Laravel Excel dari Maatwebsite. Dokumentasi Laravel Excel menggunakan perintah instalasi composer require maatwebsite/excel. (Laravel Excel)

Tahap 12.17A: Install Laravel Excel
Jalankan:
composer require maatwebsite/excel
Lalu:
php artisan optimize:clear
composer dump-autoload
Cek package:
composer show maatwebsite/excel
Jika berhasil, package akan tampil.

Tahap 12.17B: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'class_groups.export_students',
    'module' => 'class_groups',
    'action' => 'export_students',
    'display_name' => 'Export Data Siswa Per Kelas',
    'description' => 'Mengekspor daftar siswa berdasarkan rombongan belajar ke Excel.',
],

Tahap 12.17C: Buat Export Class
Buat folder export:
mkdir -p app/Exports
Buat file:
nano app/Exports/ClassGroupStudentsExport.php
Isi:
<?php

namespace App\Exports;

use App\Models\ClassGroup;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;

class ClassGroupStudentsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private int $rowNumber = 0;

    /**
     * @param Collection<int, mixed> $histories
     */
    public function __construct(
        private readonly ClassGroup $classGroup,
        private readonly ?Semester $semester,
        private readonly Collection $histories
    ) {}

    /**
     * Data utama yang akan diekspor.
     *
     * @return Collection<int, mixed>
     */
    public function collection(): Collection
    {
        return $this->histories;
    }

    /**
     * Judul kolom Excel.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Siswa',
            'NIS',
            'NISN',
            'Nomor Registrasi',
            'Tahun Ajaran',
            'Semester',
            'Rombel',
            'Tingkat',
            'Ruangan',
            'Status Penempatan',
            'Keterangan',
        ];
    }

    /**
     * Mengubah setiap data riwayat kelas menjadi baris Excel.
     *
     * @param mixed $history
     *
     * @return array<int, mixed>
     */
    public function map($history): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $history->student?->person?->full_name ?? '-',
            $history->student?->student_number ?? '-',
            $history->student?->nisn ?? '-',
            $history->student?->registration_number ?? '-',
            $history->academicYear?->name ?? $this->classGroup->academicYear?->name ?? '-',
            $history->semester?->name ?? $this->semester?->name ?? '-',
            $this->classGroup->name,
            $this->classGroup->gradeLevel?->name ?? '-',
            $this->classGroup->room?->name ?? '-',
            $history->status,
            $history->notes ?? '-',
        ];
    }

    /**
     * Nama sheet Excel.
     */
    public function title(): string
    {
        return 'Daftar Siswa';
    }

    /**
     * Styling sederhana agar header tebal.
     *
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }
}

Tahap 12.17D: Buat Controller Export Excel
Jalankan:
php artisan make:controller Admin/ClassGroupStudentExportController
Buka:
nano app/Http/Controllers/Admin/ClassGroupStudentExportController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClassGroupStudentsExport;
use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use App\Models\Semester;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClassGroupStudentExportController extends Controller
{
    /**
     * Export daftar siswa per rombongan belajar ke Excel.
     */
    public function __invoke(
        Request $request,
        ClassGroup $classGroup
    ): BinaryFileResponse {
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

        $filename = 'daftar-siswa-'
            .Str::slug($classGroup->code)
            .'.xlsx';

        return Excel::download(
            new ClassGroupStudentsExport(
                $classGroup,
                $semester,
                $histories
            ),
            $filename
        );
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

Tahap 12.17E: Tambahkan Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\ClassGroupStudentExportController;
Di dalam group admin, tambahkan setelah route cetak PDF class group:
Route::get('/class-groups/{classGroup}/students/excel', ClassGroupStudentExportController::class)
    ->middleware('permission:class_groups.export_students')
    ->name('class-groups.students.excel');
Target route:
GET admin/class-groups/{classGroup}/students/excel

Tahap 12.17F: Tambahkan Tombol Export Excel
Buka:
nano resources/views/admin/class-groups/index.blade.php
Cari bagian aksi yang sekarang berisi:
@can('permission', 'class_groups.print_students')
    <a
        href="{{ route('admin.class-groups.students.pdf', $classGroup) }}"
        target="_blank"
        class="text-sm font-medium text-blue-700 hover:text-blue-900"
    >
        Cetak Siswa
    </a>
@endcan
Tambahkan setelahnya:
@can('permission', 'class_groups.export_students')
    <a
        href="{{ route('admin.class-groups.students.excel', $classGroup) }}"
        class="text-sm font-medium text-emerald-700 hover:text-emerald-900"
    >
        Export Excel
    </a>
@endcan
Aksi rombongan belajar sekarang menjadi:
Edit
Cetak Siswa
Export Excel

Tahap 12.17G: Test Export Excel
Buat test:
php artisan make:test Admin/ClassGroupStudentExportTest
Buka:
nano tests/Feature/Admin/ClassGroupStudentExportTest.php
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

class ClassGroupStudentExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_students_by_class_group_excel(): void
    {
        $user = User::factory()->create();

        $this->grantPermissionToUser($user, 'class_groups.export_students');

        [$classGroup] = $this->createClassGroupWithStudent();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.class-groups.students.excel', $classGroup));

        $response->assertStatus(200);

        $this->assertStringContainsString(
            'spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );

        $this->assertStringContainsString(
            '.xlsx',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_user_without_permission_cannot_export_students_by_class_group_excel(): void
    {
        $user = User::factory()->create();

        [$classGroup] = $this->createClassGroupWithStudent();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.class-groups.students.excel', $classGroup));

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
                'name' => 'test_class_group_export_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Class Group Export Role',
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

Tahap 12.17H: Jalankan Pemeriksaan
Jalankan:
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=class-groups
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route tambahan:
GET|HEAD  admin/class-groups/{classGroup}/students/excel
Target test:
115+ tests passed
build berhasil
Jika muncul error:
Class "Maatwebsite\Excel\Facades\Excel" not found
jalankan:
composer dump-autoload
php artisan optimize:clear
Jika muncul error terkait extension zip, aktifkan extension ZIP pada PHP yang dipakai terminal.

Tahap 12.17I: Uji Manual
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
Export Excel
Pastikan file .xlsx terunduh dan berisi kolom:
No
Nama Siswa
NIS
NISN
Nomor Registrasi
Tahun Ajaran
Semester
Rombel
Tingkat
Ruangan
Status Penempatan
Keterangan

Tahap 12.17J: Commit
Jika semua aman:
git status
Lalu:
git add composer.json \
        composer.lock \
        app/Exports/ClassGroupStudentsExport.php \
        app/Http/Controllers/Admin/ClassGroupStudentExportController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/class-groups/index.blade.php \
        tests/Feature/Admin/ClassGroupStudentExportTest.php
Commit:
git commit -m "feat: add class group student excel export"
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
Setelah berhasil, kita lanjut ke Tahap 12.18: Filter Rombongan Belajar Berdasarkan Tahun Ajaran.

















