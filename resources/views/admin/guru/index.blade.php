@extends('layouts.admin')

@section('title', 'Data Guru - Admin')
@section('page-title', 'Data Guru')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Kelola data guru</p>
    </div>
    <div class="flex gap-3">
        <x-button variant="secondary" onclick="document.getElementById('importModal').classList.add('active')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Import
        </x-button>
        <x-button onclick="openCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Guru
        </x-button>
    </div>
</div>

<div class="filter-bar">
    <div class="search-input">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" class="form-input" placeholder="Cari nama atau NIP..." id="searchInput" onkeyup="filterTable()">
    </div>
    <select class="form-select" id="statusFilter" onchange="filterTable()">
        <option value="">Semua Status</option>
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
    </select>
</div>

@if(isset($gurus) && count($gurus) > 0)
    <div class="table-wrapper">
        <table class="table" id="guruTable">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>SK</th>
                    <th>SPMT</th>
                    <th>Status</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gurus as $guru)
                    <tr data-name="{{ strtolower($guru->name) }}" data-nip="{{ $guru->username }}" data-status="{{ $guru->status }}">
                        <td>{{ ($gurus->currentPage() - 1) * $gurus->perPage() + $loop->iteration }}</td>
                        <td class="font-medium">{{ $guru->name }}</td>
                        <td class="text-muted">{{ $guru->username }}</td>
                        <td class="text-muted">{{ $guru->guruProfile?->sk ?? '-' }}</td>
                        <td class="text-muted">{{ $guru->guruProfile?->spmt ?? '-' }}</td>
                        <td>
                            @if($guru->status === 'aktif')
                                <x-badge variant="success">Aktif</x-badge>
                            @else
                                <x-badge variant="danger">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-ghost btn-sm btn-icon" title="Detail" onclick="openDetailModal({{ $guru->id }}, '{{ addslashes($guru->name) }}', '{{ $guru->username }}', '{{ addslashes($guru->guruProfile?->sk ?? '') }}', '{{ addslashes($guru->guruProfile?->spmt ?? '') }}', '{{ $guru->status }}')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="openEditModal({{ $guru->id }}, '{{ addslashes($guru->name) }}', '{{ $guru->username }}', '{{ addslashes($guru->guruProfile?->sk ?? '') }}', '{{ addslashes($guru->guruProfile?->spmt ?? '') }}', '{{ $guru->status }}')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="confirmDelete({{ $guru->id }})">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: var(--space-4); display: flex; justify-content: center;">
        {{ $gurus->links() }}
    </div>
@else
    <x-empty-state title="Belum ada data guru" text="Tambahkan guru baru atau import dari file Excel." />
@endif

