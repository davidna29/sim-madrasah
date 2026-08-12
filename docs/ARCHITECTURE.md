# Arsitektur — SIM Madrasah

---

## 1. Ringkasan Arsitektur

SIM Madrasah memakai arsitektur MVC standar Laravel.

```txt
Request
  ↓
Routes: routes/web.php
  ↓
Middleware: auth, verified, active.account, permission
  ↓
Controller: app/Http/Controllers
  ↓
Model/Eloquent: app/Models
  ↓
Database: migrations + seeders
  ↓
View: resources/views
```

---

## 2. Layer Utama

| Layer | Lokasi | Fungsi |
|---|---|---|
| Route | `routes/web.php` | Mendefinisikan URL, middleware, dan nama route |
| Controller | `app/Http/Controllers` | Mengatur request, validasi, query, response |
| Model | `app/Models` | Relasi data dan query Eloquent |
| View | `resources/views` | Tampilan Blade |
| Middleware | `app/Http/Middleware` | Cek akses, status akun, permission |
| Migration | `database/migrations` | Struktur database |
| Seeder | `database/seeders` | Data awal, role, permission, master data |
| Test | `tests/Feature`, `tests/Unit` | Pengujian fitur dan relasi |

---

## 3. Struktur Route Admin

Route admin memakai pola:

```php
Route::middleware([
    'auth',
    'verified',
    'active.account',
])->prefix('admin')->name('admin.')->group(function (): void {
    // route admin
});
```

Setiap route sensitif harus memakai permission:

```php
->middleware('permission:students.view')
```

---

## 4. Pola RBAC

RBAC terdiri dari:

- `roles`
- `permissions`
- `role_permissions`
- `user_roles`
- `user_permissions`

Urutan pengecekan permission pada `User::hasPermission()`:

1. Permission langsung mode `deny` menolak akses.
2. Permission langsung mode `allow` memberi akses.
3. Permission dari role aktif memberi akses.

---

## 5. Pola Data Orang dan Akun

Aplikasi memisahkan data identitas dan akun login.

```txt
people
  └── users
```

`people` menyimpan identitas umum seperti nama, NIK, alamat, kontak.

`users` menyimpan akun login seperti username, email, password, status, dan account type.

Data siswa/pegawai/wali terhubung ke `people`.

---

## 6. Pola Data Siswa

```txt
people
  └── students
        ├── student_class_histories
        └── student_guardians
```

Relasi penting:

- `Student belongsTo Person`
- `Student belongsTo AcademicYear` sebagai tahun masuk
- `Student hasMany StudentClassHistory`
- `Student hasOne currentClassHistory` dengan `is_current = true`
- `Student hasMany StudentGuardian`
- `Student hasOne primaryGuardian`

---

## 7. Prinsip Histori Kelas

Jangan menyimpan kelas aktif siswa langsung sebagai kolom tunggal yang terus ditimpa.

Gunakan:

```txt
student_class_histories
```

Kolom penting:

- `student_id`
- `academic_year_id`
- `semester_id`
- `class_group_id`
- `is_current`
- `start_date`
- `end_date`
- `status`

Aturan:

- Jika siswa pindah/naik kelas, buat record baru.
- Record lama jangan dihapus.
- Untuk tampilan kelas saat ini, gunakan record `is_current = true`.

---

## 8. Export dan Cetak

PDF memakai DomPDF.

Excel memakai Laravel Excel.

Fitur yang sudah ada:

- Cetak siswa per kelas PDF.
- Export siswa per kelas Excel.
- Cetak kartu portofolio siswa PDF.

---

## 9. Rekomendasi Saat Project Membesar

Jika controller mulai terlalu panjang, pecah logic ke service class, misalnya:

```txt
app/Services/StudentSearchService.php
app/Services/StudentClassAssignmentService.php
app/Services/RbacService.php
```

Untuk saat ini, pola controller langsung masih bisa dipertahankan agar mudah dipahami pemula.
