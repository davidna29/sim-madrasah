# SIM Madrasah

SIM Madrasah adalah aplikasi Sistem Informasi Manajemen Madrasah berbasis Laravel untuk membantu pengelolaan data inti madrasah: identitas madrasah, tahun ajaran, semester, kelas, ruangan, mata pelajaran, pegawai, siswa, wali siswa, akun pengguna, hak akses, dan portofolio digital siswa.

> Status dokumentasi: dibuat sebagai paket handoff AI pada 12 Agustus 2026.  
> Status kode yang terbaca dari repository: `fd723a4 docs: tambah panduan kontribusi untuk tim`, dengan fitur terakhir `cfb94ab feat: add student search`.

---

## Teknologi Utama

- Laravel 12
- PHP `^8.2`
- Laravel Breeze
- Blade Template
- Tailwind CSS
- Vite
- SQLite untuk pengembangan lokal
- MySQL/MariaDB untuk produksi/shared hosting
- DomPDF untuk cetak PDF
- Laravel Excel untuk ekspor Excel
- Simple QRCode untuk QR portofolio siswa

---

## Modul yang Sudah Ada

| Area | Status |
|---|---|
| Authentication | Ada |
| Middleware akun aktif | Ada |
| RBAC role dan permission | Ada |
| Dashboard role-based | Ada |
| Identitas madrasah | Ada |
| Tahun ajaran dan semester | Ada |
| Tingkat kelas | Ada |
| Ruangan | Ada |
| Rombongan belajar | Ada |
| Mata pelajaran | Ada |
| Guru dan pegawai | Ada |
| Akun pegawai | Ada |
| Data siswa | Ada |
| Riwayat kelas siswa | Ada |
| Akun siswa | Ada |
| Orang tua/wali siswa | Ada |
| Akun orang tua/wali | Ada |
| Portofolio digital siswa | Ada |
| QR Code portofolio | Ada |
| Cetak kartu portofolio PDF | Ada |
| Cetak data siswa per kelas PDF | Ada |
| Export data siswa per kelas Excel | Ada |
| Filter rombel berdasarkan tahun ajaran | Ada |
| Filter siswa berdasarkan rombel aktif | Ada |
| Pencarian siswa berdasarkan nama, NIS, NISN, nomor registrasi | Ada |

---

## Cara Menjalankan Project Lokal

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Untuk mode pengembangan frontend:

```bash
npm run dev
```

---

## Pemeriksaan Wajib Setelah Mengubah Kode

```bash
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
git status
```

Catatan: kalau muncul error `Call to undefined function Illuminate\Support\mb_split()`, aktifkan ekstensi PHP `mbstring` di environment lokal/hosting.

---

## Dokumen Penting

AI atau developer baru sebaiknya membaca dokumen berikut sebelum melanjutkan:

1. [`AI-INSTRUCTIONS.md`](AI-INSTRUCTIONS.md)
2. [`docs/AI-HANDOFF.md`](docs/AI-HANDOFF.md)
3. [`docs/PROJECT-BRIEF.md`](docs/PROJECT-BRIEF.md)
4. [`docs/PROGRESS.md`](docs/PROGRESS.md)
5. [`docs/NEXT-STEPS.md`](docs/NEXT-STEPS.md)
6. [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md)
7. [`docs/DATABASE.md`](docs/DATABASE.md)
8. [`docs/RBAC.md`](docs/RBAC.md)
9. [`docs/AI-WORKFLOW.md`](docs/AI-WORKFLOW.md)

`SIM-MADRASAH-AI-HISTORY.md` hanya dipakai sebagai arsip percakapan mentah, bukan sebagai sumber kerja utama.

---

## Aturan Keamanan Repository

Jangan commit file/folder berikut:

```txt
.env
vendor/
node_modules/
.DS_Store
.phpunit.result.cache
public/build/
storage/logs/
```

Gunakan `.env.example` sebagai contoh konfigurasi, bukan `.env` asli.
