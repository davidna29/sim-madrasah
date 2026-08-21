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

## ADR-010 — Syarat Minimum PHP Dinaikkan dari `^8.2` ke `^8.4`

Status: diterima.

Latar belakang:

- `composer.json` dan seluruh dokumentasi menyatakan syarat minimum `PHP ^8.2`.
- Saat deployment production pertama, `php artisan key:generate` gagal dijalankan memakai
  PHP 8.2.30 (versi default CLI Hostinger) dengan pesan:
  `Your Composer dependencies require a PHP version ">= 8.4.1". You are running 8.2.30.`
- Penyebabnya: `vendor/` yang sebenarnya terpasang dibangun di Laravel Herd (lokal) yang
  memakai PHP 8.4. Karena `composer.json` tidak mengunci versi platform, `composer update`
  di Herd memilih versi terbaru dependency yang kompatibel dengan PHP 8.4 — dan sebagian
  dependency itu ternyata mensyaratkan PHP 8.4.1 ke atas untuk dijalankan, meski
  `composer.json` root masih menulis `^8.2`.
- Ini adalah pelanggaran terselubung dari prinsip Paritas Lokal ↔ Production (ADR-008):
  dokumentasi menjanjikan PHP 8.2 cukup, padahal `composer.lock` yang sesungguhnya dipakai
  butuh PHP 8.4+.

Keputusan:

- `composer.json` (`require.php`) diubah dari `^8.2` menjadi `^8.4`, mengikuti kenyataan
  environment yang benar-benar dipakai (Laravel Herd lokal dan Hostinger production
  sama-sama PHP 8.4).
- Semua dokumentasi (`README.md`, `docs/AI-HANDOFF.md`, `docs/DEPLOYMENT.md`) diperbarui
  mengikuti perubahan ini.

Konsekuensi:

- Setup lokal baru wajib memakai PHP 8.4 ke atas, bukan 8.2.
- Kalau di kemudian hari sengaja ingin mendukung PHP 8.2 lagi, perlu verifikasi ulang
  seluruh dependency di `composer.lock` benar-benar kompatibel dengan 8.2 (bukan cuma
  mengubah angka di `composer.json`).
- Prinsip umum: kalau ada perbedaan antara apa yang dituliskan `composer.json`/dokumentasi
  dan apa yang sungguhan dibutuhkan `vendor/`, dokumentasi harus disesuaikan dengan
  kenyataan `vendor/`, bukan sebaliknya — karena `vendor/` adalah yang benar-benar
  dieksekusi di production.

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

---
## Edit Riwayat Kelas Siswa Tanpa Fitur Hapus

Keputusan:

- Histori kelas siswa bisa dikoreksi/diedit lewat form edit.
- Tidak ada fitur hapus histori kelas siswa pada tahap ini.
- `assigned_by` tidak diperbarui saat histori diedit.

Alasan:

- `ARCHITECTURE.md` §7 menegaskan histori kelas lama tidak boleh dihapus.
- Kebutuhan paling umum adalah koreksi kesalahan input (rombel/tanggal/status salah), bukan penghapusan.
- Fitur hapus berisiko konflik dengan modul masa depan yang akan bergantung pada histori kelas (Absensi, Nilai, Jadwal Pelajaran) — akan dipertimbangkan terpisah kalau benar-benar dibutuhkan.

Konsekuensi:

- Kesalahan fatal pada histori kelas untuk saat ini diatasi lewat edit, bukan hapus-lalu-buat-ulang.
- Kalau suatu saat fitur hapus dibutuhkan, harus jadi tahap terpisah dengan aturan main sendiri (misalnya hanya boleh hapus histori yang `is_current = false` dan tidak dipakai modul lain).

---
## Urutan Seeder: InitialAdminSeeder Dipindah ke Awal

Keputusan:

- `InitialAdminSeeder` dipindah dari posisi terakhir menjadi seeder **pertama** yang dijalankan di `DatabaseSeeder.php`.

Alasan:

- Ditemukan bug: `RbacSeeder` mencari akun `superadmin` (lewat `username`) untuk diberi role `super_admin`. Karena `InitialAdminSeeder` sebelumnya dijalankan paling akhir, akun itu belum ada saat `RbacSeeder` jalan, sehingga pemberian role gagal secara diam-diam (tidak error, cuma dilewati).
- Akibatnya: instalasi baru lewat `php artisan migrate --seed` menghasilkan akun `superadmin` yang bisa login tapi tidak punya role apapun — ditolak akses (403) ke seluruh halaman admin.
- Bug ini tidak terdeteksi oleh test suite karena test membuat user dan role secara manual, tidak lewat urutan `DatabaseSeeder` yang sesungguhnya.

