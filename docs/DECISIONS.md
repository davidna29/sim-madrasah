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

## ADR-007 — `InitialAdminSeeder` Hanya Boleh Jalan di Environment Local

Status: diterima.

Alasan:

- `InitialAdminSeeder` membaca password administrator dari `.env` (`SIM_INITIAL_ADMIN_PASSWORD`),
  yang berisiko sama di banyak instalasi kalau nilai default tidak diganti.
- Administrator production sebaiknya dibuat sadar dan sengaja oleh operator, bukan otomatis lewat
  seeder yang bisa lupa dikonfigurasi ulang.
- Kode seeder ini melempar `RuntimeException` kecuali `app()->environment('local')`.

Konsekuensi:

- `docs/DEPLOYMENT.md` **tidak lagi** menyuruh menjalankan `InitialAdminSeeder` di production.
- Administrator awal production dibuat manual lewat `php artisan tinker`, langkahnya ada di
  `docs/DEPLOYMENT.md` bagian 5.1.
- Kalau nanti ingin proses ini lebih otomatis (misalnya command `artisan` khusus untuk production),
  perlu tahap lanjutan yang sengaja dirancang aman untuk production.

---

## ADR-008 — Paritas Lokal ↔ Production, Seeder Wajib vs Seeder Demo

Status: diterima.

Latar belakang:

- Saat deployment production pertama (`sim.misnu.sch.id`), halaman `/admin/madrasah` 404
  karena `MadrasahSeeder` belum pernah dijalankan.
- Saat ditelusuri, `DatabaseSeeder.php` (dipanggil `php artisan migrate --seed` sesuai
  `README.md`) ternyata hanya membuat satu user dummy, tidak memanggil `RbacSeeder`,
  `MadrasahSeeder`, atau seeder lain. Artinya bug yang sama akan terjadi juga di setup
  lokal baru manapun yang benar-benar mengikuti `README.md` dari nol — bukan cuma masalah
  hosting.

Keputusan:

- `DatabaseSeeder.php` diperbarui supaya `php artisan migrate --seed` menghasilkan aplikasi
  yang benar-benar bisa dipakai (role, permission, identitas madrasah, data referensi,
  data demo, dan administrator awal lewat `InitialAdminSeeder`).
- Seeder dibagi 3 kelompok, didokumentasikan di `docs/DEPLOYMENT.md` bagian 5.1:
  1. **Wajib** — struktur sistem, aman dan perlu dijalankan di production
     (`RbacSeeder`, `MadrasahSeeder`).
  2. **Opsional** — data referensi contoh, aman untuk production sebagai titik awal
     (`GradeLevelSeeder`, `RoomSeeder`, `SubjectSeeder`, `AcademicYearSeeder`).
  3. **Demo saja** — data fiktif (nama siswa/guru contoh, email `@sim-madrasah.test`),
     **tidak boleh** dijalankan di production
     (`ClassGroupSeeder`, `EmployeeSeeder`, `StudentSeeder`, `StudentGuardianSeeder`,
     `StudentClassHistorySeeder`).
- `.env.example` diberi nilai default `SIM_INITIAL_ADMIN_PASSWORD` supaya
  `php artisan migrate --seed` tidak error di setup lokal baru.
- Production **tidak pernah** memakai `php artisan migrate --seed` polos — selalu
  `php artisan migrate --force` diikuti seeder terpilih satu per satu (lihat
  `docs/DEPLOYMENT.md`).
- Prinsip umum ditambahkan ke `docs/AI-HANDOFF.md` bagian 3.1: sebelum menandai satu
  tahap selesai, bayangkan seseorang meng-clone repo dari nol dan mengikuti `README.md`
  persis apa adanya — kalau fitur baru butuh data yang tidak otomatis tersedia dari alur
  itu, seeder-nya harus dilengkapi atau didokumentasikan secara eksplisit.

Konsekuensi:

- Developer/AI berikutnya yang menambah controller dengan pola `Model::firstOrFail()`
  pada data singleton **wajib** memastikan seeder pembuatnya masuk kelompok "Wajib" di
  atas dan tercatat di `docs/DEPLOYMENT.md`.
- Menambah seeder baru berarti menambah entri baru ke tabel klasifikasi di
  `docs/DEPLOYMENT.md` bagian 5.1, bukan cuma menambah seeder ke `DatabaseSeeder.php`.
- Kalau nanti ada seeder wajib baru untuk modul lain (misalnya konfigurasi sistem lain
  yang sifatnya singleton), pola yang sama harus diikuti.

---

## ADR-009 — File Konfigurasi Server (`.htaccess`) Wajib Ikut Di-Commit ke Git

Status: diterima.

Latar belakang:

- Hosting production (`sim.misnu.sch.id`, Hostinger shared hosting) memakai fitur
  **Deploy Otomatis dari GitHub**: setiap `git push` ke branch `main` langsung disegarkan
  ke `public_html`.
- Paket hosting yang dipakai **tidak mendukung mengubah document root** ke folder
  `public/` Laravel — document root tetap `public_html` (root repo), sehingga dibutuhkan
  `.htaccess` di root repo yang mengarahkan semua request ke `public/index.php`.
- `.htaccess` sempat dibuat manual lewat File Manager Hostinger (tidak lewat Git). Setelah
  push berikutnya (commit `d400dcf`, perbaikan seeder), auto-deploy menyegarkan ulang
  `public_html` dan `.htaccess` manual itu **hilang**, situs kembali 403 Forbidden.
- File `.env` tidak ikut terhapus karena Hostinger secara khusus mengecualikan `.env` dari
  proses penyegaran auto-deploy. `.htaccess` tidak mendapat perlakuan khusus yang sama.

Keputusan:

- `.htaccess` di root repo (sejajar `composer.json`, bukan yang bawaan Laravel di dalam
  `public/`) **wajib ikut di-commit ke Git**, bukan dibuat manual di server.
- Prinsip umum: di hosting dengan auto-deploy dari Git, **apapun yang perlu bertahan**
  di server (file konfigurasi web server, dsb.) harus masuk repo. Perubahan manual lewat
  File Manager hanya aman untuk `.env` dan file yang secara eksplisit dikecualikan
  provider hosting dari proses auto-deploy.

Konsekuensi:

- Kalau nanti ada konfigurasi server lain yang dibutuhkan (misalnya `robots.txt` khusus,
  header keamanan tambahan), lakukan lewat file yang di-commit ke repo, bukan diedit
  langsung di server.
- Sebelum menyimpulkan sebuah perubahan server "sudah beres", cek dulu apakah perubahan
  itu bertahan setelah deploy berikutnya, bukan cuma langsung setelah dibuat.

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