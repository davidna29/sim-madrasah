# Tahap 12.19 — Filter Data Siswa Berdasarkan Rombongan Belajar

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 38452–38823.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.19: Filter Data Siswa Berdasarkan Rombongan Belajar
Tujuan tahap ini adalah menambahkan filter pada halaman Data Siswa agar admin bisa melihat siswa berdasarkan kelas aktif saat ini.
Prinsipnya tetap:
students tidak menyimpan kelas langsung
kelas siswa dibaca dari student_class_histories
filter menggunakan class_group_id
hanya data is_current = true yang dianggap kelas aktif

Tahap 12.19A: Update StudentController
Buka:
nano app/Http/Controllers/Admin/StudentController.php
Tambahkan import:
use App\Models\ClassGroup;
Lalu ubah method index() menjadi:
public function index(Request $request): View
{
    $selectedClassGroupId = $request->integer('class_group_id') ?: null;

    $classGroups = ClassGroup::query()
        ->with([
            'academicYear',
            'gradeLevel',
        ])
        ->where('is_active', true)
        ->orderByDesc('academic_year_id')
        ->orderBy('name')
        ->get();

    $students = Student::query()
        ->with([
            'person.user',
            'admissionAcademicYear',
            'currentClassHistory.academicYear',
            'currentClassHistory.semester',
            'currentClassHistory.classGroup.gradeLevel',
            'currentClassHistory.classGroup.room',
        ])
        ->when(
            $selectedClassGroupId,
            fn ($query) => $query->whereHas(
                'classHistories',
                fn ($historyQuery) => $historyQuery
                    ->where('class_group_id', $selectedClassGroupId)
                    ->where('is_current', true)
            )
        )
        ->orderByDesc('is_active')
        ->orderBy('student_number')
        ->paginate(15)
        ->withQueryString();

    return view('admin.students.index', [
        'students' => $students,
        'classGroups' => $classGroups,
        'selectedClassGroupId' => $selectedClassGroupId,
    ]);
}
Kenapa memakai is_current = true?
Karena halaman Data Siswa menampilkan posisi siswa saat ini. Riwayat lama tetap ada, tetapi tidak dipakai untuk filter utama ini.

