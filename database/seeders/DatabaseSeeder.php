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
    }
}
