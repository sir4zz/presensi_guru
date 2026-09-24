<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use App\Services\AttendanceReportService;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WeekendHolidayTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin', 'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'aktif',
        ]);
    }

    public function test_sabtu_adalah_tanggal_merah(): void
    {
        $service = app(AttendanceService::class);

        // 2026-09-26 = Sabtu, 2026-09-27 = Minggu, 2026-09-28 = Senin.
        $this->assertTrue($service->isRedDate('2026-09-26')['is_red']);
        $this->assertSame('hari Sabtu', $service->isRedDate('2026-09-26')['reason']);
        $this->assertTrue($service->isRedDate('2026-09-27')['is_red']);
        $this->assertFalse($service->isRedDate('2026-09-28')['is_red']);
    }

    public function test_hari_kerja_tidak_menghitung_sabtu(): void
    {
        $service = new AttendanceReportService;

        // Senin 21 Sep – Minggu 27 Sep 2026, tanpa libur: Senin–Jumat = 5.
        $work = $service->workingDays('2026-09-21', '2026-09-27');

        $this->assertSame(5, $work['count']);
        $this->assertNotContains('2026-09-26', $work['days']);
        $this->assertNotContains('2026-09-27', $work['days']);
        $this->assertContains('2026-09-25', $work['days']);
    }

    public function test_tambah_libur_massal_nama_beda_per_baris(): void
    {
        $this->makeAdmin();
        Holiday::create([
            'date' => '2026-12-25', 'name' => 'Natal', 'type' => 'nasional',
        ]);

        $response = $this->actingAs(User::first())->postJson(route('admin.holiday.store'), [
            'items' => [
                ['date' => '2026-12-24', 'name' => 'Cuti Natal', 'type' => 'nasional'],
                ['date' => '2026-12-26', 'name' => 'Libur Sekolah', 'type' => 'sekolah', 'description' => 'Acara sekolah'],
                ['date' => '2026-12-25', 'name' => 'Duplikat', 'type' => 'nasional'],
                ['date' => '2026-12-24', 'name' => 'Duplikat Input', 'type' => 'daerah'],
            ],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'created' => 2, 'skipped' => 2]);

        $natal = Holiday::whereDate('date', '2026-12-24')->first();
        $this->assertSame('Cuti Natal', $natal->name);
        $this->assertSame('nasional', $natal->type);

        $sekolah = Holiday::whereDate('date', '2026-12-26')->first();
        $this->assertSame('Libur Sekolah', $sekolah->name);
        $this->assertSame('sekolah', $sekolah->type);
        $this->assertSame('Acara sekolah', $sekolah->description);

        $this->assertSame(3, Holiday::count()); // 1 lama + 2 baru
    }

    public function test_tambah_libur_semua_duplikat_ditolak(): void
    {
        $this->makeAdmin();
        Holiday::create([
            'date' => '2026-12-25', 'name' => 'Natal', 'type' => 'nasional',
        ]);

        $response = $this->actingAs(User::first())->postJson(route('admin.holiday.store'), [
            'items' => [
                ['date' => '2026-12-25', 'name' => 'X', 'type' => 'nasional'],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertSame(1, Holiday::count());
    }

    public function test_tambah_libur_items_kosong_ditolak(): void
    {
        $this->makeAdmin();

        $response = $this->actingAs(User::first())->postJson(route('admin.holiday.store'), [
            'items' => [],
        ]);

        $response->assertStatus(422);
        $this->assertSame(0, Holiday::count());
    }

    public function test_edit_libur_tunggal_tetap_berfungsi(): void
    {
        $this->makeAdmin();
        $holiday = Holiday::create([
            'date' => '2026-12-25', 'name' => 'Natal', 'type' => 'nasional',
        ]);

        $response = $this->actingAs(User::first())->putJson(
            route('admin.holiday.update', $holiday->id),
            ['date' => '2026-12-25', 'name' => 'Hari Natal', 'type' => 'nasional']
        );

        $response->assertOk();
        $this->assertSame('Hari Natal', $holiday->fresh()->name);
    }
}
