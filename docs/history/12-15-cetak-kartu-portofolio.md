# Tahap 12.15 — Cetak Kartu Portofolio Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 36434–36938.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.15: Cetak Kartu Portofolio Siswa
Pada tahap ini kita membuat fitur cetak kartu portofolio siswa dalam PDF.
Kartu ini berisi:
Nama siswa
NIS
NISN
Kelas aktif
Tahun ajaran
Semester
QR Code portofolio
URL portofolio
Tujuannya agar madrasah bisa mencetak kartu ringkas untuk siswa. Saat QR dipindai, pengguna diarahkan ke halaman portofolio siswa. Namun halaman tetap aman karena masih membutuhkan login dan permission.

Tahap 12.15A: Install DomPDF
Jalankan:
composer require barryvdh/laravel-dompdf
Lalu bersihkan cache:
php artisan optimize:clear
composer dump-autoload
Cek package:
composer show barryvdh/laravel-dompdf

Tahap 12.15B: Tambahkan Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'student_portfolios.print',
    'module' => 'student_portfolios',
    'action' => 'print',
    'display_name' => 'Mencetak Kartu Portofolio Siswa',
    'description' => 'Mencetak kartu portofolio digital siswa dalam format PDF.',
],
Permission ini dipisahkan dari student_portfolios.view karena melihat portofolio dan mencetak dokumen adalah dua hak akses berbeda.

