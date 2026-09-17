<?php

namespace Database\Seeders;

use App\Models\GuruProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        User::create([
            'name' => 'Guru Contoh',
            'username' => '1987654321',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'status' => 'aktif',
        ]);

        GuruProfile::create([
            'user_id' => 2,
            'nip' => '1987654321',
            'sk' => 'SK/2024/001',
            'spmt' => 'SPMT/2024/001',
        ]);

        // School settings
        \App\Models\SchoolSetting::set('school_name', 'SMKN 11 KABUPATEN TANGERANG');
        \App\Models\SchoolSetting::set('latitude', '-6.2011');
        \App\Models\SchoolSetting::set('longitude', '106.393');
        \App\Models\SchoolSetting::set('attendance_radius', '200');
        \App\Models\SchoolSetting::set('work_start_time', '07:00');
        \App\Models\SchoolSetting::set('present_until', '08:30');
        \App\Models\SchoolSetting::set('late_until', '09:00');
        \App\Models\SchoolSetting::set('checkout_start_time', '15:00');
    }
}
