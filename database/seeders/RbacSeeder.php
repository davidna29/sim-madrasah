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
                [
                    'name' => 'academic_years.view',
                    'module' => 'academic_years',
                    'action' => 'view',
                    'display_name' => 'Melihat Tahun Ajaran',
                    'description' => 'Melihat daftar tahun ajaran dan semester.',
                ],
                [
                    'name' => 'academic_years.create',
                    'module' => 'academic_years',
                    'action' => 'create',
                    'display_name' => 'Membuat Tahun Ajaran',
                    'description' => 'Membuat tahun ajaran dan semester baru.',
                ],
                [
                    'name' => 'academic_years.update',
                    'module' => 'academic_years',
                    'action' => 'update',
                    'display_name' => 'Mengubah Tahun Ajaran',
                    'description' => 'Mengubah data tahun ajaran dan semester.',
                ],
                [
                    'name' => 'academic_years.activate',
                    'module' => 'academic_years',
                    'action' => 'activate',
                    'display_name' => 'Mengaktifkan Semester',
                    'description' => 'Menetapkan satu semester sebagai semester aktif.',
                ],
                [
                    'name' => 'academic_years.lock',
                    'module' => 'academic_years',
                    'action' => 'lock',
                    'display_name' => 'Mengunci Semester',
                    'description' => 'Mengunci semester agar transaksi periode tersebut tidak diubah sembarangan.',
                ],
                // Tambahkan permission untuk modul grade_levels dan rooms
                [
                    'name' => 'grade_levels.view',
                    'module' => 'grade_levels',
                    'action' => 'view',
                    'display_name' => 'Melihat Tingkat Kelas',
                    'description' => 'Melihat data tingkat kelas.',
                ],
                [
                    'name' => 'grade_levels.create',
                    'module' => 'grade_levels',
                    'action' => 'create',
                    'display_name' => 'Membuat Tingkat Kelas',
                    'description' => 'Menambahkan data tingkat kelas.',
                ],
                [
                    'name' => 'grade_levels.update',
                    'module' => 'grade_levels',
                    'action' => 'update',
                    'display_name' => 'Mengubah Tingkat Kelas',
                    'description' => 'Mengubah data tingkat kelas.',
                ],
                [
                    'name' => 'rooms.view',
                    'module' => 'rooms',
                    'action' => 'view',
                    'display_name' => 'Melihat Ruangan',
                    'description' => 'Melihat data ruangan.',
                ],
                [
                    'name' => 'rooms.create',
                    'module' => 'rooms',
                    'action' => 'create',
                    'display_name' => 'Membuat Ruangan',
                    'description' => 'Menambahkan data ruangan.',
                ],
                [
                    'name' => 'rooms.update',
                    'module' => 'rooms',
                    'action' => 'update',
                    'display_name' => 'Mengubah Ruangan',
                    'description' => 'Mengubah data ruangan.',
                ],

                // Tambahkan permission untuk modul class_groups
                [
                    'name' => 'class_groups.view',
                    'module' => 'class_groups',
                    'action' => 'view',
                    'display_name' => 'Melihat Rombongan Belajar',
                    'description' => 'Melihat data rombongan belajar.',
                ],
                [
                    'name' => 'class_groups.create',
                    'module' => 'class_groups',
                    'action' => 'create',
                    'display_name' => 'Membuat Rombongan Belajar',
                    'description' => 'Menambahkan rombongan belajar.',
                ],
                [
                    'name' => 'class_groups.update',
                    'module' => 'class_groups',
                    'action' => 'update',
                    'display_name' => 'Mengubah Rombongan Belajar',
                    'description' => 'Mengubah data rombongan belajar.',
                ],
                // Tambahkan permission untuk modul subjects mata pelajaran
                [
                    'name' => 'subjects.view',
                    'module' => 'subjects',
                    'action' => 'view',
                    'display_name' => 'Melihat Mata Pelajaran',
                    'description' => 'Melihat data mata pelajaran.',
                ],
                [
                    'name' => 'subjects.create',
                    'module' => 'subjects',
                    'action' => 'create',
                    'display_name' => 'Membuat Mata Pelajaran',
                    'description' => 'Menambahkan data mata pelajaran.',
                ],
                [
                    'name' => 'subjects.update',
                    'module' => 'subjects',
                    'action' => 'update',
                    'display_name' => 'Mengubah Mata Pelajaran',
                    'description' => 'Mengubah data mata pelajaran.',
                ],
                // Tambahkan permission untuk modul employees guru dan pegawai
                [
                    'name' => 'employees.view',
                    'module' => 'employees',
                    'action' => 'view',
                    'display_name' => 'Melihat Guru dan Pegawai',
                    'description' => 'Melihat data guru dan tenaga kependidikan.',
                ],
                [
                    'name' => 'employees.create',
                    'module' => 'employees',
                    'action' => 'create',
                    'display_name' => 'Membuat Guru dan Pegawai',
                    'description' => 'Menambahkan data guru dan tenaga kependidikan.',
                ],
                [
                    'name' => 'employees.update',
                    'module' => 'employees',
                    'action' => 'update',
                    'display_name' => 'Mengubah Guru dan Pegawai',
                    'description' => 'Mengubah data guru dan tenaga kependidikan.',
                ],
                // Tambahkan permission untuk membuat akun login dari data guru atau pegawai
                [
                    'name' => 'employees.account.create',
                    'module' => 'employees',
                    'action' => 'account_create',
                    'display_name' => 'Membuat Akun Pegawai',
                    'description' => 'Membuat akun login aplikasi dari data guru atau pegawai.',
                ],

                // Tambahkan permission untuk modul students siswa
                [
                    'name' => 'students.view',
                    'module' => 'students',
                    'action' => 'view',
                    'display_name' => 'Melihat Data Siswa',
                    'description' => 'Melihat data master siswa.',
                ],
                [
                    'name' => 'students.create',
                    'module' => 'students',
                    'action' => 'create',
                    'display_name' => 'Membuat Data Siswa',
                    'description' => 'Menambahkan data master siswa.',
                ],
                [
                    'name' => 'students.update',
                    'module' => 'students',
                    'action' => 'update',
                    'display_name' => 'Mengubah Data Siswa',
                    'description' => 'Mengubah data master siswa.',
                ],

                // Tambahkan permission untuk modul student_class_histories riwayat kelas siswa
                [
                    'name' => 'student_class_histories.view',
                    'module' => 'student_class_histories',
                    'action' => 'view',
                    'display_name' => 'Melihat Riwayat Kelas Siswa',
                    'description' => 'Melihat riwayat penempatan kelas siswa.',
                ],
                [
                    'name' => 'student_class_histories.create',
                    'module' => 'student_class_histories',
                    'action' => 'create',
                    'display_name' => 'Membuat Riwayat Kelas Siswa',
                    'description' => 'Menambahkan riwayat penempatan kelas siswa.',
                ],
                // Tambahkan permission untuk membuat akun login dari data siswa
                [
                    'name' => 'students.account.create',
                    'module' => 'students',
                    'action' => 'account_create',
                    'display_name' => 'Membuat Akun Siswa',
                    'description' => 'Membuat akun login aplikasi dari data siswa.',
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