Konsekuensi:

- Instalasi baru sekarang otomatis mendapat akun `superadmin` dengan role `super_admin` terpasang dengan benar.
- Perlu diingat ke depannya: kalau ada seeder lain yang mencari user berdasarkan data dari seeder lain, urutan `$this->call([...])` di `DatabaseSeeder.php` harus benar-benar diperhatikan.
---
## Cakupan Tahap 12.33 Disesuaikan Saat Pengerjaan

Keputusan:

- Fitur nonaktif dibatasi ke 3 modul sederhana: Ruangan, Mata Pelajaran, Tingkat Kelas.
- Rombongan Belajar, Siswa, Pegawai ditunda karena punya field `status`/`employment_status` terpisah dari `is_active`.
- Tombol nonaktif berbentuk aksi per baris di daftar, bukan checklist pilih banyak sekaligus.
- Edit Tahun Ajaran dan Semester ditambahkan sebagai bagian "koreksi data master" karena ditemukan gap nyata: sebelumnya tidak ada jalur edit sama sekali untuk keduanya.
- Tahun Ajaran/Semester yang `is_locked = true` tidak bisa diedit oleh siapapun, termasuk superadmin.
- Fitur buka kunci (unlock) sengaja tidak dibuat di tahap ini — dicatat sebagai usulan terpisah di `docs/NEXT-STEPS.md`.
- Soft delete (`deleted_at`) untuk data master ditunda seluruhnya.

Alasan:

- Modul dengan field status ganda (Rombongan Belajar/Siswa/Pegawai) butuh keputusan bisnis tersendiri: apakah nonaktifkan mengubah `is_active` saja atau `status` juga — supaya tidak buru-buru salah desain.
- Gap edit Tahun Ajaran/Semester ditemukan lewat pengecekan langsung ke seluruh controller admin sebelum tahap ini dimulai.
- Prinsip "jangan ubah data terkunci" (lihat semester lock di Tahap 12.30) diterapkan konsisten ke fitur edit baru ini.

---
## ADR-010 — Modul Jadwal Pelajaran Dibangun Bertahap dari Fondasi Database

Status: diterima.

Keputusan:

- Modul Jadwal Pelajaran dibangun bertahap, tidak langsung membuat auto-generate dan drag-and-drop.
- Tahap 12.34A dibatasi pada fondasi database:
  - `schedule_templates`;
  - `schedule_template_slots`;
  - `class_group_schedule_templates`.
- CRUD Template Jadwal, CRUD Slot Template, Assignment Rombel, Jadwal Manual, Lock/Pin Slot, Auto-Generate, Unassigned Pool, dan Drag-and-Drop dikerjakan pada tahap lanjutan.
- Slot non-KBM tetap disimpan dalam template, tetapi ditandai dengan `is_teaching_slot = false`.
- Satu rombel hanya boleh memiliki satu assignment template pada kombinasi tahun ajaran dan semester yang sama.

Alasan:

- Modul Jadwal Pelajaran akan menjadi modul besar dan berisiko tinggi jika langsung dibuat penuh.
- Kebutuhan pengguna mengarah pada sistem hybrid yang robust: auto-generate, manual finishing, validasi konflik, dan lock/pin slot.
- Fondasi database harus stabil dulu sebelum UI dan algoritma dibuat.
- Pemisahan template, slot, dan assignment rombel membuat jadwal lebih fleksibel untuk kebijakan khusus madrasah.

Konsekuensi:

- Tahap awal belum menghasilkan halaman yang bisa dipakai langsung oleh admin.
- Tahap lanjutan harus mengikuti urutan kecil agar mudah diuji.
- Auto-generate nanti wajib menghormati slot non-KBM dan slot yang dikunci.
- Jika auto-generate gagal menempatkan jam tertentu, desain lanjutan harus menyediakan unassigned pool agar sistem tidak error atau infinite loop.
---
---
## ADR-011 — CRUD Template Jadwal Dibuat Terproteksi Sebelum Slot dan Assignment

Status: diterima.

