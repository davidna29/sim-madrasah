# AI Handoff — SIM Madrasah

Terakhir diperbarui: 19 Agustus 2026.

Dokumen ini adalah pegangan utama untuk AI/developer berikutnya. Jangan membaca seluruh `SIM-MADRASAH-AI-HISTORY.md` kecuali ada konteks yang benar-benar hilang.

Developer berpindah-pindah device (Windows dan macOS), memakai Laravel Herd di kedua device.

---

## Tahap Terakhir Selesai

### Bug Fix — Label Semester Salah di Form Tambah Plotting Beban Mengajar

Status: selesai (di luar penomoran tahap, ditemukan saat kerja Tahap 12.34F-3).

Ringkasan:

- Di `create.blade.php`, label semester membandingkan `semester_type` dengan `'odd'`, padahal nilai yang tersimpan di database adalah `'ganjil'`/`'genap'`. Akibatnya semua pilihan semester di dropdown selalu tertulis "Genap", walau datanya benar (ada Ganjil dan Genap).
- Diperbaiki jadi membandingkan dengan `'ganjil'`.

File berubah:

- `resources/views/admin/teaching-assignments/create.blade.php`

---

### Tahap 12.34F-3 — Form Edit dan Toggle Aktif/Nonaktif Plotting Beban Mengajar

Status: selesai.

Ringkasan:

- Menambahkan route `PUT /admin/teaching-assignments/{id}/toggle-active`, `GET /admin/teaching-assignments/{id}/edit`, dan `PUT /admin/teaching-assignments/{id}`, semuanya dilindungi `permission:teaching_assignments.update`.
- Menambahkan method `toggleActive()`, `edit()`, dan `update()` di `TeachingAssignmentController`.
- Menambahkan tombol Aktifkan/Nonaktifkan per baris di halaman index (pola sama seperti modul Mata Pelajaran).
- Menambahkan view `admin/teaching-assignments/edit.blade.php`, salinan form tambah dengan data terisi (prefilled) dan memakai `@method('PUT')`.
- Menambahkan link "Edit" per baris di halaman index, satu grup dengan tombol toggle.
- Validasi duplikasi kombinasi academic_year + semester + class_group + subject + teacher tetap dicek di controller saat update, tapi mengecualikan record yang sedang diedit sendiri (`where('id', '!=', ...)`) — supaya data tidak dianggap "bentrok" dengan dirinya sendiri.
- Merapikan logic pengambilan daftar guru (dibatasi role `guru_mata_pelajaran`, `wali_kelas`, `guru_bk`, status `active`) jadi satu method privat `teachers()`, dipakai bersama oleh `create()` dan `edit()`.
- Menambahkan 8 test baru: toggle berhasil/ditolak tanpa permission, lihat form edit dengan/tanpa permission, update berhasil, update tidak menolak data yang tidak berubah (memastikan pengecualian diri sendiri berfungsi), update menolak duplikat dengan record lain, update menolak data tidak valid.

File berubah:

- `routes/web.php`
- `app/Http/Controllers/Admin/TeachingAssignmentController.php`
- `resources/views/admin/teaching-assignments/edit.blade.php` (baru)
- `resources/views/admin/teaching-assignments/index.blade.php`
- `resources/views/admin/teaching-assignments/create.blade.php` (bug fix label semester)
- `tests/Feature/Admin/TeachingAssignmentTest.php`
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`

Validasi (dijalankan di macOS/Herd):

- `php artisan test --filter=TeachingAssignmentTest`: 16 passed (57 assertions).
- `php artisan test`: 197 passed (663 assertions).
- `npm run build` berhasil.

Catatan:

- Daftar guru di form edit tetap dibatasi ke role guru berstatus `active` — sama seperti form tambah. Kalau guru yang sedang diplot ternyata dinonaktifkan akunnya, dia tidak akan muncul di dropdown edit (belum ditangani, dianggap kasus langka untuk tahap ini).
- Tahap ini belum membuat rekap beban guru.
- Tahap ini belum membuat ketersediaan guru.
- Tahap ini belum membuat jadwal aktual pelajaran.
- Ada beberapa catatan keinginan fitur baru dari developer (modal edit slot jadwal, copy jadwal antar hari, generate dummy slot, template Excel untuk master data, copy penugasan dari semester sebelumnya, prinsip umum shortcut input berulang) — dicatat sebagai backlog di `docs/NEXT-STEPS.md`, belum dikerjakan.

Tahap berikutnya:

- Belum ditentukan — lihat daftar backlog di `docs/NEXT-STEPS.md` untuk pilihan prioritas berikutnya.

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