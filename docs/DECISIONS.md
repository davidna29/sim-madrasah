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