# Tahap 12.18 — Filter Rombongan Belajar Berdasarkan Tahun Ajaran

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 38167–38451.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.18: Filter Rombongan Belajar Berdasarkan Tahun Ajaran
Pada tahap ini kita menambahkan filter pada halaman Rombongan Belajar.
Tujuannya agar data rombel tidak menumpuk ketika sistem sudah memiliki banyak tahun ajaran.
Contoh:
2026/2027
- VII A
- VIII A
- IX A

2027/2028
- VII A
- VIII A
- IX A
Tanpa filter, semua rombel dari semua tahun akan tampil sekaligus. Itu akan membingungkan admin. Maka kita tambahkan filter berdasarkan academic_year_id.

Tahap 12.18A: Update Controller Rombongan Belajar
Buka:
nano app/Http/Controllers/Admin/ClassGroupController.php
Pastikan import Request dan AcademicYear sudah ada:
use App\Models\AcademicYear;
use Illuminate\Http\Request;
Lalu ubah method index() menjadi:
public function index(Request $request): View
{
    $selectedAcademicYearId = $request->integer('academic_year_id') ?: null;

    $academicYears = AcademicYear::query()
        ->orderByDesc('start_date')
        ->get();

    $classGroups = ClassGroup::query()
        ->with([
            'academicYear',
            'gradeLevel',
            'room',
            'homeroomTeacher',
        ])
        ->when(
            $selectedAcademicYearId,
            fn ($query) => $query->where(
                'academic_year_id',
                $selectedAcademicYearId
            )
        )
        ->orderByDesc('academic_year_id')
        ->orderBy('name')
        ->paginate(15)
        ->withQueryString();

    return view('admin.class-groups.index', [
        'classGroups' => $classGroups,
        'academicYears' => $academicYears,
        'selectedAcademicYearId' => $selectedAcademicYearId,
    ]);
}
Bagian pentingnya:
->when($selectedAcademicYearId, ...)
Artinya filter hanya aktif jika pengguna memilih tahun ajaran.
Bagian ini juga penting:
->withQueryString()
Agar saat pindah halaman pagination, filter tetap ikut terbawa.

Tahap 12.18B: Update View Index Rombongan Belajar
Buka:
nano resources/views/admin/class-groups/index.blade.php
Cari bagian ini:
<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6 overflow-x-auto">
Tambahkan form filter tepat sebelum tabel.
Jadi bagian awal card menjadi:
<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6">
        <form
            method="GET"
            action="{{ route('admin.class-groups.index') }}"
            class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4"
        >
            <div class="md:col-span-2">
                <x-input-label for="academic_year_id" value="Filter Tahun Ajaran" />

                <select
                    id="academic_year_id"
                    name="academic_year_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                >
                    <option value="">Semua Tahun Ajaran</option>

                    @foreach ($academicYears as $academicYear)
                        <option
                            value="{{ $academicYear->id }}"
                            @selected((string) $selectedAcademicYearId === (string) $academicYear->id)
                        >
                            {{ $academicYear->name }}
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
                    href="{{ route('admin.class-groups.index') }}"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Reset
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
Lalu cari penutup setelah pagination. Biasanya bagian akhir seperti ini:
                    <div class="mt-6">
                        {{ $classGroups->links() }}
                    </div>
                </div>
            </div>
Sesuaikan agar div baru tadi tertutup dengan benar:
                    <div class="mt-6">
                        {{ $classGroups->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
Intinya, struktur card menjadi:
card
  p-6
    form filter
    overflow-x-auto
      table
      pagination

Tahap 12.18C: Tambahkan Test Filter Rombongan Belajar
Buka:
nano tests/Feature/Admin/ClassGroupTest.php
Tambahkan method berikut di dalam class ClassGroupTest:
public function test_user_can_filter_class_groups_by_academic_year(): void
{
    $user = User::factory()->create();

    $this->grantPermissionToUser($user, 'class_groups.view');

    $academicYear2026 = AcademicYear::create([
        'code' => '2026-2027',
        'name' => '2026/2027',
        'start_date' => '2026-07-01',
        'end_date' => '2027-06-30',
        'status' => 'active',
        'is_active' => true,
        'is_locked' => false,
    ]);

    $academicYear2027 = AcademicYear::create([
        'code' => '2027-2028',
        'name' => '2027/2028',
        'start_date' => '2027-07-01',
        'end_date' => '2028-06-30',
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

    ClassGroup::create([
        'academic_year_id' => $academicYear2026->id,
        'grade_level_id' => $gradeLevel->id,
        'room_id' => $room->id,
        'code' => 'VII-A-2026',
        'name' => 'Kelas VII A 2026',
        'parallel_name' => 'A',
        'capacity' => 32,
        'status' => 'active',
        'is_active' => true,
    ]);

    ClassGroup::create([
        'academic_year_id' => $academicYear2027->id,
        'grade_level_id' => $gradeLevel->id,
        'room_id' => $room->id,
        'code' => 'VII-A-2027',
        'name' => 'Kelas VII A 2027',
        'parallel_name' => 'A',
        'capacity' => 32,
        'status' => 'active',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/admin/class-groups?academic_year_id='.$academicYear2026->id);

    $response
        ->assertStatus(200)
        ->assertSee('Kelas VII A 2026')
        ->assertDontSee('Kelas VII A 2027')
        ->assertSee('2026/2027');
}
Test ini memastikan filter benar-benar membatasi data berdasarkan tahun ajaran.

Tahap 12.18D: Jalankan Pemeriksaan
Jalankan:
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
116+ tests passed
build berhasil

Tahap 12.18E: Uji Manual
Jalankan seeder jika perlu:
php artisan db:seed --class=AcademicYearSeeder
php artisan db:seed --class=GradeLevelSeeder
php artisan db:seed --class=RoomSeeder
php artisan db:seed --class=ClassGroupSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/class-groups
Uji:
1. Pilih Tahun Ajaran 2026/2027.
2. Klik Terapkan.
3. Pastikan hanya rombel tahun ajaran itu yang tampil.
4. Klik Reset.
5. Pastikan semua rombel tampil kembali.

Tahap 12.18F: Commit
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/Admin/ClassGroupController.php \
        resources/views/admin/class-groups/index.blade.php \
        tests/Feature/Admin/ClassGroupTest.php
Commit:
git commit -m "feat: add academic year filter for class groups"
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
Setelah berhasil, kita lanjut ke Tahap 12.19: Filter Data Siswa Berdasarkan Rombongan Belajar.

