Tahap 12.15C: Update Controller Portofolio
Buka:
nano app/Http/Controllers/Admin/StudentPortfolioController.php
Ganti seluruh isi file menjadi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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

    /**
     * Mencetak kartu portofolio siswa dalam PDF.
     */
    public function card(Student $student): Response
    {
        $student->load([
            'person',
            'admissionAcademicYear',
            'currentClassHistory.academicYear',
            'currentClassHistory.semester',
            'currentClassHistory.classGroup.gradeLevel',
            'currentClassHistory.classGroup.room',
        ]);

        $portfolioUrl = route('admin.students.portfolio.show', $student);

        $qrCodeSvg = (string) QrCode::format('svg')
            ->size(180)
            ->margin(1)
            ->generate($portfolioUrl);

        $qrCodeDataUri = 'data:image/svg+xml;base64,'.base64_encode($qrCodeSvg);

        $filename = 'kartu-portofolio-'.Str::slug(
            $student->student_number ?: $student->person?->full_name ?: 'siswa-'.$student->id
        ).'.pdf';

        $pdf = Pdf::loadView('admin.students.portfolio.card-pdf', [
            'student' => $student,
            'portfolioUrl' => $portfolioUrl,
            'qrCodeDataUri' => $qrCodeDataUri,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream($filename);
    }
}

Tahap 12.15D: Tambahkan Route
Buka:
nano routes/web.php
Pastikan import ini sudah ada:
use App\Http\Controllers\Admin\StudentPortfolioController;
Tambahkan route ini setelah route portofolio:
Route::get('/students/{student}/portfolio/card', [StudentPortfolioController::class, 'card'])
    ->middleware('permission:student_portfolios.print')
    ->name('students.portfolio.card');
Target route:
GET admin/students/{student}/portfolio/card

Tahap 12.15E: Tambahkan Tombol Cetak di Halaman Portofolio
Buka:
nano resources/views/admin/students/portfolio/show.blade.php
Cari bagian header:
<a
    href="{{ route('admin.students.index') }}"
    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
>
    Kembali
</a>
Ganti menjadi:
<div class="flex flex-wrap gap-2">
    @can('permission', 'student_portfolios.print')
        <a
            href="{{ route('admin.students.portfolio.card', $student) }}"
            target="_blank"
            class="rounded-md bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800"
        >
            Cetak Kartu
        </a>
    @endcan

    <a
        href="{{ route('admin.students.index') }}"
        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
    >
        Kembali
    </a>
</div>

Tahap 12.15F: Buat View PDF Kartu
Buat file:
nano resources/views/admin/students/portfolio/card-pdf.blade.php
Isi:
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Portofolio Siswa</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 24px;
        }

        .card {
            width: 100%;
            border: 2px solid #166534;
            border-radius: 12px;
            padding: 22px;
        }

        .header {
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 14px;
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
            color: #374151;
        }

        .content {
            width: 100%;
        }

        .left {
            width: 65%;
            vertical-align: top;
        }

        .right {
            width: 35%;
            text-align: center;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .value {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
        }

        .qr {
            width: 150px;
            height: 150px;
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin-bottom: 8px;
        }

        .url {
            font-size: 9px;
            color: #6b7280;
            word-break: break-all;
        }

        .footer {
            margin-top: 20px;
            border-top: 1px solid #d1d5db;
            padding-top: 12px;
            font-size: 10px;
            color: #6b7280;
        }

        .note {
            margin-top: 10px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="school">
                SIM Madrasah
            </div>

            <div class="title">
                Kartu Portofolio Digital Siswa
            </div>
        </div>

        <table class="content">
            <tr>
                <td class="left">
                    <div class="label">Nama Lengkap</div>
                    <div class="value">
                        {{ $student->person?->full_name ?? '-' }}
                    </div>

                    <div class="label">NIS</div>
                    <div class="value">
                        {{ $student->student_number ?? '-' }}
                    </div>

                    <div class="label">NISN</div>
                    <div class="value">
                        {{ $student->nisn ?? '-' }}
                    </div>

                    <div class="label">Nomor Registrasi</div>
                    <div class="value">
                        {{ $student->registration_number ?? '-' }}
                    </div>

                    <div class="label">Tahun Ajaran Masuk</div>
                    <div class="value">
                        {{ $student->admissionAcademicYear?->name ?? '-' }}
                    </div>

                    <div class="label">Kelas Saat Ini</div>
                    <div class="value">
                        {{ $student->currentClassHistory?->classGroup?->name ?? 'Belum ada kelas aktif' }}
                    </div>

                    <div class="label">Semester</div>
                    <div class="value">
                        {{ $student->currentClassHistory?->semester?->name ?? '-' }}
                    </div>

                    <div>
                        <span class="badge">
                            Status: {{ $student->status }}
                        </span>
                    </div>
                </td>

                <td class="right">
                    <img
                        src="{{ $qrCodeDataUri }}"
                        class="qr"
                        alt="QR Code Portofolio"
                    >

                    <div class="label">
                        Scan untuk membuka portofolio
                    </div>

                    <div class="url">
                        {{ $portfolioUrl }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            Kartu ini digunakan untuk mengakses portofolio digital siswa.
            Halaman portofolio tetap membutuhkan login dan hak akses yang sesuai.
        </div>

        <div class="note">
            Dicetak otomatis dari Sistem Informasi Manajemen Madrasah.
        </div>
    </div>
</body>
</html>
Kenapa CSS-nya inline dan sederhana?
Karena DomPDF lebih stabil dengan CSS sederhana. Tailwind tidak dipakai langsung pada PDF agar hasil cetak lebih konsisten.

Tahap 12.15G: Update Test Portofolio
Buka:
nano tests/Feature/Admin/StudentPortfolioTest.php
Tambahkan method berikut:
public function test_user_can_print_student_portfolio_card_pdf(): void
{
    $user = User::factory()->create();

    $this->grantPermissionToUser($user, 'student_portfolios.print');

    [$student] = $this->createPortfolioData();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.students.portfolio.card', $student));

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
Tambahkan juga test permission:
public function test_user_without_permission_cannot_print_student_portfolio_card_pdf(): void
{
    $user = User::factory()->create();

    [$student] = $this->createPortfolioData();

    $response = $this
        ->actingAs($user)
        ->get(route('admin.students.portfolio.card', $student));

    $response->assertForbidden();
}

Tahap 12.15H: Jalankan Pemeriksaan
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
GET|HEAD  admin/students/{student}/portfolio/card
Target test:
111+ tests passed
build berhasil
Jika muncul error:
Class "Barryvdh\DomPDF\Facade\Pdf" not found
Jalankan:
composer dump-autoload
php artisan optimize:clear
Jika PDF berhasil dibuat tetapi QR tidak muncul, pastikan QR Code masih menghasilkan SVG:
php artisan tinker
Lalu:
QrCode::format('svg')->size(100)->generate('test');

Tahap 12.15I: Uji Manual
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Klik:
Portofolio
Lalu klik:
Cetak Kartu
Pastikan browser membuka PDF yang berisi:
Kartu Portofolio Digital Siswa
Nama siswa
NIS
NISN
Kelas saat ini
QR Code
URL portofolio
Scan QR dari PDF. Hasil scan harus menuju halaman portofolio siswa.

Tahap 12.15J: Commit
Jika semua aman:
git status
Lalu:
git add composer.json \
        composer.lock \
        app/Http/Controllers/Admin/StudentPortfolioController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/portfolio/show.blade.php \
        resources/views/admin/students/portfolio/card-pdf.blade.php \
        tests/Feature/Admin/StudentPortfolioTest.php
Commit:
git commit -m "feat: add student portfolio card pdf"
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
Setelah berhasil, kita lanjut ke Tahap 12.16: Cetak Data Siswa Per Kelas.
















