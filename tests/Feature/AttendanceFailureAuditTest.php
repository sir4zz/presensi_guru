<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceFailureAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function makeGuru(): User
    {
        return User::create([
            'name' => 'Guru Uji',
            'username' => '123456',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'status' => 'aktif',
        ]);
    }

    public function test_absen_masuk_di_luar_radius_tercatat_di_audit_log(): void
    {
        $guru = $this->makeGuru();

        $response = $this->actingAs($guru)->postJson(route('guru.attendance.store'), [
            // Koordinat jauh dari sekolah default (-6.2011, 106.393).
            'latitude' => -6.9,
            'longitude' => 107.6,
            'selfie' => \Illuminate\Http\UploadedFile::fake()->image('selfie.jpg', 640, 480),
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('attendances', 0);

        $log = AuditLog::where('action', 'absensi_gagal')->latest()->first();
        $this->assertNotNull($log);
        $this->assertSame($guru->id, $log->user_id);
        $this->assertSame('absensi', $log->module);
        $this->assertStringContainsString('masuk', $log->description);
        $this->assertStringContainsString('radius', $log->description);
        $this->assertArrayHasKey('jarak_meter', $log->new_data);
        $this->assertArrayHasKey('ip', $log->new_data);
    }

    public function test_absen_masuk_sukses_tidak_mencatat_audit_log(): void
    {
        $guru = $this->makeGuru();

        // Radius longgar + batas waktu longgar agar absensi pasti lolos validasi.
        \App\Models\SchoolSetting::set('attendance_radius', 1000);
        \App\Models\SchoolSetting::set('late_until', '23:59');
        \App\Models\SchoolSetting::set('present_until', '23:59');

        $response = $this->actingAs($guru)->postJson(route('guru.attendance.store'), [
            'latitude' => -6.2011,
            'longitude' => 106.393,
            'selfie' => \Illuminate\Http\UploadedFile::fake()->image('selfie.jpg', 640, 480),
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
