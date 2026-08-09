<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassGroupController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\MadrasahProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware([
        'auth',
        'verified',
        'active.account',
        'permission:dashboard.view',
    ])
    ->name('dashboard');

Route::middleware([
    'auth',
    'verified',
    'active.account',
])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');

    Route::get('/roles/{role}', [RoleController::class, 'show'])
        ->middleware('permission:roles.view')
        ->name('roles.show');

    // --- TAMBAHAN ROUTE UNTUK ATUR PERMISSION ROLE ---
    Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])
        ->middleware('permission:roles.permission.assign')
        ->name('roles.permissions');

    Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
        ->middleware('permission:roles.permission.assign')
        ->name('roles.permissions.update');

    Route::get('/permissions', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.view')
        ->name('permissions.index');

    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])
        ->middleware('permission:permissions.view')
        ->name('permissions.show');

    // --- TAMBAHAN ROUTE UNTUK MANAJEMEN PROFIL MADRASAH ---
    Route::get('/madrasah', [MadrasahProfileController::class, 'edit'])
        ->middleware('permission:madrasah.view')
        ->name('madrasah.edit');

    Route::put('/madrasah', [MadrasahProfileController::class, 'update'])
        ->middleware('permission:madrasah.update')
        ->name('madrasah.update');

    // --- TAMBAHAN ROUTE UNTUK MANAJEMEN TAHUN AKADEMIK DAN SEMESTER ---
    Route::get('/academic-years', [AcademicYearController::class, 'index'])
        ->middleware('permission:academic_years.view')
        ->name('academic-years.index');

    Route::get('/academic-years/create', [AcademicYearController::class, 'create'])
        ->middleware('permission:academic_years.create')
        ->name('academic-years.create');

    Route::post('/academic-years', [AcademicYearController::class, 'store'])
        ->middleware('permission:academic_years.create')
        ->name('academic-years.store');

    Route::put('/semesters/{semester}/activate', [AcademicYearController::class, 'activateSemester'])
        ->middleware('permission:academic_years.activate')
        ->name('semesters.activate');

    Route::put('/semesters/{semester}/lock', [AcademicYearController::class, 'lockSemester'])
        ->middleware('permission:academic_years.lock')
        ->name('semesters.lock');
    Route::get('/grade-levels', [GradeLevelController::class, 'index'])
        ->middleware('permission:grade_levels.view')
        ->name('grade-levels.index');

    // Tambahkan route untuk create, store, edit, dan update grade levels
    Route::get('/grade-levels/create', [GradeLevelController::class, 'create'])
        ->middleware('permission:grade_levels.create')
        ->name('grade-levels.create');

    Route::post('/grade-levels', [GradeLevelController::class, 'store'])
        ->middleware('permission:grade_levels.create')
        ->name('grade-levels.store');

    Route::get('/grade-levels/{gradeLevel}/edit', [GradeLevelController::class, 'edit'])
        ->middleware('permission:grade_levels.update')
        ->name('grade-levels.edit');

    Route::put('/grade-levels/{gradeLevel}', [GradeLevelController::class, 'update'])
        ->middleware('permission:grade_levels.update')
        ->name('grade-levels.update');

    Route::get('/rooms', [RoomController::class, 'index'])
        ->middleware('permission:rooms.view')
        ->name('rooms.index');

    Route::get('/rooms/create', [RoomController::class, 'create'])
        ->middleware('permission:rooms.create')
        ->name('rooms.create');

    Route::post('/rooms', [RoomController::class, 'store'])
        ->middleware('permission:rooms.create')
        ->name('rooms.store');

    Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])
        ->middleware('permission:rooms.update')
        ->name('rooms.edit');

    Route::put('/rooms/{room}', [RoomController::class, 'update'])
        ->middleware('permission:rooms.update')
        ->name('rooms.update');

    // Tambahkan route untuk manajemen rombongan belajar (class groups)
    Route::get('/class-groups', [ClassGroupController::class, 'index'])
        ->middleware('permission:class_groups.view')
        ->name('class-groups.index');

    Route::get('/class-groups/create', [ClassGroupController::class, 'create'])
        ->middleware('permission:class_groups.create')
        ->name('class-groups.create');

    Route::post('/class-groups', [ClassGroupController::class, 'store'])
        ->middleware('permission:class_groups.create')
        ->name('class-groups.store');

    Route::get('/class-groups/{classGroup}/edit', [ClassGroupController::class, 'edit'])
        ->middleware('permission:class_groups.update')
        ->name('class-groups.edit');

    Route::put('/class-groups/{classGroup}', [ClassGroupController::class, 'update'])
        ->middleware('permission:class_groups.update')
        ->name('class-groups.update');
    // Tambahkan route untuk manajemen mata pelajaran (subjects)
    Route::get('/subjects', [SubjectController::class, 'index'])
        ->middleware('permission:subjects.view')
        ->name('subjects.index');

    Route::get('/subjects/create', [SubjectController::class, 'create'])
        ->middleware('permission:subjects.create')
        ->name('subjects.create');

    Route::post('/subjects', [SubjectController::class, 'store'])
        ->middleware('permission:subjects.create')
        ->name('subjects.store');

    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])
        ->middleware('permission:subjects.update')
        ->name('subjects.edit');

    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
        ->middleware('permission:subjects.update')
        ->name('subjects.update');
    // Tambahkan route untuk manajemen pegawai (employees)
    Route::get('/employees', [EmployeeController::class, 'index'])
        ->middleware('permission:employees.view')
        ->name('employees.index');

    Route::get('/employees/create', [EmployeeController::class, 'create'])
        ->middleware('permission:employees.create')
        ->name('employees.create');

    Route::post('/employees', [EmployeeController::class, 'store'])
        ->middleware('permission:employees.create')
        ->name('employees.store');

    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])
        ->middleware('permission:employees.update')
        ->name('employees.edit');

    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])
        ->middleware('permission:employees.update')
        ->name('employees.update');
});

Route::middleware([
    'auth',
    'active.account',
])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
