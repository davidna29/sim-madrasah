# Project Brief — SIM Madrasah

---

## 1. Nama Project

SIM Madrasah.

Nama alternatif yang sempat muncul di dokumentasi kontribusi: Sisdigmad.

---

## 2. Tujuan Project

Membangun sistem informasi manajemen madrasah berbasis web yang membantu pengelolaan data administratif dan akademik secara bertahap.

Sasaran awal adalah membuat pondasi sistem yang stabil, meliputi:

- autentikasi pengguna,
- role dan permission,
- data master madrasah,
- data guru/pegawai,
- data siswa,
- data wali siswa,
- rombongan belajar,
- riwayat kelas siswa,
- portofolio digital siswa,
- cetak PDF dan export Excel.

---

## 3. Karakter Pengembangan

Project ini dikembangkan oleh pemula dengan bantuan AI. Karena itu dokumentasi harus:

- jelas,
- bertahap,
- tidak terlalu abstrak,
- mudah dibaca oleh AI lain,
- mudah dilanjutkan meskipun percakapan AI sebelumnya hilang.

---

## 4. Batasan Teknis

Aplikasi ditargetkan bisa berjalan pada shared hosting umum. Karena itu hindari teknologi yang terlalu berat.

Direkomendasikan:

- Laravel standar.
- Blade.
- Tailwind CSS.
- MySQL/MariaDB.
- Upload file biasa melalui storage Laravel.
- Export/cetak berbasis request biasa.

Hindari kecuali benar-benar dibutuhkan:

- Docker sebagai syarat utama deployment.
- Redis.
- WebSocket.
- Queue worker permanen.
- PostgreSQL sebagai satu-satunya database.
- SPA frontend berat.

---

## 5. Prinsip Data Akademik

Data akademik harus historis.

Contoh penting:

- Siswa tidak boleh hanya punya satu kolom `class_group_id` yang ditimpa terus.
- Penempatan siswa ke kelas harus masuk ke `student_class_histories`.
- Jika siswa naik kelas atau pindah rombel, buat record riwayat baru.
- Record lama tetap menjadi jejak histori.

---

## 6. Pengguna Sasaran

- Super Admin.
- Kepala Madrasah.
- Wakamad Kurikulum.
- Wakamad Kesiswaan.
- Wakamad Sarpras.
- Wakamad Humas.
- Tata Usaha.
- Bendahara.
- Wali Kelas.
- Guru Mata Pelajaran.
- Guru BK.
- Petugas Perpustakaan.
- Petugas Laboratorium.
- Editor Berita.
- Orang Tua.
- Siswa.

---

## 7. Target Jangka Pendek

Menyelesaikan fondasi Data Master dan Data Siswa sampai siap dipakai untuk modul berikutnya:

- absensi,
- jadwal pelajaran,
- nilai,
- rapor,
- PPDB,
- pembayaran,
- surat menyurat,
- arsip,
- PKKM,
- akreditasi.
