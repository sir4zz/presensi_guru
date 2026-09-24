<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendancePermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PermissionDetailEditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $guru;

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
    }

    protected function makeIzin(string $tanggal = '2026-09-21', string $status = 'izin'): AttendancePermission
    {
        Attendance::create([
            'guru_id' => $this->guru->id, 'tanggal' => $tanggal, 'status' => $status,
            'keterangan' => 'Sakit perut',
        ]);
        return AttendancePermission::create([
            'guru_id' => $this->guru->id, 'creator_id' => $this->admin->id,
            'tanggal' => $tanggal, 'alasan' => 'Sakit perut', 'status' => $status,
        ]);
    }

    public function test_index_menampilkan_kolom_lampiran(): void
    {
        $this->makeIzin();

        $response = $this->actingAs($this->admin)->get(route('admin.permission.index'));

        $response->assertOk();
        $response->assertSee('Lampiran');
    }

    public function test_edit_alasan_sinkron_ke_absensi(): void
    {
        $perm = $this->makeIzin();

        $response = $this->actingAs($this->admin)->put(route('admin.permission.update', $perm->id), [
            'tanggal' => '2026-09-21', 'status' => 'izin', 'alasan' => 'Alasan baru',
        ]);

        $response->assertRedirect(route('admin.permission.index'));
        $this->assertSame('Alasan baru', $perm->fresh()->alasan);
        $this->assertSame('Alasan baru', Attendance::where('guru_id', $this->guru->id)->first()->keterangan);
        $this->assertDatabaseHas('audit_logs', ['action' => 'update', 'module' => 'izin']);
    }

    public function test_pindah_tanggal_memindahkan_absensi(): void
    {
        $perm = $this->makeIzin('2026-09-21');

        $response = $this->actingAs($this->admin)->put(route('admin.permission.update', $perm->id), [
            'tanggal' => '2026-09-22', 'status' => 'izin', 'alasan' => 'Sakit perut',
        ]);

        $response->assertRedirect(route('admin.permission.index'));
        $this->assertSame('2026-09-22', substr((string) $perm->fresh()->tanggal, 0, 10));
        $this->assertSame('2026-09-22', substr((string) Attendance::where('guru_id', $this->guru->id)->first()->tanggal, 0, 10));
    }

    public function test_pindah_ke_tanggal_bentrok_ditolak(): void
    {
        $perm = $this->makeIzin('2026-09-21');
        Attendance::create([
            'guru_id' => $this->guru->id, 'tanggal' => '2026-09-22',
            'status' => 'hadir', 'jam_masuk' => '07:00:00',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.permission.update', $perm->id), [
            'tanggal' => '2026-09-22', 'status' => 'izin', 'alasan' => 'X',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame('2026-09-21', substr((string) $perm->fresh()->tanggal, 0, 10));
    }

    public function test_ganti_lampiran_menghapus_file_lama(): void
    {
        Storage::disk('public')->put('attendance/evidence/lama.pdf', '%PDF-1.4 fake');
        $perm = $this->makeIzin();
        $att = Attendance::where('guru_id', $this->guru->id)->first();
        $att->update(['bukti_file' => 'attendance/evidence/lama.pdf']);

        $response = $this->actingAs($this->admin)->put(route('admin.permission.update', $perm->id), [
            'tanggal' => '2026-09-21', 'status' => 'izin', 'alasan' => 'Sakit perut',
            'bukti_file' => UploadedFile::fake()->image('baru.jpg', 800, 600),
        ]);

        $response->assertRedirect(route('admin.permission.index'));
        $newPath = $att->fresh()->bukti_file;
        $this->assertStringEndsWith('.webp', $newPath);
        $this->assertFalse(Storage::disk('public')->exists('attendance/evidence/lama.pdf'));
    }

    public function test_lampiran_palsu_ditolak(): void
    {
        $perm = $this->makeIzin();
        $fake = UploadedFile::fake()->create('palsu.pdf', 100, 'text/plain');

        $response = $this->actingAs($this->admin)->put(route('admin.permission.update', $perm->id), [
            'tanggal' => '2026-09-21', 'status' => 'izin', 'alasan' => 'X',
            'bukti_file' => $fake,
        ]);

        $response->assertSessionHasErrors('bukti_file');
    }
}
