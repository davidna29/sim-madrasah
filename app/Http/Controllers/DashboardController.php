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
