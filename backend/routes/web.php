<?php

use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\LeaveController;
use App\Http\Controllers\Web\OfficeController;
use App\Http\Controllers\Web\PayrollController;
use App\Http\Controllers\Web\RoleController;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return redirect()->route('web.login');
});

// Alias buat default auth middleware (redirect ke web.login)
Route::get('/login', function () {
    return redirect()->route('web.login');
})->name('login');

// Auth routes
Route::prefix('admin')->name('web.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Protected routes — minimal view
    Route::middleware(['auth', 'jabatan:view'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Absensi — view-only
        Route::prefix('attendances')->name('attendances.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/laporan', [AttendanceController::class, 'laporan'])->name('laporan');
            Route::get('/export', [AttendanceController::class, 'exportCsv'])->name('export');
            Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
        });
    });

    // Manage level — manage & full
    Route::middleware(['auth', 'jabatan:manage'])->group(function () {
        // Karyawan
        Route::resource('employees', EmployeeController::class)->names([
            'index' => 'employees.index',
            'create' => 'employees.create',
            'store' => 'employees.store',
            'show' => 'employees.show',
            'edit' => 'employees.edit',
            'update' => 'employees.update',
            'destroy' => 'employees.destroy',
        ]);
        Route::post('employees/{employee}/toggle-aktif', [EmployeeController::class, 'toggleAktif'])
            ->name('employees.toggle-aktif');

        // Permohonan Izin
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [LeaveController::class, 'index'])->name('index');
            Route::get('/{leave}', [LeaveController::class, 'show'])->name('show');
            Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
        });

        // Kantor / Lokasi — admin (full) atau HRD
        Route::middleware('office-access')->group(function () {
            Route::resource('offices', OfficeController::class)->names([
                'index' => 'offices.index',
                'create' => 'offices.create',
                'store' => 'offices.store',
                'edit' => 'offices.edit',
                'update' => 'offices.update',
                'destroy' => 'offices.destroy',
            ])->except(['show']);
        });

        // Penggajian — khusus administrator (full)
        Route::middleware('jabatan:full')->group(function () {
            Route::get('payrolls', [PayrollController::class, 'index'])->name('payrolls.index');
            Route::get('payrolls/create', [PayrollController::class, 'create'])->name('payrolls.create');
            Route::post('payrolls', [PayrollController::class, 'store'])->name('payrolls.store');
            Route::post('payrolls/{payroll}/paid', [PayrollController::class, 'markPaid'])->name('payrolls.paid');
            Route::delete('payrolls/{payroll}', [PayrollController::class, 'destroy'])->name('payrolls.destroy');
        });

        // Master Role (hanya administrator)
        Route::middleware('admin')->group(function () {
            Route::resource('roles', RoleController::class)->names([
                'index' => 'roles.index',
                'create' => 'roles.create',
                'store' => 'roles.store',
                'edit' => 'roles.edit',
                'update' => 'roles.update',
                'destroy' => 'roles.destroy',
            ])->except(['show']);
        });
    });
});
