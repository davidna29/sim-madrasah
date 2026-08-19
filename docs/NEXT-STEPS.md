# Next Steps — SIM Madrasah

Dokumen ini berisi urutan kerja kecil yang direkomendasikan.

---
## Prioritas Terdekat

Tahap terakhir selesai: **Tahap 12.34G-1 — Fondasi Database Ketersediaan Guru**.

Tahap berikutnya yang direkomendasikan:

### Tahap 12.34G-2 — CRUD Ketersediaan Guru: Permission, Route, dan Halaman Daftar

Tujuan kecil:

1. Menambahkan permission ketersediaan guru di `RbacSeeder`.
2. Menambahkan route admin untuk daftar ketersediaan guru.
3. Menambahkan controller `TeacherAvailabilityController`.
4. Menambahkan halaman daftar ketersediaan guru.
5. Menambahkan menu sidebar jika permission tersedia.
6. Menambahkan test akses halaman daftar.

Catatan batasan:

- Jangan langsung membuat form tambah/edit dalam tahap yang sama kalau terlalu besar.
- Jangan langsung membuat auto-generate jadwal.
- Jangan membuat fitur unlock semester/tahun ajaran.
- Validasi bentrok jam ketersediaan bisa dikerjakan setelah form tambah tersedia.

### Tahap 12.34G-1 — Fondasi Database Ketersediaan Guru

Status: selesai.

Tujuan yang tercapai:

- Model `TeacherAvailability`. ✓
- Tabel `teacher_availabilities`. ✓
- Relasi ke `AcademicYear`. ✓
- Relasi ke `Semester`. ✓
- Relasi ke `User` sebagai guru. ✓
- Relasi ke `User` sebagai pembuat data. ✓
- Test fondasi database. ✓

---

## Backlog — Usulan Efisiensi Input dari Developer (dicatat 19 Agustus 2026)

Catatan mentah dari developer, belum dikerjakan, belum dipecah jadi tahap resmi:

| # | Usulan | Halaman/modul terkait | Catatan |
|---|---|---|---|
| 1 | Edit slot template jadwal jadi jendela mengambang (modal), bukan halaman penuh | Slot Template Jadwal | Perubahan UI, ukuran kecil–sedang |
| 2 | Copy jadwal slot dari satu hari ke hari lain (misal Senin → Selasa) | Slot Template Jadwal | Perlu didesain: salin semua slot satu hari sekaligus |
| 3 | Generate slot dummy otomatis sesuai jam pelajaran standar madrasah | Slot Template Jadwal | Perlu disepakati dulu apa itu "standar jam pelajaran" (durasi per jam, jumlah jam per hari, dll) |
| 4 | Template Excel untuk tambah data massal: tingkat kelas, ruangan, rombel, mapel, guru/pegawai, siswa | Semua master data | Cukup besar, kemungkinan perlu dipecah per modul jadi beberapa tahap terpisah (import Excel per jenis data) |
| 5 | Opsi "copy penugasan dari semester sebelumnya" di halaman Plotting Beban Mengajar | Plotting Beban Mengajar | Supaya operator tidak input ulang kalau penugasan sama seperti semester lalu |
| 6 | Prinsip umum: semua input yang sifatnya berulang sebaiknya dikasih jalan pintas/shortcut | Lintas modul | Ini prinsip desain jangka panjang, bukan satu tahap — dipakai sebagai pertimbangan setiap kali mendesain form baru ke depan |

Belum ada urutan prioritas resmi untuk 6 item ini — akan didiskusikan dan dipecah jadi tahap kecil satu per satu saat developer siap mengerjakannya.

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