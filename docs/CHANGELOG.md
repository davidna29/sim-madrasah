# Changelog — SIM Madrasah

Format mengikuti gaya sederhana: tanggal, tahap/commit, perubahan.

---
## 19 Agustus 2026 — Tahap 12.34B

### Added

- Menambahkan CRUD Template Jadwal.
- Menambahkan controller `ScheduleTemplateController`.
- Menambahkan route admin untuk Template Jadwal.
- Menambahkan halaman daftar Template Jadwal.
- Menambahkan halaman tambah Template Jadwal.
- Menambahkan halaman edit Template Jadwal.
- Menambahkan form partial Template Jadwal.
- Menambahkan fitur duplicate/clone Template Jadwal.
- Menambahkan menu sidebar Template Jadwal.
- Menambahkan permission `schedule_templates.view`.
- Menambahkan permission `schedule_templates.create`.
- Menambahkan permission `schedule_templates.update`.
- Menambahkan permission `schedule_templates.delete`.
- Menambahkan test `ScheduleTemplateCrudTest`.

### Changed

- Memperbarui `RbacSeeder` untuk permission Template Jadwal.
- Memperbarui sidebar agar menampilkan menu Template Jadwal sesuai permission.
- Memperbarui dokumentasi handoff, progress, next steps, RBAC, dan decisions.

### Protection

- Template Jadwal aktif tidak boleh dihapus.
- Template Jadwal yang sudah memiliki assignment rombel tidak boleh dihapus.
- Template hasil clone dibuat `draft` dan `is_active = false`.

### Notes

- Tidak ada perubahan struktur database pada tahap ini.
- Belum ada CRUD Slot Template.
- Belum ada Assignment Rombel ke Template.
- Belum ada auto-generate.
- Belum ada drag-and-drop.
- Test penuh berhasil: 158 passed.
- Build frontend berhasil.

## 2026-08-19

### Tahap 12.34A

### Added

- Menambahkan fondasi database Modul Jadwal Pelajaran.
- Menambahkan model `ScheduleTemplate`.
- Menambahkan model `ScheduleTemplateSlot`.
- Menambahkan model `ClassGroupScheduleTemplate`.
- Menambahkan tabel `schedule_templates`.
- Menambahkan tabel `schedule_template_slots`.
- Menambahkan tabel `class_group_schedule_templates`.
- Menambahkan test `ScheduleTemplateFoundationTest`.

### Changed

- Menambahkan relasi awal jadwal pada model `AcademicYear`.
- Menambahkan relasi awal jadwal pada model `Semester`.
- Menambahkan relasi awal jadwal pada model `ClassGroup`.
- Memperbarui dokumentasi handoff, progress, next steps, database, dan keputusan teknis.

### Notes

- Belum ada UI, route, controller, permission, auto-generate, drag-and-drop, atau jadwal aktual pelajaran.
- Tidak ada perubahan RBAC.
- Test penuh berhasil: 150 passed.
- Build frontend berhasil.


### `feat: add active toggle and master data correction`

- Menambahkan tombol Nonaktifkan/Aktifkan per baris untuk Ruangan, Mata Pelajaran, Tingkat Kelas.
- Menambahkan Edit untuk Tahun Ajaran dan Semester.
- Tahun Ajaran/Semester terkunci tidak bisa diedit.
- Menambahkan banner pesan error umum di halaman Tahun Ajaran.

## 2026-08-18

### `fix: assign super_admin role correctly on fresh install`

- Memindahkan `InitialAdminSeeder` ke urutan pertama di `DatabaseSeeder.php`.
- Memperbaiki bug: akun `superadmin` pada instalasi baru sebelumnya tidak mendapat role `super_admin`, menyebabkan akses ditolak (403) walau login berhasil.

## 2026-08-15

### `feat: add student class history edit`

- Menambahkan halaman edit riwayat kelas siswa.
- Menambahkan permission `student_class_histories.update`.
- Mengedit histori menjadi "kelas saat ini" menonaktifkan histori aktif lain.
- Menambah test edit dan update riwayat kelas siswa.

## 2026-08-14

### `feat: default active semester in academic forms`

- Menambahkan semester aktif sebagai nilai awal pada form riwayat kelas.
- Menambahkan semester aktif sebagai nilai awal pada form bulk rombel.
- Menampilkan informasi semester aktif di form akademik.
- Menambahkan test tampilan semester aktif di form.
- Menambahkan test validasi default semester.

### `feat: auto start date from active semester in bulk assignment`

- Menambahkan auto tanggal mulai berdasarkan semester aktif pada bulk assignment.
- Tanggal mulai tersinkronkan ketika semester diganti di form.
- Menambahkan JavaScript untuk sinkronisasi tanggal real-time.
- Menambahkan test auto tanggal mulai di form bulk assignment.

## 2026-08-13

### `feat: clarify active academic semester`

- Menambahkan kartu Semester Aktif Sistem pada halaman Tahun Ajaran.
- Memperkuat proses aktivasi semester.
- Memastikan hanya satu semester yang aktif.
- Menolak aktivasi semester terkunci.
- Memperkuat test AcademicYear terkait semester aktif sistem.

### `chore: review bulk student class assignment`

- Melakukan review fitur bulk assignment siswa ke rombel.
- Memastikan validasi konteks akademik berjalan.
- Memastikan guard histori kelas per semester tetap berjalan.
- Memastikan test otomatis dan build frontend berhasil.
- Mengarahkan tahap berikutnya ke penentuan semester aktif sistem.

### `feat: add bulk student class assignment`

