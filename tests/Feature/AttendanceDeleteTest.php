<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceDeleteTest extends TestCase
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

    protected function makeAttendance(string $tanggal, string $status = 'hadir', ?string $foto = null, ?User $guru = null): Attendance
    {
        if ($foto) {
            Storage::disk('public')->put($foto, 'fake-image');
        }
        return Attendance::create([
            'guru_id' => ($guru ?? $this->guru)->id, 'tanggal' => $tanggal, 'status' => $status,
            'jam_masuk' => '07:10:00', 'foto_masuk' => $foto,
        ]);
    }

    protected function makeGuru(string $username, string $name = 'Guru'): User
    {
        return User::create([
            'name' => $name, 'username' => $username,
            'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'aktif',
        ]);
    }

    public function test_filter_hari_menampilkan_tanggal_diminta(): void
    {
        $this->makeAttendance('2026-09-20');
        $this->makeAttendance('2026-09-21');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.index', ['tanggal' => '2026-09-20']));

        $response->assertOk();
        // Hanya 1 baris data (selain header) untuk tanggal tersebut.
        $this->assertSame(1, Attendance::whereDate('tanggal', '2026-09-20')->count());
        $response->assertSee('20 September 2026', false);
    }

    public function test_hapus_satu_record_beserta_file(): void
    {
        $att = $this->makeAttendance('2026-09-21', 'hadir', 'attendance/selfie/a.webp');

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.attendance.delete', $att->id));

        $response->assertRedirect();
        $this->assertNull($att->fresh());
        $this->assertFalse(Storage::disk('public')->exists('attendance/selfie/a.webp'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'delete', 'module' => 'absensi']);
    }

    public function test_bulk_hapus_atomik_id_fiktif_menggagalkan_semua(): void
    {
        $guru2 = $this->makeGuru('199005122025211002', 'Guru Dua');
        $a = $this->makeAttendance('2026-09-21');
        $b = $this->makeAttendance('2026-09-21', 'terlambat', null, $guru2);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.attendance.bulk-delete'), ['ids' => [$a->id, 999999]]);

        $response->assertSessionHasErrors('ids.1');
        $this->assertNotNull($a->fresh());
        $this->assertNotNull($b->fresh());
    }

    public function test_bulk_hapus_berhasil(): void
    {
        $guru2 = $this->makeGuru('199005122025211002', 'Guru Dua');
        $a = $this->makeAttendance('2026-09-21', 'hadir', 'attendance/selfie/a.webp');
        $b = $this->makeAttendance('2026-09-21', 'hadir', 'attendance/selfie/b.webp', $guru2);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.attendance.bulk-delete'), ['ids' => [$a->id, $b->id]]);

        $response->assertRedirect();
        $this->assertSame(0, Attendance::count());
        $this->assertFalse(Storage::disk('public')->exists('attendance/selfie/a.webp'));
        $this->assertFalse(Storage::disk('public')->exists('attendance/selfie/b.webp'));
    }

    public function test_file_dishare_tidak_ikut_terhapus(): void
    {
        Storage::disk('public')->put('attendance/evidence/shared.pdf', '%PDF-1.4 x');
        $a = $this->makeAttendance('2026-09-21');
        $a->update(['bukti_file' => 'attendance/evidence/shared.pdf']);
        $b = $this->makeAttendance('2026-09-22');
        $b->update(['bukti_file' => 'attendance/evidence/shared.pdf']);

        $this->actingAs($this->admin)->delete(route('admin.attendance.delete', $a->id));

        $this->assertTrue(Storage::disk('public')->exists('attendance/evidence/shared.pdf'));
    }

    public function test_purge_preview_dan_purge_harian(): void
    {
        $this->makeAttendance('2026-09-21', 'hadir', 'attendance/selfie/a.webp');
        $this->makeAttendance('2026-09-22');

        $preview = $this->actingAs($this->admin)->getJson(
            route('admin.report.purge-preview', ['scope' => 'day', 'date' => '2026-09-21'])
        );
        $preview->assertOk()->assertJson(['records' => 1, 'files' => 1]);

        $purge = $this->actingAs($this->admin)->postJson(route('admin.report.purge'), [
            'scope' => 'day', 'date' => '2026-09-21', 'confirm' => 'HAPUS',
        ]);
        $purge->assertOk()->assertJson(['success' => true]);

        $this->assertSame(1, Attendance::count());
        $this->assertFalse(Storage::disk('public')->exists('attendance/selfie/a.webp'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'delete', 'module' => 'absensi']);
    }

    public function test_purge_tanpa_konfirmasi_ditolak(): void
    {
        $this->makeAttendance('2026-09-21');

        $response = $this->actingAs($this->admin)->postJson(route('admin.report.purge'), [
            'scope' => 'day', 'date' => '2026-09-21', 'confirm' => 'hapus',
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, Attendance::count());
    }

    public function test_purge_bulanan_dan_tahunan_tepat_cakupan(): void
    {
        $this->makeAttendance('2026-09-05');
        $this->makeAttendance('2026-09-20');
        $this->makeAttendance('2026-10-02');
        $this->makeAttendance('2025-09-10');

        $this->actingAs($this->admin)->postJson(route('admin.report.purge'), [
            'scope' => 'month', 'month' => 9, 'year' => 2026, 'confirm' => 'HAPUS',
        ])->assertOk();

        $this->assertSame(['2025-09-10', '2026-10-02'], Attendance::orderBy('tanggal')->pluck('tanggal')->map(
            fn ($d) => substr((string) $d, 0, 10)
        )->all());

        $this->actingAs($this->admin)->postJson(route('admin.report.purge'), [
            'scope' => 'year', 'year' => 2025, 'confirm' => 'HAPUS',
        ])->assertOk();

        $this->assertSame(['2026-10-02'], Attendance::pluck('tanggal')->map(
            fn ($d) => substr((string) $d, 0, 10)
        )->all());
    }

    public function test_purge_menghormati_filter_guru(): void
    {
        $guru2 = User::create([
            'name' => 'Guru Dua', 'username' => '199005122025211002',
            'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'aktif',
        ]);
        $this->makeAttendance('2026-09-21');
        Attendance::create([
            'guru_id' => $guru2->id, 'tanggal' => '2026-09-21',
            'status' => 'hadir', 'jam_masuk' => '07:00:00',
        ]);

        $this->actingAs($this->admin)->postJson(route('admin.report.purge'), [
            'scope' => 'day', 'date' => '2026-09-21',
            'guru_id' => $guru2->id, 'confirm' => 'HAPUS',
        ])->assertOk();

        $this->assertSame(1, Attendance::count());
        $this->assertSame($this->guru->id, Attendance::first()->guru_id);
    }
}
