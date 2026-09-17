<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AuditLogService;

class AttendanceController extends Controller
{
    public function index()
    {
        $query = Attendance::with('guru');

        if (request('guru_id')) $query->where('guru_id', request('guru_id'));
        if (request('status')) $query->where('status', request('status'));
        if (request('date')) $query->whereDate('tanggal', request('date'));

        $attendances = $query->latest('tanggal')->paginate(20);
        $gurus = User::where('role', 'guru')->get();

        return view('admin.attendance.index', compact('attendances', 'gurus'));
    }

    public function export()
    {
        $query = Attendance::with('guru');

        if (request('guru_id')) $query->where('guru_id', request('guru_id'));
        if (request('status')) $query->where('status', request('status'));
        if (request('date_from')) $query->whereDate('tanggal', '>=', request('date_from'));
        if (request('date_to')) $query->whereDate('tanggal', '<=', request('date_to'));

        $attendances = $query->orderBy('tanggal', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="absensi_' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama', 'NIP', 'Tanggal', 'Status', 'Jam Masuk', 'Jam Pulang', 'Keterangan']);

            $no = 1;
            foreach ($attendances as $att) {
                fputcsv($file, [
                    $no++,
                    $att->guru->name ?? '-',
                    $att->guru->username ?? '-',
                    $att->tanggal,
                    ucfirst($att->status),
                    $att->jam_masuk ?? '-',
                    $att->jam_pulang ?? '-',
                    $att->keterangan ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $attendance = Attendance::with('guru')->findOrFail($id);
        return response()->json($attendance);
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);
        $oldData = $attendance->toArray();

        $attendance->update($request->only(['status', 'jam_masuk', 'jam_pulang', 'keterangan']));

        AuditLogService::updated('absensi', $attendance, $oldData, $attendance->fresh()->toArray(), "Koreksi absensi guru: {$attendance->guru->name}");

        return response()->json(['success' => true, 'message' => 'Koreksi berhasil disimpan.']);
    }
}
