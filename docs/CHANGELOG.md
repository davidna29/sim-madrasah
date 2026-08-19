# Changelog — SIM Madrasah

Format mengikuti gaya sederhana: tanggal, tahap/commit, perubahan.

---

## 19 Agustus 2026 — Tahap 12.34F-4

### Added

- Menambahkan halaman Rekap Beban Guru di `/admin/teaching-assignments/teacher-workload`.
- Menambahkan route `GET /admin/teaching-assignments/teacher-workload`.
- Menambahkan method `teacherWorkload()` di `TeachingAssignmentController`.
- Menambahkan view `admin/teaching-assignments/teacher-workload.blade.php`.
- Menambahkan tombol "Rekap Beban Guru" di halaman daftar Plotting Beban Mengajar.
- Menambahkan test `TeachingAssignmentWorkloadTest`.

### Changed

- Halaman daftar Plotting Beban Mengajar sekarang menyediakan akses cepat ke rekap beban guru.
- Merapikan guard Blade di halaman daftar agar memakai pola `@can('permission', '...')`.

### Validation

- Rekap hanya menghitung plotting aktif (`is_active = true`).
- Rekap dapat difilter berdasarkan tahun ajaran dan semester.
- Total jam/minggu dihitung dari `SUM(weekly_hours)` per guru.

### Notes

- Tidak ada perubahan struktur database pada tahap ini.
- Tidak ada permission baru pada tahap ini.
- Halaman rekap memakai permission lama `teaching_assignments.view`.
- Belum ada validasi batas maksimal beban guru.
- Belum ada ketersediaan guru.
- Belum ada jadwal aktual pelajaran.
- Test penuh berhasil: 200 passed (673 assertions).
- Build frontend berhasil.

---

## 19 Agustus 2026 — Tahap 12.34F-3

### Added

- Menambahkan route `PUT /admin/teaching-assignments/{id}/toggle-active`.
- Menambahkan route `GET /admin/teaching-assignments/{id}/edit`.
- Menambahkan route `PUT /admin/teaching-assignments/{id}`.
- Menambahkan method `toggleActive()`, `edit()`, dan `update()` di `TeachingAssignmentController`.
- Menambahkan view `admin/teaching-assignments/edit.blade.php` (form edit plotting, prefilled dari data yang ada).
- Menambahkan tombol Aktifkan/Nonaktifkan per baris di halaman index.
- Menambahkan link Edit per baris di halaman index.
- Menambahkan 8 test baru: toggle berhasil, toggle ditolak tanpa permission, lihat form edit (dengan/tanpa permission), update berhasil, update tidak menolak data yang tidak berubah, update menolak duplikat dengan record lain, update menolak data tidak valid.

### Changed

- Merapikan logic pengambilan daftar guru (dibatasi role `guru_mata_pelajaran`, `wali_kelas`, `guru_bk`, status `active`) jadi satu method privat `teachers()`, dipakai bersama oleh `create()` dan `edit()`.
- Memperbarui dokumentasi handoff, progress, next steps, dan changelog.

### Fixed

- Memperbaiki label semester di form tambah plotting (`create.blade.php`) yang sebelumnya selalu menampilkan "Genap" untuk semua pilihan — penyebabnya salah membandingkan `semester_type` dengan `'odd'`, padahal nilai yang tersimpan adalah `'ganjil'`/`'genap'`.

### Validation

- Validasi duplikasi kombinasi guru + mapel + rombel + semester saat update tetap dicek di controller, mengecualikan record yang sedang diedit sendiri (`where('id', '!=', $teachingAssignment->id)`).

### Notes

