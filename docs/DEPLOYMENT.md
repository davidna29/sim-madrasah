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
php artisan db:seed --class=InitialAdminSeeder --force
```

Seeder lain bisa dijalankan sesuai kebutuhan data awal.

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
