# Testing — SIM Madrasah

---

## 1. Perintah Test

Jalankan:

```bash
php artisan optimize:clear
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
```

---

## 2. Jika Error `mb_split()`

Jika muncul:

```txt
Call to undefined function Illuminate\Support\mb_split()
```

Artinya ekstensi PHP `mbstring` belum aktif.

Solusi Ubuntu/Debian:

```bash
sudo apt install php-mbstring
sudo service apache2 restart
```

Solusi Laragon/XAMPP:

- Buka `php.ini`.
- Aktifkan `extension=mbstring`.
- Restart web server.

Solusi hosting:

- Buka menu PHP Extensions.
- Aktifkan `mbstring`.

---

## 3. Lokasi Test Penting

| Area | File Test |
|---|---|
| Authentication | `tests/Feature/Auth/` |
| RBAC | `tests/Feature/Rbac/` |
| Dashboard | `tests/Feature/Dashboard/` |
| Madrasah | `tests/Feature/Admin/MadrasahProfileTest.php` |
| Tahun ajaran | `tests/Feature/Admin/AcademicYearTest.php` |
| Grade level & room | `tests/Feature/Admin/GradeLevelRoomCrudTest.php` |
| Rombel | `tests/Feature/Admin/ClassGroupTest.php` |
| Cetak siswa per kelas | `tests/Feature/Admin/ClassGroupStudentPrintTest.php` |
| Export siswa per kelas | `tests/Feature/Admin/ClassGroupStudentExportTest.php` |
| Mata pelajaran | `tests/Feature/Admin/SubjectTest.php` |
| Pegawai | `tests/Feature/Admin/EmployeeTest.php` |
| Akun pegawai | `tests/Feature/Admin/EmployeeAccountTest.php` |
| Siswa | `tests/Feature/Admin/StudentTest.php` |
| Riwayat kelas siswa | `tests/Feature/Admin/StudentClassHistoryTest.php` |
| Akun siswa | `tests/Feature/Admin/StudentAccountTest.php` |
| Wali siswa | `tests/Feature/Admin/StudentGuardianTest.php` |
| Akun wali siswa | `tests/Feature/Admin/StudentGuardianAccountTest.php` |
| Portofolio siswa | `tests/Feature/Admin/StudentPortfolioTest.php` |

---

## 4. Aturan Menambah Test

Setiap fitur admin minimal punya test untuk:

- user tanpa permission tidak boleh akses,
- user dengan permission boleh akses,
- validasi input penting,
- data berhasil tersimpan/terupdate,
- filter/search jika fitur berupa pencarian.

---

## 5. Checklist Sebelum Commit

- [ ] `php artisan optimize:clear` berhasil.
- [ ] `./vendor/bin/pint` berhasil.
- [ ] `./vendor/bin/pint --test` berhasil.
- [ ] `php artisan test` berhasil.
- [ ] `npm run build` berhasil.
- [ ] `git status` hanya menampilkan file yang memang mau dicommit.
- [ ] Dokumentasi sudah diupdate.