Keputusan:

- CRUD Template Jadwal dibuat sebelum CRUD Slot Template dan Assignment Rombel.
- Template Jadwal aktif tidak boleh dihapus.
- Template Jadwal yang sudah memiliki assignment rombel tidak boleh dihapus.
- Fitur duplicate/clone disediakan agar admin dapat membuat variasi template tanpa mengulang dari nol.
- Template hasil clone dibuat `draft` dan `is_active = false`.
- Clone template ikut menyalin slot yang sudah ada agar fitur ini tetap berguna setelah Slot Template dibuat.

Alasan:

- Template Jadwal adalah fondasi sebelum slot, assignment rombel, jadwal manual, dan auto-generate.
- Delete tanpa proteksi berisiko merusak jadwal rombel yang sudah memakai template tertentu.
- Clone template mengurangi pekerjaan admin saat madrasah memiliki pola jadwal mirip dengan sedikit penyesuaian.
- Template hasil clone tidak langsung aktif agar admin sempat meninjau ulang sebelum dipakai.

Konsekuensi:

- Admin harus menonaktifkan template sebelum dapat menghapusnya.
- Template yang sudah pernah dipakai rombel tidak dapat dihapus langsung.
- Tahap berikutnya harus membuat CRUD Slot Template agar template dapat memiliki detail jam per hari.
---
---
## ADR-012 — Slot Template Jadwal Dikunci Setelah Dipakai Rombel

Status: diterima.

Keputusan:

- Slot Template Jadwal dibuat setelah CRUD Template Jadwal selesai.
- Slot hanya boleh dibuat pada hari aktif template.
- Slot tidak boleh memiliki nomor urut dobel pada hari yang sama.
- Slot tidak boleh memiliki rentang waktu yang bertabrakan pada hari yang sama.
- Slot `kbm` otomatis ditandai sebagai `is_teaching_slot = true`.
- Slot selain `kbm` otomatis ditandai sebagai `is_teaching_slot = false`.
- Slot template tidak boleh ditambah, diedit, atau dihapus jika template sudah dipakai oleh rombel.
- Tahap ini tidak menambah permission baru dan memakai permission `schedule_templates`.

Alasan:

- Slot template adalah pola waktu dasar sebelum rombel memakai template tersebut.
- Perubahan slot setelah template dipakai rombel dapat mengubah jadwal rombel secara diam-diam.
- Validasi urutan dan waktu diperlukan agar template tidak memiliki jam yang ambigu.
- Pemisahan slot KBM dan non-KBM dibutuhkan agar modul jadwal lanjutan tidak menempatkan mata pelajaran pada istirahat, upacara, atau kegiatan rutin.

Konsekuensi:

- Admin harus menyelesaikan slot template sebelum melakukan assignment rombel.
- Jika template sudah dipakai rombel, perubahan slot harus ditangani melalui mekanisme lanjutan, bukan edit langsung.
- Tahap berikutnya dapat fokus pada Assignment Rombel ke Template karena template dan slot sudah tersedia.
---
---
## ADR-013 — Assignment Rombel ke Template Jadwal Memakai Validasi Konflik dan Replace Eksplisit

Status: diterima.

Keputusan:

- Assignment rombel ke template jadwal dibuat setelah Template Jadwal dan Slot Template tersedia.
- Satu rombel hanya boleh memiliki satu template jadwal pada kombinasi tahun ajaran dan semester yang sama.
- Jika rombel sudah memiliki assignment, sistem menolak assignment baru secara default.
- Admin dapat mengganti assignment lama dengan mencentang opsi `replace_existing`.
- Template yang dapat dipilih harus aktif.
- Template yang dapat dipilih harus sudah memiliki slot.
- Semester harus sesuai dengan tahun ajaran yang dipilih.
- Rombel harus sesuai dengan tahun ajaran yang dipilih.
- Release assignment dilakukan dengan menghapus record assignment.
- Tahap ini tidak menambah permission baru dan tetap memakai permission `schedule_templates`.

Alasan:

- Assignment adalah titik penghubung antara pola jadwal dan rombel.
- Konflik assignment harus dicegah agar satu rombel tidak memiliki dua pola jadwal dalam semester yang sama.
- Replace dibuat eksplisit agar admin sadar ketika mengganti template rombel.
- Template tanpa slot tidak boleh dipakai karena belum memiliki struktur jam.
- Template nonaktif tidak boleh dipakai agar admin tidak menggunakan template yang belum siap.

