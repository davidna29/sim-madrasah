# Database — SIM Madrasah

Dokumen ini menjelaskan struktur database utama berdasarkan migration yang ada.

---

## 1. Database Development dan Production

- Lokal/testing: SQLite dapat digunakan.
- Production: MySQL/MariaDB lebih disarankan untuk shared hosting.

---

## 2. Tabel Sistem Laravel

| Tabel | Fungsi |
|---|---|
| `users` | Akun pengguna |
| `password_reset_tokens` | Token reset password |
| `sessions` | Session login |
| `cache` | Cache Laravel |
| `cache_locks` | Lock cache |
| `jobs` | Queue jobs bawaan Laravel |
| `job_batches` | Batch jobs |
| `failed_jobs` | Job gagal |

---

## 3. Tabel Identitas

### `people`

Menyimpan identitas orang umum.

Kolom penting:

- `id`
- `national_id_number`
- `full_name`
- `birth_place`
- `birth_date`
- `gender`
- `religion`
- `email`
- `phone`
- `address`
- `created_at`
- `updated_at`

Dipakai oleh:

- `users`
- `employees`
- `students`
- `student_guardians`

---

## 4. Tabel Akun dan RBAC

### `users`

Kolom bawaan dan tambahan:

- `id`
- `person_id`
- `name`
- `username`
- `email`
- `password`
- `account_type`
- `status`
- `last_login_at`
- `last_login_ip`
- `password_changed_at`
- `locked_until`
- `failed_login_count`
- `email_verified_at`
- `remember_token`
- `deleted_at`
- `created_at`
- `updated_at`

### `roles`

- `name`
- `display_name`
- `description`
- `is_system`
- `is_active`

### `permissions`

- `name`
- `module`
- `action`
- `display_name`
- `description`
- `is_active`

### Pivot RBAC

- `role_permissions`
- `user_roles`
- `user_permissions`

`user_permissions` memiliki `permission_mode` dengan nilai utama `allow` atau `deny`.

---

## 5. Tabel Identitas Madrasah

### `madrasahs`

Kolom penting:

- `code`
- `name`
- `nsm`
- `npsn`
- `email`
- `phone`
- `address`
- `village`
- `district`
- `city`
- `province`
- `postal_code`
- `timezone`
- `is_active`

---

## 6. Tabel Akademik Dasar

### `academic_years`

- `code`
- `name`
- `start_date`
- `end_date`
- `status`
- `is_active`
- `is_locked`
- `locked_at`
- `locked_by`

### `semesters`

- `academic_year_id`
- `code`
- `name`
- `semester_type`
- `start_date`
- `end_date`
- `status`
- `is_active`
- `is_locked`
- `locked_at`
- `locked_by`

Unique penting:

- `academic_year_id + semester_type`
- `academic_year_id + code`

---

## 7. Tabel Kelas, Ruangan, dan Mapel

### `grade_levels`

- `code`
- `name`
- `level_number`
- `description`
- `is_active`

### `rooms`

- `code`
- `name`
- `room_type`
- `capacity`
- `location`
- `description`
- `is_active`

### `class_groups`

- `academic_year_id`
- `grade_level_id`
- `room_id`
- `homeroom_teacher_user_id`
- `code`
- `name`
- `parallel_name`
- `capacity`
- `status`
- `is_active`

Unique penting:

- `academic_year_id + code`

### `subjects`

- `code`
- `name`
- `subject_group`
- `is_local_content`
- `is_religious`
- `is_active`
- `description`

---

## 8. Tabel Pegawai

### `employees`

- `person_id`
- `employee_number`
- `nip`
- `nuptk`
- `employee_type`
- `employment_status`
- `position`
- `join_date`
- `end_date`
- `education_level`
- `major`
- `is_teacher`
- `is_active`
- `notes`

Unique penting:

- `person_id`
- `employee_number`
- `nip`
- `nuptk`

---

## 9. Tabel Siswa

### `students`

- `person_id`
- `admission_academic_year_id`
- `student_number`
- `nisn`
- `registration_number`
- `admission_date`
- `graduation_date`
- `status`
- `previous_school`
- `notes`
- `is_active`

Unique penting:

- `person_id`
- `student_number`
- `nisn`
- `registration_number`

Status siswa yang digunakan pada validasi:

```txt
active
inactive
transferred
graduated
alumni
```

---

## 10. Tabel Riwayat Kelas Siswa

### `student_class_histories`

