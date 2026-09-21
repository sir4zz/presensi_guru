<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\CarbonPeriod;

class DemoAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Ahmad Hidayat', 'Siti Nurhayati', 'Budi Santoso', 'Dewi Anggraeni',
            'Rizky Pratama', 'Nuraeni Saputri', 'Fajar Nugraha', 'Rina Marlina',
            'Andi Kurniawan', 'Yuni Astuti', 'Dedi Supriyadi', 'Maya Lestari',
        ];

        $gurus = collect($names)->map(function (string $name, int $index) {
            return User::updateOrCreate(
                ['username' => 'demo.guru' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                ['name' => $name, 'password' => Hash::make('password'), 'role' => 'guru', 'status' => 'aktif']
            );
        });

        $start = now()->startOfMonth();
        $end = now()->endOfDay();
        $created = 0;

        foreach (CarbonPeriod::create($start, $end) as $date) {
            if ($date->isWeekend()) continue;

            foreach ($gurus as $index => $guru) {
                $pattern = ($date->day + $index) % 12;
                $status = match (true) {
                    $pattern === 11 => AttendanceStatus::Alpha->value,
                    $pattern === 10 => AttendanceStatus::Sakit->value,
                    $pattern === 9 => AttendanceStatus::Izin->value,
                    $pattern === 8 => AttendanceStatus::DinasLuar->value,
                    $pattern === 7 || $pattern === 6 => AttendanceStatus::Terlambat->value,
                    default => AttendanceStatus::Hadir->value,
                };

                $isPresent = in_array($status, [AttendanceStatus::Hadir->value, AttendanceStatus::Terlambat->value], true);
                $jamMasuk = $status === AttendanceStatus::Terlambat->value ? '08:45:00' : '07:20:00';
                $jamPulang = $status === AttendanceStatus::Terlambat->value && $pattern === 6
                    ? '14:20:00'
                    : ($isPresent ? '15:30:00' : null);

                $values = [
                    'status' => $status,
                    'jam_masuk' => $isPresent ? $jamMasuk : null,
                    'jam_pulang' => $jamPulang,
                    'keterangan' => match ($status) {
                        AttendanceStatus::Izin->value => 'Izin demo untuk simulasi laporan.',
                        AttendanceStatus::Sakit->value => 'Sakit demo untuk simulasi laporan.',
                        AttendanceStatus::DinasLuar->value => 'Kegiatan dinas luar demo.',
                        default => null,
                    },
                    'keperluan_dinas' => $status === AttendanceStatus::DinasLuar->value ? 'Rapat koordinasi kedinasan' : null,
                    'lokasi_dinas' => $status === AttendanceStatus::DinasLuar->value ? 'Kantor Cabang Dinas' : null,
                    'dinas_verified_by' => $status === AttendanceStatus::DinasLuar->value && $index % 2 === 0 ? 1 : null,
                    'dinas_verified_at' => $status === AttendanceStatus::DinasLuar->value && $index % 2 === 0 ? now() : null,
                ];

                Attendance::updateOrCreate(
                    ['guru_id' => $guru->id, 'tanggal' => $date->toDateString()],
                    $values
                );
                $created++;
            }
        }

        $this->command?->info("Demo attendance siap: {$gurus->count()} guru dan {$created} record bulan berjalan.");
        $this->command?->info('Login demo guru: demo.guru01 s.d. demo.guru12 / password');
    }
}
