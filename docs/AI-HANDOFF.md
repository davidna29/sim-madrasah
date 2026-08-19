# AI Handoff — SIM Madrasah

Terakhir diperbarui: 19 Agustus 2026.

Dokumen ini adalah pegangan utama untuk AI/developer berikutnya. Jangan membaca seluruh `SIM-MADRASAH-AI-HISTORY.md` kecuali ada konteks yang benar-benar hilang.

Developer berpindah-pindah device (Windows dan macOS), memakai Laravel Herd di kedua device.

---

## Tahap Terakhir Selesai

### Tahap 12.34G-2 — Halaman Daftar Ketersediaan Guru

Status: selesai.

Ringkasan:

- Menambahkan permission `teacher_availabilities.view` di `RbacSeeder`.
- Menambahkan controller `Admin\TeacherAvailabilityController`.
- Menambahkan route `GET /admin/teacher-availabilities`, dilindungi permission `teacher_availabilities.view`.
- Menambahkan halaman daftar Ketersediaan Guru.
- Menambahkan filter Tahun Ajaran, Semester, dan Guru.
- Menampilkan tabel Tahun Ajaran, Semester, Guru, Hari, Jam, Tipe, Alasan, dan Status.
- Menambahkan menu sidebar "Ketersediaan Guru" yang hanya tampil jika user punya permission `teacher_availabilities.view`.
- Menambahkan test `TeacherAvailabilityIndexTest`.
- Tidak menambahkan permission `.create` atau `.update` dulu supaya tidak ada permission baru yang menganggur.

File berubah:

