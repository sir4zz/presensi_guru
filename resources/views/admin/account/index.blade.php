@extends('layouts.admin')

@section('title', 'Pengaturan Akun - Admin')
@section('page-title', 'Pengaturan Akun')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Kelola akun sendiri dan semua akun pengguna</p>
    </div>
</div>

<div style="display: flex; gap: var(--space-2); margin-bottom: var(--space-5);">
    <a href="{{ route('admin.account.index', ['tab' => 'mine']) }}" class="btn btn-sm {{ $tab === 'mine' ? 'btn-primary' : 'btn-secondary' }}">Akun Saya</a>
    <a href="{{ route('admin.account.index', ['tab' => 'all']) }}" class="btn btn-sm {{ $tab === 'all' ? 'btn-primary' : 'btn-secondary' }}">Semua Akun</a>
</div>

@if($tab === 'mine')
    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <h3 class="card-title">Informasi Akun</h3>
        </div>
        <div class="account-info">
            <div class="account-field">
                <span class="account-field-label">Nama</span>
                <span class="account-field-value">{{ Auth::user()->name }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Username</span>
                <span class="account-field-value">{{ Auth::user()->username }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Role</span>
                <x-badge variant="info">{{ ucfirst(Auth::user()->role) }}</x-badge>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: var(--space-6);">
        <div class="card-header">
            <h3 class="card-title">Ubah Profil</h3>
        </div>
        <form method="POST" action="{{ route('admin.account.profile') }}">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="name" class="form-label">Nama</label>
                <input type="text" id="name" name="name" class="form-input @error('name') form-input-error @enderror" value="{{ old('name', Auth::user()->name) }}" required>
                @error('name')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" style="margin-bottom: var(--space-6);">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-input @error('username') form-input-error @enderror" value="{{ old('username', Auth::user()->username) }}" required>
                @error('username')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            <x-button type="submit">Simpan Profil</x-button>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ganti Password</h3>
        </div>
        <form method="POST" action="{{ route('admin.account.password') }}">
            @csrf
            @method('PUT')
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="current_password" class="form-label">Password Lama</label>
                <input type="password" id="current_password" name="current_password" class="form-input @error('current_password') form-input-error @enderror" required>
                @error('current_password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="password" class="form-label">Password Baru</label>
                <input type="password" id="password" name="password" class="form-input @error('password') form-input-error @enderror" required>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-group" style="margin-bottom: var(--space-6);">
                <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
            </div>
            <x-button type="submit">Ganti Password</x-button>
        </form>
    </div>
@else
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.account.index') }}" style="display: contents;">
            <input type="hidden" name="tab" value="all">
            <div class="search-input">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="q" class="form-input" placeholder="Cari nama atau username..." value="{{ request('q') }}">
            </div>
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="guru" {{ request('role') === 'guru' ? 'selected' : '' }}>Guru</option>
            </select>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <x-button type="submit" variant="secondary">Cari</x-button>
        </form>
        <x-button onclick="document.getElementById('addAdminModal').classList.add('active')">Tambah Admin</x-button>
    </div>

    @if(isset($users) && count($users) > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td class="font-medium">{{ $u->name }}{{ $u->id === auth()->id() ? ' (Anda)' : '' }}</td>
                            <td class="text-muted">{{ $u->username }}</td>
                            <td><x-badge variant="{{ $u->role === 'admin' ? 'info' : 'neutral' }}">{{ ucfirst($u->role) }}</x-badge></td>
                            <td>
                                @if($u->status === 'aktif')
                                    <x-badge variant="success">Aktif</x-badge>
                                @else
                                    <x-badge variant="danger">Nonaktif</x-badge>
                                @endif
                            </td>
                            <td>
                                @if($u->id === auth()->id())
                                    <span class="text-muted text-sm">-</span>
                                @else
                                    <div style="display: flex; gap: var(--space-1); flex-wrap: wrap;">
                                        <button class="btn btn-secondary btn-sm" title="Reset password" onclick='openResetModal({{ $u->id }}, @json($u->name))'>Reset</button>
                                        <form method="POST" action="{{ route('admin.account.status', $u) }}" style="display: inline;" onsubmit="return confirm('Ubah status {{ $u->name }}?')">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-secondary btn-sm" title="Aktif/Nonaktif">{{ $u->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>
                                        @if($u->status === 'nonaktif')
                                            <button class="btn btn-danger btn-sm" title="Hapus" onclick='openDeleteModal({{ $u->id }}, @json($u->name))'>Hapus</button>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: var(--space-4); display: flex; justify-content: center;">
            {{ $users->links() }}
        </div>
    @else
        <x-empty-state title="Tidak ada akun" text="Tidak ada akun yang cocok dengan filter." />
    @endif
@endif

{{-- Modal Tambah Admin --}}
<x-modal id="addAdminModal" title="Tambah Admin" size="md">
    <form method="POST" action="{{ route('admin.account.store-admin') }}">
        @csrf
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="admin_name" class="form-label">Nama</label>
            <input type="text" id="admin_name" name="name" class="form-input" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="admin_username" class="form-label">Username</label>
            <input type="text" id="admin_username" name="username" class="form-input" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="admin_password" class="form-label">Password</label>
            <input type="password" id="admin_password" name="password" class="form-input" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="admin_password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" id="admin_password_confirmation" name="password_confirmation" class="form-input" required>
        </div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('addAdminModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

{{-- Modal Reset Password --}}
<x-modal id="resetModal" title="Reset Password">
    <p style="margin-bottom: var(--space-4);">Reset password untuk <strong id="resetName">-</strong>?</p>
    <form method="POST" id="resetForm">
        @csrf
        @method('PUT')
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="reset_password" class="form-label">Password Baru</label>
            <input type="password" id="reset_password" name="password" class="form-input" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="reset_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="reset_password_confirmation" name="password_confirmation" class="form-input" required>
        </div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('resetModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit" variant="danger">Reset</x-button>
        </div>
    </form>
</x-modal>

{{-- Modal Hapus --}}
<x-modal id="deleteModal" title="Hapus Akun">
    <p style="margin-bottom: var(--space-4);">Hapus akun <strong id="deleteName">-</strong>? Data absensi ikut terhapus dan file terkait dibersihkan. Tindakan ini tidak dapat dibatalkan.</p>
    <form method="POST" id="deleteForm">
        @csrf
        @method('DELETE')
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('deleteModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit" variant="danger">Hapus</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function openResetModal(id, name) {
    document.getElementById('resetName').textContent = name;
    document.getElementById('resetForm').action = `/admin/akun/${id}/reset-password`;
    document.getElementById('resetModal').classList.add('active');
}

function openDeleteModal(id, name) {
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteForm').action = `/admin/akun/${id}`;
    document.getElementById('deleteModal').classList.add('active');
}
</script>
@endpush
