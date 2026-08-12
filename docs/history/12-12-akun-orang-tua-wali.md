# Tahap 12.12 — Akun Orang Tua/Wali Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 34612–35430.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.12: Akun Orang Tua/Wali Siswa
Pada tahap ini kita membuat akun login untuk orang tua atau wali siswa.
Strukturnya tetap:
people
  ↓
student_guardians
  ↓
users
Artinya:
people             = identitas orang tua/wali
student_guardians = hubungan wali dengan siswa
users              = akun login aplikasi
Satu orang tua atau wali hanya boleh punya satu akun login karena users.person_id sudah unik.
Catatan penting dari kasus username sebelumnya: kita tetap gunakan username tanpa titik. Contoh aman:
wali1
wali_ahmad
wali-001

Tahap 12.12A: Pastikan Relasi Person ke User
Buka:
nano app/Models/Person.php
Pastikan sudah ada import:
use Illuminate\Database\Eloquent\Relations\HasOne;
Pastikan method ini sudah ada:
public function user(): HasOne
{
    return $this->hasOne(User::class);
}
Kalau sudah ada dari tahap akun pegawai atau akun siswa, jangan ditulis ulang.

Tahap 12.12B: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'student_guardians.account.create',
    'module' => 'student_guardians',
    'action' => 'account_create',
    'display_name' => 'Membuat Akun Orang Tua/Wali',
    'description' => 'Membuat akun login aplikasi dari data orang tua atau wali siswa.',
],
Permission ini khusus untuk membuat akun login wali.

Tahap 12.12C: Buat Controller Akun Orang Tua/Wali
Jalankan:
php artisan make:controller Admin/StudentGuardianAccountController
Buka:
nano app/Http/Controllers/Admin/StudentGuardianAccountController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StudentGuardianAccountController extends Controller
{
    /**
     * Menampilkan form pembuatan akun orang tua atau wali.
     */
    public function create(
        Student $student,
        StudentGuardian $guardian
    ): View|RedirectResponse {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $guardian->load('person.user');

        if ($guardian->person?->user !== null) {
            return redirect()
                ->route('admin.students.guardians.index', $student)
                ->with('success', 'Orang tua atau wali ini sudah memiliki akun.');
        }

        $roles = Role::query()
            ->where('is_active', true)
            ->where('name', 'orang_tua')
            ->orderBy('display_name')
            ->get();

        return view('admin.students.guardians.accounts.create', [
            'student' => $student->load('person'),
            'guardian' => $guardian,
            'roles' => $roles,
            'suggestedUsername' => 'wali'.$guardian->id,
        ]);
    }

    /**
     * Menyimpan akun login orang tua atau wali.
     */
    public function store(
        Request $request,
        Student $student,
        StudentGuardian $guardian
    ): RedirectResponse {
        $this->ensureGuardianBelongsToStudent($student, $guardian);

        $guardian->load('person.user');

        if ($guardian->person?->user !== null) {
            return redirect()
                ->route('admin.students.guardians.index', $student)
                ->with('success', 'Orang tua atau wali ini sudah memiliki akun.');
        }

        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                'unique:users,username',
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where('name', 'orang_tua')
                            ->where('is_active', true)
                    ),
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        DB::transaction(function () use ($validated, $guardian, $request): void {
            $user = new User();

            $user->forceFill([
                'person_id' => $guardian->person_id,
                'name' => $guardian->person->full_name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'parent',
                'status' => $validated['status'],
                'password_changed_at' => now(),
                'failed_login_count' => 0,
                'locked_until' => null,
            ])->save();

            $user->roles()->syncWithoutDetaching([
                $validated['role_id'] => [
                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ],
            ]);
        });

        return redirect()
            ->route('admin.students.guardians.index', $student)
            ->with('success', 'Akun login orang tua atau wali berhasil dibuat.');
    }

    private function ensureGuardianBelongsToStudent(
        Student $student,
        StudentGuardian $guardian
    ): void {
        abort_unless(
            (int) $guardian->student_id === (int) $student->id,
            404
        );
    }
}

Tahap 12.12D: Update StudentGuardianController
Buka:
nano app/Http/Controllers/Admin/StudentGuardianController.php
Cari method index().
Ubah bagian query dari:
$guardians = $student->guardians()
    ->with('person')
menjadi:
$guardians = $student->guardians()
    ->with('person.user')
Hasil method index() menjadi:
public function index(Student $student): View
{
    $student->load('person');

    $guardians = $student->guardians()
        ->with('person.user')
        ->orderByDesc('is_primary_contact')
        ->orderBy('relationship')
        ->paginate(15);

    return view('admin.students.guardians.index', [
        'student' => $student,
        'guardians' => $guardians,
    ]);
}
Tujuannya agar halaman wali bisa menampilkan status akun login.

Tahap 12.12E: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\StudentGuardianAccountController;
Di dalam group admin, tambahkan setelah route guardians:
Route::get('/students/{student}/guardians/{guardian}/account/create', [StudentGuardianAccountController::class, 'create'])
    ->middleware('permission:student_guardians.account.create')
    ->name('students.guardians.accounts.create');

