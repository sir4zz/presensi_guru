<?php

namespace Tests\Feature;

use App\Models\GuruProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuruProfilePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_akun_menampilkan_info_sama_seperti_admin(): void
    {
        $guru = User::create([
            'name' => 'Guru Uji', 'username' => '199005122025211001',
            'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'aktif',
        ]);
        GuruProfile::create([
            'user_id' => $guru->id, 'nip' => '199005122025211001',
            'nuptk' => '1234567890123456', 'jenis_kelamin' => 'laki-laki',
            'agama' => 'Islam', 'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-05-12', 'nik' => '3603011205900001',
            'status_kepegawaian' => 'PPPK Paruh Waktu', 'jabatan' => 'Guru',
            'alamat' => 'Kp. Sukamaju', 'no_hp' => '081234567890',
            'email' => 'budi@example.com',
        ]);

        $response = $this->actingAs($guru)->get(route('guru.profile'));

        $response->assertOk();
        // Data Pribadi
        $response->assertSee('Guru Uji');
        $response->assertSee('1234567890123456');
        $response->assertSee('Laki-laki');
        $response->assertSee('3603011205900001');
        // Kepegawaian
        $response->assertSee('PPPK Paruh Waktu');
        // Kontak
        $response->assertSee('Kp. Sukamaju');
        $response->assertSee('081234567890');
        // Fitur lama tetap ada
        $response->assertSee('Ganti Password');
        $response->assertSee('Keluar');
        // Tidak ada kontrol edit admin
        $response->assertDontSee('Tambah Guru');
    }
}
