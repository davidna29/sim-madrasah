# Technical Decisions — SIM Madrasah

Dokumen ini mencatat keputusan teknis penting agar AI/developer berikutnya tidak mengulang perdebatan yang sama.

---

## Template Keputusan Baru

```md
## ADR-XXX — Judul keputusan

Status: diusulkan/diterima/ditolak.

Alasan:

- ...

Konsekuensi:

- ...
```

---

## ADR-001 — Menggunakan Laravel 12

Status: diterima.

Alasan:

- Laravel cocok untuk aplikasi administrasi madrasah.
- Struktur MVC mudah dipahami pemula.
- Ekosistem PDF, Excel, auth, migration, dan testing matang.

Konsekuensi:

- Developer harus memahami route, controller, model, migration, seeder, Blade, dan middleware Laravel.

---

## ADR-002 — Menggunakan Blade + Tailwind, bukan SPA berat

Status: diterima.

Alasan:

- Target shared hosting.
- Lebih mudah dipahami pemula.
- Tidak perlu build frontend rumit seperti React/Vue SPA.

Konsekuensi:

- Interaksi frontend kompleks sebaiknya dibuat bertahap.

---

## ADR-003 — Histori kelas siswa memakai `student_class_histories`

Status: diterima.

Alasan:

- Kelas siswa berubah setiap tahun/semester.
- Data lama harus tetap bisa dilacak.
- Modul rapor, absensi, jadwal, dan nilai butuh histori akademik.

Konsekuensi:

- Jangan menaruh `class_group_id` langsung di tabel `students` sebagai sumber utama kelas saat ini.
- Kelas saat ini dibaca dari `student_class_histories.is_current = true`.

---

## ADR-004 — RBAC custom sederhana

Status: diterima.

Alasan:

- Project butuh role dan permission yang bisa dipahami pemula.
- Custom RBAC membuat struktur data terlihat jelas.

Konsekuensi:

- Setiap permission baru harus ditambahkan ke `RbacSeeder`.
- Route admin harus diberi middleware permission.

---

## ADR-005 — File AI history hanya arsip, bukan dokumen kerja utama

Status: diterima.

Alasan:

- `SIM-MADRASAH-AI-HISTORY.md` terlalu besar untuk dibaca terus-menerus.
- AI baru lebih efektif membaca ringkasan handoff.

Konsekuensi:

- Setelah setiap tahap, update file di `docs/`.
- History boleh tetap disimpan, tetapi tidak wajib dibaca penuh.

---

## ADR-006 — Hindari teknologi berat untuk shared hosting

Status: diterima.

Alasan:

- Target deployment sederhana.
- Pemilik project masih pemula.

Dihindari sebagai dependency wajib:

- Docker,
- Redis,
- WebSocket,
- queue worker permanen,
- PostgreSQL-only,
- microservice.

---

## Export Data Siswa Tahap Awal Menggunakan CSV

Keputusan:

- Export data siswa tahap awal memakai format CSV.

Alasan:

- CSV tidak membutuhkan package tambahan.
- CSV ringan untuk data tabular.
- CSV mudah diuji melalui feature test.
- CSV tetap bisa dibuka di aplikasi spreadsheet umum.

Konsekuensi:

- Format tampilan tidak sefleksibel Excel.
- Jika nanti dibutuhkan format `.xlsx`, perlu tahap lanjutan dan kemungkinan package tambahan.

---

## Label Status pada Export Data Siswa

Keputusan:

- Status siswa pada export CSV ditampilkan sebagai label Indonesia.

Contoh:

- `active` menjadi `Aktif`.
- `inactive` menjadi `Nonaktif`.
- `transferred` menjadi `Pindah`.
- `graduated` menjadi `Lulus`.
- `alumni` menjadi `Alumni`.

Alasan:

- File CSV ditujukan untuk admin madrasah.
- Label Indonesia lebih mudah dibaca daripada nilai teknis database.
- Nilai teknis tetap disimpan di database, sedangkan label hanya dipakai pada tampilan export.

Konsekuensi:

- Test export harus memastikan CSV memakai label, bukan nilai mentah.
- Jika status baru ditambahkan nanti, daftar label harus ikut diperbarui.

---
## Guard Histori Kelas Mengikuti Constraint Database

Keputusan:

