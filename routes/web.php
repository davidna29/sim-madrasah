<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassGroupController;
use App\Http\Controllers\Admin\ClassGroupStudentExportController;
use App\Http\Controllers\Admin\ClassGroupStudentPrintController;
use App\Http\Controllers\Admin\EmployeeAccountController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\MadrasahProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\ScheduleTemplateController;
use App\Http\Controllers\Admin\ScheduleTemplateSlotController;
use App\Http\Controllers\Admin\StudentAccountController;
use App\Http\Controllers\Admin\StudentBulkClassAssignmentController;
use App\Http\Controllers\Admin\StudentClassHistoryController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentGuardianAccountController;
use App\Http\Controllers\Admin\StudentGuardianController;
use App\Http\Controllers\Admin\StudentPortfolioController;
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

    Route::get('/academic-years/{academicYear}/edit', [AcademicYearController::class, 'edit'])
        ->middleware('permission:academic_years.update')
        ->name('academic-years.edit');

    Route::put('/academic-years/{academicYear}', [AcademicYearController::class, 'update'])
        ->middleware('permission:academic_years.update')
        ->name('academic-years.update');

    Route::put('/semesters/{semester}/activate', [AcademicYearController::class, 'activateSemester'])
        ->middleware('permission:academic_years.activate')
        ->name('semesters.activate');

    Route::put('/semesters/{semester}/lock', [AcademicYearController::class, 'lockSemester'])
        ->middleware('permission:academic_years.lock')
        ->name('semesters.lock');
    Route::get('/grade-levels', [GradeLevelController::class, 'index'])
        ->middleware('permission:grade_levels.view')
        ->name('grade-levels.index');

    Route::get('/semesters/{semester}/edit', [AcademicYearController::class, 'editSemester'])
        ->middleware('permission:academic_years.update')
        ->name('semesters.edit');

    Route::put('/semesters/{semester}', [AcademicYearController::class, 'updateSemester'])
        ->middleware('permission:academic_years.update')
        ->name('semesters.update');

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

    Route::put('/grade-levels/{gradeLevel}/toggle-active', [GradeLevelController::class, 'toggleActive'])
        ->middleware('permission:grade_levels.update')
        ->name('grade-levels.toggle-active');

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

    Route::put('/rooms/{room}/toggle-active', [RoomController::class, 'toggleActive'])
        ->middleware('permission:rooms.update')
        ->name('rooms.toggle-active');

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
    // Tambahkan route untuk mencetak daftar siswa dalam format PDF
    Route::get('/class-groups/{classGroup}/students/pdf', ClassGroupStudentPrintController::class)
        ->middleware('permission:class_groups.print_students')
        ->name('class-groups.students.pdf');

    // Tambahkan route untuk mengekspor daftar siswa dalam format Excel
    Route::get('/class-groups/{classGroup}/students/excel', ClassGroupStudentExportController::class)
        ->middleware('permission:class_groups.export_students')
        ->name('class-groups.students.excel');

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

    Route::put('/subjects/{subject}/toggle-active', [SubjectController::class, 'toggleActive'])
        ->middleware('permission:subjects.update')
        ->name('subjects.toggle-active');

    // Tambahkan route untuk manajemen template jadwal pelajaran
    Route::get('/schedule-templates', [ScheduleTemplateController::class, 'index'])
        ->middleware('permission:schedule_templates.view')
        ->name('schedule-templates.index');

    Route::get('/schedule-templates/create', [ScheduleTemplateController::class, 'create'])
        ->middleware('permission:schedule_templates.create')
        ->name('schedule-templates.create');

    Route::post('/schedule-templates', [ScheduleTemplateController::class, 'store'])
        ->middleware('permission:schedule_templates.create')
        ->name('schedule-templates.store');

    Route::get('/schedule-templates/{scheduleTemplate}/edit', [ScheduleTemplateController::class, 'edit'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-templates.edit');

    Route::put('/schedule-templates/{scheduleTemplate}', [ScheduleTemplateController::class, 'update'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-templates.update');

    Route::post('/schedule-templates/{scheduleTemplate}/clone', [ScheduleTemplateController::class, 'clone'])
        ->middleware('permission:schedule_templates.create')
        ->name('schedule-templates.clone');

    Route::delete('/schedule-templates/{scheduleTemplate}', [ScheduleTemplateController::class, 'destroy'])
        ->middleware('permission:schedule_templates.delete')
        ->name('schedule-templates.destroy');

    // Tambahkan route untuk manajemen slot template jadwal
    Route::get('/schedule-templates/{scheduleTemplate}/slots', [ScheduleTemplateSlotController::class, 'index'])
        ->middleware('permission:schedule_templates.view')
        ->name('schedule-templates.slots.index');

    Route::get('/schedule-templates/{scheduleTemplate}/slots/create', [ScheduleTemplateSlotController::class, 'create'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-templates.slots.create');

    Route::post('/schedule-templates/{scheduleTemplate}/slots', [ScheduleTemplateSlotController::class, 'store'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-templates.slots.store');

    Route::get('/schedule-template-slots/{scheduleTemplateSlot}/edit', [ScheduleTemplateSlotController::class, 'edit'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-template-slots.edit');

    Route::put('/schedule-template-slots/{scheduleTemplateSlot}', [ScheduleTemplateSlotController::class, 'update'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-template-slots.update');

    Route::delete('/schedule-template-slots/{scheduleTemplateSlot}', [ScheduleTemplateSlotController::class, 'destroy'])
        ->middleware('permission:schedule_templates.update')
        ->name('schedule-template-slots.destroy');

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

    // Tambahkan route untuk manajemen akun pegawai (employee accounts)
    Route::get('/employees/{employee}/account/create', [EmployeeAccountController::class, 'create'])
        ->middleware('permission:employees.account.create')
        ->name('employees.accounts.create');

    Route::post('/employees/{employee}/account', [EmployeeAccountController::class, 'store'])
        ->middleware('permission:employees.account.create')
        ->name('employees.accounts.store');

    // Tambahkan route untuk export siswa
    Route::get('/students/export', [StudentController::class, 'export'])
        ->middleware('permission:students.view') // sesuaikan middleware jika ada
        ->name('students.export');

    // Tambahkan route untuk bulk assignment siswa ke rombel
    Route::get('/students/bulk-class-assignment', [StudentBulkClassAssignmentController::class, 'create'])
        ->middleware('permission:student_class_histories.create')
        ->name('students.bulk-class-assignment.create');

    Route::post('/students/bulk-class-assignment', [StudentBulkClassAssignmentController::class, 'store'])
        ->middleware('permission:student_class_histories.create')
        ->name('students.bulk-class-assignment.store');

    // Tambahkan route untuk manajemen siswa (students)
    Route::get('/students', [StudentController::class, 'index'])
        ->middleware('permission:students.view')
        ->name('students.index');

    Route::get('/students/create', [StudentController::class, 'create'])
        ->middleware('permission:students.create')
        ->name('students.create');

    Route::post('/students', [StudentController::class, 'store'])
        ->middleware('permission:students.create')
        ->name('students.store');

    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])
        ->middleware('permission:students.update')
        ->name('students.edit');

    Route::put('/students/{student}', [StudentController::class, 'update'])
        ->middleware('permission:students.update')
        ->name('students.update');

    // Tambahkan route untuk manajemen riwayat kelas siswa (student class histories)
    Route::get('/students/{student}/class-histories', [StudentClassHistoryController::class, 'index'])
        ->middleware('permission:student_class_histories.view')
        ->name('students.class-histories.index');

    Route::get('/students/{student}/class-histories/create', [StudentClassHistoryController::class, 'create'])
        ->middleware('permission:student_class_histories.create')
        ->name('students.class-histories.create');

    Route::post('/students/{student}/class-histories', [StudentClassHistoryController::class, 'store'])
        ->middleware('permission:student_class_histories.create')
        ->name('students.class-histories.store');

    // Tambahkan route untuk edit dan update riwayat kelas siswa
    Route::get('/students/{student}/class-histories/{classHistory}/edit', [StudentClassHistoryController::class, 'edit'])
        ->middleware('permission:student_class_histories.update')
        ->name('students.class-histories.edit');

    Route::put('/students/{student}/class-histories/{classHistory}', [StudentClassHistoryController::class, 'update'])
        ->middleware('permission:student_class_histories.update')
        ->name('students.class-histories.update');

    // Tambahkan route untuk manajemen akun siswa (student accounts)
    Route::get('/students/{student}/account/create', [StudentAccountController::class, 'create'])
        ->middleware('permission:students.account.create')
        ->name('students.accounts.create');

    Route::post('/students/{student}/account', [StudentAccountController::class, 'store'])
        ->middleware('permission:students.account.create')
        ->name('students.accounts.store');

    // Tambahkan route untuk manajemen wali siswa (student guardians)
    Route::get('/students/{student}/guardians', [StudentGuardianController::class, 'index'])
        ->middleware('permission:student_guardians.view')
        ->name('students.guardians.index');

    Route::get('/students/{student}/guardians/create', [StudentGuardianController::class, 'create'])
        ->middleware('permission:student_guardians.create')
        ->name('students.guardians.create');

    Route::post('/students/{student}/guardians', [StudentGuardianController::class, 'store'])
        ->middleware('permission:student_guardians.create')
        ->name('students.guardians.store');

    Route::get('/students/{student}/guardians/{guardian}/edit', [StudentGuardianController::class, 'edit'])
        ->middleware('permission:student_guardians.update')
        ->name('students.guardians.edit');

    Route::put('/students/{student}/guardians/{guardian}', [StudentGuardianController::class, 'update'])
        ->middleware('permission:student_guardians.update')
        ->name('students.guardians.update');

    // Tambahkan route untuk manajemen akun wali siswa (student guardian accounts)
    Route::get('/students/{student}/guardians/{guardian}/account/create', [StudentGuardianAccountController::class, 'create'])
        ->middleware('permission:student_guardians.account.create')
        ->name('students.guardians.accounts.create');

    Route::post('/students/{student}/guardians/{guardian}/account', [StudentGuardianAccountController::class, 'store'])
        ->middleware('permission:student_guardians.account.create')
        ->name('students.guardians.accounts.store');

    // Tambahkan route untuk manajemen ringkasn portofolio siswa (student portfolios)
    Route::get('/students/{student}/portfolio', [StudentPortfolioController::class, 'show'])
        ->middleware('permission:student_portfolios.view')
        ->name('students.portfolio.show');

    // Tambahkan route untuk mencetak kartu portofolio siswa dalam format PDF
    Route::get('/students/{student}/portfolio/card', [StudentPortfolioController::class, 'card'])
        ->middleware('permission:student_portfolios.print')
        ->name('students.portfolio.card');

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