- `database/seeders/RbacSeeder.php`
- `app/Http/Controllers/Admin/TeacherAvailabilityController.php` (baru)
- `routes/web.php`
- `resources/views/layouts/sidebar.blade.php`
- `resources/views/admin/teacher-availabilities/index.blade.php` (baru)
- `tests/Feature/Admin/TeacherAvailabilityIndexTest.php` (baru)
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`
- `docs/RBAC.md`
- `docs/DECISIONS.md`

Validasi:

- `php artisan test --filter=TeacherAvailabilityIndexTest`: 5 passed (19 assertions).
- `php artisan test`: isi sesuai hasil test penuh terakhir.
- `npm run build`: isi sesuai hasil build terakhir.

Catatan:

- Tahap ini belum membuat form tambah.
- Tahap ini belum membuat form edit.
- Tahap ini belum membuat toggle aktif/nonaktif.
- Tahap ini belum membuat validasi bentrok jam ketersediaan di level aplikasi.
- Tahap ini belum menghubungkan ketersediaan guru ke jadwal aktual.
- Dropdown guru di filter menampilkan semua guru aktif dengan role `guru_mata_pelajaran`, `wali_kelas`, atau `guru_bk`.
- Saat test filter, nama guru lain tetap bisa muncul di dropdown, sehingga assertion negatif cukup mengecek konten baris tabel seperti alasan, bukan nama guru.

Tahap berikutnya:

- Tahap 12.34G-3 — Form Tambah Ketersediaan Guru.

---

## 2. Teknologi

- Laravel 12
- PHP `^8.4`
- Laravel Breeze
- Blade
- Tailwind CSS
- Vite
- SQLite untuk lokal/testing
- MySQL/MariaDB untuk production
- DomPDF
- Laravel Excel
- Simple QRCode

---

## 3. Prinsip Arsitektur

- Aplikasi dibuat bertahap agar mudah dipahami pemula.
- Backend mengikuti pola MVC Laravel.
- UI menggunakan Blade + Tailwind.
- Route admin wajib memakai middleware `auth`, `verified`, `active.account`, dan permission sesuai kebutuhan.
- Data identitas orang disimpan di `people`.
- Akun login disimpan di `users` dan dapat terhubung ke `people` melalui `person_id`.
- Data siswa tidak menyimpan kelas sebagai data tunggal yang ditimpa.
- Riwayat kelas siswa disimpan di `student_class_histories`.
- Kelas aktif siswa dibaca dari relasi `currentClassHistory` dengan `is_current = true`.

### 3.1 Prinsip Paritas Lokal ↔ Production

Lihat ADR-008 di `docs/DECISIONS.md` untuk latar belakang lengkap.

- Fitur yang baru dibuat harus bisa diuji lewat alur setup standar di `README.md`
  (`php artisan migrate --seed`), bukan cuma lewat data yang kebetulan sudah ada
  di database lokal AI/developer saat itu.
- Kalau sebuah halaman/controller bergantung pada satu baris data tertentu
  (misalnya `Model::where(...)->firstOrFail()` pada data singleton seperti
  identitas madrasah), pastikan seeder yang membuat data itu masuk daftar
  seeder **wajib** di `DatabaseSeeder.php` dan didokumentasikan di
  `docs/DEPLOYMENT.md` bagian "Seeder Wajib vs Seeder Demo".
- Seeder wajib (struktur sistem, bukan data fiktif) harus aman dijalankan
  di production. Seeder demo (data contoh siswa/guru fiktif) tidak boleh
  ikut daftar wajib dan tidak boleh disarankan untuk production.
- Sebelum menandai satu tahap selesai, bayangkan seseorang meng-clone repo
  dari nol lalu mengikuti `README.md` persis apa adanya — apakah fitur baru
  tetap berfungsi? Kalau tidak, itu tanda ada seeder/config/migration yang
  belum lengkap.

---

## 4. Modul yang Sudah Ada

1. Authentication Laravel Breeze.
2. Middleware akun aktif.
3. RBAC role dan permission.
4. Dashboard role-based.
5. Sidebar admin.
6. Identitas madrasah.
7. Tahun ajaran dan semester.
8. Tingkat kelas.
9. Ruangan.
10. Rombongan belajar.
11. Mata pelajaran.
12. Guru dan pegawai.
13. Akun pegawai.
14. Data siswa.
15. Riwayat kelas siswa.
16. Akun siswa.
17. Orang tua/wali siswa.
18. Akun orang tua/wali siswa.
19. Portofolio digital siswa.
20. QR Code portofolio siswa.
21. Cetak kartu portofolio siswa PDF.
22. Cetak data siswa per kelas PDF.
23. Export data siswa per kelas Excel.
24. Filter rombel berdasarkan tahun ajaran.
25. Filter siswa berdasarkan rombel aktif.
26. Pencarian siswa berdasarkan nama, NIS, NISN, dan nomor registrasi.
27. Fondasi database Modul Jadwal Pelajaran.
28. CRUD Template Jadwal Pelajaran.
29. CRUD Slot Template Jadwal.
30. Assignment Rombel ke Template Jadwal.
31. Fondasi database Plotting Beban Mengajar.
32. CRUD Plotting Beban Mengajar (daftar, tambah, edit, toggle aktif/nonaktif).
33. Rekap Beban Guru dari Plotting Beban Mengajar.
34. Fondasi Database Ketersediaan Guru.
35. Halaman Daftar Ketersediaan Guru.

---

## 5. File Penting Saat Ini

| Fungsi | File |
|---|---|
| Route web | `routes/web.php` |
| Dashboard | `app/Http/Controllers/DashboardController.php` |
| RBAC seeder | `database/seeders/RbacSeeder.php` |
| Middleware permission | `app/Http/Middleware/EnsureUserHasPermission.php` |
| Middleware akun aktif | `app/Http/Middleware/EnsureAccountIsActive.php` |
| Model user | `app/Models/User.php` |
| Model siswa | `app/Models/Student.php` |
| Controller siswa | `app/Http/Controllers/Admin/StudentController.php` |
| View daftar siswa | `resources/views/admin/students/index.blade.php` |
| Test siswa | `tests/Feature/Admin/StudentTest.php` |
| Export siswa per kelas | `app/Exports/ClassGroupStudentsExport.php` |

---

## 6. Pemeriksaan Wajib

Setelah mengubah kode:

```bash
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
git status
```

Catatan hasil cek di environment AI: `php artisan test` tidak bisa dijalankan karena ekstensi PHP `mbstring` tidak aktif, ditandai error `Call to undefined function Illuminate\Support\mb_split()`. Ini masalah environment, bukan bukti bahwa test project gagal.

---

## 7. Tugas Berikutnya yang Direkomendasikan

Lihat detail di `docs/NEXT-STEPS.md`.

---

## 8. Instruksi untuk AI Berikutnya

Jangan langsung menulis banyak kode. Mulai dari satu tahap paling kecil.

Format kerja:

```txt
Saya akan mengerjakan Tahap [nomor] — [nama tahap].
File yang akan disentuh:
- ...
Target hasil:
- ...
Pemeriksaan:
- php artisan test
- npm run build
```

Setelah selesai, update dokumentasi.