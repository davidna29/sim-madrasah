# Tahap 12.7 — Akun Login Guru dan Pegawai

> Arsip hasil percakapan AI untuk proyek SIM Madrasah.
> Sumber asli: `SIM-MADRASAH-AI-HISTORY.md`, baris 29022–29734.
> File ini adalah arsip historis. Untuk melanjutkan proyek, baca `docs/AI-HANDOFF.md` terlebih dahulu.

---

Tahap 12.7: Membuat Akun Login dari Data Guru dan Pegawai
Pada tahap ini, kita membuat akun aplikasi untuk guru atau pegawai yang sudah ada di tabel employees.
Strukturnya tetap:
people
  ↓
employees
  ↓
users
Artinya:
people     = identitas orang
employees  = status kepegawaian
users      = akun login aplikasi
Satu person hanya boleh punya satu user, karena tabel users sudah memiliki person_id yang unik. Jadi sistem tidak boleh membuat akun ganda untuk orang yang sama.

Tahap 12.7A: Relasi Person ke User
Buka:
nano app/Models/Person.php
Tambahkan import:
use Illuminate\Database\Eloquent\Relations\HasOne;
Lalu tambahkan method ini di dalam class Person:
public function user(): HasOne
{
    return $this->hasOne(User::class);
}
Relasi ini penting agar dari data pegawai kita bisa tahu apakah orang tersebut sudah memiliki akun login atau belum.

Tahap 12.7B: Permission RBAC
Buka:
nano database/seeders/RbacSeeder.php
Tambahkan permission berikut ke array $permissions:
[
    'name' => 'employees.account.create',
    'module' => 'employees',
    'action' => 'account_create',
    'display_name' => 'Membuat Akun Pegawai',
    'description' => 'Membuat akun login aplikasi dari data guru atau pegawai.',
],
Permission ini khusus untuk membuat akun login dari data pegawai.

