<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan seeder mengikuti dependency data. Lihat penjelasan lengkap
     * "Seeder Wajib vs Seeder Demo" di docs/DEPLOYMENT.md.
     *
     * PENTING: seeder ini dipakai untuk `php artisan migrate --seed`
     * (setup lokal, lihat README.md). JANGAN jalankan perintah ini
     * di production karena berisi data demo/dummy. Production memakai
     * seeder terpilih satu per satu, lihat docs/DEPLOYMENT.md.
     */
    public function run(): void
    {
        $this->call([
            // Wajib supaya aplikasi bisa dipakai sama sekali
            // (role/permission, dan identitas madrasah untuk /admin/madrasah).
            RbacSeeder::class,
            MadrasahSeeder::class,

            // Data referensi umum, aman dipakai sebagai titik awal,
            // boleh diedit lagi lewat UI (tingkat kelas, ruangan, mapel, tahun ajaran).
            GradeLevelSeeder::class,
            RoomSeeder::class,
            SubjectSeeder::class,
            AcademicYearSeeder::class,

            // Data DEMO/dummy untuk latihan dan testing lokal saja.
            ClassGroupSeeder::class,
            EmployeeSeeder::class,
            StudentSeeder::class,
            StudentGuardianSeeder::class,
            StudentClassHistorySeeder::class,

            // Administrator awal. Otomatis memblokir diri sendiri
            // kecuali APP_ENV=local, jadi aman dipanggil di sini.
            InitialAdminSeeder::class,
        ]);
    }
}