{{-- Modal Tambah Guru --}}
<x-modal id="createModal" title="Tambah Guru" size="lg">
    <form method="POST" action="{{ route('admin.guru.store') }}">
        @csrf
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" class="form-input @error('name') form-input-error @enderror" value="{{ old('name') }}" required pattern="[A-Za-z\s]+" placeholder="Masukkan nama lengkap">
            @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">NIP</label>
            <input type="text" name="nip" class="form-input @error('nip') form-input-error @enderror" value="{{ old('nip') }}" required pattern="[0-9]{1,20}" inputmode="numeric" placeholder="Masukkan NIP (angka saja)">
            @error('nip') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
            <div class="form-group">
                <label class="form-label">SK</label>
                <input type="text" name="sk" class="form-input" value="{{ old('sk') }}">
            </div>
            <div class="form-group">
                <label class="form-label">SPMT</label>
                <input type="text" name="spmt" class="form-input" value="{{ old('spmt') }}">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-input @error('password') form-input-error @enderror" required>
            @error('password') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label class="form-label">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" class="form-input" required>
        </div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('createModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

{{-- Modal Edit Guru --}}
<x-modal id="editModal" title="Edit Guru" size="lg">
    <form method="POST" id="editForm">
        @csrf
        @method('PUT')
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" id="edit_name" name="name" class="form-input" required pattern="[A-Za-z\s]+" placeholder="Masukkan nama lengkap">
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">NIP</label>
            <input type="text" id="edit_nip" name="nip" class="form-input" required pattern="[0-9]{1,20}" inputmode="numeric" placeholder="Masukkan NIP (angka saja)">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
            <div class="form-group">
                <label for="edit_sk" class="form-label">SK</label>
                <input type="text" id="edit_sk" name="sk" class="form-input">
            </div>
            <div class="form-group">
                <label for="edit_spmt" class="form-label">SPMT</label>
                <input type="text" id="edit_spmt" name="spmt" class="form-input">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_status" class="form-label">Status</label>
            <select id="edit_status" name="status" class="form-select">
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_password" class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" id="edit_password" name="password" class="form-input @error('password') form-input-error @enderror">
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="edit_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="edit_password_confirmation" name="password_confirmation" class="form-input">
        </div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('editModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan Perubahan</x-button>
        </div>
    </form>
</x-modal>

{{-- Modal Detail Guru --}}
<x-modal id="detailModal" title="Detail Guru">
    <div style="display: flex; flex-direction: column; gap: var(--space-4);">
        <div class="account-field">
            <span class="account-field-label">Nama</span>
            <span class="account-field-value" id="detail_name">-</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">NIP</span>
            <span class="account-field-value" id="detail_nip">-</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">SK</span>
            <span class="account-field-value" id="detail_sk">-</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">SPMT</span>
            <span class="account-field-value" id="detail_spmt">-</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">Status</span>
            <span id="detail_status">-</span>
        </div>
    </div>
    <div class="modal-footer" style="padding: 0; border: none; margin-top: var(--space-6);">
        <x-button variant="secondary" onclick="document.getElementById('detailModal').classList.remove('active')">Tutup</x-button>
    </div>
</x-modal>

{{-- Modal Hapus --}}
<x-modal id="deleteModal" title="Hapus Guru">
    <p style="margin-bottom: var(--space-4);">Apakah Anda yakin ingin menghapus guru ini? Data yang dihapus tidak dapat dikembalikan.</p>
    <form method="POST" id="deleteForm">
        @csrf
        @method('DELETE')
        <div class="modal-footer" style="padding: 0; border: none; margin-top: var(--space-4);">
            <x-button variant="secondary" onclick="document.getElementById('deleteModal').classList.remove('active')">Batal</x-button>
            <x-button variant="danger" type="submit">Hapus</x-button>
        </div>
    </form>
</x-modal>

{{-- Modal Import --}}
<x-modal id="importModal" title="Import Guru dari CSV">
    <form method="POST" action="{{ route('admin.guru.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="import_file" class="form-label">File CSV (.csv)</label>
            <input type="file" id="import_file" name="csv_file" class="form-input" accept=".csv" required>
        </div>
        <div style="background: var(--color-bg-secondary); padding: var(--space-3); border-radius: var(--radius-md); margin-bottom: var(--space-4);">
            <p style="font-size: var(--text-sm); font-weight: 500; margin-bottom: var(--space-2);">Format CSV:</p>
            <code style="font-size: var(--text-xs); display: block; white-space: pre;">name,nip,sk,spmt
Budi Santoso,1987654321,SK-001,SPMT-001
Siti Aminah,1987654322,SK-002,SPMT-002</code>
            <p style="font-size: var(--text-xs); color: var(--color-text-muted); margin-top: var(--space-2);">Password default: <code>password</code>. NIP yang sudah ada akan dilewati.</p>
        </div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('importModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Import</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Block non-numeric input on NIP fields
    document.querySelectorAll('input[name="nip"]').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40, 35, 36].indexOf(e.keyCode) !== -1) return;
            if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].indexOf(e.keyCode) !== -1) return;
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        el.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });

    // Auto-open create modal if there are validation errors
    var createForm = document.querySelector('#createModal form');
    if (createForm && createForm.querySelector('.form-error')) {
        document.getElementById('createModal').classList.add('active');
    }
});

function openCreateModal() {
    document.getElementById('createModal').classList.add('active');
}

function openEditModal(id, name, nip, sk, spmt, status) {
    document.getElementById('editForm').action = '/admin/guru/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_nip').value = nip;
    document.getElementById('edit_sk').value = sk || '';
    document.getElementById('edit_spmt').value = spmt || '';
    document.getElementById('edit_status').value = status;
    document.getElementById('editModal').classList.add('active');
}

function openDetailModal(id, name, nip, sk, spmt, status) {
    document.getElementById('detail_name').textContent = name;
    document.getElementById('detail_nip').textContent = nip;
    document.getElementById('detail_sk').textContent = sk || '-';
    document.getElementById('detail_spmt').textContent = spmt || '-';
    document.getElementById('detail_status').innerHTML = status === 'aktif'
        ? '<span class="badge badge-success">Aktif</span>'
        : '<span class="badge badge-danger">Nonaktif</span>';
    document.getElementById('detailModal').classList.add('active');
}

function confirmDelete(id) {
    document.getElementById('deleteForm').action = `/admin/guru/${id}`;
    document.getElementById('deleteModal').classList.add('active');
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const rows = document.querySelectorAll('#guruTable tbody tr');
    rows.forEach(row => {
        const name = row.dataset.name;
        const nip = row.dataset.nip;
        const status = row.dataset.status;
        const matchSearch = name.includes(search) || nip.includes(search);
        const matchStatus = !statusFilter || status === statusFilter;
        row.style.display = matchSearch && matchStatus ? '' : 'none';
    });
}
</script>
@endpush
