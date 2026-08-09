<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\MadrasahProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
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
