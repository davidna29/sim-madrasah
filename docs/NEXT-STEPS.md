# Next Steps — SIM Madrasah

Dokumen ini berisi urutan kerja kecil yang direkomendasikan.

---

## Prioritas Terdekat

### Tahap 12.34D — Assignment Rombel ke Template Jadwal

Status: selesai.

Tujuan yang tercapai:

- Menambahkan route admin untuk Assignment Template Jadwal. ✓
- Menambahkan controller `ScheduleTemplateAssignmentController`. ✓
- Menambahkan halaman daftar Assignment Template Jadwal. ✓
- Menambahkan halaman tambah Assignment Template Jadwal. ✓
- Menambahkan form partial Assignment Template Jadwal. ✓
- Menambahkan filter tahun ajaran dan semester. ✓
- Menambahkan fitur assign rombel ke template jadwal. ✓
- Menambahkan validasi konflik assignment rombel. ✓
- Menambahkan opsi replace assignment lama. ✓
- Menambahkan fitur release/lepas assignment. ✓
- Menambahkan menu sidebar Assignment Jadwal. ✓
- Menambahkan test Assignment Template Jadwal. ✓

Catatan proteksi:

- Satu rombel hanya boleh memiliki satu template jadwal pada semester yang sama.
- Assignment baru ditolak jika rombel sudah punya template dan opsi replace tidak dicentang.
- Template harus aktif sebelum dipakai rombel.
- Template harus sudah memiliki slot sebelum dipakai rombel.
- Semester dan rombel harus sesuai dengan tahun ajaran yang dipilih.

Belum dikerjakan:

- Jadwal aktual pelajaran.
- Plotting Beban Mengajar.
- Ketersediaan Guru.
- Validasi konflik guru.
- Lock/Pin Slot.
- Auto-Generate.
- Unassigned Pool.
- Drag-and-Drop.

Tahap berikutnya:

- Tahap 12.34E — Fondasi Jadwal Pelajaran Aktual atau Modul Prasyarat Plotting Beban Mengajar.

Rekomendasi:

- Sebelum membuat auto-generate, sebaiknya buat Modul Plotting Beban Mengajar dan Ketersediaan Guru terlebih dahulu.
- Jika ingin mulai dari jadwal manual, tahap berikutnya dapat membuat tabel jadwal aktual pelajaran dan halaman grid manual basic.

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
