# Tahap 12.10 — Akun Siswa

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 32422–33161.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.10: Akun Siswa dari Data Student
Pada tahap ini kita membuat akun login untuk siswa yang sudah ada di tabel students.
Strukturnya tetap:
people
  ↓
students
  ↓
users
Artinya:
people   = identitas orang
students = data kesiswaan
users    = akun login aplikasi
Satu siswa hanya boleh punya satu akun login karena users.person_id sudah unik.

Tahap 12.10A: Pastikan Relasi Person ke User
Buka:
nano app/Models/Person.php
Pastikan sudah ada import ini:
use Illuminate\Database\Eloquent\Relations\HasOne;
Pastikan method ini sudah ada:
public function user(): HasOne
{
    return $this->hasOne(User::class);
}
Kalau sudah ada dari tahap akun pegawai, jangan ditulis ulang.

Tahap 12.10B: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'students.account.create',
    'module' => 'students',
    'action' => 'account_create',
    'display_name' => 'Membuat Akun Siswa',
    'description' => 'Membuat akun login aplikasi dari data siswa.',
],
Permission ini khusus untuk membuat akun login siswa.

Tahap 12.10C: Buat Controller Akun Siswa
Jalankan:
php artisan make:controller Admin/StudentAccountController
Buka:
nano app/Http/Controllers/Admin/StudentAccountController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class StudentAccountController extends Controller
{
    /**
     * Menampilkan form pembuatan akun siswa.
     */
    public function create(Student $student): View|RedirectResponse
    {
        $student->load('person.user');

        if ($student->person?->user !== null) {
            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Siswa ini sudah memiliki akun.');
        }

        $roles = Role::query()
            ->where('is_active', true)
            ->whereIn('name', [
                'siswa',
                'orang_tua',
            ])
            ->orderBy('display_name')
            ->get();

        return view('admin.students.accounts.create', [
            'student' => $student,
            'roles' => $roles,
            'suggestedUsername' => $this->suggestUsername($student),
        ]);
    }

    /**
     * Menyimpan akun login siswa.
     */
    public function store(
        Request $request,
        Student $student
    ): RedirectResponse {
        $student->load('person.user');

        if ($student->person?->user !== null) {
            return redirect()
                ->route('admin.students.index')
                ->with('success', 'Siswa ini sudah memiliki akun.');
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
                'exists:roles,id',
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);

        DB::transaction(function () use ($validated, $student, $request): void {
            $user = new User();

            $user->forceFill([
                'person_id' => $student->person_id,
                'name' => $student->person->full_name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'student',
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
            ->route('admin.students.index')
            ->with('success', 'Akun login siswa berhasil dibuat.');
    }

    private function suggestUsername(Student $student): string
    {
        if ($student->student_number) {
            return Str::lower(
                Str::slug($student->student_number, '.')
            );
        }

        return 'siswa'.$student->id;
    }
}

Tahap 12.10D: Update StudentController
Buka:
nano app/Http/Controllers/Admin/StudentController.php
Cari method index().
Ubah bagian query dari:
$students = Student::query()
    ->with([
        'person',
        'admissionAcademicYear',
    ])
menjadi:
$students = Student::query()
    ->with([
        'person.user',
        'admissionAcademicYear',
    ])
Hasil method index() menjadi:
public function index(): View
{
    $students = Student::query()
        ->with([
            'person.user',
            'admissionAcademicYear',
        ])
        ->orderByDesc('is_active')
        ->orderBy('student_number')
        ->paginate(15);

    return view('admin.students.index', [
        'students' => $students,
    ]);
}
Ini agar halaman siswa bisa menampilkan status akun login.

Tahap 12.10E: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\StudentAccountController;
Di dalam group admin, tambahkan route berikut setelah route students:
Route::get('/students/{student}/account/create', [StudentAccountController::class, 'create'])
    ->middleware('permission:students.account.create')
    ->name('students.accounts.create');

Route::post('/students/{student}/account', [StudentAccountController::class, 'store'])
    ->middleware('permission:students.account.create')
    ->name('students.accounts.store');

Tahap 12.10F: Update View Data Siswa
Buka:
nano resources/views/admin/students/index.blade.php
Cari header tabel:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
Ubah menjadi:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Akun</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Aksi</th>
Lalu cari kolom kontak:
<td class="px-4 py-3">
    <div>{{ $student->person?->email ?? '-' }}</div>
    <div class="text-xs text-gray-500">
        {{ $student->person?->phone ?? '-' }}
    </div>
</td>
Tambahkan setelahnya:
<td class="px-4 py-3">
    @if ($student->person?->user)
        <div class="font-mono text-xs text-gray-900">
            {{ $student->person->user->username }}
        </div>

        <div class="mt-1 text-xs text-gray-500">
            {{ $student->person->user->status }}
        </div>
    @else
        @can('permission', 'students.account.create')
            <a
                href="{{ route('admin.students.accounts.create', $student) }}"
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

Tahap 12.10G: Buat View Form Akun Siswa
Buat folder:
mkdir -p resources/views/admin/students/accounts
Buat file:
nano resources/views/admin/students/accounts/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Buat Akun Siswa
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat akun login aplikasi berdasarkan data siswa.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">
                    Data Siswa
                </h3>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <div>
                        <div class="text-gray-500">Nama</div>
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
                        <div class="text-gray-500">NISN</div>
                        <div class="font-medium text-gray-900">
                            {{ $student->nisn ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Email Data Pribadi</div>
                        <div class="font-medium text-gray-900">
                            {{ $student->person?->email ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.students.accounts.store', $student) }}"
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
                            :value="old('email', $student->person?->email)"
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
                        href="{{ route('admin.students.index') }}"
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

Tahap 12.10H: Test Akun Siswa
Buat test:
php artisan make:test Admin/StudentAccountTest
Buka:
nano tests/Feature/Admin/StudentAccountTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_student_account_form(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'students.account.create');

        $student = $this->createStudent();

        Role::create([
            'name' => 'siswa',
            'display_name' => 'Siswa',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.students.accounts.create', $student));

        $response
            ->assertStatus(200)
            ->assertSee('Buat Akun Siswa')
            ->assertSee('Ahmad Siswa');
    }

    public function test_user_can_create_student_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'students.account.create');

        $student = $this->createStudent();

        $role = Role::create([
            'name' => 'siswa',
            'display_name' => 'Siswa',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.accounts.store', $student), [
                'username' => 'siswa.test',
                'email' => 'siswa.login@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', [
            'person_id' => $student->person_id,
            'username' => 'siswa.test',
            'email' => 'siswa.login@test.local',
            'account_type' => 'student',
            'status' => 'active',
        ]);

        $createdUser = User::query()
            ->where('username', 'siswa.test')
            ->firstOrFail();

        $this->assertTrue(
            $createdUser->hasRole('siswa')
        );
    }

    public function test_student_cannot_have_duplicate_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'students.account.create');

        $student = $this->createStudent();

        User::factory()->create([
            'person_id' => $student->person_id,
            'username' => 'existing.student',
            'email' => 'existing.student@test.local',
        ]);

        $role = Role::create([
            'name' => 'siswa',
            'display_name' => 'Siswa',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.students.accounts.store', $student), [
                'username' => 'new.student',
                'email' => 'new.student@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('users', [
            'username' => 'new.student',
        ]);
    }

    private function createStudent(): Student
    {
        $person = Person::create([
            'full_name' => 'Ahmad Siswa',
            'email' => 'ahmad.siswa@test.local',
        ]);

        return Student::create([
            'person_id' => $person->id,
            'student_number' => 'SIS-100',
            'nisn' => '1000000100',
            'registration_number' => 'REG-100',
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
                'name' => 'test_student_account_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Student Account Role',
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

Tahap 12.10I: Jalankan Pemeriksaan
Jalankan:
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=students
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route tambahan:
GET|HEAD  admin/students/{student}/account/create
POST      admin/students/{student}/account
Target test:
99+ tests passed
build berhasil

Tahap 12.10J: Uji Manual
Jalankan:
php artisan db:seed --class=StudentSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/students
Pada siswa yang belum punya akun, klik:
Buat Akun
Isi:
Username: siswa.test
Email Login: siswa.login@test.local
Password Awal: buat password sendiri
Role Awal: Siswa
Status Akun: Aktif
Setelah berhasil, logout dan coba login menggunakan akun siswa tersebut.

Tahap 12.10K: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/Person.php \
        app/Http/Controllers/Admin/StudentAccountController.php \
        app/Http/Controllers/Admin/StudentController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/students/index.blade.php \
        resources/views/admin/students/accounts/ \
        tests/Feature/Admin/StudentAccountTest.php
Commit:
git commit -m "feat: add student account creation"
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
Setelah berhasil, kita lanjut ke Tahap 12.11: Data Orang Tua/Wali Siswa.