Route::post('/students/{student}/guardians/{guardian}/account', [StudentGuardianAccountController::class, 'store'])
    ->middleware('permission:student_guardians.account.create')
    ->name('students.guardians.accounts.store');

Tahap 12.12F: Update View Orang Tua/Wali
Buka:
nano resources/views/admin/students/guardians/index.blade.php
Cari header tabel:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Pekerjaan</th>
Ubah menjadi:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Akun</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Pekerjaan</th>
Lalu cari kolom kontak:
<td class="px-4 py-3">
    <div>{{ $guardian->person?->email ?? '-' }}</div>
    <div class="text-xs text-gray-500">
        {{ $guardian->person?->phone ?? '-' }}
    </div>
</td>
Tambahkan setelahnya:
<td class="px-4 py-3">
    @if ($guardian->person?->user)
        <div class="font-mono text-xs text-gray-900">
            {{ $guardian->person->user->username }}
        </div>

        <div class="mt-1 text-xs text-gray-500">
            {{ $guardian->person->user->status }}
        </div>
    @else
        @can('permission', 'student_guardians.account.create')
            <a
                href="{{ route('admin.students.guardians.accounts.create', [$student, $guardian]) }}"
                class="text-sm font-medium text-green-700 hover:text-green-900"
            >
                Buat Akun
            </a>
        @else
            <span class="text-xs text-gray-500">
                Belum ada akun
            </span>
        @endcan
    @endif
</td>
Karena jumlah kolom bertambah dari 7 menjadi 8, ubah empty state:
<td colspan="7" class="px-4 py-6 text-center text-gray-500">
menjadi:
<td colspan="8" class="px-4 py-6 text-center text-gray-500">

