<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\CalendarController as AdminCalendarController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\AttendanceController as GuruAttendanceController;
use App\Http\Controllers\Guru\HistoryController;
use App\Http\Controllers\Guru\CalendarController;
use App\Http\Controllers\Guru\ProfileController as GuruProfileController;

// Guest routes
Route::get('/', fn () => redirect()->route('admin.login'));

// Admin login
Route::get('/admin/login', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('guru.dashboard');
    }
    return view('auth.admin-login');
})->name('admin.login');

Route::post('/admin/login', function () {
    $credentials = request()->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        request()->session()->regenerate();
        $user = Auth::user();

        if ($user->status !== 'aktif') {
            Auth::logout();
            return back()->withErrors(['username' => 'Akun Anda tidak aktif.']);
        }
        if ($user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors(['username' => 'Username atau password salah.']);
        }
        return redirect()->route('admin.dashboard');
    }

    return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
})->name('admin.login.submit');

// Guru login
Route::get('/login', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('guru.dashboard');
    }
    return view('auth.guru-login');
})->name('guru.login');

Route::post('/login', function () {
    $credentials = request()->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        request()->session()->regenerate();
        $user = Auth::user();

        if ($user->status !== 'aktif') {
            Auth::logout();
            return back()->withErrors(['username' => 'Akun Anda tidak aktif.']);
        }
        if ($user->role !== 'guru') {
            Auth::logout();
            return back()->withErrors(['username' => 'Username atau password salah.']);
        }
        return redirect()->route('guru.dashboard');
    }

    return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
})->name('guru.login.submit');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('guru.login');
})->name('logout');

// ========================
// ADMIN ROUTES
// ========================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    // Guru CRUD
    Route::get('/guru', [GuruController::class, 'index'])->name('guru.index');
    Route::get('/guru/create', [GuruController::class, 'create'])->name('guru.create');
    Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
    Route::get('/guru/{id}', [GuruController::class, 'show'])->name('guru.show');
    Route::get('/guru/{id}/edit', [GuruController::class, 'edit'])->name('guru.edit');
    Route::put('/guru/{id}', [GuruController::class, 'update'])->name('guru.update');
    Route::delete('/guru/{id}', [GuruController::class, 'destroy'])->name('guru.delete');
    Route::post('/guru/import', [GuruController::class, 'import'])->name('guru.import');

    // Attendance
    Route::get('/absensi/export', [AttendanceController::class, 'export'])->name('attendance.export');
    Route::get('/absensi', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/absensi/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/absensi/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    // Permission
    Route::get('/izin', [PermissionController::class, 'index'])->name('permission.index');
    Route::post('/izin', [PermissionController::class, 'store'])->name('permission.store');
    Route::delete('/izin/{id}', [PermissionController::class, 'destroy'])->name('permission.delete');

    // Report
    Route::get('/laporan', [ReportController::class, 'index'])->name('report.index');
    Route::get('/laporan/export', [ReportController::class, 'export'])->name('report.export');

    // Settings
    Route::get('/pengaturan', [SettingController::class, 'index'])->name('setting.index');
    Route::put('/pengaturan', [SettingController::class, 'update'])->name('setting.update');

    // Audit Log
    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    // Calendar
    Route::get('/kalender', [AdminCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/kalender/{date}', [AdminCalendarController::class, 'dayDetail'])->name('calendar.day');

    // Holidays
    Route::get('/hari-libur', [HolidayController::class, 'index'])->name('holiday.index');
    Route::post('/hari-libur', [HolidayController::class, 'store'])->name('holiday.store');
    Route::put('/hari-libur/{id}', [HolidayController::class, 'update'])->name('holiday.update');
    Route::delete('/hari-libur/{id}', [HolidayController::class, 'destroy'])->name('holiday.delete');

    // Profile & Password
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
});

// ========================
// GURU ROUTES
// ========================
Route::prefix('guru')->name('guru.')->middleware(['auth', 'role:guru'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

    // Attendance
    Route::get('/absensi', [GuruAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/absensi', [GuruAttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/absensi/pulang', [GuruAttendanceController::class, 'checkout'])->name('attendance.checkout');

    // History
    Route::get('/riwayat', [HistoryController::class, 'index'])->name('history.index');

    // Calendar
    Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar.index');

    // Profile & Password
    Route::get('/profil', [GuruProfileController::class, 'index'])->name('profile');
    Route::put('/password', [GuruProfileController::class, 'updatePassword'])->name('password.update');
});
