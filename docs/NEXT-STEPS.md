# Next Steps — SIM Madrasah

Dokumen ini berisi urutan kerja kecil yang direkomendasikan.

---

## Prioritas Terdekat

### Tahap 12.27 — Guard Histori Kelas Aktif

Tujuan:

- Membuat aturan aplikasi agar satu siswa tidak memiliki dua histori kelas aktif pada semester yang sama.
- Validasi dilakukan pada proses tambah histori kelas siswa.
- Jika siswa sudah memiliki histori aktif pada semester yang sama, sistem harus menolak input baru.
- Histori lama tidak boleh dihapus otomatis pada tahap ini.

File kemungkinan berubah:

- `app/Http/Controllers/Admin/StudentClassHistoryController.php`
- `tests/Feature/Admin/StudentClassHistoryTest.php`
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`

Catatan:

- Tidak membuat migration baru pada tahap awal.
- Tidak membuat permission baru.
- Jika nanti dibutuhkan constraint database, itu dibuat pada tahap terpisah.

---

### Tahap 12.28 — Bulk Assignment Siswa ke Rombel

Tujuan:

- Memilih banyak siswa.
- Menempatkan siswa ke rombel tertentu pada tahun ajaran dan semester tertentu.
- Membuat record `student_class_histories`.
- Menonaktifkan histori aktif lama jika diperlukan.
- Mengikuti guard histori kelas aktif yang sudah dibuat pada Tahap 12.27.

Perhatian:

- Jangan hapus histori lama.
- Wajib validasi satu siswa tidak boleh punya dua record aktif untuk semester yang sama.
- Fitur ini dikerjakan setelah guard histori kelas aktif aman.

---

### Tahap 12.29 — Awal Modul Jadwal Pelajaran

Tujuan:

- Memulai modul Jadwal Pelajaran setelah modul Data Siswa dan histori kelas stabil.
- Modul ini membutuhkan guru, mata pelajaran, rombel, tahun ajaran, dan semester.
- Tahap awal harus dimulai dari desain database dan relasi, bukan langsung UI.

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

## Prompt untuk Memulai Tahap Berikutnya

```txt
Lanjutkan project SIM Madrasah.
Baca dulu README.md, AI-INSTRUCTIONS.md, docs/AI-HANDOFF.md, docs/PROGRESS.md, docs/NEXT-STEPS.md, docs/ARCHITECTURE.md, docs/DATABASE.md, dan docs/RBAC.md.

Kerjakan Tahap 12.21 — Filter Siswa Berdasarkan Status.
Jangan melompat tahap.
Jelaskan konsep dulu, lalu beri perubahan kode per file.
Setelah selesai, berikan update dokumentasi untuk docs/AI-HANDOFF.md, docs/PROGRESS.md, docs/NEXT-STEPS.md, dan docs/CHANGELOG.md.
```
