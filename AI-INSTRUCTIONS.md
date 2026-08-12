# AI Instructions — SIM Madrasah

Dokumen ini wajib dibaca oleh AI/developer yang akan melanjutkan project SIM Madrasah.

---

## Tujuan

Membuat SIM Madrasah berbasis Laravel yang rapi, bertahap, mudah dipahami pemula, dan bisa dijalankan pada shared hosting umum seperti Hostinger/Niagahoster.

---

## File yang Wajib Dibaca Sebelum Bekerja

Baca berurutan:

1. `README.md`
2. `docs/AI-HANDOFF.md`
3. `docs/PROGRESS.md`
4. `docs/NEXT-STEPS.md`
5. `docs/ARCHITECTURE.md`
6. `docs/DATABASE.md`
7. `docs/RBAC.md`
8. `docs/DECISIONS.md`

Jika konteks masih kurang, baru buka:

```txt
SIM-MADRASAH-AI-HISTORY.md
```

File history adalah arsip mentah. Jangan menjadikannya sumber kerja utama.

---

## Cara Menjawab untuk Pemilik Project

- Jelaskan sederhana dulu sebelum memberi kode.
- Jangan langsung membuat banyak perubahan sekaligus.
- Kerjakan satu tahap kecil per giliran.
- Jangan melompat modul tanpa diminta.
- Beri perintah terminal yang jelas.
- Jelaskan file mana yang diubah dan kenapa.
- Setelah satu tahap selesai, siapkan update dokumentasi.

---

## Aturan Teknis

- Gunakan Laravel 12 dan pola MVC standar Laravel.
- Gunakan Blade + Tailwind, jangan menambah framework frontend berat.
- Hindari Docker, Redis, WebSocket, queue worker permanen, PostgreSQL, atau teknologi yang menyulitkan shared hosting.
- Jangan mengubah histori kelas siswa secara destructive.
- Naik kelas harus membuat record baru di `student_class_histories`.
- Gunakan permission middleware untuk route admin.
- Validasi input wajib ada di controller/request.
- Test fitur penting harus ditambahkan.

---

## Update Dokumentasi Wajib Setelah Setiap Tahap

Minimal update:

- `docs/AI-HANDOFF.md`
- `docs/PROGRESS.md`
- `docs/NEXT-STEPS.md`
- `docs/CHANGELOG.md`

Update tambahan:

- `docs/DATABASE.md` jika ada perubahan tabel/relasi/migration.
- `docs/RBAC.md` jika ada permission/role baru.
- `docs/DECISIONS.md` jika ada keputusan teknis penting.
- `docs/MODULES.md` jika ada modul baru.
- `docs/TESTING.md` jika ada test baru atau bug testing.

---

## Format Ringkas Saat Melanjutkan Tahap

Gunakan pola ini:

```txt
Tahap: [nomor/nama tahap]
Tujuan: [apa yang akan dibuat]
File yang diubah:
- ...
Langkah:
1. ...
2. ...
Validasi:
- php artisan test
- npm run build
Dokumentasi yang diupdate:
- ...
```
