<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceDetailViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_absensi_menampilkan_lampiran_dan_modal_lengkap(): void
    {
        Storage::fake('public');
        $admin = User::create([
            'name' => 'Admin', 'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'aktif',
        ]);
        $guru = User::create([
            'name' => 'Guru Uji', 'username' => '199005122025211001',
            'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'aktif',
        ]);
        Storage::disk('public')->put('attendance/evidence/bukti.pdf', '%PDF-1.4 test');
        Attendance::create([
            'guru_id' => $guru->id, 'tanggal' => now()->toDateString(),
            'status' => 'dinas_luar', 'keterangan' => 'Tugas',
            'keperluan_dinas' => 'Rapat', 'lokasi_dinas' => 'Dinas Pendidikan',
            'bukti_file' => 'attendance/evidence/bukti.pdf',
            'surat_tugas_file' => 'attendance/evidence/bukti.pdf',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $response->assertOk();
        // Kolom lampiran + tombol PDF
        $response->assertSee('Lampiran');
        $response->assertSee('attendance/evidence/bukti.pdf');
        // Modal detail: seksi dinas + lampiran + foto pulang
        $response->assertSee('detail_dinas_wrap');
        $response->assertSee('detail_bukti_section');
        $response->assertSee('detail_foto_pulang_section');
        // Modal koreksi tetap ada
        $response->assertSee('edit_dinas_group');
    }
}
