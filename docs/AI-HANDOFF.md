# AI Handoff — SIM Madrasah

Terakhir diperbarui: 12 Agustus 2026, 12:29 WIB.

Dokumen ini adalah pegangan utama untuk AI/developer berikutnya. Jangan membaca seluruh `SIM-MADRASAH-AI-HISTORY.md` kecuali ada konteks yang benar-benar hilang.

saya mengembangkannya sekarang di macbook air m2 dengan laravel herd 

---

## Tahap Terakhir Selesai

### Tahap 12.34A — Fondasi Database Modul Jadwal Pelajaran

Status: selesai.

Ringkasan:

- Menambahkan fondasi database awal untuk Modul Jadwal Pelajaran.
- Menambahkan tabel `schedule_templates` untuk menyimpan model/template jadwal.
- Menambahkan tabel `schedule_template_slots` untuk menyimpan slot per hari dan jam pada template.
- Menambahkan tabel `class_group_schedule_templates` untuk assignment rombel ke template jadwal.
- Slot template sudah membedakan slot KBM dan non-KBM melalui `slot_type` dan `is_teaching_slot`.
- Template jadwal sudah mendukung hari aktif, hari libur, maksimal slot per hari, dan durasi standar slot.
- Assignment rombel dibatasi satu record per kombinasi tahun ajaran, semester, dan rombel.
- Belum membuat UI CRUD.
- Belum membuat menu Jadwal Pelajaran.
- Belum membuat route/controller.
- Belum membuat permission baru.
- Belum membuat auto-generate.
- Belum membuat drag-and-drop.
- Belum membuat jadwal aktual pelajaran.
- Belum membuat unassigned pool.

File berubah:

- `app/Models/ScheduleTemplate.php`
- `app/Models/ScheduleTemplateSlot.php`
- `app/Models/ClassGroupScheduleTemplate.php`
- `app/Models/AcademicYear.php`
- `app/Models/Semester.php`
- `app/Models/ClassGroup.php`
- `database/migrations/2026_08_19_003802_create_schedule_templates_table.php`
- `database/migrations/2026_08_19_003803_create_schedule_template_slots_table.php`
- `database/migrations/2026_08_19_003804_create_class_group_schedule_templates_table.php`
- `tests/Feature/Admin/ScheduleTemplateFoundationTest.php`
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`
- `docs/DATABASE.md`
- `docs/DECISIONS.md`

Validasi:

- `php artisan test --filter=ScheduleTemplateFoundationTest` berhasil.
- `./vendor/bin/pint` berhasil.
- `./vendor/bin/pint --test` berhasil.
- `php artisan test` berhasil: 150 passed.
- `npm run build` berhasil.

Catatan:

- Tidak ada permission baru pada tahap ini.
- Tidak ada perubahan RBAC.
- Tahap ini sengaja dibatasi sebagai fondasi database agar Modul Jadwal Pelajaran tidak langsung terlalu besar.
- Modul ini akan dikembangkan bertahap dengan arah hybrid: template, slot, assignment rombel, jadwal manual, lock/pin slot, validasi konflik, auto-generate, unassigned pool, lalu drag-and-drop finishing.

Tahap berikutnya:

- Tahap 12.34B — CRUD Template Jadwal.

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