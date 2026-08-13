# Next Steps — SIM Madrasah

Dokumen ini berisi urutan kerja kecil yang direkomendasikan.

---

## Prioritas Terdekat

### Tahap 12.30 — Penentuan Semester Aktif Sistem

Tujuan:

- Menentukan semester aktif sistem dari menu akademik.
- Semester aktif sistem menjadi acuan awal untuk proses akademik.
- Bulk Assignment dan Tambah Riwayat Kelas nantinya diarahkan agar mengikuti semester aktif.
- Hanya user berwenang yang boleh mengaktifkan semester.
- Tahap awal perlu membaca ulang struktur `academic_years` dan `semesters`.

Catatan:

- Saat ini kelas aktif siswa masih ditentukan oleh `student_class_histories.is_current`.
- Semester aktif sistem belum otomatis mengunci input histori kelas.
- Perubahan ke arah semester aktif sistem harus dilakukan bertahap agar tidak merusak fitur yang sudah berjalan.

Setelah tahap ini stabil:

- Tahap 12.31 — Koreksi dan Edit Riwayat Kelas Siswa.
- Tahap 12.32 — Kebijakan Nonaktif, Soft Delete, dan Koreksi Data Master.
- Tahap 12.33 — Awal Modul Jadwal Pelajaran.

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