- Menambahkan fitur bulk assignment siswa ke rombel.
- Menambahkan route dan controller bulk assignment.
- Menambahkan halaman pemilihan siswa massal.
- Assignment membuat record `student_class_histories`.
- Histori aktif lama dinonaktifkan sebelum histori baru dibuat.
- Menolak assignment jika siswa sudah memiliki histori kelas pada semester yang sama.
- Menambahkan validasi semester harus sesuai tahun ajaran.
- Menambahkan validasi rombel harus sesuai tahun ajaran.
- Menambahkan validasi tanggal mulai harus berada dalam rentang semester.
- Menambahkan test bulk assignment siswa.

### `feat: guard student class history per semester`

- Menambahkan guard agar siswa tidak memiliki lebih dari satu histori kelas pada semester yang sama.
- Mengikuti unique constraint database pada `student_id` dan `semester_id`.
- Memperkuat test histori kelas agar validasi terjadi sebelum database error.

### `docs: sync next steps after student module review`

- Menyinkronkan arah kerja setelah modul Data Siswa stabil.
- Menentukan Tahap 12.27 sebagai Guard Histori Kelas Aktif.
- Menempatkan Bulk Assignment Siswa ke Rombel setelah guard histori kelas aktif.
- Menempatkan Modul Jadwal Pelajaran sebagai tahap setelah histori kelas siswa lebih stabil.

### `chore: review student module`

- Melakukan review akhir modul Data Siswa.
- Mengecek fitur daftar, pencarian, filter, dan export CSV.
- Memastikan test otomatis dan build frontend berhasil.
- Memperbarui dokumentasi status modul Data Siswa.

### `refactor: polish student csv export`

- Merapikan export CSV data siswa.
- Mengubah status siswa pada CSV agar memakai label Indonesia.
- Merapikan tombol filter dan export pada halaman Data Siswa.
- Memperbaiki struktur tombol Tambah Siswa.
- Memperkuat test export CSV siswa.

## 2026-08-12

### `feat: export filtered students to csv`

- Menambahkan export CSV pada halaman Data Siswa.
- Export mengikuti filter aktif: pencarian, rombel, status, dan tahun masuk.
- Menambahkan route `admin.students.export`.
- Memindahkan query filter siswa ke helper `filteredStudentsQuery()`.
- Menambahkan test export siswa berdasarkan filter aktif.

### `feat: add student admission year filter`

- Menambahkan filter tahun ajaran masuk pada halaman Data Siswa.
- Filter memakai `students.admission_academic_year_id`.
- Dropdown tahun masuk mengambil data dari tabel `academic_years`.
- Filter tahun masuk dapat digabung dengan pencarian, filter rombel aktif, dan filter status.
- Menambahkan test filter siswa berdasarkan tahun ajaran masuk.

### `feat: add student status filter`

- Menambahkan filter status pada halaman Data Siswa.
- Filter status mendukung `active`, `inactive`, `transferred`, `graduated`, dan `alumni`.
- Filter status dapat digabung dengan pencarian dan filter rombel aktif.
- Menambahkan test filter siswa berdasarkan status.

### `feat: add student search`

- Menambahkan pencarian siswa pada halaman Data Siswa.
- Pencarian mendukung nama, NIS, dan NISN.
- Pencarian dapat digabung dengan filter rombongan belajar.
- Menambahkan test pencarian siswa.

### `docs: add AI handoff documentation`

- Menambahkan dokumentasi handoff AI.
- Menambahkan README project pengganti README Laravel bawaan.
- Menambahkan instruksi kerja AI.
- Menambahkan dokumentasi database, RBAC, modul, testing, deployment, dan next steps.

### `docs: tambah panduan kontribusi untuk tim`

- Menambahkan panduan kontribusi.
- Mengatur branch, commit, PR, dan rilis.

### `feat: add student search`

- Menambahkan pencarian siswa pada halaman Data Siswa.
- Pencarian mendukung nama, NIS, NISN, dan nomor registrasi.
- Menambahkan test pencarian siswa.

### `feat: add class group filter for students`

- Menambahkan filter siswa berdasarkan rombongan belajar aktif.
- Menampilkan kolom kelas saat ini di daftar siswa.
- Menambahkan test filter siswa berdasarkan rombel.

---

## Sebelumnya

### `feat: add academic year filter for class groups`

- Menambahkan filter rombel berdasarkan tahun ajaran.

### `feat: add class group student excel export`

- Menambahkan export daftar siswa per kelas ke Excel.

### `feat: add class group student print pdf`

- Menambahkan cetak daftar siswa per kelas ke PDF.

### `feat: add student portfolio card pdf`

- Menambahkan cetak kartu portofolio siswa.

### `feat: add student portfolio qr code`

- Menambahkan QR Code untuk portofolio siswa.

### `feat: add student portfolio summary`

- Menambahkan halaman ringkasan portofolio digital siswa.

### `feat: add student guardian account creation`

- Menambahkan pembuatan akun wali siswa.

### `feat: add student guardian module`

- Menambahkan modul orang tua/wali siswa.

### `feat: add student account creation`

- Menambahkan pembuatan akun siswa.

### `feat: add student class history module`

- Menambahkan modul riwayat kelas siswa.

### `feat: add student module`

- Menambahkan modul data siswa.

### `feat: add employee account creation`

- Menambahkan pembuatan akun pegawai.

### `feat: add employee module`

- Menambahkan modul guru dan pegawai.

### `feat: add subject module`

- Menambahkan modul mata pelajaran.

### `feat: add class group module`

- Menambahkan modul rombongan belajar.

### `feat: add grade level and room CRUD pages`

- Menambahkan halaman CRUD tingkat kelas dan ruangan.

### `feat: add academic year and semester module`

- Menambahkan modul tahun ajaran dan semester.

### `feat: add madrasah identity module`

- Menambahkan modul identitas madrasah.
