<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\SchoolSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
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
            $lateUntil = $this->settings['late_until'];

            if ($jamMasuk > $lateUntil) {
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

            if (!$attendance) {
                return ['success' => false, 'message' => 'Anda belum absen masuk hari ini.', 'code' => 422];
            }

            if ($attendance->jam_pulang) {
                return ['success' => false, 'message' => 'Anda sudah absen pulang hari ini.', 'code' => 422];
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
                'jam_pulang' => now()->format('H:i:s'),
                'lat_pulang' => $data['latitude'],
                'lng_pulang' => $data['longitude'],
                'distance_pulang' => round($distance, 2),
                'accuracy_pulang' => $data['accuracy'] ?? null,
            ]);

            $filename = 'selfie_' . $user->id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $data['selfie']->getClientOriginalExtension();
            $path = $data['selfie']->storeAs('attendance/selfie', $filename, 'public');
            $attendance->update(['foto_pulang' => $path]);

            return ['success' => true, 'message' => 'Absensi pulang berhasil disimpan.'];
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

    public function getSettings(): array
    {
        return $this->settings;
    }
}
