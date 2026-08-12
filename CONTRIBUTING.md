# Panduan Kontribusi — SIM Madrasah

Dokumen ini berisi aturan kerja sederhana agar pengembangan SIM Madrasah tetap rapi, aman, dan mudah dilanjutkan oleh developer maupun AI lain.

---

## 1. Struktur Branch

- `main` — versi stabil/rilis. Jangan commit langsung ke sini kecuali project masih dikerjakan sendirian dan Anda sadar risikonya.
- `develop` — tempat gabungan fitur sebelum rilis.
- `fitur/nama-fitur` — branch untuk satu fitur kecil.

Contoh:

```bash
git checkout develop
git pull origin develop
git checkout -b fitur/filter-status-siswa
```

---

## 2. Format Commit

Gunakan format:

```txt
jenis: penjelasan singkat
```

Jenis yang dipakai:

- `feat` — fitur baru
- `fix` — perbaikan bug
- `refactor` — merapikan kode tanpa mengubah fungsi
- `test` — menambah/memperbaiki test
- `docs` — dokumentasi
- `chore` — pekerjaan teknis lain

Contoh:

```bash
git commit -m "feat: add student status filter"
git commit -m "fix: validate duplicate nisn on student update"
git commit -m "docs: update AI handoff after student search"
```

---

## 3. Alur Kerja Satu Tahap

Setiap fitur harus kecil dan jelas.

1. Baca `docs/AI-HANDOFF.md` dan `docs/NEXT-STEPS.md`.
2. Tentukan satu tahap yang akan dikerjakan.
3. Ubah kode seperlunya.
4. Tambahkan/ubah test.
5. Jalankan pemeriksaan.
6. Commit kode.
7. Update dokumentasi.
8. Commit dokumentasi.

---

## 4. Pemeriksaan Wajib

```bash
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
git status
```

Jika `php artisan test` gagal karena `mb_split()`, aktifkan ekstensi PHP `mbstring`.

---

## 5. Dokumentasi yang Wajib Diupdate

Setelah satu tahap selesai, minimal update:

```txt
docs/AI-HANDOFF.md
docs/PROGRESS.md
docs/NEXT-STEPS.md
docs/CHANGELOG.md
```

Jika berubah, update juga:

```txt
docs/DATABASE.md
docs/RBAC.md
docs/MODULES.md
docs/DECISIONS.md
docs/TESTING.md
```

---

## 6. Hal yang Wajib Dihindari

Jangan commit:

```txt
.env
vendor/
node_modules/
.DS_Store
.phpunit.result.cache
public/build/
storage/logs/
```

Jangan mengubah data histori secara destructive, khususnya data `student_class_histories`.

---

## 7. Pull Request

Setiap PR sebaiknya berisi:

- Ringkasan perubahan.
- Tahap terkait.
- File utama yang berubah.
- Cara uji manual.
- Hasil `php artisan test` dan `npm run build`.

Contoh:

```md
## Ringkasan
Menambahkan filter status siswa di halaman Data Siswa.

## Tahap
Tahap 12.21 — Filter siswa berdasarkan status.

## Cara Test
1. Login sebagai superadmin.
2. Buka `/admin/students`.
3. Pilih status Aktif/Nonaktif.
4. Klik Terapkan.

## Hasil Pemeriksaan
- php artisan test: passed
- npm run build: passed
```