Tahap 12.7C: Buat Controller Akun Pegawai
Jalankan:
php artisan make:controller Admin/EmployeeAccountController
Buka:
nano app/Http/Controllers/Admin/EmployeeAccountController.php
Isi:
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class EmployeeAccountController extends Controller
{
    /**
     * Menampilkan form pembuatan akun pegawai.
     */
    public function create(Employee $employee): View|RedirectResponse
    {
        $employee->load('person.user');

        if ($employee->person?->user !== null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Guru atau pegawai ini sudah memiliki akun.');
        }

        $roles = Role::query()
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();

        return view('admin.employees.accounts.create', [
            'employee' => $employee,
            'roles' => $roles,
            'suggestedUsername' => 'pegawai'.$employee->id,
        ]);
    }

    /**
     * Menyimpan akun login pegawai.
     */
    public function store(
        Request $request,
        Employee $employee
    ): RedirectResponse {
        $employee->load('person.user');

        if ($employee->person?->user !== null) {
            return redirect()
                ->route('admin.employees.index')
                ->with('success', 'Guru atau pegawai ini sudah memiliki akun.');
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

        DB::transaction(function () use ($validated, $employee, $request): void {
            $user = new User();

            $user->forceFill([
                'person_id' => $employee->person_id,
                'name' => $employee->person->full_name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'account_type' => 'internal',
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
            ->route('admin.employees.index')
            ->with('success', 'Akun login guru atau pegawai berhasil dibuat.');
    }
}

Tahap 12.7D: Update EmployeeController
Buka:
nano app/Http/Controllers/Admin/EmployeeController.php
Cari method index().
Ubah bagian query dari:
$employees = Employee::query()
    ->with('person')
menjadi:
$employees = Employee::query()
    ->with('person.user')
Jadi method index() menjadi:
public function index(): View
{
    $employees = Employee::query()
        ->with('person.user')
        ->orderByDesc('is_active')
        ->orderBy('employee_type')
        ->orderBy('position')
        ->paginate(15);

    return view('admin.employees.index', [
        'employees' => $employees,
    ]);
}
Ini agar halaman daftar pegawai bisa menampilkan status akun login.

Tahap 12.7E: Route
Buka:
nano routes/web.php
Tambahkan import:
use App\Http\Controllers\Admin\EmployeeAccountController;
Di dalam group admin, tambahkan route berikut setelah route employees:
Route::get('/employees/{employee}/account/create', [EmployeeAccountController::class, 'create'])
    ->middleware('permission:employees.account.create')
    ->name('employees.accounts.create');

Route::post('/employees/{employee}/account', [EmployeeAccountController::class, 'store'])
    ->middleware('permission:employees.account.create')
    ->name('employees.accounts.store');

Tahap 12.7F: Update View Index Pegawai
Buka:
nano resources/views/admin/employees/index.blade.php
Tambahkan kolom baru setelah kolom Kontak.
Pada bagian header tabel, cari:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
Ubah menjadi:
<th class="px-4 py-3 text-left font-semibold text-gray-700">Kontak</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Akun</th>
<th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
Lalu setelah kolom kontak:
<td class="px-4 py-3">
    <div>{{ $employee->person?->email ?? '-' }}</div>
    <div class="text-xs text-gray-500">
        {{ $employee->person?->phone ?? '-' }}
    </div>
</td>
Tambahkan:
<td class="px-4 py-3">
    @if ($employee->person?->user)
        <div class="font-mono text-xs text-gray-900">
            {{ $employee->person->user->username }}
        </div>

        <div class="mt-1 text-xs text-gray-500">
            {{ $employee->person->user->status }}
        </div>
    @else
        @can('permission', 'employees.account.create')
            <a
                href="{{ route('admin.employees.accounts.create', $employee) }}"
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
Karena jumlah kolom bertambah dari 7 menjadi 8, ubah bagian empty:
<td colspan="7" class="px-4 py-6 text-center text-gray-500">
menjadi:
<td colspan="8" class="px-4 py-6 text-center text-gray-500">

Tahap 12.7G: Buat View Form Akun Pegawai
Buat folder:
mkdir -p resources/views/admin/employees/accounts
Buat file:
nano resources/views/admin/employees/accounts/create.blade.php
Isi:
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Buat Akun Guru/Pegawai
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Buat akun login aplikasi berdasarkan data pegawai.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 rounded-lg border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="font-semibold text-gray-900">
                    Data Pegawai
                </h3>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 text-sm">
                    <div>
                        <div class="text-gray-500">Nama</div>
                        <div class="font-medium text-gray-900">
                            {{ $employee->person?->full_name }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Nomor Pegawai</div>
                        <div class="font-medium text-gray-900">
                            {{ $employee->employee_number ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Jabatan</div>
                        <div class="font-medium text-gray-900">
                            {{ $employee->position ?? '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Email Data Pribadi</div>
                        <div class="font-medium text-gray-900">
                            {{ $employee->person?->email ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.employees.accounts.store', $employee) }}"
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
                            :value="old('email', $employee->person?->email)"
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
                        href="{{ route('admin.employees.index') }}"
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

Tahap 12.7H: Test Akun Pegawai
Buat test:
php artisan make:test Admin/EmployeeAccountTest
Buka:
nano tests/Feature/Admin/EmployeeAccountTest.php
Isi:
<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_open_employee_account_form(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'employees.account.create');

        $employee = $this->createEmployee();

        Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.employees.accounts.create', $employee));

        $response
            ->assertStatus(200)
            ->assertSee('Buat Akun Guru/Pegawai')
            ->assertSee('Guru Test');
    }

    public function test_user_can_create_employee_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'employees.account.create');

        $employee = $this->createEmployee();

        $role = Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.employees.accounts.store', $employee), [
                'username' => 'guru.test',
                'email' => 'guru.login@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('users', [
            'person_id' => $employee->person_id,
            'username' => 'guru.test',
            'email' => 'guru.login@test.local',
            'account_type' => 'internal',
            'status' => 'active',
        ]);

        $createdUser = User::query()
            ->where('username', 'guru.test')
            ->firstOrFail();

        $this->assertTrue(
            $createdUser->hasRole('guru_mata_pelajaran')
        );
    }

    public function test_employee_cannot_have_duplicate_account(): void
    {
        $admin = User::factory()->create();

        $this->grantPermissionToUser($admin, 'employees.account.create');

        $employee = $this->createEmployee();

        User::factory()->create([
            'person_id' => $employee->person_id,
            'username' => 'existing.user',
            'email' => 'existing@test.local',
        ]);

        $role = Role::create([
            'name' => 'guru_mata_pelajaran',
            'display_name' => 'Guru Mata Pelajaran',
            'is_system' => true,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('admin.employees.accounts.store', $employee), [
                'username' => 'new.user',
                'email' => 'new@test.local',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
                'role_id' => $role->id,
                'status' => 'active',
            ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseMissing('users', [
            'username' => 'new.user',
        ]);
    }

    private function createEmployee(): Employee
    {
        $person = Person::create([
            'full_name' => 'Guru Test',
            'email' => 'guru@test.local',
        ]);

        return Employee::create([
            'person_id' => $person->id,
            'employee_number' => 'EMP-100',
            'employee_type' => 'teacher',
            'position' => 'Guru Mata Pelajaran',
            'is_teacher' => true,
            'is_active' => true,
        ]);
    }

    private function grantPermissionToUser(
        User $user,
        string $permissionName
    ): void {
        $role = Role::firstOrCreate(
            [
                'name' => 'test_employee_account_role_'.str_replace('.', '_', $permissionName),
            ],
            [
                'display_name' => 'Test Employee Account Role',
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

Tahap 12.7I: Jalankan Pemeriksaan
Jalankan:
php artisan db:seed --class=RbacSeeder
php artisan optimize:clear
php artisan route:list --path=employees
./vendor/bin/pint
./vendor/bin/pint --test
php artisan test
npm run build
Target route tambahan:
GET|HEAD  admin/employees/{employee}/account/create
POST      admin/employees/{employee}/account
Target test:
90+ tests passed
build berhasil

Tahap 12.7J: Uji Manual
Jalankan:
php artisan db:seed --class=EmployeeSeeder
php artisan db:seed --class=RbacSeeder
Login sebagai superadmin.
Buka:
open http://sim-madrasah.test/admin/employees
Pada pegawai yang belum punya akun, klik:
Buat Akun
Isi:
Username: guru.test
Email Login: guru.login@test.local
Password Awal: buat password sendiri
Role Awal: Guru Mata Pelajaran
Status Akun: Aktif
Setelah berhasil, logout dan coba login menggunakan akun baru tersebut.

Tahap 12.7K: Commit
Jika semua aman:
git status
Lalu:
git add app/Models/Person.php \
        app/Http/Controllers/Admin/EmployeeAccountController.php \
        app/Http/Controllers/Admin/EmployeeController.php \
        database/seeders/RbacSeeder.php \
        routes/web.php \
        resources/views/admin/employees/index.blade.php \
        resources/views/admin/employees/accounts/ \
        tests/Feature/Admin/EmployeeAccountTest.php
Commit:
git commit -m "feat: add employee account creation"
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
Setelah berhasil, kita lanjut ke Tahap 12.8: Data Siswa.