- `student_id`
- `academic_year_id`
- `semester_id`
- `class_group_id`
- `status`
- `start_date`
- `end_date`
- `is_current`
- `assigned_by`
- `notes`

Unique penting:

- `student_id + semester_id`

Prinsip:

- Jangan hapus histori lama.
- Jika siswa pindah/naik kelas, buat record baru.
- Tampilan kelas saat ini memakai `is_current = true`.

---

## 11. Tabel Wali Siswa

### `student_guardians`

- `student_id`
- `person_id`
- `relationship`
- `occupation`
- `education_level`
- `income_range`
- `is_primary_contact`
- `is_emergency_contact`
- `is_financial_responsible`
- `is_active`
- `notes`

Unique penting:

- `student_id + person_id`

---
## 12. Tabel Fondasi Jadwal Pelajaran

### `schedule_templates`

Fungsi:

- Menyimpan model/template jadwal pelajaran.
- Satu template dapat dipakai oleh banyak rombel.
- Template berisi pola hari aktif, hari libur, maksimal slot, dan durasi standar slot.

Kolom utama:

- `code`
- `name`
- `description`
- `active_days`
- `holiday_days`
- `max_slots_per_day`
- `standard_slot_duration_minutes`
- `status`
- `is_active`
- `created_by`

Unique penting:

- `code`

Index penting:

- `status`
- `is_active`
- `created_by`

Catatan:

- `active_days` dan `holiday_days` disimpan dalam bentuk JSON array.
- Contoh nilai hari:
  - `1` = Senin
  - `2` = Selasa
  - `3` = Rabu
  - `4` = Kamis
  - `5` = Jumat
  - `6` = Sabtu
  - `7` = Minggu

### `schedule_template_slots`

Fungsi:

- Menyimpan slot jam pada template jadwal.
- Slot dapat berupa KBM atau non-KBM.
- Modul jadwal lanjutan hanya boleh memasukkan mata pelajaran ke slot KBM.

Kolom utama:

- `schedule_template_id`
- `day_of_week`
- `sort_order`
- `starts_at`
- `ends_at`
- `slot_type`
- `label`
- `is_teaching_slot`
- `notes`

Unique penting:

- `schedule_template_id + day_of_week + sort_order`

Index penting:

- `day_of_week`
- `slot_type`
- `is_teaching_slot`

Nilai awal `slot_type` yang direncanakan:

```txt
kbm
istirahat
upacara
kegiatan_rutin
```
Catatan:

is_teaching_slot = true berarti slot boleh diisi mata pelajaran.
is_teaching_slot = false berarti slot hanya tampil sebagai kegiatan non-KBM.
Jam mulai dan selesai disimpan per slot agar template tetap fleksibel untuk kasus khusus seperti hari Jumat.
class_group_schedule_templates

Fungsi:

Menghubungkan rombel dengan template jadwal tertentu.
Menjaga agar satu rombel hanya memiliki satu assignment template untuk satu tahun ajaran dan semester.

Kolom utama:

academic_year_id
semester_id
class_group_id
schedule_template_id
is_active
assigned_at
assigned_by
notes

Unique penting:

academic_year_id + semester_id + class_group_id

Index penting:

schedule_template_id
is_active
assigned_by

Catatan:

Jika rombel ingin dipindahkan ke template lain pada semester yang sama, record assignment yang ada harus diperbarui, bukan dibuat dobel.
---

## 13. Relasi Utama

```txt
people 1--1 users
people 1--1 employees
people 1--1 students
people 1--N student_guardians

academic_years 1--N semesters
academic_years 1--N class_groups
academic_years 1--N students as admission year

grade_levels 1--N class_groups
rooms 1--N class_groups
users 1--N class_groups as homeroom teacher

students 1--N student_class_histories
students 1--N student_guardians
class_groups 1--N student_class_histories
semesters 1--N student_class_histories

schedule_templates 1--N schedule_template_slots
schedule_templates 1--N class_group_schedule_templates
academic_years 1--N class_group_schedule_templates
semesters 1--N class_group_schedule_templates
class_groups 1--N class_group_schedule_templates
users 1--N schedule_templates as creator
users 1--N class_group_schedule_templates as assigner
```

---

## 14. Catatan Perubahan Database Berikutnya

Jika membuat modul baru, selalu tambahkan:

- nama migration,
- nama tabel,
- kolom utama,
- relasi,
- unique/index penting,
- aturan data yang tidak boleh dilanggar.
