# RBAC — Role dan Permission SIM Madrasah

---

## 1. Tujuan RBAC

RBAC digunakan untuk mengatur akses berdasarkan role dan permission.

Route admin tidak boleh hanya mengandalkan login. Route sensitif harus memakai permission middleware.

Contoh:

```php
->middleware('permission:students.view')
```

---

## 2. Role Sistem

Role sistem yang terdaftar pada `RbacSeeder`:

| Role | Nama Tampilan |
|---|---|
| `super_admin` | Super Admin |
| `kepala_madrasah` | Kepala Madrasah |
| `wakamad_kurikulum` | Wakamad Kurikulum |
| `wakamad_kesiswaan` | Wakamad Kesiswaan |
| `wakamad_sarpras` | Wakamad Sarpras |
| `wakamad_humas` | Wakamad Humas |
| `tata_usaha` | Tata Usaha |
| `bendahara` | Bendahara |
| `wali_kelas` | Wali Kelas |
| `guru_mata_pelajaran` | Guru Mata Pelajaran |
| `guru_bk` | Guru BK |
| `petugas_perpustakaan` | Petugas Perpustakaan |
| `petugas_laboratorium` | Petugas Laboratorium |
| `editor_berita` | Editor Berita |
| `orang_tua` | Orang Tua |
| `siswa` | Siswa |

---

## 3. Permission Aktif yang Terbaca

| Permission | Modul | Aksi |
|---|---|---|
| `dashboard.view` | dashboard | view |
| `profile.view` | profile | view |
| `profile.update` | profile | update |
| `users.view` | users | view |
| `users.create` | users | create |
| `users.update` | users | update |
| `users.deactivate` | users | deactivate |
| `roles.view` | roles | view |
| `roles.create` | roles | create |
| `roles.update` | roles | update |
| `roles.assign` | roles | assign |
| `roles.permission.assign` | roles | permission_assign |
| `permissions.view` | permissions | view |
| `permissions.create` | permissions | create |
| `permissions.update` | permissions | update |
| `settings.view` | settings | view |
| `settings.update` | settings | update |
| `madrasah.view` | madrasah | view |
| `madrasah.update` | madrasah | update |
| `activity_logs.view` | activity_logs | view |
| `audit_logs.view` | audit_logs | view |
| `academic_years.view` | academic_years | view |
| `academic_years.create` | academic_years | create |
| `academic_years.update` | academic_years | update |
| `academic_years.activate` | academic_years | activate |
| `academic_years.lock` | academic_years | lock |
| `grade_levels.view` | grade_levels | view |
| `grade_levels.create` | grade_levels | create |
| `grade_levels.update` | grade_levels | update |
| `rooms.view` | rooms | view |
| `rooms.create` | rooms | create |
| `rooms.update` | rooms | update |
| `class_groups.view` | class_groups | view |
| `class_groups.create` | class_groups | create |
| `class_groups.update` | class_groups | update |
| `class_groups.print_students` | class_groups | print_students |
| `class_groups.export_students` | class_groups | export_students |
| `subjects.view` | subjects | view |
| `subjects.create` | subjects | create |
| `subjects.update` | subjects | update |
| `employees.view` | employees | view |
| `employees.create` | employees | create |
| `employees.update` | employees | update |
| `employees.account.create` | employees | account_create |
| `students.view` | students | view |
| `students.create` | students | create |
| `students.update` | students | update |
| `students.account.create` | students | account_create |
| `student_class_histories.view` | student_class_histories | view |
| `student_class_histories.create` | student_class_histories | create |
| `student_guardians.view` | student_guardians | view |
| `student_guardians.create` | student_guardians | create |
| `student_guardians.update` | student_guardians | update |
| `student_guardians.account.create` | student_guardians | account_create |
| `student_portfolios.view` | student_portfolios | view |
| `student_portfolios.print` | student_portfolios | print |

---

## 4. Cara Permission Dicek

Model `User` memiliki method `hasPermission(string $permissionName)`.

Urutan pengecekan:

1. Cek permission langsung di `user_permissions`.
2. Jika ada permission langsung mode `deny`, akses ditolak.
3. Jika ada permission langsung mode `allow`, akses diberikan.
4. Jika tidak ada permission langsung, cek permission dari role aktif.

---

## 5. Cara Menambah Permission Baru

1. Tambahkan item permission di `database/seeders/RbacSeeder.php`.
2. Beri nama konsisten:

```txt
nama_modul.aksi
```

Contoh:

```txt
students.export
students.import
student_attendances.view
student_attendances.create
```

3. Jalankan seeder:

```bash
php artisan db:seed --class=RbacSeeder
```

4. Pasang permission di route:

```php
->middleware('permission:students.export')
```

5. Update dokumen ini.

---

## 6. Catatan untuk AI

Jangan membuat route admin tanpa permission, kecuali route tersebut benar-benar aman untuk semua user login.

Untuk UI Blade, gunakan:

```blade
@can('permission', 'students.create')
    <!-- tombol -->
@endcan
```
