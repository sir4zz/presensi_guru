<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
    /**
     * Batas akhir absensi pulang (WIB).
     * Awal absensi pulang memakai pengaturan Admin `checkout_start_time`
     * (default 15:00). Batas akhir 17:00 sesuai ketentuan, tanpa
     * menambah pengaturan baru.
     */
    public const CHECKOUT_END_TIME = '17:00';

    protected array $settings;

    public function __construct()
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $saved = SchoolSetting::allAsArray();
        $this->settings = array_merge([
            'school_name' => 'SMKN 11 KABUPATEN TANGERANG',
            'latitude' => -6.2011,
            'longitude' => 106.393,
            'attendance_radius' => 200,
            'work_start_time' => '07:00',
            'present_until' => '08:30',
            'late_until' => '09:00',
            'checkout_start_time' => '15:00',
        ], $saved);
    }

    public function checkIn(User $user, array $data): array
    {
        $today = now()->toDateString();

        return DB::transaction(function () use ($user, $data, $today) {
            $existing = Attendance::where('guru_id', $user->id)
                ->whereDate('tanggal', $today)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return ['success' => false, 'message' => 'Anda sudah absen masuk hari ini.', 'code' => 422];
            }

            // Tanggal merah (Minggu/libur): sistem absensi ditutup.
            $redDate = $this->isRedDate($today);
            if ($redDate['is_red']) {
                return ['success' => false, 'message' => 'Sistem absensi ditutup. Hari ini ' . $redDate['reason'] . '.', 'code' => 422];
            }

            $distance = $this->calculateDistance(
                $data['latitude'], $data['longitude'],
                (float) $this->settings['latitude'], (float) $this->settings['longitude']
            );

            $radius = (int) $this->settings['attendance_radius'];
            if ($distance > $radius) {
                return [
                    'success' => false,
                    'message' => "Anda berada di luar radius sekolah. Jarak: " . round($distance) . "m (maks: {$radius}m)",
                    'code' => 422,
                ];
            }

            $jamMasuk = now()->format('H:i:s');
            $lateUntil = substr((string) $this->settings['late_until'], 0, 5);
            $jamMasukShort = substr($jamMasuk, 0, 5);

            if ($jamMasukShort > $lateUntil) {
                return ['success' => false, 'message' => 'Batas waktu absen masuk telah lewat (' . $lateUntil . '). Anda tidak dapat melakukan absensi.', 'code' => 422];
            }

            $status = $this->determineStatus($jamMasuk);

            $attendance = Attendance::create([
                'guru_id' => $user->id,
                'tanggal' => $today,
                'status' => $status->value,
                'jam_masuk' => $jamMasuk,
                'lat_masuk' => $data['latitude'],
                'lng_masuk' => $data['longitude'],
                'distance_masuk' => round($distance, 2),
                'accuracy_masuk' => $data['accuracy'] ?? null,
            ]);

            $filename = 'selfie_' . $user->id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $data['selfie']->getClientOriginalExtension();
            $path = $data['selfie']->storeAs('attendance/selfie', $filename, 'public');
            $attendance->update(['foto_masuk' => $path]);

            return [
                'success' => true,
                'message' => 'Absensi masuk berhasil disimpan.',
                'status' => $status->value,
                'status_label' => $status->label(),
            ];
        });
    }

    public function checkOut(User $user, array $data): array
    {
        $today = now()->toDateString();

        return DB::transaction(function () use ($user, $data, $today) {
            $attendance = Attendance::where('guru_id', $user->id)
                ->whereDate('tanggal', $today)
                ->lockForUpdate()
                ->first();

            if (!$attendance || !$attendance->jam_masuk) {
                $redDate = $this->isRedDate($today);
                if ($redDate['is_red']) {
                    return ['success' => false, 'message' => 'Sistem absensi ditutup. Hari ini ' . $redDate['reason'] . '.', 'code' => 422];
                }

                return ['success' => false, 'message' => 'Anda belum melakukan absensi masuk hari ini.', 'code' => 422];
            }

            if ($attendance->jam_pulang) {
                return ['success' => false, 'message' => 'Anda sudah melakukan absensi pulang hari ini.', 'code' => 422];
            }

            // Hanya status kehadiran (masuk) yang boleh absen pulang.
            if (!in_array($attendance->status, [AttendanceStatus::Hadir->value, AttendanceStatus::Terlambat->value], true)) {
                return ['success' => false, 'message' => 'Absensi pulang hanya untuk kehadiran masuk (Hadir/Terlambat).', 'code' => 422];
            }

            // Pulang sebelum checkout_start_time tetap diperbolehkan agar PS tercatat.
            $now = now(); // timezone mengikuti config app Asia/Jakarta
            $serverTime = $now->format('H:i');
            $checkoutStart = substr((string) ($this->settings['checkout_start_time'] ?? '15:00'), 0, 5);
            $checkoutEnd = substr(self::CHECKOUT_END_TIME, 0, 5);

            if ($serverTime > $checkoutEnd) {
                return ['success' => false, 'message' => 'Waktu absensi pulang sudah berakhir (batas ' . $checkoutEnd . ' WIB).', 'code' => 422];
            }

            $distance = $this->calculateDistance(
                $data['latitude'], $data['longitude'],
                (float) $this->settings['latitude'], (float) $this->settings['longitude']
            );

            $radius = (int) $this->settings['attendance_radius'];
            if ($distance > $radius) {
                return [
                    'success' => false,
                    'message' => "Anda berada di luar radius sekolah. Jarak: " . round($distance) . "m (maks: {$radius}m)",
                    'code' => 422,
                ];
            }

            $attendance->update([
                'jam_pulang' => $now->format('H:i:s'),
                'lat_pulang' => $data['latitude'],
                'lng_pulang' => $data['longitude'],
                'distance_pulang' => round($distance, 2),
                'accuracy_pulang' => $data['accuracy'] ?? null,
            ]);

            $filename = 'selfie_' . $user->id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $data['selfie']->getClientOriginalExtension();
            $path = $data['selfie']->storeAs('attendance/selfie', $filename, 'public');
            $attendance->update(['foto_pulang' => $path]);

            return [
                'success' => true,
                'message' => 'Absensi pulang berhasil disimpan.',
                'jam_pulang' => $attendance->fresh()->jam_pulang,
            ];
        });
    }

    public function determineStatus(string $jamMasuk): AttendanceStatus
    {
        $presentUntil = $this->settings['present_until'];

        if ($jamMasuk > $presentUntil) {
            return AttendanceStatus::Terlambat;
        }

        return AttendanceStatus::Hadir;
    }

    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function markMissing(): int
    {
        $yesterday = now()->subDay()->toDateString();

        // Tanggal merah tidak dibuatkan TAK.
        if ($this->isRedDate($yesterday)['is_red']) {
            return 0;
        }

        $guruIds = User::where('role', 'guru')
            ->where('status', 'aktif')
            ->pluck('id');

        $count = 0;
        foreach ($guruIds as $guruId) {
            $exists = Attendance::where('guru_id', $guruId)
                ->whereDate('tanggal', $yesterday)
                ->exists();

            if (!$exists) {
                Attendance::create([
                    'guru_id' => $guruId,
                    'tanggal' => $yesterday,
                    'status' => AttendanceStatus::Alpha->value,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /** Ringkasan disiplin bulan berjalan (menit dan konversi hari). */
    public function getDisciplineSummary(int $guruId, int $month, int $year): array
    {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth()->toDateString();
        return $this->getDisciplineSummaryForRange($guruId, $start, $end);
    }

    /** Ringkasan disiplin untuk rentang tanggal, dipakai export bulanan/tahunan. */
    public function getDisciplineSummaryForRange(int $guruId, string $startDate, string $endDate): array
    {
        $rows = Attendance::where('guru_id', $guruId)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();
        $presentUntil = substr((string) $this->settings['present_until'], 0, 5);
        $checkoutStart = substr((string) $this->settings['checkout_start_time'], 0, 5);
        $lateMinutes = 0;
        $earlyMinutes = 0;
        foreach ($rows as $row) {
            if ($row->status === AttendanceStatus::Terlambat->value && $row->jam_masuk) {
                $lateMinutes += max(0, $this->minutesBetween($presentUntil, substr($row->jam_masuk, 0, 5)));
            }
            if ($row->jam_pulang) {
                $earlyMinutes += max(0, $this->minutesBetween(substr($row->jam_pulang, 0, 5), $checkoutStart));
            }
        }
        $alpha = $rows->where('status', AttendanceStatus::Alpha->value)->count();
        $totalMinutes = $lateMinutes + $earlyMinutes;
        return [
            'terlambat_menit' => $lateMinutes,
            'pulang_awal_menit' => $earlyMinutes,
            'konversi_jam' => round($totalMinutes / 60, 2),
            'konversi_hari' => round($totalMinutes / 60 / 7, 2),
            'tmtb' => $alpha,
            'total_hari' => round(($totalMinutes / 60 / 7) + $alpha, 2),
        ];
    }

    protected function minutesBetween(string $from, string $to): int
    {
        [$fromHour, $fromMinute] = array_map('intval', explode(':', $from));
        [$toHour, $toMinute] = array_map('intval', explode(':', $to));
        return (($toHour * 60) + $toMinute) - (($fromHour * 60) + $fromMinute);
    }

    public function getSettings(): array
    {
        return $this->settings;
    }

    /**
     * Status jendela absensi pulang berdasarkan waktu server Asia/Jakarta.
     * @return array{start:string,end:string,server_time:string,status:string,can_checkout:bool}
     */
    /**
     * Cek apakah tanggal adalah tanggal merah (hari Minggu atau hari libur).
     * Waktu mengikuti timezone server Asia/Jakarta.
     *
     * @return array{is_red:bool,reason:string|null,holiday:\App\Models\Holiday|null}
     */
    public function isRedDate(?string $date = null): array
    {
        $date ??= now()->toDateString();

        if (\Carbon\Carbon::parse($date)->dayOfWeek === 0) {
            return ['is_red' => true, 'reason' => 'hari Minggu', 'holiday' => null];
        }

        $holiday = Holiday::whereDate('date', $date)->first();

        if ($holiday) {
            return ['is_red' => true, 'reason' => 'libur (' . $holiday->name . ')', 'holiday' => $holiday];
        }

        return ['is_red' => false, 'reason' => null, 'holiday' => null];
    }

    public function getCheckoutWindow(?string $serverTime = null): array
    {
        $serverTime ??= now()->format('H:i');
        $serverTime = substr($serverTime, 0, 5);
        $start = substr((string) ($this->settings['checkout_start_time'] ?? '15:00'), 0, 5);
        $end = substr(self::CHECKOUT_END_TIME, 0, 5);

        if ($serverTime > $end) {
            $status = 'closed';
        } else {
            $status = 'open';
        }

        return [
            'start' => $start,
            'end' => $end,
            'server_time' => $serverTime,
            'status' => $status,
            'can_checkout' => $status === 'open',
        ];
    }
}