- Tidak ada perubahan struktur database pada tahap ini.
- Tidak ada permission baru pada tahap ini (permission `.update` sudah didaftarkan di Tahap 12.34F-1).
- Daftar guru di form edit tetap dibatasi ke role guru berstatus `active` — sama seperti form tambah. Kalau guru yang sedang diplot dinonaktifkan akunnya, dia tidak akan muncul di dropdown edit (belum ditangani, kasus langka untuk tahap ini).
- Belum ada rekap beban guru, ketersediaan guru, atau jadwal aktual pelajaran.
- Ada 6 usulan fitur baru dari developer dicatat sebagai backlog di `docs/NEXT-STEPS.md` (modal edit slot jadwal, copy jadwal antar hari, generate dummy slot, template Excel untuk master data, copy penugasan dari semester sebelumnya, prinsip umum shortcut untuk input berulang) — belum dikerjakan.
- Test penuh berhasil: 197 passed (663 assertions).
- Build frontend berhasil.

---
## 19 Agustus 2026 — Tahap 12.34F-2

### Added

- Menambahkan route `GET /admin/teaching-assignments/create` dan `POST /admin/teaching-assignments`.
- Menambahkan method `create()` dan `store()` di `TeachingAssignmentController`.
- Menambahkan view `admin/teaching-assignments/create.blade.php` (form tambah plotting).
- Menambahkan tombol "+ Tambah Plotting" di halaman index (hanya muncul jika punya permission).
- Menambahkan helper `createTeacher()` di `TeachingAssignmentTest`.
- Menambahkan 5 test baru: lihat form (dengan/tanpa permission), simpan berhasil, tolak duplikat, tolak data tidak valid.

### Changed

- Memperbarui `TeachingAssignmentController` — menambahkan method `create()` dan `store()`.
- Memperbarui `resources/views/admin/teaching-assignments/index.blade.php` — menambahkan tombol "+ Tambah Plotting".
- Memperbarui dokumentasi handoff, progress, next steps, changelog, dan decisions.

### Validation

- Semua field wajib diisi (tahun ajaran, semester, rombel, mapel, guru, jam per minggu).
- Kombinasi guru + mapel + rombel + semester tidak boleh duplikat.
- Pilihan guru dibatasi hanya akun aktif dengan role `guru_mata_pelajaran`, `wali_kelas`, atau `guru_bk`.
- Field `status` diisi otomatis `'active'`; `is_active` diisi otomatis `true`.

### Notes

- Tidak ada perubahan struktur database pada tahap ini.
- Tidak ada permission baru pada tahap ini (permission `.create` sudah didaftarkan di Tahap 12.34F-1).
- Belum ada form edit dan toggle aktif/nonaktif.
- Belum ada rekap beban guru.
- Belum ada ketersediaan guru.
- Test penuh berhasil: 189 passed (641 assertions).
- Build frontend berhasil.

---

## 19 Agustus 2026 — 12.34F-1
permission baru + route + Controller kosong + halaman daftar (index) — bisa lihat data yang sudah ada dari seeder demo.
catatan> changelog ini belum sempat terupdate secara lengkap

## 19 Agustus 2026 — Tahap 12.34E

### Added

- Menambahkan fondasi database Plotting Beban Mengajar.
- Menambahkan model `TeachingAssignment`.
- Menambahkan tabel `teaching_assignments`.
- Menambahkan relasi plotting beban mengajar pada model `AcademicYear`.
- Menambahkan relasi plotting beban mengajar pada model `Semester`.
- Menambahkan relasi plotting beban mengajar pada model `ClassGroup`.
- Menambahkan relasi plotting beban mengajar pada model `Subject`.
- Menambahkan relasi plotting beban mengajar pada model `User`.
- Menambahkan test `TeachingAssignmentFoundationTest`.

### Database

- Menambahkan tabel `teaching_assignments`.
- Menambahkan unique constraint untuk kombinasi tahun ajaran, semester, rombel, mapel, dan guru.
- Menambahkan foreign key ke tahun ajaran, semester, rombel, mapel, dan user guru.

### Notes

