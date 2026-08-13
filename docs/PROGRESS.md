# Progress Pengembangan — SIM Madrasah

Terakhir diperbarui: 13 Agustus 2026.

---

## Ringkasan Status

| Area | Status | Catatan |
|---|---:|---|
| Setup Laravel | Selesai | Laravel 12, Breeze, Tailwind, Vite |
| Authentication | Selesai | Login/register bawaan Breeze, disesuaikan akun aktif |
| Middleware akun aktif | Selesai | `active.account` |
| RBAC | Selesai awal | Role, permission, pivot, middleware permission |
| Dashboard | Selesai awal | Role-based summary dan shortcut |
| Layout admin | Selesai awal | Sidebar dan komponen UI |
| Identitas madrasah | Selesai awal | Edit/update profil madrasah |
| Tahun ajaran & semester | Selesai awal | Aktivasi dan lock semester |
| Tingkat kelas | Selesai awal | CRUD |
| Ruangan | Selesai awal | CRUD |
| Rombongan belajar | Selesai awal | CRUD + filter tahun ajaran |
| Mata pelajaran | Selesai awal | CRUD |
| Pegawai | Selesai awal | CRUD |
| Akun pegawai | Selesai awal | Create akun dari data pegawai |
| Siswa | Stabil tahap 12.25 | CRUD, filter rombel, pencarian, filter status, filter tahun masuk, export CSV, review akhir |
| Riwayat kelas siswa | Selesai tahap 12.28 | Tambah histori kelas siswa, guard satu histori per siswa per semester, bulk assignment siswa ke rombel, validasi konteks akademik |
| Akun siswa | Selesai awal | Create akun dari data siswa |
| Wali siswa | Selesai awal | CRUD wali per siswa |
| Akun wali siswa | Selesai awal | Create akun wali |
| Portofolio siswa | Selesai awal | Ringkasan identitas, kelas, wali |
| QR portofolio | Selesai awal | QR menuju portofolio siswa |
| Cetak kartu portofolio | Selesai awal | PDF |
| Cetak siswa per kelas | Selesai awal | PDF |
| Export siswa per kelas | Selesai awal | Excel |
| Dokumentasi handoff AI | Baru dibuat | Paket docs ini |

---

## Timeline Commit Terakhir yang Terbaca

```txt
fd723a4 docs: tambah panduan kontribusi untuk tim
cfb94ab feat: add student search
e5de86a feat: add class group filter for students
570e511 feat: add academic year filter for class groups
ff4a033 feat: add class group student excel export
776a7e6 feat: add class group student print pdf
956aaa7 feat: add student portfolio card pdf
8ca81f4 feat: add student portfolio qr code
712efc0 feat: add student portfolio summary
a1358e1 feat: add student guardian account creation
2b9f53f feat: add student guardian module
c6b9dcc feat: add student account creation
7dfbb67 feat: add student class history module
6629ae0 feat: add student module
cd7db19 feat: add employee account creation
6c649f5 feat: add employee module
c771617 feat: add subject module
a619108 feat: add class group module
1b6b9b9 feat: add grade level and room CRUD pages
6d4ea2a feat: add grade level and room foundations
a91392b feat: add academic year and semester module
addfd94 feat: add madrasah identity module
```

---

## Fitur Terakhir yang Sudah Tampak di Kode

### Bulk Assignment Siswa ke Rombel

Status: selesai.

Fitur:

- admin dapat memilih banyak siswa;
- admin dapat memilih tahun ajaran, semester, rombel, dan tanggal mulai;
- sistem membuat histori kelas untuk semua siswa terpilih;
- histori aktif lama dinonaktifkan;
- histori baru dibuat sebagai `is_current = true`;
- assignment ditolak jika siswa sudah memiliki histori kelas pada semester yang sama;
- semester harus sesuai dengan tahun ajaran;
- rombel harus sesuai dengan tahun ajaran;
- tanggal mulai harus berada dalam rentang tanggal semester.

Validasi:

- `StudentBulkClassAssignmentTest` berhasil;
- `php artisan test` berhasil;
- `npm run build` berhasil.

---

## Catatan yang Perlu Dirapikan

1. `SIM-MADRASAH-AI-HISTORY.md` sangat besar dan sebaiknya hanya menjadi arsip.
2. Folder `vendor/` dan `node_modules/` tidak perlu masuk zip handoff.
3. `php artisan test` di environment AI gagal karena ekstensi PHP `mbstring` tidak aktif.
