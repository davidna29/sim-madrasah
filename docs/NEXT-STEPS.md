# Next Steps — SIM Madrasah

Dokumen ini berisi urutan kerja kecil yang direkomendasikan.

---

## Prioritas Terdekat

### Tahap 12.34E — Fondasi Database Plotting Beban Mengajar

Status: selesai.

Tujuan yang tercapai:

- Menambahkan tabel `teaching_assignments`. ✓
- Menambahkan model `TeachingAssignment`. ✓
- Menambahkan relasi ke `AcademicYear`. ✓
- Menambahkan relasi ke `Semester`. ✓
- Menambahkan relasi ke `ClassGroup`. ✓
- Menambahkan relasi ke `Subject`. ✓
- Menambahkan relasi ke `User` sebagai guru. ✓
- Menambahkan relasi ke `User` sebagai pembuat data. ✓
- Menambahkan unique constraint agar plotting yang sama tidak dobel. ✓
- Menambahkan test fondasi database Plotting Beban Mengajar. ✓

Catatan:

- Plotting beban mengajar belum menjadi jadwal aktual.
- Plotting hanya menyimpan guru, mapel, rombel, semester, dan jumlah jam per minggu.
- Data ini akan menjadi sumber untuk jadwal manual dan auto-generate.

Belum dikerjakan:

- CRUD Plotting Beban Mengajar.
- Validasi UI Plotting Beban Mengajar.
- Rekap beban guru.
- Ketersediaan Guru.
- Jadwal Aktual Pelajaran.
- Validasi konflik guru.
- Auto-Generate.
- Unassigned Pool.
- Drag-and-Drop.

Tahap berikutnya:

- Tahap 12.34F — CRUD Plotting Beban Mengajar.

---

### Tahap 12.31 — Integrasi Semester Aktif ke Riwayat Kelas dan Bulk Rombel
Status: selesai.
- Tahap 12.32 — Koreksi dan Edit Riwayat Kelas Siswa. Status: selesai.
- Tahap 12.33 — Kebijakan Nonaktif dan Koreksi Data Master. Selesai (cakupan disesuaikan — lihat `docs/DECISIONS.md`).
- Tahap 12.34 — Awal Modul Jadwal Pelajaran.

---

### Tahap 12.28 — Bulk Assignment Siswa ke Rombel

Tujuan:

- Memilih banyak siswa.
- Menempatkan siswa ke rombel tertentu pada tahun ajaran dan semester tertentu.
- Membuat record `student_class_histories`.
- Mengikuti guard histori kelas dari Tahap 12.27.
- Menolak assignment jika siswa sudah memiliki histori kelas pada semester yang sama.

Perhatian:

- Jangan hapus histori lama.
- Wajib validasi satu siswa tidak boleh punya dua record aktif untuk semester yang sama.
- Fitur ini dikerjakan setelah guard histori kelas aktif aman.

---

### Tahap 12.29 / 12.34 — Awal Modul Jadwal Pelajaran

Status: mulai dikerjakan melalui Tahap 12.34A.

Catatan:

- Penomoran terbaru mengikuti handoff terakhir, yaitu Tahap 12.34.
- Fondasi database awal sudah selesai pada Tahap 12.34A.
- Pengembangan berikutnya dilanjutkan ke CRUD Template Jadwal.

---

## Backlog Modul Setelah Data Siswa Stabil

| Prioritas | Modul | Catatan |
|---:|---|---|
| 1 | Jadwal Pelajaran | Butuh guru, mapel, rombel, semester |
| 2 | Absensi Siswa | Butuh rombel aktif dan kalender akademik |
| 3 | Nilai | Butuh mapel, guru, siswa, semester |
| 4 | Rapor | Butuh nilai, absensi, ekstrakurikuler |
| 5 | PPDB | Bisa terpisah dari siswa aktif |
| 6 | Keuangan | Tagihan, pembayaran, kwitansi |
| 7 | Surat Menyurat | Nomor surat, template, arsip |
| 8 | Arsip Digital | Upload, kategori, permission |
| 9 | PKKM | Indikator, bukti, laporan |
| 10 | Akreditasi | Instrumen, dokumen bukti, monitoring |

---

### Usulan — Buka Kunci (Unlock) Semester dan Tahun Ajaran

Tujuan:

- Menyediakan cara resmi membuka kunci semester/tahun ajaran yang terlanjur dikunci secara keliru.

Catatan penting sebelum dikerjakan:

- Saat ini `lockSemester()` sengaja tidak punya pasangan `unlockSemester()` — ini keputusan desain, bukan celah yang lupa dibuat. Lihat `docs/ARCHITECTURE.md` §7 (prinsip jangan ubah histori data lama) dan `docs/DECISIONS.md` (semester terkunci tidak boleh diaktifkan kembali).
- Kalau dikerjakan, sebaiknya tidak sekadar tombol biasa:
  - Permission terpisah dan lebih ketat (misal `academic_years.unlock`), bukan numpang ke `academic_years.update`.
  - Dicatat siapa yang membuka kunci, kapan, dan kenapa (audit trail) — mirip `locked_by`/`locked_at` yang sudah ada, tapi versi unlock.
  - Pertimbangkan apakah perlu alasan wajib diisi saat unlock.
- Belum ada urgensi mendesak — dicatat di sini supaya tidak terlupa, bukan berarti harus segera dikerjakan.

--- 

## Prompt untuk Memulai Tahap Berikutnya

```txt
Lanjutkan project SIM Madrasah.
Baca dulu README.md, AI-INSTRUCTIONS.md, docs/AI-HANDOFF.md, docs/PROGRESS.md, docs/NEXT-STEPS.md, docs/ARCHITECTURE.md, docs/DATABASE.md, dan docs/RBAC.md.

Kerjakan Tahap xx.xx — keterangan detail projek.
Jangan melompat tahap.
Jelaskan konsep dulu, lalu beri perubahan kode per file.
Setelah selesai, berikan update dokumentasi untuk docs/AI-HANDOFF.md, docs/PROGRESS.md, docs/NEXT-STEPS.md, dan docs/CHANGELOG.md.
```
