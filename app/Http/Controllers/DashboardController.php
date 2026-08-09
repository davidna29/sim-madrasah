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
