# Deployment — SIM Madrasah

Dokumen ini adalah panduan awal deployment. Sesuaikan dengan hosting yang digunakan.

---

## 1. Target Hosting

Project ditargetkan bisa berjalan di shared hosting yang mendukung:

- PHP 8.2 atau lebih baru,
- Composer,
- MySQL/MariaDB,
- ekstensi PHP umum Laravel.

Ekstensi PHP yang perlu dicek:

```txt
mbstring
openssl
pdo
pdo_mysql
tokenizer
xml
ctype
json
bcmath
fileinfo
zip
gd
```

---

## 2. File yang Tidak Boleh Diupload Publik

Jangan upload/commit:

```txt
.env
.git/
node_modules/
vendor/ jika hosting menjalankan composer install sendiri
.DS_Store
storage/logs/*.log
```

Pada shared hosting yang tidak punya Composer, `vendor/` bisa diupload, tetapi jangan commit ke Git.

---

## 3. Perintah Build Lokal Sebelum Upload

```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Environment Production

Contoh konfigurasi `.env` production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.sch.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
```

Jangan bagikan `.env` production ke AI atau pihak lain.

---

## 5. Migrasi Database

```bash
php artisan migrate --force
php artisan db:seed --class=RbacSeeder --force
php artisan db:seed --class=MadrasahSeeder --force
```

> **Penting:** `InitialAdminSeeder` **tidak bisa** dijalankan di production. Seeder ini sengaja
> memblokir dirinya sendiri kecuali `APP_ENV=local` (lihat ADR-007 di `docs/DECISIONS.md`), supaya
> tidak ada password administrator bawaan yang sama di semua instalasi production. Ikuti langkah
> 5.2 di bawah untuk membuat administrator awal di production.

### 5.1 Seeder Wajib vs Seeder Demo

**Jangan pernah menjalankan `php artisan migrate --seed` (tanpa `--class`) di production** —
perintah itu menjalankan `DatabaseSeeder`, yang sengaja menyertakan data demo/dummy untuk
kebutuhan pengembangan lokal (lihat ADR-008 di `docs/DECISIONS.md`).

| Seeder | Wajib di production? | Alasan |
|---|---|---|
| `RbacSeeder` | **Ya** | Tanpa ini, tidak ada role/permission — tidak ada yang bisa login dengan akses apapun. |
| `MadrasahSeeder` | **Ya** | Halaman Identitas Madrasah (`/admin/madrasah`) memakai `firstOrFail()`, akan 404 tanpa ini. |
| `GradeLevelSeeder`, `RoomSeeder`, `SubjectSeeder`, `AcademicYearSeeder` | Opsional | Data referensi contoh (tingkat kelas, ruangan, mapel, tahun ajaran). Boleh dipakai sebagai titik awal lalu diedit lewat UI, atau dilewati kalau madrasah ingin input manual dari nol. |
| `ClassGroupSeeder`, `EmployeeSeeder`, `StudentSeeder`, `StudentGuardianSeeder`, `StudentClassHistorySeeder` | **Tidak** | Berisi data siswa/guru **fiktif** (contoh: "Ahmad Siswa", email `@sim-madrasah.test`). Hanya untuk demo/testing lokal. |
| `InitialAdminSeeder` | Tidak bisa (diblokir) | Lihat bagian 5.2 di bawah, buat manual lewat tinker. |

Kalau ingin data referensi awal (tingkat kelas, ruangan, mapel, tahun ajaran) langsung ada,
jalankan seeder yang bertanda "Opsional" satu per satu sesuai kebutuhan:

```bash
php artisan db:seed --class=GradeLevelSeeder --force
php artisan db:seed --class=RoomSeeder --force
php artisan db:seed --class=SubjectSeeder --force
php artisan db:seed --class=AcademicYearSeeder --force
```

Seeder lain bisa dijalankan sesuai kebutuhan data awal.

### 5.2 Membuat Administrator Awal di Production

Karena `InitialAdminSeeder` diblokir di production, buat administrator awal secara manual lewat
`php artisan tinker`:

```bash
php artisan tinker
```

Lalu jalankan (ganti nama, email, username, dan password sesuai kebutuhan):

```php
$person = App\Models\Person::create([
    'full_name' => 'Nama Anda',
    'email' => 'admin@domain-anda.sch.id',
]);

$user = App\Models\User::create([
    'person_id' => $person->id,
    'name' => 'Nama Anda',
    'username' => 'superadmin',
    'email' => 'admin@domain-anda.sch.id',
    'password' => Hash::make('PasswordKuatAnda123!'),
    'account_type' => 'internal',
    'status' => 'active',
    'failed_login_count' => 0,
]);

$role = App\Models\Role::where('name', 'super_admin')->firstOrFail();
$user->roles()->attach($role->id, ['assigned_at' => now()]);
```

Sebelum menjalankan `User::create()`, disarankan cek dulu apakah username/email sudah dipakai
(misalnya sisa dari percobaan sebelumnya), supaya tidak kena `UniqueConstraintViolationException`:

```php
App\Models\User::withTrashed()
    ->where('username', 'superadmin')
    ->orWhere('email', 'admin@domain-anda.sch.id')
    ->first();
```

Kalau user sudah ada tapi belum punya role (`$user->roles` kosong), cukup pasang role-nya saja
tanpa membuat `Person`/`User` baru:

```php
$role = App\Models\Role::where('name', 'super_admin')->firstOrFail();
$user->roles()->attach($role->id, ['assigned_at' => now()]);
```

---

## 6. Storage Link

Jika memakai upload file:

```bash
php artisan storage:link
```

Jika shared hosting tidak mendukung symbolic link, perlu strategi alternatif dengan folder public khusus.

---

## 7. Checklist Setelah Deploy

- [ ] Login superadmin bisa dilakukan.
- [ ] Dashboard terbuka.
- [ ] Sidebar tampil sesuai permission.
- [ ] Data madrasah bisa diedit.
- [ ] Data tahun ajaran/semester bisa dibuat.
- [ ] Data siswa bisa dicari dan difilter.
- [ ] Cetak PDF berjalan.
- [ ] Export Excel berjalan.
- [ ] `APP_DEBUG=false`.
- [ ] `.env` tidak terlihat publik.