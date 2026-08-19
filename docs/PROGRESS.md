# Progress Pengembangan — SIM Madrasah

Terakhir diperbarui: 13 Agustus 2026.

---

## Ringkasan Status

| Area | Status | Catatan |
|---|---:|---|
| Setup Laravel | Selesai | Laravel 12, Breeze, Tailwind, Vite |
| Authentication | Selesai | Login/register bawaan Breeze, disesuaikan akun aktif |
| Middleware akun aktif | Selesai | `active.account` |
| RBAC | Selesai awal | Role, permission, pivot, middleware permission |
| Dashboard | Selesai awal | Role-based summary dan shortcut |
| Layout admin | Selesai awal | Sidebar dan komponen UI |
| Identitas madrasah | Selesai awal | Edit/update profil madrasah |
| Tahun ajaran & semester | Selesai tahap 12.30 | Aktivasi semester, lock semester, kartu Semester Aktif Sistem |
| Tingkat kelas | Selesai awal | CRUD |
| Ruangan | Selesai awal | CRUD |
| Rombongan belajar | Selesai awal | CRUD + filter tahun ajaran |
| Mata pelajaran | Selesai awal | CRUD |
| Pegawai | Selesai awal | CRUD |
| Akun pegawai | Selesai awal | Create akun dari data pegawai |
| Siswa | Stabil tahap 12.25 | CRUD, filter rombel, pencarian, filter status, filter tahun masuk, export CSV, review akhir |
| Riwayat kelas siswa | Selesai tahap 12.32 | Tambah histori, edit histori, bulk rombel, default semester aktif, auto tanggal |
| Jadwal Pelajaran | Selesai tahap 12.34E | Fondasi jadwal, template, slot, assignment rombel, dan database plotting beban mengajar sudah tersedia |
| Akun siswa | Selesai awal | Create akun dari data siswa |
| Wali siswa | Selesai awal | CRUD wali per siswa |
| Akun wali siswa | Selesai awal | Create akun wali |
| Portofolio siswa | Selesai awal | Ringkasan identitas, kelas, wali |
| QR portofolio | Selesai awal | QR menuju portofolio siswa |
| Cetak kartu portofolio | Selesai awal | PDF |
| Cetak siswa per kelas | Selesai awal | PDF |
| Export siswa per kelas | Selesai awal | Excel |
| Dokumentasi handoff AI | Baru dibuat | Paket docs ini |

---

## Timeline Commit Terakhir yang Terbaca

```txt
fd723a4 docs: tambah panduan kontribusi untuk tim
cfb94ab feat: add student search
e5de86a feat: add class group filter for students
570e511 feat: add academic year filter for class groups
ff4a033 feat: add class group student excel export
776a7e6 feat: add class group student print pdf
956aaa7 feat: add student portfolio card pdf
8ca81f4 feat: add student portfolio qr code
712efc0 feat: add student portfolio summary
a1358e1 feat: add student guardian account creation
2b9f53f feat: add student guardian module
c6b9dcc feat: add student account creation
7dfbb67 feat: add student class history module
6629ae0 feat: add student module
cd7db19 feat: add employee account creation
6c649f5 feat: add employee module
c771617 feat: add subject module
a619108 feat: add class group module
1b6b9b9 feat: add grade level and room CRUD pages
6d4ea2a feat: add grade level and room foundations
a91392b feat: add academic year and semester module
addfd94 feat: add madrasah identity module
```

---

## Fitur Terakhir yang Sudah Tampak di Kode

### Fondasi Database Plotting Beban Mengajar

Status: selesai tahap 12.34E.

Fitur:

- tabel `teaching_assignments`;
- model `TeachingAssignment`;
- relasi plotting beban mengajar pada `AcademicYear`;
- relasi plotting beban mengajar pada `Semester`;
- relasi plotting beban mengajar pada `ClassGroup`;
- relasi plotting beban mengajar pada `Subject`;
- relasi plotting beban mengajar pada `User`;
- test fondasi database Plotting Beban Mengajar.

Fungsi data:

- menyimpan guru yang mengajar mata pelajaran tertentu;
- menyimpan rombel tujuan;
- menyimpan tahun ajaran dan semester;
- menyimpan jumlah jam per minggu;
- menjadi bahan dasar jadwal manual dan auto-generate.

Validasi database:

- kombinasi `academic_year_id`, `semester_id`, `class_group_id`, `subject_id`, dan `teacher_user_id` tidak boleh dobel;
- relasi ke tahun ajaran, semester, rombel, mapel, dan guru sudah tersedia;
- `weekly_hours` disimpan sebagai integer;
- `is_active` disimpan sebagai boolean.

Cakupan yang belum dibuat:

- CRUD Plotting Beban Mengajar;
- filter plotting;
- rekap beban mengajar guru;
- validasi beban maksimal guru;
- ketersediaan guru;
- jadwal aktual pelajaran;
- validasi konflik guru;
- auto-generate;
- unassigned pool;
- drag-and-drop.

Validasi teknis:

- `TeachingAssignmentFoundationTest` berhasil;
- `php artisan test` berhasil: 181 passed;
- `npm run build` berhasil.

### Assignment Rombel ke Template Jadwal

Status: selesai tahap 12.34D.

Fitur:

- daftar Assignment Template Jadwal;
- tambah Assignment Template Jadwal;
- filter assignment berdasarkan tahun ajaran;
- filter assignment berdasarkan semester;
- assign rombel ke template jadwal;
- replace assignment lama jika rombel sudah memiliki template;
- release/lepas assignment dari rombel;
- menu sidebar Assignment Jadwal;
- test Assignment Rombel ke Template Jadwal.

Validasi:

- satu rombel hanya boleh memiliki satu template pada kombinasi tahun ajaran dan semester yang sama;
- assignment baru ditolak jika rombel sudah memiliki template dan opsi replace tidak dicentang;
- assignment lama dapat diganti jika opsi replace dicentang;
- semester harus sesuai dengan tahun ajaran yang dipilih;
- rombel harus sesuai dengan tahun ajaran yang dipilih;
- template jadwal harus aktif;
- template jadwal harus sudah memiliki slot.

Permission:

- tidak ada permission baru;
- melihat assignment memakai `schedule_templates.view`;
- tambah, replace, dan lepas assignment memakai `schedule_templates.update`.

Cakupan yang belum dibuat:

- jadwal aktual pelajaran;
- plotting beban mengajar;
- ketersediaan guru;
- validasi konflik guru;
- lock/pin slot jadwal aktual;
- auto-generate;
- unassigned pool;
- drag-and-drop.

Validasi teknis:

- `ScheduleTemplateAssignmentTest` berhasil;
- `php artisan test` berhasil: 177 passed;
- `npm run build` berhasil.

### CRUD Slot Template Jadwal

Status: selesai tahap 12.34C.

Fitur:

- daftar slot template per hari;
- tambah slot template;
- edit slot template;
- hapus slot template;
- tombol Slot dari daftar Template Jadwal;
- pengelompokan slot berdasarkan hari;
- dukungan jenis slot KBM dan non-KBM;
- penguncian slot jika template sudah dipakai rombel;
- test CRUD Slot Template Jadwal.

Validasi:

- slot hanya boleh dibuat pada hari aktif template;
- nomor urut slot tidak boleh dobel pada hari yang sama;
- waktu slot tidak boleh bertabrakan pada hari yang sama;
- jam selesai harus lebih besar dari jam mulai;
- slot `kbm` otomatis `is_teaching_slot = true`;
- slot selain `kbm` otomatis `is_teaching_slot = false`.

Permission:

- tidak ada permission baru;
- melihat slot memakai `schedule_templates.view`;
- tambah, edit, dan hapus slot memakai `schedule_templates.update`.

Cakupan yang belum dibuat:

- Assignment Rombel ke Template;
- jadwal aktual pelajaran;
- validasi konflik guru;
- lock/pin slot;
- auto-generate;
- unassigned pool;
- drag-and-drop.

Validasi teknis:

- `ScheduleTemplateSlotCrudTest` berhasil;
- `php artisan test` berhasil: 168 passed;
- `npm run build` berhasil.


### CRUD Template Jadwal Pelajaran

Status: selesai tahap 12.34B.

Fitur:

- daftar Template Jadwal;
- tambah Template Jadwal;
- edit Template Jadwal;
- duplicate/clone Template Jadwal;
- delete Template Jadwal dengan proteksi;
- menu sidebar Template Jadwal;
- permission RBAC untuk Template Jadwal;
- test CRUD Template Jadwal.

Proteksi:

