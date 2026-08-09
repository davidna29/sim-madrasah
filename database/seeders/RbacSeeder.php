<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    /**
     * Membuat role, permission dasar, dan memberikan
     * role super_admin kepada akun superadmin lokal.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $roles = [
                [
                    'name' => 'super_admin',
                    'display_name' => 'Super Admin',
                    'description' => 'Mengelola seluruh sistem SIM Madrasah.',
                    'is_system' => true,
                ],
                [
                    'name' => 'kepala_madrasah',
                    'display_name' => 'Kepala Madrasah',
                    'description' => 'Memantau laporan, persetujuan, PKKM, dan akreditasi.',
                    'is_system' => true,
                ],
                [
                    'name' => 'wakamad_kurikulum',
                    'display_name' => 'Wakamad Kurikulum',
                    'description' => 'Mengelola akademik, jadwal, kurikulum, nilai, dan rapor.',
                    'is_system' => true,
                ],
                [
                    'name' => 'wakamad_kesiswaan',
                    'display_name' => 'Wakamad Kesiswaan',
                    'description' => 'Mengelola kesiswaan, prestasi, pelanggaran, dan pembiasaan.',
                    'is_system' => true,
                ],
                [
                    'name' => 'wakamad_sarpras',
                    'display_name' => 'Wakamad Sarpras',
                    'description' => 'Mengelola sarana, prasarana, inventaris, ruangan, dan laboratorium.',
                    'is_system' => true,
                ],
                [
                    'name' => 'wakamad_humas',
                    'display_name' => 'Wakamad Humas',
                    'description' => 'Mengelola publikasi, berita, agenda, alumni, dan kerja sama.',
                    'is_system' => true,
                ],
                [
                    'name' => 'tata_usaha',
                    'display_name' => 'Tata Usaha',
                    'description' => 'Mengelola administrasi siswa, pegawai, surat, dan arsip.',
                    'is_system' => true,
                ],
                [
                    'name' => 'bendahara',
                    'display_name' => 'Bendahara',
                    'description' => 'Mengelola tagihan, pembayaran, kwitansi, dan laporan keuangan.',
                    'is_system' => true,
                ],
                [
                    'name' => 'wali_kelas',
                    'display_name' => 'Wali Kelas',
                    'description' => 'Memantau siswa dalam kelas, rapor, absensi, dan perkembangan siswa.',
                    'is_system' => true,
                ],
                [
                    'name' => 'guru_mata_pelajaran',
                    'display_name' => 'Guru Mata Pelajaran',
                    'description' => 'Mengelola pembelajaran, jurnal mengajar, absensi, dan nilai.',
                    'is_system' => true,
                ],
                [
                    'name' => 'guru_bk',
                    'display_name' => 'Guru BK',
                    'description' => 'Mengelola konseling, tindak lanjut, dan data kedisiplinan tertentu.',
                    'is_system' => true,
                ],
                [
                    'name' => 'petugas_perpustakaan',
                    'display_name' => 'Petugas Perpustakaan',
                    'description' => 'Mengelola buku, anggota, peminjaman, pengembalian, dan ebook.',
                    'is_system' => true,
                ],
                [
                    'name' => 'petugas_laboratorium',
                    'display_name' => 'Petugas Laboratorium',
                    'description' => 'Mengelola alat, bahan, peminjaman, kerusakan, dan pemeliharaan laboratorium.',
                    'is_system' => true,
                ],
                [
                    'name' => 'editor_berita',
                    'display_name' => 'Editor Berita',
                    'description' => 'Mengelola naskah, gambar, SEO, review, dan publikasi berita.',
                    'is_system' => true,
                ],
                [
                    'name' => 'orang_tua',
                    'display_name' => 'Orang Tua',
                    'description' => 'Mengakses portal orang tua dan data anak yang terhubung.',
                    'is_system' => true,
                ],
                [
                    'name' => 'siswa',
                    'display_name' => 'Siswa',
                    'description' => 'Mengakses portal siswa dan portofolio pribadi.',
                    'is_system' => true,
                ],
            ];

            foreach ($roles as $role) {
                Role::query()->updateOrCreate(
                    ['name' => $role['name']],
                    [
                        'display_name' => $role['display_name'],
                        'description' => $role['description'],
                        'is_system' => $role['is_system'],
                        'is_active' => true,
                    ]
                );
            }

            $permissions = [
                [
                    'name' => 'dashboard.view',
                    'module' => 'dashboard',
                    'action' => 'view',
                    'display_name' => 'Melihat Dashboard',
                ],
                [
                    'name' => 'profile.view',
                    'module' => 'profile',
                    'action' => 'view',
                    'display_name' => 'Melihat Profil',
                ],
                [
                    'name' => 'profile.update',
                    'module' => 'profile',
                    'action' => 'update',
                    'display_name' => 'Mengubah Profil',
                ],
                [
                    'name' => 'users.view',
                    'module' => 'users',
                    'action' => 'view',
                    'display_name' => 'Melihat Pengguna',
                ],
                [
                    'name' => 'users.create',
                    'module' => 'users',
                    'action' => 'create',
                    'display_name' => 'Membuat Pengguna',
                ],
                [
                    'name' => 'users.update',
                    'module' => 'users',
                    'action' => 'update',
                    'display_name' => 'Mengubah Pengguna',
                ],
                [
                    'name' => 'users.deactivate',
                    'module' => 'users',
                    'action' => 'deactivate',
                    'display_name' => 'Menonaktifkan Pengguna',
                ],
                [
                    'name' => 'roles.view',
                    'module' => 'roles',
                    'action' => 'view',
                    'display_name' => 'Melihat Role',
                ],
                [
                    'name' => 'roles.create',
                    'module' => 'roles',
                    'action' => 'create',
                    'display_name' => 'Membuat Role',
                ],
                [
                    'name' => 'roles.update',
                    'module' => 'roles',
                    'action' => 'update',
                    'display_name' => 'Mengubah Role',
                ],
                [
                    'name' => 'roles.assign',
                    'module' => 'roles',
                    'action' => 'assign',
                    'display_name' => 'Memberikan Role ke Pengguna',
                ],
                [
                    'name' => 'permissions.view',
                    'module' => 'permissions',
                    'action' => 'view',
                    'display_name' => 'Melihat Permission',
                ],
                [
                    'name' => 'permissions.create',
                    'module' => 'permissions',
                    'action' => 'create',
                    'display_name' => 'Membuat Permission',
                ],
                [
                    'name' => 'permissions.update',
                    'module' => 'permissions',
                    'action' => 'update',
                    'display_name' => 'Mengubah Permission',
                ],
                [
                    'name' => 'settings.view',
                    'module' => 'settings',
                    'action' => 'view',
                    'display_name' => 'Melihat Pengaturan Sistem',
                ],
                [
                    'name' => 'settings.update',
                    'module' => 'settings',
                    'action' => 'update',
                    'display_name' => 'Mengubah Pengaturan Sistem',
                ],
                [
                    'name' => 'madrasah.view',
                    'module' => 'madrasah',
                    'action' => 'view',
                    'display_name' => 'Melihat Identitas Madrasah',
                    'description' => 'Melihat profil dan identitas dasar madrasah.',
                ],
                [
                    'name' => 'madrasah.update',
                    'module' => 'madrasah',
                    'action' => 'update',
                    'display_name' => 'Mengubah Identitas Madrasah',
                    'description' => 'Mengubah profil dan identitas dasar madrasah.',
                ],
                [
                    'name' => 'activity_logs.view',
                    'module' => 'activity_logs',
                    'action' => 'view',
                    'display_name' => 'Melihat Activity Log',
                ],
                [
                    'name' => 'audit_logs.view',
                    'module' => 'audit_logs',
                    'action' => 'view',
                    'display_name' => 'Melihat Audit Log',
                ],
                [
                    'name' => 'roles.permission.assign',
                    'module' => 'roles',
                    'action' => 'permission_assign',
                    'display_name' => 'Mengatur Permission Role',
                    'description' => 'Memberikan atau mencabut permission dari role.',
                    'is_active' => true,
                ],
            ];

            foreach ($permissions as $permission) {
                Permission::query()->updateOrCreate(
                    ['name' => $permission['name']],
                    [
                        'module' => $permission['module'],
                        'action' => $permission['action'],
                        'display_name' => $permission['display_name'],
                        'description' => $permission['description'] ?? null,
                        'is_active' => true,
                    ]
                );
            }

            $basePermissionNames = [
                'dashboard.view',
                'profile.view',
                'profile.update',
            ];

            $basePermissionIds = Permission::query()
                ->whereIn('name', $basePermissionNames)
                ->pluck('id')
                ->all();

            Role::query()
                ->where('is_active', true)
                ->get()
                ->each(function (Role $role) use ($basePermissionIds): void {
                    $role->permissions()->syncWithoutDetaching(
                        $basePermissionIds
                    );
                });

            $superAdminRole = Role::query()
                ->where('name', 'super_admin')
                ->firstOrFail();

            $allPermissionIds = Permission::query()
                ->pluck('id')
                ->all();

            $superAdminRole->permissions()->sync($allPermissionIds);

            $superAdminUser = User::query()
                ->where('username', 'superadmin')
                ->first();

            if ($superAdminUser !== null) {
                $superAdminUser->roles()->syncWithoutDetaching([
                    $superAdminRole->id => [
                        'assigned_at' => now(),
                    ],
                ]);
            }
        });
    }
}