- Tidak ada UI pada tahap ini.
- Tidak ada route pada tahap ini.
- Tidak ada sidebar pada tahap ini.
- Tidak ada permission baru pada tahap ini.
- Belum ada CRUD Plotting Beban Mengajar.
- Belum ada ketersediaan guru.
- Belum ada jadwal aktual pelajaran.
- Belum ada auto-generate.
- Test penuh berhasil: 181 passed.
- Build frontend berhasil.

## 19 Agustus 2026 — Tahap 12.34D

### Added

- Menambahkan Assignment Rombel ke Template Jadwal.
- Menambahkan controller `ScheduleTemplateAssignmentController`.
- Menambahkan route admin untuk Assignment Template Jadwal.
- Menambahkan halaman daftar Assignment Template Jadwal.
- Menambahkan halaman tambah Assignment Template Jadwal.
- Menambahkan form partial Assignment Template Jadwal.
- Menambahkan filter assignment berdasarkan tahun ajaran dan semester.
- Menambahkan fitur assign rombel ke template jadwal.
- Menambahkan fitur replace assignment lama.
- Menambahkan fitur release/lepas assignment.
- Menambahkan menu sidebar Assignment Jadwal.
- Menambahkan test `ScheduleTemplateAssignmentTest`.

### Changed

- Memperbarui sidebar agar menampilkan menu Assignment Jadwal sesuai permission.
- Memperbarui dokumentasi handoff, progress, next steps, changelog, dan decisions.

### Validation

- Semester harus sesuai dengan tahun ajaran yang dipilih.
- Rombel harus sesuai dengan tahun ajaran yang dipilih.
- Template jadwal harus aktif.
- Template jadwal harus sudah memiliki slot.
- Satu rombel hanya boleh memiliki satu template pada semester yang sama.
- Assignment baru ditolak jika rombel sudah memiliki template dan opsi replace tidak dicentang.
- Assignment lama dapat diganti jika opsi replace dicentang.

### Notes

- Tidak ada perubahan struktur database pada tahap ini.
- Tidak ada permission baru pada tahap ini.
- Belum ada jadwal aktual pelajaran.
- Belum ada plotting beban mengajar.
- Belum ada ketersediaan guru.
- Belum ada auto-generate.
- Belum ada drag-and-drop.
- Test penuh berhasil: 177 passed.
- Build frontend berhasil.

## 19 Agustus 2026 — Tahap 12.34C

### Added

- Menambahkan CRUD Slot Template Jadwal.
- Menambahkan controller `ScheduleTemplateSlotController`.
- Menambahkan route admin untuk Slot Template Jadwal.
- Menambahkan halaman daftar Slot Template Jadwal.
- Menambahkan halaman tambah Slot Template Jadwal.
- Menambahkan halaman edit Slot Template Jadwal.
- Menambahkan form partial Slot Template Jadwal.
- Menambahkan tombol Slot pada daftar Template Jadwal.
- Menambahkan test `ScheduleTemplateSlotCrudTest`.

### Changed

- Memperbarui halaman daftar Template Jadwal agar memiliki akses ke daftar slot.
- Memperbarui dokumentasi handoff, progress, next steps, changelog, dan decisions.

### Validation

- Slot hanya boleh dibuat pada hari aktif template.
- Nomor urut slot tidak boleh dobel pada hari yang sama.
- Waktu slot tidak boleh bertabrakan pada hari yang sama.
- Jam selesai harus lebih besar dari jam mulai.
- Slot `kbm` otomatis menjadi slot mengajar.
- Slot non-KBM otomatis menjadi slot non-mengajar.
- Slot template dikunci jika template sudah dipakai rombel.

### Notes

- Tidak ada perubahan struktur database pada tahap ini.
- Tidak ada permission baru pada tahap ini.
- Belum ada Assignment Rombel ke Template.
- Belum ada jadwal aktual pelajaran.
- Belum ada auto-generate.
- Belum ada drag-and-drop.
- Test penuh berhasil: 168 passed.
- Build frontend berhasil.

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