- template aktif tidak boleh dihapus;
- template yang sudah memiliki assignment rombel tidak boleh dihapus;
- clone template dibuat nonaktif agar tidak langsung dipakai tanpa pengecekan admin.

Permission baru:

- `schedule_templates.view`;
- `schedule_templates.create`;
- `schedule_templates.update`;
- `schedule_templates.delete`.

Cakupan yang belum dibuat:

- CRUD Slot Template;
- Assignment Rombel ke Template;
- jadwal aktual pelajaran;
- validasi konflik guru;
- lock/pin slot;
- auto-generate;
- unassigned pool;
- drag-and-drop.

Validasi:

- `ScheduleTemplateCrudTest` berhasil;
- `php artisan test` berhasil: 158 passed;
- `npm run build` berhasil.

### Koreksi dan Edit Riwayat Kelas Siswa

Status: selesai.

Fitur:

- edit histori kelas siswa yang sudah tercatat;
- mengedit ke "kelas saat ini" menonaktifkan histori aktif lain milik siswa yang sama;
- validasi akademik tetap berjalan saat edit;
- belum ada fitur hapus histori.

Validasi:

- `StudentClassHistoryTest` berhasil;
- `php artisan test` berhasil;
- `npm run build` berhasil.

### Integrasi Semester Aktif ke Riwayat Kelas dan Bulk Rombel

Status: selesai.

Fitur:

- form tambah riwayat kelas memakai semester aktif sebagai default;
- form bulk rombel memakai semester aktif sebagai default;
- tanggal mulai pada bulk rombel mengikuti semester aktif;
- tanggal mulai tersinkronkan jika semester diganti;
- informasi semester aktif ditampil di form;
- validasi tahun ajaran, semester, rombel, dan tanggal tetap aktif.

Validasi:

- test riwayat kelas berhasil;
- test bulk assignment berhasil;
- `php artisan test` berhasil;
- `npm run build` berhasil;
- uji manual di Laravel Herd berhasil.

---
### Kebijakan Nonaktif dan Koreksi Data Master

Status: selesai (sebagian — lihat catatan cakupan).

Fitur:

- toggle Nonaktifkan/Aktifkan per baris untuk Ruangan, Mata Pelajaran, Tingkat Kelas;
- edit Tahun Ajaran dan Semester (sebelumnya tidak bisa dikoreksi sama sekali);
- Tahun Ajaran/Semester terkunci tidak bisa diedit;
- banner pesan error umum di halaman Tahun Ajaran.

Cakupan yang ditunda:

- toggle nonaktif untuk Rombongan Belajar, Siswa, Pegawai (field status lebih kompleks);
- soft delete (`deleted_at`) untuk data master;
- fitur buka kunci (unlock) semester/tahun ajaran.

Validasi:

- `GradeLevelRoomCrudTest`, `SubjectTest`, `AcademicYearTest` berhasil;
- `php artisan test` berhasil (147 passed);
- `npm run build` berhasil.

### Fondasi Database Modul Jadwal Pelajaran

Status: selesai tahap 12.34A.

Fitur:

- tabel `schedule_templates` untuk model/template jadwal;
- tabel `schedule_template_slots` untuk slot harian template;
- tabel `class_group_schedule_templates` untuk assignment rombel ke template;
- dukungan hari aktif dan hari libur pada template;
- dukungan maksimal slot per hari dan durasi standar slot;
- dukungan slot KBM dan non-KBM;
- batas satu assignment template untuk satu rombel dalam satu semester;
- relasi model awal ke `AcademicYear`, `Semester`, dan `ClassGroup`.

Cakupan yang belum dibuat:

- CRUD template jadwal;
- CRUD slot template;
- assignment rombel melalui UI;
- jadwal aktual pelajaran;
- validasi konflik guru;
- lock/pin slot;
- auto-generate;
- unassigned pool;
- drag-and-drop.

Validasi:

- `ScheduleTemplateFoundationTest` berhasil;
- `php artisan test` berhasil: 150 passed;
- `npm run build` berhasil.

---

## Catatan yang Perlu Dirapikan

1. `SIM-MADRASAH-AI-HISTORY.md` sangat besar dan sebaiknya hanya menjadi arsip.
2. Folder `vendor/` dan `node_modules/` tidak perlu masuk zip handoff.
3. `php artisan test` di environment AI gagal karena ekstensi PHP `mbstring` tidak aktif.
