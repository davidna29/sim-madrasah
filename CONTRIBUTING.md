# Panduan Kontribusi — SIM Madrasah (Sisdigmad)

Dokumen ini berisi aturan main sederhana untuk tim (David + kontributor) saat mengembangkan project ini bareng-bareng. Tujuannya bukan bikin ribet, tapi biar kita tidak saling menimpa kerjaan dan histori project tetap rapi buat belajar.

## 1. Struktur Branch

- `main` — versi stabil, hanya diisi dari `develop` saat rilis (misal v0.2.0). Jangan commit langsung ke sini.
- `develop` — tempat gabungan semua fitur yang sedang berjalan. Semua branch fitur bermuara ke sini.
- `fitur/nama-fitur` — branch kerja harian, satu branch untuk satu tahap/fitur kecil.

Contoh nama branch fitur:
```
fitur/filter-status-siswa
fitur/import-excel-siswa
fitur/bulk-assign-rombel
```

## 2. Alur Kerja Harian

1. Sebelum mulai kerja, update dulu branch `develop`:
   ```bash
   git checkout develop
   git pull origin develop
   ```
2. Buat branch fitur baru dari `develop`:
   ```bash
   git checkout -b fitur/nama-fitur
   ```
3. Kerja, commit sesering mungkin dengan pesan yang jelas (lihat format di bawah).
4. Kalau fitur sudah siap dicek, push branch-nya:
   ```bash
   git push origin fitur/nama-fitur
   ```
5. Buka Pull Request (PR) di GitHub, arah dari `fitur/nama-fitur` ke `develop`.
6. Minta rekan lain review sebelum di-merge.
7. Setelah di-merge, hapus branch fitur yang sudah selesai (biar repo tidak penuh branch mati).

## 3. Format Commit Message

Pakai format singkat: `jenis: penjelasan singkat`

Jenis yang dipakai:
- `feat` — fitur baru
- `fix` — perbaikan bug
- `refactor` — rapikan kode tanpa ubah fungsi
- `test` — nambah/ubah test
- `docs` — dokumentasi
- `chore` — hal teknis lain (dependency, config, dll)

Contoh:
```
feat: tambah filter siswa berdasarkan status
fix: perbaiki validasi NISN kosong di form siswa
test: tambah unit test untuk StudentClassHistory
docs: update alur instalasi di README
```

## 4. Pull Request

Setiap PR sebaiknya mencantumkan:
- **Apa yang diubah** (singkat, 1-3 kalimat)
- **Tahap terkait** (misal: Tahap 12.21 — Filter siswa berdasarkan status)
- **Cara test** (langkah singkat untuk cek fitur ini jalan)

PR sebaiknya kecil dan fokus satu fitur/tahap, biar gampang direview dan kalau ada masalah gampang dilacak.

## 5. Review

- Minimal satu orang lain baca perubahan sebelum merge ke `develop`.
- Kalau ada yang janggal, komentar langsung di baris kode terkait di tab "Files changed".
- Tidak masalah kalau review-nya sederhana ("sudah dicoba jalan, oke") — yang penting ada proses cek, bukan langsung merge sendiri.

## 6. Rilis ke `main`

- `develop` di-merge ke `main` hanya saat mau rilis resmi (misal Release 0.2: Admin Akademik MVP).
- Setelah merge, buat tag versi:
  ```bash
  git tag -a v0.2.0 -m "Release 0.2: Admin Akademik MVP"
  git push origin v0.2.0
  ```

## 7. Hal yang Wajib Dihindari

- Jangan commit file `.env` (pastikan sudah ada di `.gitignore`).
- Jangan commit folder `vendor/` atau `node_modules/`.
- Jangan force-push (`git push -f`) ke `develop` atau `main`.
- Jangan kerja langsung di branch `main` atau `develop`, selalu lewat branch fitur + PR.

## 8. Setup Lokal untuk Kontributor Baru

```bash
git clone https://github.com/davidna29/sim-madrasah.git
cd sim-madrasah
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

Sesuaikan konfigurasi database di `.env` masing-masing sebelum migrate.
