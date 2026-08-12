# AI Handoff — SIM Madrasah

Terakhir diperbarui: 12 Agustus 2026, 12:29 WIB.

Dokumen ini adalah pegangan utama untuk AI/developer berikutnya. Jangan membaca seluruh `SIM-MADRASAH-AI-HISTORY.md` kecuali ada konteks yang benar-benar hilang.

saya mengembangkannya sekarang di macbook air m2 dengan laravel herd 

---

## 1. Status Terakhir Project

Project SIM Madrasah berbasis Laravel sudah memiliki fondasi admin, RBAC, data master, modul siswa, wali siswa, portofolio siswa, cetak PDF, export Excel, filter, dan pencarian siswa.

Status kode yang terbaca dari Git:

```txt
fd723a4 docs: tambah panduan kontribusi untuk tim
cfb94ab feat: add student search
e5de86a feat: add class group filter for students
570e511 feat: add academic year filter for class groups
ff4a033 feat: add class group student excel export
```

Kesimpulan kerja terakhir:

- Tahap filter siswa berdasarkan rombel aktif sudah ada.
- Tahap pencarian siswa berdasarkan nama, NIS, NISN, dan nomor registrasi sudah ada di `StudentController@index`.
- Test pencarian siswa sudah ada di `tests/Feature/Admin/StudentTest.php`.
- README lama masih bawaan Laravel dan perlu diganti dengan README project.
- `SIM-MADRASAH-AI-HISTORY.md` belum tracked di Git menurut hasil `git status` saat dicek.

## Tahap Terakhir Selesai

### Tahap 12.20 — Pencarian Data Siswa Berdasarkan Nama, NIS, dan NISN

Status: selesai.

Perubahan utama:

- Menambahkan parameter pencarian `q` pada halaman Data Siswa.
- Pencarian mendukung nama siswa melalui relasi `person.full_name`.
- Pencarian mendukung NIS melalui `students.student_number`.
- Pencarian mendukung NISN melalui `students.nisn`.
- Pencarian tetap bisa digabung dengan filter rombongan belajar `class_group_id`.
- Query daftar siswa tetap memakai `withQueryString()` agar filter tidak hilang saat pagination.

File berubah:

- `app/Http/Controllers/Admin/StudentController.php`
- `resources/views/admin/students/index.blade.php`
- `tests/Feature/Admin/StudentTest.php`
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`

Catatan:

- Tidak ada perubahan database.
- Tidak ada perubahan permission.
- Tidak ada keputusan teknis besar.

Tahap berikutnya:

- Tahap 12.21 — Filter Siswa Berdasarkan Status.

---

## 2. Teknologi

- Laravel 12
- PHP `^8.2`
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

1. Tahap 12.21 — filter siswa berdasarkan status.
2. Tahap 12.22 — filter siswa berdasarkan tahun ajaran masuk.
3. Tahap 12.23 — tombol export hasil filter siswa.
4. Tahap 12.24 — bulk assignment siswa ke rombel.
5. Tahap 12.25 — validasi satu siswa hanya punya satu kelas aktif per semester.

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
