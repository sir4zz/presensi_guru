<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAccountRequest;
use App\Http\Requests\Guru\UpdatePasswordRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function __construct(protected FileUploadService $files)
    {
    }

    public function index()
    {
        $tab = request('tab') === 'all' ? 'all' : 'mine';

        $users = User::query()
            ->when(request('q'), fn ($q, $v) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$v}%")->orWhere('username', 'like', "%{$v}%")
            ))
            ->when(request('role'), fn ($q, $v) => $q->where('role', $v))
            ->when(request('status'), fn ($q, $v) => $q->where('status', $v))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.account.index', compact('users', 'tab'));
    }

    public function updateProfile(UpdateAccountRequest $request)
    {
        $user = $request->user();
        $old = $user->only(['name', 'username']);
        $user->update($request->only(['name', 'username']));

        AuditLogService::updated('akun', $user, $old, $user->fresh()->only(['name', 'username']), "Memperbarui profil sendiri");

        return redirect()->route('admin.account.index', ['tab' => 'mine'])->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama salah.']);
        }

        $user->update(['password' => Hash::make($request->password)]);
        AuditLogService::log('update', 'akun', 'Mengganti password sendiri');

        return redirect()->route('admin.account.index', ['tab' => 'mine'])->with('success', 'Password berhasil diubah.');
    }

    public function storeAdmin(StoreAdminRequest $request)
    {
        $user = User::create([
            'name' => $request->validated()['name'],
            'username' => $request->validated()['username'],
            'password' => Hash::make($request->validated()['password']),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        AuditLogService::created('akun', $user, "Menambahkan admin baru: {$user->name}");

        return redirect()->route('admin.account.index', ['tab' => 'all'])->with('success', 'Akun admin berhasil dibuat.');
    }

    public function resetPassword(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'Gunakan tab Akun Saya untuk mengganti password sendiri.');

        request()->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user->update(['password' => Hash::make(request('password'))]);
        AuditLogService::log('update', 'akun', "Mereset password akun: {$user->name}");

        return redirect()->route('admin.account.index', ['tab' => 'all'])->with('success', "Password {$user->name} berhasil direset.");
    }

    public function toggleStatus(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'Tidak dapat menonaktifkan akun sendiri.');

        $old = $user->status;
        $user->update(['status' => $old === 'aktif' ? 'nonaktif' : 'aktif']);
        AuditLogService::log('update', 'akun', "Mengubah status {$user->name}: {$old} → {$user->status}");

        return redirect()->route('admin.account.index', ['tab' => 'all'])->with('success', "Status {$user->name} menjadi {$user->status}.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 422, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->status === 'aktif', 422, 'Nonaktifkan akun terlebih dahulu sebelum menghapus.');

        $name = $user->name;

        // Guru: bersihkan file absensi agar tidak orphan (pola GuruController@destroy).
        if ($user->role === 'guru') {
            $paths = $user->attendances()
                ->get(['foto_masuk', 'foto_pulang', 'bukti_file', 'surat_tugas_file'])
                ->flatMap(fn ($a) => [$a->foto_masuk, $a->foto_pulang, $a->bukti_file, $a->surat_tugas_file])
                ->filter()->unique()->values();
        }

        $user->delete();

        if (isset($paths)) {
            foreach ($paths as $path) {
                $this->files->deleteFileIfOrphan($path);
            }
        }

        AuditLogService::log('delete', 'akun', "Menghapus akun: {$name}");

        return redirect()->route('admin.account.index', ['tab' => 'all'])->with('success', "Akun {$name} dihapus.");
    }
}
