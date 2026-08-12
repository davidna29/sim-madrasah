# Tahap 12.14 — QR Code Portofolio Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 36204–36433.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.14: QR Code Portofolio Siswa
Pada tahap ini kita menambahkan QR Code pada halaman portofolio siswa.
QR Code ini akan berisi URL halaman portofolio siswa:
/admin/students/{student}/portfolio
Kita buat QR Code tetap mengarah ke halaman yang membutuhkan login dan permission. Ini lebih aman daripada membuat halaman publik tanpa proteksi.
Simple QrCode memang dibuat untuk Laravel. Paket simplesoftwareio/simple-qrcode membutuhkan PHP >=7.2|^8.0 dan ext-gd, sedangkan ext-imagick hanya disarankan untuk membuat QR format PNG. Karena itu, untuk tahap ini kita pakai SVG, bukan PNG. SVG lebih ringan dan tidak perlu menyimpan file gambar QR ke storage. (Packagist)

Tahap 12.14A: Cek Extension GD
Jalankan:
php -m | grep -i gd
Target minimal muncul:
gd
Jika tidak muncul, jangan lanjut dulu. Aktifkan extension GD di PHP Herd atau environment PHP yang sedang dipakai.

Tahap 12.14B: Install Simple QR Code
Jalankan:
composer require simplesoftwareio/simple-qrcode
Setelah selesai, jalankan:
composer show simplesoftwareio/simple-qrcode
Pastikan package terpasang.
Lalu bersihkan cache:
php artisan optimize:clear

Tahap 12.14C: Update Controller Portofolio Siswa
Buka:
nano app/Http/Controllers/Admin/StudentPortfolioController.php
Tambahkan import ini di bagian atas:
use SimpleSoftwareIO\QrCode\Facades\QrCode;
Lalu ubah isi controller menjadi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Contracts\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

        $portfolioUrl = route('admin.students.portfolio.show', $student);

        $qrCodeSvg = QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($portfolioUrl);

        return view('admin.students.portfolio.show', [
            'student' => $student,
            'classHistories' => $classHistories,
            'guardians' => $guardians,
            'portfolioUrl' => $portfolioUrl,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }
}
Kenapa QR dibuat di controller?
Karena controller menyiapkan data. View cukup menampilkan. Ini menjaga Blade tetap rapi.

Tahap 12.14D: Update View Portofolio
Buka:
nano resources/views/admin/students/portfolio/show.blade.php
Cari bagian kartu:
<div class="bg-white shadow-sm sm:rounded-lg border border-gray-100">
    <div class="p-6">
        <h3 class="font-semibold text-gray-900">
            Status Ringkas
        </h3>
Di dalam kartu Status Ringkas, tambahkan blok QR Code setelah bagian Kelas Saat Ini.
Cari penutup blok ini:
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
Tambahkan tepat setelahnya:
                            <div class="border-t border-gray-100 pt-4">
                                <div class="text-gray-500">
                                    QR Code Portofolio
                                </div>

                                <div class="mt-3 inline-block rounded-lg border border-gray-100 bg-white p-3">
                                    {!! $qrCodeSvg !!}
                                </div>

                                <div class="mt-3 break-all text-xs text-gray-500">
                                    {{ $portfolioUrl }}
                                </div>
                            </div>
Hasilnya, kartu kanan akan menampilkan:
Status Siswa
Status Akun
Kelas Saat Ini
QR Code Portofolio

Tahap 12.14E: Test QR Code Portofolio
Buka test yang sudah ada:
nano tests/Feature/Admin/StudentPortfolioTest.php
Tambahkan method berikut setelah test_user_can_view_student_portfolio():
public function test_student_portfolio_shows_qr_code(): void
{
    $user = User::factory()->create();

    $this->grantPermissionToUser($user, 'student_portfolios.view');

    [$student] = $this->createPortfolioData();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.students.portfolio.show', $student));

    $response
        ->assertStatus(200)
        ->assertSee('QR Code Portofolio')
        ->assertSee(route('admin.students.portfolio.show', $student))
        ->assertSee('<svg', false);
}
Kenapa assertSee('<svg', false)?
Karena QR Code yang kita tampilkan berbentuk SVG. Parameter false dipakai agar Laravel tidak melakukan escaping HTML saat mengecek isi response.

Tahap 12.14F: Jalankan Pemeriksaan
Jalankan:
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target:
109+ tests passed
build berhasil
Jika muncul error seperti:
Class "SimpleSoftwareIO\QrCode\Facades\QrCode" not found
jalankan:
composer dump-autoload
php artisan optimize:clear
Jika muncul error tentang GD:
ext-gd missing
berarti extension GD belum aktif pada PHP yang dipakai terminal.

Tahap 12.14G: Uji Manual
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Klik:
Portofolio
Pastikan muncul:
QR Code Portofolio
gambar QR dalam bentuk SVG
URL portofolio siswa
Coba scan QR dari layar. Hasil scan harus menuju halaman portofolio siswa. Jika belum login, sistem harus meminta login terlebih dahulu.

Tahap 12.14H: Commit
Jika semua aman:
git status
Lalu:
git add composer.json \
        composer.lock \
        app/Http/Controllers/Admin/StudentPortfolioController.php \
        resources/views/admin/students/portfolio/show.blade.php \
        tests/Feature/Admin/StudentPortfolioTest.php
Commit:
git commit -m "feat: add student portfolio qr code"
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
Setelah berhasil, kita lanjut ke Tahap 12.15: Cetak Kartu Portofolio Siswa.













