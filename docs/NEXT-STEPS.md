# Next Steps — SIM Madrasah

Dokumen ini berisi urutan kerja kecil yang direkomendasikan setelah fitur pencarian siswa.

---

## Prioritas Terdekat

### Tahap 12.24 — Review dan Perapian Export Data Siswa

Tujuan:

- Mengecek ulang isi kolom CSV.
- Memastikan label status mudah dibaca.
- Memastikan nama file export konsisten.
- Memastikan tombol export tetap nyaman pada tampilan desktop dan mobile.
- Menentukan apakah tahap berikutnya perlu export Excel atau cukup CSV.

File kemungkinan berubah:

- `app/Http/Controllers/Admin/StudentController.php`
- `resources/views/admin/students/index.blade.php`
- `tests/Feature/Admin/StudentTest.php`
- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`

Validasi:

```bash
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
```

---

### Tahap 12.22 — Filter Siswa Berdasarkan Tahun Ajaran Masuk

Tujuan:

- Menambahkan filter `admission_academic_year_id` pada daftar siswa.
- Filter tetap bisa digabung dengan `q`, `class_group_id`, dan `status`.

---

### Tahap 12.23 — Export Hasil Filter Siswa

Tujuan:

- Export daftar siswa sesuai hasil filter di halaman `Data Siswa`.
- Format awal: Excel.
- Hindari export semua data jika user sedang memakai filter.

---

### Tahap 12.24 — Bulk Assignment Siswa ke Rombel

Tujuan:

- Memilih banyak siswa.
- Menempatkan ke rombel tertentu pada tahun ajaran dan semester tertentu.
- Membuat record `student_class_histories`.
- Menonaktifkan `is_current` lama jika masih satu siswa.

Perhatian:

- Jangan hapus histori lama.
- Wajib validasi satu siswa tidak boleh punya dua record aktif untuk semester yang sama.

---

### Tahap 12.25 — Guard Histori Kelas Aktif

Tujuan:

- Membuat aturan aplikasi agar hanya satu `student_class_histories.is_current = true` per siswa.
- Bisa dimulai dari logic controller/service.
- Pertimbangkan index/constraint jika database mendukung.

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