Tahap 12.12G: Buat View Form Akun Wali
Buat folder:
mkdir -p resources/views/admin/students/guardians/accounts
Buat file:
nano resources/views/admin/students/guardians/accounts/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Buat Akun Orang Tua/Wali
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat akun login untuk orang tua atau wali siswa.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">
                    Data Siswa dan Wali
                </h3>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <div>
                        <div class="text-gray-500">Nama Siswa</div>
                        <div class="font-medium text-gray-900">
                            {{ $student->person?->full_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">NIS</div>
                        <div class="font-medium text-gray-900">
                            {{ $student->student_number ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Nama Wali</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->person?->full_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Hubungan</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->relationship }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Email Data Pribadi</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->person?->email ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Telepon</div>
                        <div class="font-medium text-gray-900">
                            {{ $guardian->person?->phone ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.students.guardians.accounts.store', [$student, $guardian]) }}"
                class="bg-white shadow-sm sm:rounded-lg border border-gray-100"
            >
                @csrf

                <div class="p-6 space-y-6">
                    <div>
                        <x-input-label for="username" value="Username" />

                        <x-text-input
                            id="username"
                            name="username"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('username', $suggestedUsername)"
                            required
                        />

                        <x-input-error :messages="$errors->get('username')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email Login" />

                        <x-text-input
                            id="email"
                            name="email"
                            type="email"
                            class="mt-1 block w-full"
                            :value="old('email', $guardian->person?->email)"
                            required
                        />

                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Password Awal" />

                        <x-text-input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />

                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Password" />

                        <x-text-input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                        />
                    </div>

                    <div>
                        <x-input-label for="role_id" value="Role Awal" />

                        <select
                            id="role_id"
                            name="role_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="">Pilih Role</option>

                            @foreach ($roles as $role)
                                <option
                                    value="{{ $role->id }}"
                                    @selected((string) old('role_id') === (string) $role->id)
                                >
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </select>

                        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="status" value="Status Akun" />

                        <select
                            id="status"
                            name="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required
                        >
                            <option value="active" @selected(old('status', 'active') === 'active')>
                                Aktif
                            </option>

                            <option value="inactive" @selected(old('status') === 'inactive')>
                                Nonaktif
                            </option>
                        </select>

                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-between border-t border-gray-100 bg-gray-50 px-6 py-4">
                    <a
                        href="{{ route('admin.students.guardians.index', $student) }}"
                        class="rounded-md border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="rounded-md bg-green-700 px-5 py-2 text-sm font-medium text-white hover:bg-green-800"
                    >
                        Buat Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

Tahap 12.12H: Test Akun Orang Tua/Wali
Buat test:
php artisan make:test Admin/StudentGuardianAccountTest
Buka:
nano tests/Feature/Admin/StudentGuardianAccountTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGuardianAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_guardian_account_form(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        Role::create([
            'name' => 'orang_tua',
            'display_name' => 'Orang Tua',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.students.guardians.accounts.create', [$student, $guardian]));

        $response
            ->assertStatus(200)
            ->assertSee('Buat Akun Orang Tua/Wali')
            ->assertSee('Wali Ahmad');
    }

    public function test_user_can_create_guardian_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        $role = Role::create([
            'name' => 'orang_tua',
            'display_name' => 'Orang Tua',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.guardians.accounts.store', [$student, $guardian]), [
                'username' => 'wali_test',
                'email' => 'wali.login@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseHas('users', [
            'person_id' => $guardian->person_id,
            'username' => 'wali_test',
            'email' => 'wali.login@test.local',
            'account_type' => 'parent',
            'status' => 'active',
        ]);

        $createdUser = User::query()
            ->where('username', 'wali_test')
            ->firstOrFail();

        $this->assertTrue(
            $createdUser->hasRole('orang_tua')
        );
    }

    public function test_guardian_cannot_have_duplicate_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        User::factory()->create([
            'person_id' => $guardian->person_id,
            'username' => 'existing_wali',
            'email' => 'existing.wali@test.local',
        ]);

        $role = Role::create([
            'name' => 'orang_tua',
            'display_name' => 'Orang Tua',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.guardians.accounts.store', [$student, $guardian]), [
                'username' => 'new_wali',
                'email' => 'new.wali@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.guardians.index', $student));

        $this->assertDatabaseMissing('users', [
            'username' => 'new_wali',
        ]);
    }

    public function test_cannot_create_account_for_guardian_from_different_student_route(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'student_guardians.account.create');

        [$student, $guardian] = $this->createStudentGuardianData();

        $otherStudent = $this->createStudent(
            'Siswa Lain',
            'siswa.lain@test.local',
            'SIS-002'
        );

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.students.guardians.accounts.create', [$otherStudent, $guardian]));

        $response->assertNotFound();
    }

    /**
     * @return array{0: Student, 1: StudentGuardian}
     */
    private function createStudentGuardianData(): array
    {
        $student = $this->createStudent(
            'Ahmad Siswa',
            'ahmad.siswa@test.local',
            'SIS-001'
        );

        $guardianPerson = Person::create([
            'full_name' => 'Wali Ahmad',
            'email' => 'wali.ahmad@test.local',
        ]);

        $guardian = StudentGuardian::create([
            'student_id' => $student->id,
            'person_id' => $guardianPerson->id,
            'relationship' => 'father',
            'occupation' => 'Petani',
            'education_level' => 'SMA',
            'income_range' => '1-3 juta',
            'is_primary_contact' => true,
            'is_emergency_contact' => true,
            'is_financial_responsible' => true,
            'is_active' => true,
        ]);

        return [$student, $guardian];
    }

    private function createStudent(
        string $fullName,
        string $email,
        string $studentNumber
    ): Student {
        $person = Person::create([
            'full_name' => $fullName,
            'email' => $email,
        ]);

        return Student::create([
            'person_id' => $person->id,
            'student_number' => $studentNumber,
            'nisn' => fake()->unique()->numerify('##########'),
            'registration_number' => 'REG-'.$studentNumber,
            'admission_date' => '2026-07-01',
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_guardian_account_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Guardian Account Role',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $dashboardPermission = Permission::firstOrCreate(
            [
                'name' => 'dashboard.view',
            ],
            [
                'module' => 'dashboard',
                'action' => 'view',
                'display_name' => 'Melihat Dashboard',
                'is_active' => true,
            ]
        );

        $role->permissions()->syncWithoutDetaching([
            $dashboardPermission->id,
        ]);

        [$module, $action] = explode('.', $permissionName);

        $permission = Permission::firstOrCreate(
            [
                'name' => $permissionName,
            ],
            [
                'module' => $module,
                'action' => $action,
                'display_name' => $permissionName,
                'is_active' => true,
            ]
        );

        $role->permissions()->syncWithoutDetaching([
            $permission->id,
        ]);

        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'assigned_at' => now(),
            ],
        ]);
    }
}

Tahap 12.12I: Jalankan Pemeriksaan
Jalankan:
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=guardians
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route tambahan:
GET|HEAD  admin/students/{student}/guardians/{guardian}/account/create
POST      admin/students/{student}/guardians/{guardian}/account
Target test:
106+ tests passed
build berhasil

Tahap 12.12J: Uji Manual
Jalankan:
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=StudentGuardianSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Klik:
Wali
Pada wali yang belum punya akun, klik:
Buat Akun
Isi contoh:
Username: wali_ahmad
Email Login: wali.ahmad.login@test.local
Password Awal: buat password sendiri
Role Awal: Orang Tua
Status Akun: Aktif
Setelah berhasil, logout dan coba login menggunakan akun wali tersebut.

Tahap 12.12K: Commit
Jika semua aman:
git status
Lalu:
git add app/Http/Controllers/Admin/StudentGuardianAccountController.php \
        app/Http/Controllers/Admin/StudentGuardianController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/guardians/index.blade.php \
        resources/views/admin/students/guardians/accounts/ \
        tests/Feature/Admin/StudentGuardianAccountTest.php
Commit:
git commit -m "feat: add student guardian account creation"
Push:
git push
Periksa:
git status
Target:
nothing to commit, working tree clean
Kirim hasil:
php artisan test
npm run build
git status
Setelah berhasil, kita lanjut ke Tahap 12.13: Ringkasan Portofolio Digital Siswa.















