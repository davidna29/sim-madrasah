# AI Handoff — SIM Madrasah

Terakhir diperbarui: 12 Agustus 2026, 12:29 WIB.

Dokumen ini adalah pegangan utama untuk AI/developer berikutnya. Jangan membaca seluruh `SIM-MADRASAH-AI-HISTORY.md` kecuali ada konteks yang benar-benar hilang.

saya mengembangkannya sekarang di macbook air m2 dengan laravel herd 

---

## Tahap Terakhir Selesai

### Tahap 12.33 — Kebijakan Nonaktif dan Koreksi Data Master

Status: selesai.

Ringkasan:

- Menambahkan tombol Nonaktifkan/Aktifkan per baris (toggle `is_active`) untuk: Ruangan, Mata Pelajaran, Tingkat Kelas.
- Tombol memakai permission `*.update` yang sudah ada, tidak ada permission baru untuk toggle ini.
- Menambahkan Edit untuk Tahun Ajaran (kode, nama, tanggal mulai/selesai).
- Menambahkan Edit untuk Semester (kode, nama, jenis semester, tanggal mulai/selesai).
- Tahun Ajaran/Semester yang sudah `is_locked = true` tidak bisa diedit — tombol Edit disembunyikan di view, dan diblokir juga di controller.
- Menambahkan banner pesan error umum di halaman Tahun Ajaran (`$errors->any()`).
- Ditemukan dan diperbaiki bug terpisah: urutan seeder membuat akun superadmin baru tidak dapat role (lihat `docs/DECISIONS.md`, sudah di-commit terpisah sebelum tahap ini).
- Fitur buka kunci (unlock) semester/tahun ajaran sengaja TIDAK dibuat — dicatat sebagai usulan di `docs/NEXT-STEPS.md`.
- Soft delete (`deleted_at`) untuk data master: ditunda, tidak dikerjakan di tahap ini.

File berubah:

- `app/Http/Controllers/Admin/RoomController.php`
- `app/Http/Controllers/Admin/SubjectController.php`
- `app/Http/Controllers/Admin/GradeLevelController.php`
- `app/Http/Controllers/Admin/AcademicYearController.php`
- `routes/web.php`
- `resources/views/admin/rooms/index.blade.php`
- `resources/views/admin/subjects/index.blade.php`
- `resources/views/admin/grade-levels/index.blade.php`
- `resources/views/admin/academic-years/index.blade.php`
- `resources/views/admin/academic-years/edit.blade.php`
- `resources/views/admin/academic-years/semesters/edit.blade.php`
- `tests/Feature/Admin/GradeLevelRoomCrudTest.php`
- `tests/Feature/Admin/SubjectTest.php`
- `tests/Feature/Admin/AcademicYearTest.php`
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`
- `docs/RBAC.md`

Catatan:

- Tidak ada perubahan struktur database.
- Tidak ada permission baru (toggle dan edit memakai permission `*.update` yang sudah ada sebelumnya tapi belum dipakai).
- Rombongan Belajar, Siswa, dan Pegawai sengaja TIDAK dapat tombol nonaktif cepat di tahap ini — field status mereka lebih kompleks (ada `status`/`employment_status` terpisah dari `is_active`), butuh tahap sendiri.

Tahap berikutnya:

- Tahap 12.34 — Awal Modul Jadwal Pelajaran.
- Usulan (belum prioritas): Buka Kunci (Unlock) Semester dan Tahun Ajaran — lihat `docs/NEXT-STEPS.md`.

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

Rekomendasi tahap kecil berikutnya:

1. Tahap 12.27 — Guard Histori Kelas Aktif.
2. Tahap 12.28 — Bulk Assignment Siswa ke Rombel.
3. Tahap 12.29 — Awal Modul Jadwal Pelajaran.

Alasan urutan:

- Modul Data Siswa sudah stabil pada Tahap 12.25.
- Guard histori kelas aktif perlu dibuat sebelum bulk assignment.
- Bulk assignment akan membuat banyak record `student_class_histories`.
- Modul Jadwal Pelajaran sebaiknya dimulai setelah data siswa dan histori kelas lebih aman.

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