Konsekuensi:

- Admin harus menyiapkan Template Jadwal dan Slot Template sebelum melakukan assignment.
- Jika ingin mengganti template rombel, admin harus memilih opsi replace.
- Slot template akan terkunci setelah template dipakai rombel karena perubahan slot dapat memengaruhi jadwal rombel.
- Tahap berikutnya perlu menentukan apakah melanjutkan ke jadwal aktual manual atau membuat modul prasyarat seperti Plotting Beban Mengajar dan Ketersediaan Guru.
---
---
## ADR-014 — Plotting Beban Mengajar Dibuat Sebelum Jadwal Aktual

Status: diterima.

Keputusan:

- Modul Plotting Beban Mengajar dibuat sebelum Jadwal Aktual Pelajaran.
- Tahap 12.34E dibatasi pada fondasi database dan model relasi.
- Plotting beban mengajar disimpan pada tabel `teaching_assignments`.
- Data plotting menyimpan guru, mapel, rombel, tahun ajaran, semester, dan jumlah jam per minggu.
- Satu kombinasi tahun ajaran, semester, rombel, mapel, dan guru tidak boleh dobel.
- Plotting beban mengajar belum menjadi jadwal harian.

Alasan:

- Jadwal aktual membutuhkan sumber data yang jelas.
- Auto-generate tidak bisa berjalan benar tanpa data guru mengajar mapel apa di rombel mana.
- Validasi konflik guru juga membutuhkan daftar beban mengajar sebagai sumber.
- Pemisahan plotting dan jadwal aktual membuat sistem lebih fleksibel untuk kebijakan madrasah.
- Satu mapel pada rombel yang sama tetap dapat dibagi ke lebih dari satu guru jika guru berbeda.

Konsekuensi:

- Tahap berikutnya perlu membuat CRUD Plotting Beban Mengajar.
- Jadwal manual dan auto-generate harus mengambil data dari `teaching_assignments`.
- Ketersediaan guru tetap perlu dibuat sebagai modul terpisah sebelum auto-generate penuh.
- Validasi konflik guru juga membutuhkan daftar beban mengajar sebagai sumber.
- Pemisahan plotting dan jadwal aktual membuat sistem lebih fleksibel untuk kebijakan madrasah.
- Satu mapel pada rombel yang sama tetap dapat dibagi ke lebih dari satu guru jika guru berbeda.

Konsekuensi:

- Tahap berikutnya perlu membuat CRUD Plotting Beban Mengajar.
- Jadwal manual dan auto-generate harus mengambil data dari `teaching_assignments`.
- Ketersediaan guru tetap perlu dibuat sebagai modul terpisah sebelum auto-generate penuh.
- Rekap beban guru dapat dibuat dari tabel ini pada tahap lanjutan.

---
## ADR-015 — CRUD Plotting Beban Mengajar Dipecah per Sub-Tahap, Status Dikelola Lewat is_active

Status: diterima.

Keputusan:

- Tahap 12.34F (CRUD Plotting Beban Mengajar) dipecah menjadi sub-tahap kecil: 12.34F-1 (permission + route + halaman daftar), 12.34F-2 (form tambah), 12.34F-3 (form edit + toggle aktif/nonaktif), 12.34F-4 (rekap beban guru).
- Permission `teaching_assignments.view`, `.create`, dan `.update` didaftarkan sekaligus di Tahap 12.34F-1, meskipun `.create` dan `.update` baru dipasang ke route pada sub-tahap berikutnya — mengikuti pola yang sama seperti modul `schedule_templates`.
- Kolom `status` pada `teaching_assignments` tidak diberi input manual di form; nilainya otomatis `'active'` saat data dibuat. Aktif/nonaktif dikelola lewat kolom `is_active`, konsisten dengan pola modul lain (Mata Pelajaran, Template Jadwal).
- Guru yang bisa dipilih pada form plotting (direncanakan untuk 12.34F-2) mencakup role `guru_mata_pelajaran`, `wali_kelas`, dan `guru_bk` — bukan hanya `guru_mata_pelajaran` — karena di praktik banyak madrasah, wali kelas dan guru BK juga mengajar mapel tertentu.

Alasan:

- Modul ini pemula-friendly jika dipecah kecil, sesuai prinsip project (lihat `AI-INSTRUCTIONS.md`).
- Mendaftarkan permission sekaligus menghindari perlu mengubah seeder berkali-kali untuk modul yang sama.
- Field `status` bertipe teks bebas berisiko ambigu kalau diisi manual tanpa pilihan yang jelas; menunda ini ke tahap lanjutan (kalau memang dibutuhkan) lebih aman daripada menebak nilai yang valid sekarang.
- Membatasi guru hanya ke `guru_mata_pelajaran` akan membuat wali kelas dan guru BK yang juga mengajar tidak bisa diplot, padahal secara riil banyak yang merangkap.

Konsekuensi:

- Tahap 12.34F-2 harus mengimplementasikan filter role saat mengambil daftar user untuk dropdown guru.
- Jika suatu saat dibutuhkan status selain `active`/nonaktif (misal `draft`, `dibatalkan`), perlu ADR baru untuk mendesain form dan migrasi datanya.
- `.create` dan `.update` yang belum dipasang ke route tidak boleh dianggap permission menganggur yang perlu dihapus — lihat catatan di `docs/RBAC.md`.

---
## Catatan Dokumentasi — docs/CHANGELOG.md Ada di GitHub, Tetapi Pernah Tidak Terbawa di ZIP

Saat Tahap 12.34F-1 dikerjakan, `docs/CHANGELOG.md` dirujuk oleh `README.md` dan riwayat `docs/AI-HANDOFF.md`, tetapi file ini tidak ada di repo hasil unggahan ZIP pada sesi tersebut.

Pada sesi Tahap 12.34F-4, developer mengonfirmasi bahwa file `docs/CHANGELOG.md` ada di GitHub dan mengunggah salinannya. Mulai tahap ini, changelog tetap diperlakukan sebagai dokumentasi wajib yang harus diperbarui sebelum commit.

Catatan untuk AI/developer berikutnya:

- Jika bekerja dari ZIP dan `docs/CHANGELOG.md` tidak ada, cek GitHub atau histori git dulu.
- Jangan membuat changelog baru dari nol kalau file aslinya masih ada di remote.
- Jika file hilang karena hasil download/export ZIP, pulihkan dari GitHub sebelum melanjutkan dokumentasi tahap.

---
## ADR-016 — Validasi Duplikat Plotting Dilakukan di Controller, Bukan Hanya di Database

Status: diterima.

Keputusan:

- Validasi duplikasi kombinasi `academic_year_id + semester_id + class_group_id + subject_id + teacher_user_id` dilakukan secara eksplisit di method `store()` controller sebelum `create()`, bukan hanya mengandalkan unique constraint database.

Alasan:

- Pesan error dari unique constraint database tidak ramah pengguna (exception mentah).
- Validasi di controller memungkinkan pesan error yang jelas dan spesifik ditampilkan di form.
- Pola ini konsisten dengan modul lain di project ini yang juga melakukan cek duplikat di controller.

Konsekuensi:

- Unique constraint di database tetap dipertahankan sebagai lapisan keamanan kedua.
- Method `store()` perlu mempertahankan cek duplikat ini saat diedit di tahap berikutnya (edit plotting).
---
---

## ADR-017 — Rekap Beban Guru Menghitung Plotting Aktif Saja

Status: diterima.

Keputusan:

- Rekap Beban Guru dibuat dari tabel `teaching_assignments`, bukan dari tabel jadwal aktual.
- Rekap hanya menghitung data dengan `is_active = true`.
- Total beban guru dihitung dari penjumlahan `weekly_hours` per guru.
- Halaman rekap memakai permission `teaching_assignments.view`, bukan permission baru.
- Tahap ini tidak menambahkan validasi maksimal jam mengajar guru.

Alasan:

- Rekap ini adalah ringkasan administratif dari plotting yang sudah ada, belum jadwal harian.
- Plotting nonaktif dianggap tidak berlaku, sehingga tidak boleh ikut total beban.
- Permission `teaching_assignments.view` sudah cukup karena halaman rekap hanya membaca data, tidak mengubah data.
- Validasi maksimal jam guru membutuhkan aturan kebijakan madrasah yang belum ditentukan, jadi tidak ditebak pada tahap ini.

Konsekuensi:

- Jika nanti dibuat batas maksimal beban guru, perlu tahap lanjutan dengan aturan yang jelas.
- Jika nanti jadwal aktual sudah ada, rekap dari plotting dan rekap dari jadwal aktual bisa berbeda fungsi dan perlu dibedakan.
- Ketersediaan guru tetap perlu dibuat sebagai modul terpisah sebelum auto-generate jadwal.
---
---

## ADR-018 — Ketersediaan Guru Disimpan Per Tahun Ajaran dan Semester

Status: diterima.

Keputusan:

- Ketersediaan guru disimpan di tabel `teacher_availabilities`.
- Setiap data ketersediaan terikat ke `academic_year_id` dan `semester_id`.
- Guru yang dimaksud disimpan sebagai `teacher_user_id`, mengarah ke tabel `users`.
- Hari disimpan sebagai angka `day_of_week`:
  - 1 = Senin
  - 2 = Selasa
  - 3 = Rabu
  - 4 = Kamis
  - 5 = Jumat
  - 6 = Sabtu
  - 7 = Minggu
- Rentang waktu disimpan dengan `starts_at` dan `ends_at`.
- Tipe default saat ini adalah `unavailable`.
- Tahap 12.34G-1 belum menambahkan UI, route, controller, sidebar, atau permission.

Alasan:

- Jadwal pelajaran berjalan dalam konteks tahun ajaran dan semester, sehingga ketersediaan guru juga harus mengikuti konteks akademik yang sama.
- Menyimpan guru sebagai `teacher_user_id` konsisten dengan `teaching_assignments`.
- Format angka untuk hari lebih mudah dipakai untuk query dan validasi jadwal.
- Memulai dari tipe `unavailable` menjaga cakupan tetap kecil dan aman.

Konsekuensi:

- Kalau nanti dibutuhkan tipe lain seperti `preferred` atau `available`, nilai `availability_type` sudah bisa menampungnya tanpa perubahan struktur awal.
- Validasi bentrok jam perlu dibuat di level aplikasi pada tahap CRUD.
- Integrasi ke jadwal aktual perlu tahap tersendiri.
---
---

## ADR-019 — Permission Ketersediaan Guru Ditambahkan Bertahap

Status: diterima.

Keputusan:

- Pada Tahap 12.34G-2 hanya permission `teacher_availabilities.view` yang ditambahkan.
- Permission `teacher_availabilities.create`, `teacher_availabilities.update`, dan permission lain belum ditambahkan.
- Halaman daftar Ketersediaan Guru memakai permission `teacher_availabilities.view`.
- Menu sidebar Ketersediaan Guru juga memakai permission `teacher_availabilities.view`.

Alasan:

- Tahap 12.34G-2 hanya membuat halaman daftar, belum membuat form tambah/edit.
- Menambahkan permission yang belum dipakai pernah menyebabkan kebingungan di tahap sebelumnya.
- Permission baru sebaiknya ditambahkan ketika route/fitur yang memakainya juga dibuat.

Konsekuensi:

- Tahap form tambah nanti perlu menambahkan permission `teacher_availabilities.create`.
- Tahap edit/toggle nanti perlu menambahkan permission `teacher_availabilities.update`.
- Developer/AI berikutnya wajib cek dulu permission yang sudah ada sebelum menambah permission baru.
---
---

## ADR-020 — Form Tambah Ketersediaan Guru Dibuat Sebelum Edit dan Toggle

Status: diterima.

Keputusan:

- Tahap 12.34G-3 hanya membuat form tambah dan proses simpan Ketersediaan Guru.
- Permission yang ditambahkan hanya `teacher_availabilities.create`.
- Permission `teacher_availabilities.update` belum ditambahkan.
- Validasi duplikasi pada tahap ini hanya menolak data aktif yang sama persis.
- Validasi bentrok jam overlap belum dibuat pada tahap ini.

Alasan:

- Cakupan dibuat kecil agar mudah diuji dan dipahami.
- Permission baru hanya ditambahkan ketika route yang memakainya sudah dibuat.
- Validasi overlap membutuhkan aturan tambahan dan sebaiknya dikerjakan setelah CRUD dasar stabil.

Konsekuensi:

- Tahap berikutnya perlu membuat edit dan toggle aktif/nonaktif.
- Validasi overlap seperti 07:00-09:00 bentrok dengan 08:00-10:00 perlu tahap terpisah.
- Integrasi ketersediaan guru ke jadwal aktual belum dilakukan.