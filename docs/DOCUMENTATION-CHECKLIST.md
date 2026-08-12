# Documentation Checklist — SIM Madrasah

Gunakan checklist ini setiap selesai satu tahap.

---

## Checklist Wajib

- [ ] `docs/AI-HANDOFF.md` sudah menyebut tahap terakhir.
- [ ] `docs/PROGRESS.md` sudah diperbarui.
- [ ] `docs/NEXT-STEPS.md` sudah menunjuk tahap berikutnya.
- [ ] `docs/CHANGELOG.md` sudah mencatat perubahan.
- [ ] Commit message sudah ditulis jelas.

---

## Checklist Jika Ada Perubahan Database

- [ ] `docs/DATABASE.md` sudah diperbarui.
- [ ] Migration baru dicatat.
- [ ] Relasi baru dicatat.
- [ ] Unique/index penting dicatat.
- [ ] Seeder baru dicatat jika ada.

---

## Checklist Jika Ada Perubahan Permission

- [ ] Permission baru ditambahkan di `RbacSeeder`.
- [ ] Route memakai middleware permission.
- [ ] Tombol/action di Blade memakai `@can` jika perlu.
- [ ] `docs/RBAC.md` sudah diperbarui.
- [ ] Test akses permission sudah ditambahkan.

---

## Checklist Jika Ada Modul Baru

- [ ] Controller dicatat di `docs/MODULES.md`.
- [ ] Model dicatat.
- [ ] View dicatat.
- [ ] Route dicatat.
- [ ] Test dicatat di `docs/TESTING.md`.
- [ ] Next step modul dicatat.

---

## Checklist Keamanan

- [ ] `.env` tidak ikut commit.
- [ ] `vendor/` tidak ikut commit.
- [ ] `node_modules/` tidak ikut commit.
- [ ] Tidak ada password/token asli di dokumentasi.
- [ ] Tidak ada data siswa nyata yang sensitif di seeder/test.