Tahap 12.19B: Update View Data Siswa
Buka:
nano resources/views/admin/students/index.blade.php
Cari bagian:
<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6 overflow-x-auto">
Ganti awal card menjadi:
<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6">
        <form
            method="GET"
            action="{{ route('admin.students.index') }}"
            class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4"
        >
            <div class="md:col-span-2">
                <x-input-label for="class_group_id" value="Filter Rombongan Belajar" />

                <select
                    id="class_group_id"
                    name="class_group_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >
                    <option value="">Semua Rombongan Belajar</option>

                    @foreach ($classGroups as $classGroup)
                        <option
                            value="{{ $classGroup->id }}"
                            @selected((string) $selectedClassGroupId === (string) $classGroup->id)
                        >
                            {{ $classGroup->academicYear?->name }}
                            -
                            {{ $classGroup->name }}
                            -
                            {{ $classGroup->gradeLevel?->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
                >
                    Terapkan
                </button>

                <a
                    href="{{ route('admin.students.index') }}"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
Lalu tambahkan kolom Kelas Saat Ini.
Cari header:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Masuk</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
Ubah menjadi:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Tahun Masuk</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kelas Saat Ini</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
Cari kolom Tahun Masuk:
<td class="px-4 py-3">
    {{ $student->admissionAcademicYear?->name ?? '-' }}
</td>
Tambahkan setelahnya:
<td class="px-4 py-3">
    @if ($student->currentClassHistory)
        <div class="font-medium text-gray-900">
            {{ $student->currentClassHistory->classGroup?->name ?? '-' }}
        </div>

        <div class="text-xs text-gray-500">
            {{ $student->currentClassHistory->semester?->name ?? '-' }}
        </div>
    @else
        <span class="text-gray-500">
            Belum ada kelas aktif
        </span>
    @endif
</td>
Karena kolom bertambah, ubah empty state:
<td colspan="8" class="px-4 py-6 text-center text-gray-500">
menjadi:
<td colspan="9" class="px-4 py-6 text-center text-gray-500">
Pastikan struktur penutup card menjadi seperti ini:
                    <div class="mt-6">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

Tahap 12.19C: Tambahkan Test Filter Siswa
Buka:
nano tests/Feature/Admin/StudentTest.php
Tambahkan import berikut di bagian atas:
use App\Models\ClassGroup;
use App\Models\GradeLevel;
use App\Models\Room;
use App\Models\Semester;
use App\Models\StudentClassHistory;
Tambahkan method test ini di dalam class StudentTest:
public function test_user_can_filter_students_by_current_class_group(): void
{
    $user = User::factory()->create();

    $this->grantPermissionToUser($user, 'students.view');

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

    $roomA = Room::create([
        'code' => 'R-VII-A',
        'name' => 'Ruang VII A',
        'room_type' => 'classroom',
        'capacity' => 32,
        'is_active' => true,
    ]);

    $roomB = Room::create([
        'code' => 'R-VII-B',
        'name' => 'Ruang VII B',
        'room_type' => 'classroom',
        'capacity' => 32,
        'is_active' => true,
    ]);

    $classGroupA = ClassGroup::create([
        'academic_year_id' => $academicYear->id,
        'grade_level_id' => $gradeLevel->id,
        'room_id' => $roomA->id,
        'code' => 'VII-A',
        'name' => 'Kelas VII A',
        'parallel_name' => 'A',
        'capacity' => 32,
        'status' => 'active',
        'is_active' => true,
    ]);

    $classGroupB = ClassGroup::create([
        'academic_year_id' => $academicYear->id,
        'grade_level_id' => $gradeLevel->id,
        'room_id' => $roomB->id,
        'code' => 'VII-B',
        'name' => 'Kelas VII B',
        'parallel_name' => 'B',
        'capacity' => 32,
        'status' => 'active',
        'is_active' => true,
    ]);

    $personA = Person::create([
        'full_name' => 'Ahmad Siswa',
        'email' => 'ahmad@test.local',
    ]);

    $studentA = Student::create([
        'person_id' => $personA->id,
        'admission_academic_year_id' => $academicYear->id,
        'student_number' => 'SIS-001',
        'nisn' => '1000000001',
        'registration_number' => 'REG-001',
        'admission_date' => '2026-07-01',
        'status' => 'active',
        'is_active' => true,
    ]);

    $personB = Person::create([
        'full_name' => 'Siti Siswa',
        'email' => 'siti@test.local',
    ]);

    $studentB = Student::create([
        'person_id' => $personB->id,
        'admission_academic_year_id' => $academicYear->id,
        'student_number' => 'SIS-002',
        'nisn' => '1000000002',
        'registration_number' => 'REG-002',
        'admission_date' => '2026-07-01',
        'status' => 'active',
        'is_active' => true,
    ]);

    StudentClassHistory::create([
        'student_id' => $studentA->id,
        'academic_year_id' => $academicYear->id,
        'semester_id' => $semester->id,
        'class_group_id' => $classGroupA->id,
        'status' => 'active',
        'start_date' => '2026-07-01',
        'is_current' => true,
    ]);

    StudentClassHistory::create([
        'student_id' => $studentB->id,
        'academic_year_id' => $academicYear->id,
        'semester_id' => $semester->id,
        'class_group_id' => $classGroupB->id,
        'status' => 'active',
        'start_date' => '2026-07-01',
        'is_current' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/students?class_group_id='.$classGroupA->id);

    $response
        ->assertStatus(200)
        ->assertSee('Ahmad Siswa')
        ->assertSee('Kelas VII A')
        ->assertDontSee('Siti Siswa');
}

Tahap 12.19D: Jalankan Pemeriksaan
Jalankan:
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
117+ tests passed
build berhasil

Tahap 12.19E: Uji Manual
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
open http://sim-madrasah.test/admin/students
Uji:
1. Pilih rombongan belajar.
2. Klik Terapkan.
3. Pastikan hanya siswa dari kelas aktif tersebut yang tampil.
4. Klik Reset.
5. Pastikan semua siswa tampil kembali.

Tahap 12.19F: Commit
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/Admin/StudentController.php \
        resources/views/admin/students/index.blade.php \
        tests/Feature/Admin/StudentTest.php
Commit:
git commit -m "feat: add class group filter for students"
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
Setelah berhasil, kita lanjut ke Tahap 12.20: Pencarian Data Siswa Berdasarkan Nama, NIS, dan NISN.















