<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin', 'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin', 'status' => 'aktif',
        ]);
    }

    protected function makeGuru(string $username = '199005122025211001'): User
    {
        return User::create([
            'name' => 'Guru Uji', 'username' => $username,
            'password' => Hash::make('password'),
            'role' => 'guru', 'status' => 'aktif',
        ]);
    }

    public function test_tab_akun_saya_menampilkan_info(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.account.index'));

        $response->assertOk();
        $response->assertSee('Admin');
        $response->assertSee('Ubah Profil');
        $response->assertSee('Ganti Password');
    }

    public function test_update_profil_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.account.profile'), [
            'name' => 'Admin Baru', 'username' => 'admin',
        ]);

        $response->assertRedirect(route('admin.account.index', ['tab' => 'mine']));
        $this->assertSame('Admin Baru', $this->admin->fresh()->name);
    }

    public function test_update_profil_username_duplikat_ditolak(): void
    {
        $this->makeGuru();

        $response = $this->actingAs($this->admin)->put(route('admin.account.profile'), [
            'name' => 'Admin', 'username' => '199005122025211001',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertSame('admin', $this->admin->fresh()->username);
    }

    public function test_ganti_password_sendiri(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.account.password'), [
            'current_password' => 'password',
            'password' => 'baru123', 'password_confirmation' => 'baru123',
        ]);

        $response->assertRedirect(route('admin.account.index', ['tab' => 'mine']));
        $this->assertTrue(Hash::check('baru123', $this->admin->fresh()->password));
    }

    public function test_ganti_password_lama_salah_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.account.password'), [
            'current_password' => 'salah',
            'password' => 'baru123', 'password_confirmation' => 'baru123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_tambah_admin_bisa_login(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.account.store-admin'), [
            'name' => 'Admin Dua', 'username' => 'admin2',
            'password' => 'rahasia1', 'password_confirmation' => 'rahasia1',
        ]);

        $response->assertRedirect(route('admin.account.index', ['tab' => 'all']));
        $baru = User::where('username', 'admin2')->first();
        $this->assertNotNull($baru);
        $this->assertSame('admin', $baru->role);
        $this->assertTrue(Hash::check('rahasia1', $baru->password));
    }

    public function test_reset_password_guru(): void
    {
        $guru = $this->makeGuru();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.account.reset-password', $guru), [
                'password' => 'ganti123', 'password_confirmation' => 'ganti123',
            ]);

        $response->assertRedirect(route('admin.account.index', ['tab' => 'all']));
        $this->assertTrue(Hash::check('ganti123', $guru->fresh()->password));
        $this->assertFalse(Hash::check('password', $guru->fresh()->password));
    }

    public function test_reset_password_sendiri_ditolak(): void
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.account.reset-password', $this->admin), [
                'password' => 'ganti123', 'password_confirmation' => 'ganti123',
            ]);

        $response->assertStatus(422);
        $this->assertTrue(Hash::check('password', $this->admin->fresh()->password));
    }

    public function test_toggle_status_akun_lain(): void
    {
        $guru = $this->makeGuru();

        $this->actingAs($this->admin)->patch(route('admin.account.status', $guru));
        $this->assertSame('nonaktif', $guru->fresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.account.status', $guru));
        $this->assertSame('aktif', $guru->fresh()->status);
    }

    public function test_nonaktifkan_akun_sendiri_ditolak(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('admin.account.status', $this->admin));

        $response->assertStatus(422);
        $this->assertSame('aktif', $this->admin->fresh()->status);
    }

    public function test_hapus_akun_aktif_ditolak_harus_nonaktif_dulu(): void
    {
        $guru = $this->makeGuru();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.account.destroy', $guru));

        $response->assertStatus(422);
        $this->assertNotNull($guru->fresh());
    }

    public function test_hapus_akun_nonaktif_berhasil(): void
    {
        $guru = $this->makeGuru();
        $guru->update(['status' => 'nonaktif']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.account.destroy', $guru));

        $response->assertRedirect(route('admin.account.index', ['tab' => 'all']));
        $this->assertNull($guru->fresh());
    }

    public function test_hapus_akun_sendiri_ditolak(): void
    {
        $this->admin->update(['status' => 'nonaktif']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.account.destroy', $this->admin));

        $response->assertStatus(422);
        $this->assertNotNull($this->admin->fresh());
    }

    public function test_route_profil_lama_redirect(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.profile'));

        $response->assertRedirect(route('admin.account.index'));
    }
}
