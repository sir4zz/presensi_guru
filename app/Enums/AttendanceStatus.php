<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Hadir = 'hadir';
    case Terlambat = 'terlambat';
    case Izin = 'izin';
    case Sakit = 'sakit';
    case Alpha = 'alpha';

    public function label(): string
    {
        return match($this) {
            self::Hadir => 'Hadir',
            self::Terlambat => 'Terlambat',
            self::Izin => 'Izin',
            self::Sakit => 'Sakit',
            self::Alpha => 'Tidak Ada Keterangan',
        };
    }

    public function badgeVariant(): string
    {
        return match($this) {
            self::Hadir => 'success',
            self::Terlambat => 'warning',
            self::Izin => 'info',
            self::Sakit => 'danger',
            self::Alpha => 'danger',
        };
    }
}
