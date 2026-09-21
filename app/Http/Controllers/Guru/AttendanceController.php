<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreAttendanceRequest;
use App\Models\SchoolSetting;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    public function create()
    {
        $user = auth()->user();
        $todayAttendance = $user->attendances()->whereDate('tanggal', now()->toDateString())->first();
        $settings = $this->attendanceService->getSettings();

        $schoolSettings = (object) [
            'school_name' => $settings['school_name'] ?? 'SMKN 11 KABUPATEN TANGERANG',
            'latitude' => (float) ($settings['latitude'] ?? -6.2011),
            'longitude' => (float) ($settings['longitude'] ?? 106.393),
            'attendance_radius' => (int) ($settings['attendance_radius'] ?? 200),
            'max_accuracy' => 100,
        ];

        // Waktu server Asia/Jakarta (mengikuti config app.timezone).
        $serverTimeFull = now()->format('H:i:s');
        $serverTime = substr($serverTimeFull, 0, 5);
        $lateUntil = substr((string) ($settings['late_until'] ?? '09:00'), 0, 5);
        $canCheckIn = $serverTime <= $lateUntil;

        // Jendela absensi pulang memakai checkout_start_time Admin s.d. 17:00.
        $checkoutWindow = $this->attendanceService->getCheckoutWindow($serverTime);
        $checkoutStart = $checkoutWindow['start'];
        $checkoutEnd = $checkoutWindow['end'];
        $checkoutStatus = $checkoutWindow['status'];
        $canCheckout = $checkoutWindow['can_checkout'];

        $isCheckIn = !$todayAttendance || !$todayAttendance->jam_masuk;
        $hasCheckedOut = (bool) ($todayAttendance?->jam_pulang);

        // Tanggal merah (Minggu/libur): sistem absensi ditutup.
        $redDate = $this->attendanceService->isRedDate(now()->toDateString());
        return view('guru.attendance.create', compact(
            'todayAttendance', 'schoolSettings',
            'canCheckIn', 'lateUntil',
            'serverTime', 'serverTimeFull',
            'checkoutStart', 'checkoutEnd', 'checkoutStatus', 'canCheckout',
            'isCheckIn', 'hasCheckedOut', 'redDate'
        ));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $result = $this->attendanceService->checkIn($request->user(), $request->validated());
        $code = $result['success'] ? 200 : ($result['code'] ?? 400);
        return response()->json($result, $code);
    }

    public function checkout(StoreAttendanceRequest $request)
    {
        $result = $this->attendanceService->checkOut($request->user(), $request->validated());
        $code = $result['success'] ? 200 : ($result['code'] ?? 400);
        return response()->json($result, $code);
    }
}
