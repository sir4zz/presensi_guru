<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceKoreksiMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $guru;

    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::create([
            'name' => 'Admin', 'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'aktif',
        ]);
        $this->guru = User::create([
            'name' => 'Guru Uji', 'username' => '199005122025211001',
            'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'aktif',
        ]);
        $this->attendance = Attendance::create([
            'guru_id' => $this->guru->id, 'tanggal' => '2026-09-22',
            'status' => 'hadir', 'jam_masuk' => '07:10:00',
        ]);
    }

    protected function koreksi(array $payload)
    {
        return $this->actingAs($this->admin)->putJson(
            route('admin.attendance.update', $this->attendance->id),
            $payload
        );
    }

    public function test_hadir_bisa_ubah_jam(): void
    {
        $response = $this->koreksi([
            'status' => 'terlambat', 'jam_masuk' => '08:45',
            'keterangan' => 'Macet', 'alasan_koreksi' => 'Perbaikan',
        ]);

        $response->assertOk();
        $fresh = $this->attendance->fresh();
        $this->assertSame('terlambat', $fresh->status);
        $this->assertSame('08:45', substr((string) $fresh->jam_masuk, 0, 5));
    }

    public function test_izin_dengan_jam_ditolak_backend(): void
    {
        $response = $this->koreksi([
            'status' => 'izin', 'jam_masuk' => '07:10', 'jam_pulang' => '15:00',
            'alasan_koreksi' => 'Coba langgar',
        ]);

        $response->assertStatus(422);
        $this->assertSame('hadir', $this->attendance->fresh()->status);
    }

    public function test_ganti_ke_izin_mengosongkan_jam(): void
    {
        $response = $this->koreksi([
            'status' => 'izin', 'keterangan' => 'Sakit',
            'alasan_koreksi' => 'Surat dokter',
        ]);

        $response->assertOk();
        $fresh = $this->attendance->fresh();
        $this->assertSame('izin', $fresh->status);
        $this->assertNull($fresh->jam_masuk);
        $this->assertNull($fresh->jam_pulang);
    }

    public function test_izin_dengan_lampiran_ditolak(): void
    {
        $response = $this->koreksi([
            'status' => 'izin', 'alasan_koreksi' => 'X',
            'bukti_file' => UploadedFile::fake()->image('x.jpg', 100, 100),
        ]);

        $response->assertStatus(422);
    }

    public function test_dinas_bisa_ganti_lampiran_dan_lokasi(): void
    {
        $this->attendance->update(['status' => 'dinas_luar']);

        $response = $this->koreksi([
            'status' => 'dinas_luar', 'alasan_koreksi' => 'Revisi',
            'keperluan_dinas' => 'Rapat', 'lokasi_dinas' => 'Dinas Pendidikan',
            'bukti_file' => UploadedFile::fake()->image('bukti.jpg', 800, 600),
        ]);

        $response->assertOk();
        $fresh = $this->attendance->fresh();
        $this->assertSame('Rapat', $fresh->keperluan_dinas);
        $this->assertSame('Dinas Pendidikan', $fresh->lokasi_dinas);
        $this->assertStringEndsWith('.webp', $fresh->bukti_file);
        $this->assertStringEndsWith('.webp', $fresh->surat_tugas_file);
    }

    public function test_alasan_koreksi_wajib(): void
    {
        $response = $this->koreksi(['status' => 'hadir', 'jam_masuk' => '07:10']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('alasan_koreksi');
    }
}
