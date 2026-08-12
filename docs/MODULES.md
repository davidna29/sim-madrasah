# Modul — SIM Madrasah

---

## 1. Authentication dan Profile

Fungsi:

- Login.
- Logout.
- Profile user.
- Password update.
- Email verification bawaan Breeze.

Lokasi:

```txt
app/Http/Controllers/Auth/
app/Http/Controllers/ProfileController.php
resources/views/auth/
resources/views/profile/
```

---

## 2. Dashboard

Fungsi:

- Dashboard role-based.
- Shortcut modul.
- Ringkasan sistem untuk super admin.

Lokasi:

```txt
app/Http/Controllers/DashboardController.php
resources/views/dashboard.blade.php
```

---

## 3. RBAC

Fungsi:

- Melihat role.
- Melihat permission.
- Mengatur permission role.
- Mengecek akses route.

Lokasi:

```txt
app/Models/Role.php
app/Models/Permission.php
app/Models/User.php
app/Http/Controllers/Admin/RoleController.php
app/Http/Controllers/Admin/PermissionController.php
app/Http/Middleware/EnsureUserHasPermission.php
database/seeders/RbacSeeder.php
```

---

## 4. Identitas Madrasah

Fungsi:

- Mengelola data dasar madrasah.

Lokasi:

```txt
app/Models/Madrasah.php
app/Http/Controllers/Admin/MadrasahProfileController.php
resources/views/admin/madrasah/edit.blade.php
```

---

## 5. Tahun Ajaran dan Semester

Fungsi:

- Membuat tahun ajaran.
- Mengaktifkan semester.
- Mengunci semester.

Lokasi:

```txt
app/Models/AcademicYear.php
app/Models/Semester.php
app/Http/Controllers/Admin/AcademicYearController.php
resources/views/admin/academic-years/
```

---

## 6. Kelas, Ruangan, dan Rombel

Fungsi:

- CRUD tingkat kelas.
- CRUD ruangan.
- CRUD rombongan belajar.
- Filter rombel berdasarkan tahun ajaran.
- Cetak daftar siswa per kelas PDF.
- Export daftar siswa per kelas Excel.

Lokasi:

```txt
app/Models/GradeLevel.php
app/Models/Room.php
app/Models/ClassGroup.php
app/Http/Controllers/Admin/GradeLevelController.php
app/Http/Controllers/Admin/RoomController.php
app/Http/Controllers/Admin/ClassGroupController.php
app/Http/Controllers/Admin/ClassGroupStudentPrintController.php
app/Http/Controllers/Admin/ClassGroupStudentExportController.php
app/Exports/ClassGroupStudentsExport.php
resources/views/admin/class-groups/
```

---

## 7. Mata Pelajaran

Fungsi:

- CRUD mata pelajaran.

Lokasi:

```txt
app/Models/Subject.php
app/Http/Controllers/Admin/SubjectController.php
resources/views/admin/subjects/
```

---

## 8. Guru dan Pegawai

Fungsi:

- CRUD pegawai.
- Membuat akun pegawai.

Lokasi:

```txt
app/Models/Employee.php
app/Http/Controllers/Admin/EmployeeController.php
app/Http/Controllers/Admin/EmployeeAccountController.php
resources/views/admin/employees/
```

---

## 9. Siswa

Fungsi:

- CRUD siswa.
- Pencarian siswa.
- Filter siswa berdasarkan rombel aktif.
- Membuat akun siswa.
- Melihat riwayat kelas.
- Melihat wali.
- Membuka portofolio.

Lokasi:

```txt
app/Models/Student.php
app/Http/Controllers/Admin/StudentController.php
app/Http/Controllers/Admin/StudentAccountController.php
resources/views/admin/students/
```

---

## 10. Riwayat Kelas Siswa

Fungsi:

- Menyimpan histori kelas siswa per tahun ajaran dan semester.
- Menentukan kelas aktif melalui `is_current`.

Lokasi:

```txt
app/Models/StudentClassHistory.php
app/Http/Controllers/Admin/StudentClassHistoryController.php
resources/views/admin/students/class-histories/
```

---

## 11. Orang Tua/Wali Siswa

Fungsi:

- CRUD wali siswa.
- Menandai kontak utama, kontak darurat, dan penanggung jawab keuangan.
- Membuat akun wali siswa.

Lokasi:

```txt
app/Models/StudentGuardian.php
app/Http/Controllers/Admin/StudentGuardianController.php
app/Http/Controllers/Admin/StudentGuardianAccountController.php
resources/views/admin/students/guardians/
```

---

## 12. Portofolio Digital Siswa

Fungsi:

- Ringkasan identitas siswa.
- Kelas aktif.
- Riwayat kelas.
- Data wali.
- QR Code.
- Cetak kartu PDF.

Lokasi:

```txt
app/Http/Controllers/Admin/StudentPortfolioController.php
resources/views/admin/students/portfolio/
```
