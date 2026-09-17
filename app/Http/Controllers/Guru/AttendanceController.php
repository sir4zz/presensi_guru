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
        $settings = SchoolSetting::allAsArray();

        $schoolSettings = (object) [
            'latitude' => $settings['latitude'] ?? -6.2011,
            'longitude' => $settings['longitude'] ?? 106.393,
            'attendance_radius' => $settings['attendance_radius'] ?? 200,
        ];

        return view('guru.attendance.create', compact('todayAttendance', 'schoolSettings'));
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
