<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountController;
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
use App\Http\Controllers\Guru\DinasLuarController;
use App\Http\Controllers\Guru\ProfileController as GuruProfileController;

// Guest routes
Route::get('/', fn () => redirect()->route('guru.login'));

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
    Route::get('/guru/export', [GuruController::class, 'export'])->name('guru.export');
    Route::get('/guru/template', [GuruController::class, 'template'])->name('guru.template');
    Route::post('/guru', [GuruController::class, 'store'])->name('guru.store');
    Route::get('/guru/{id}', [GuruController::class, 'show'])->name('guru.show');
    Route::get('/guru/{id}/edit', [GuruController::class, 'edit'])->name('guru.edit');
    Route::put('/guru/{id}', [GuruController::class, 'update'])->name('guru.update');
    Route::delete('/guru/{id}', [GuruController::class, 'destroy'])->name('guru.delete');
    Route::post('/guru/import', [GuruController::class, 'import'])->name('guru.import');

    // Attendance — pantauan harian + hapus satuan/massal terpilih.
    // Rekap bulanan/tahunan + export + purge periode ada di menu Laporan.
    Route::get('/absensi', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/absensi/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::put('/absensi/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/absensi/{id}', [AttendanceController::class, 'destroy'])->name('attendance.delete');
    Route::post('/absensi/bulk', [AttendanceController::class, 'bulkDestroy'])->name('attendance.bulk-delete');
    Route::post('/absensi/{attendance}/verifikasi-dinas', [AttendanceController::class, 'verifyDinas'])->name('attendance.verify-dinas');
    Route::post('/absensi/{attendance}/tolak-dinas', [AttendanceController::class, 'rejectDinas'])->name('attendance.reject-dinas');

    // Permission
    Route::get('/izin', [PermissionController::class, 'index'])->name('permission.index');
    Route::post('/izin', [PermissionController::class, 'store'])->name('permission.store');
    Route::put('/izin/{id}', [PermissionController::class, 'update'])->name('permission.update');
    Route::delete('/izin/{id}', [PermissionController::class, 'destroy'])->name('permission.delete');

    // Report
    Route::get('/laporan', [ReportController::class, 'index'])->name('report.index');
    Route::get('/laporan/export', [ReportController::class, 'export'])->name('report.export');
    Route::get('/laporan/purge-preview', [ReportController::class, 'purgePreview'])->name('report.purge-preview');
    Route::post('/laporan/purge', [ReportController::class, 'purge'])->name('report.purge');

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
    Route::post('/hari-libur/sync', [HolidayController::class, 'sync'])->name('holiday.sync');
    Route::put('/hari-libur/{id}', [HolidayController::class, 'update'])->name('holiday.update');
    Route::delete('/hari-libur/{id}', [HolidayController::class, 'destroy'])->name('holiday.delete');

    // Profile & Password (legacy, dialihkan ke Pengaturan Akun)
    Route::get('/profil', fn () => redirect()->route('admin.account.index'))->name('profile');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // Pengaturan Akun
    Route::get('/akun', [AccountController::class, 'index'])->name('account.index');
    Route::put('/akun/profil', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/akun/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/akun/admin', [AccountController::class, 'storeAdmin'])->name('account.store-admin');
    Route::put('/akun/{user}/reset-password', [AccountController::class, 'resetPassword'])->name('account.reset-password');
    Route::patch('/akun/{user}/status', [AccountController::class, 'toggleStatus'])->name('account.status');
    Route::delete('/akun/{user}', [AccountController::class, 'destroy'])->name('account.destroy');
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

    // Kedinasan (Dinas Luar) — halaman biasa, bukan bottom nav.
    Route::get('/kedinasan', [DinasLuarController::class, 'create'])->name('kedinasan.index');
    Route::post('/kedinasan', [DinasLuarController::class, 'store'])->name('kedinasan.store');
    Route::delete('/kedinasan/{attendance}', [DinasLuarController::class, 'destroy'])->name('kedinasan.destroy');

    // Profile & Password
    Route::get('/profil', [GuruProfileController::class, 'index'])->name('profile');
    Route::put('/password', [GuruProfileController::class, 'updatePassword'])->name('password.update');
});