- Satu siswa hanya boleh memiliki satu histori kelas pada semester yang sama.
- Aturan memakai kombinasi `student_id` dan `semester_id`.
- Aturan ini mengikuti unique constraint yang sudah ada pada database.

Alasan:

- Database sudah menolak duplikasi `student_id` dan `semester_id`.
- Aplikasi harus memberi error validasi yang ramah sebelum database melempar error SQL.
- Bulk assignment siswa ke rombel harus mengikuti aturan ini.

Konsekuensi:

- Histori aktif maupun nonaktif tidak boleh dobel pada semester yang sama.
- Jika nanti ingin mengizinkan lebih dari satu histori dalam semester yang sama, perlu migration untuk mengubah constraint database.

## Bulk Assignment Siswa Mengikuti Guard dan Konteks Akademik

Keputusan:

- Bulk assignment siswa ke rombel membuat record `student_class_histories`.
- Histori aktif lama dinonaktifkan sebelum histori baru dibuat.
- Assignment ditolak jika siswa sudah memiliki histori kelas pada semester yang sama.
- Semester harus sesuai dengan tahun ajaran yang dipilih.
- Rombel harus sesuai dengan tahun ajaran yang dipilih.
- Tanggal mulai harus berada dalam rentang tanggal semester.

Alasan:

- Kelas siswa harus tetap berbasis histori.
- `currentClassHistory` membutuhkan satu histori aktif yang jelas.
- Guard semester dari Tahap 12.27 mencegah data ganda.
- Validasi konteks akademik mencegah kombinasi tahun ajaran, semester, rombel, dan tanggal yang tidak logis.

Konsekuensi:

- Fitur bulk assignment tidak menghapus histori lama.
- Assignment massal berhenti jika ada siswa yang melanggar guard semester.
- User tidak bisa menggabungkan tahun ajaran 2027/2028 dengan semester atau rombel 2026/2027.
- User tidak bisa memakai tanggal mulai di luar rentang semester.
- Jika nanti ingin mode skip siswa bermasalah, perlu tahap lanjutan.

---
## Semester Aktif Sistem Berbasis `semesters.is_active`

Keputusan:

- Semester aktif sistem ditentukan dari kolom `semesters.is_active`.
- Hanya satu semester yang boleh aktif pada satu waktu.
- Tahun ajaran dari semester aktif ikut ditandai aktif.
- Semester terkunci tidak boleh diaktifkan kembali.

Alasan:

- Struktur database sudah memiliki `status`, `is_active`, dan `is_locked` pada `academic_years` dan `semesters`.
- Proses akademik perlu satu acuan semester aktif.
- Bulk Rombel, Riwayat Kelas, Absensi, Nilai, dan Jadwal Pelajaran akan membutuhkan acuan semester aktif.

Konsekuensi:

- Menu Tahun Ajaran menjadi pusat aktivasi semester sistem.
- Fitur akademik berikutnya sebaiknya membaca semester aktif sebagai nilai awal.
- Penguncian penuh input berdasarkan semester aktif dilakukan bertahap.

---

## Semester Aktif Sebagai Default Form Akademik

Keputusan:

- Form riwayat kelas dan bulk rombel memakai semester aktif sistem sebagai nilai awal.
- Tanggal mulai pada bulk rombel diambil dari `start_date` semester aktif.
- Tanggal mulai tersinkronkan otomatis saat semester diganti di form.
- User masih bisa mengganti pilihan semester dan tanggal manual.
- Penguncian penuh input berdasarkan semester aktif belum diterapkan.

Alasan:

- Memudahkan input harian untuk pengguna admin.
- Mengurangi salah pilih semester dan tanggal.
- Menjaga flow lama tetap aman dan reversibel.
- Semester aktif sudah tersedia dari Tahap 12.30.
- Validasi masih berjalan normal, tidak ada perubahan business logic.

Konsekuensi:

- Form akademik lebih cepat diisi, namun user tetap punya fleksibilitas.
- JavaScript tambahan untuk sinkronisasi tanggal, tetapi tidak merusak validasi server-side.
- Jika nanti semester aktif diubah saat user mengedit form, nilai form tidak otomatis berubah (hanya saat halaman dibuka ulang).
- Tahap lanjutan bisa menambahkan penguncian penuh input jika semester aktif berubah di sistem.