@extends('layouts.admin')

@section('title', 'Data Guru - Admin')
@section('page-title', 'Data Guru')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Kelola data guru beserta data kepegawaian, pendidikan, sertifikasi dan SK.</p>
    </div>
    <div class="flex gap-3">
        <x-button variant="secondary" href="{{ route('admin.guru.export') }}" download>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export XLSX
        </x-button>
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
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($gurus as $guru)
                    <tr data-name="{{ strtolower($guru->name) }}" data-nip="{{ $guru->username }}" data-status="{{ $guru->status }}">
                        <td>{{ ($gurus->currentPage() - 1) * $gurus->perPage() + $loop->iteration }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: var(--space-3);">
                                <div class="avatar avatar-sm">
                                    @if($guru->guruProfile?->foto)
                                        <img src="{{ $guru->guruProfile->foto }}" alt="{{ $guru->name }}">
                                    @else
                                        {{ substr($guru->name, 0, 1) }}
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium">{{ $guru->name }}</div>
                                    <div class="text-muted" style="font-size: var(--text-xs);">{{ $guru->guruProfile?->nipppk ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $guru->username }}</td>
                        <td class="text-muted">{{ $guru->guruProfile?->jabatan ?? '-' }}</td>
                        <td>
                            @if($guru->status === 'aktif')
                                <x-badge variant="success">Aktif</x-badge>
                            @else
                                <x-badge variant="danger">Nonaktif</x-badge>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-ghost btn-sm btn-icon" title="Detail" onclick="window.location.href='{{ route('admin.guru.show', $guru->id) }}'">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick="openEditModal({{ $guru->id }})">
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
    <x-empty-state title="Belum ada data guru" text="Tambahkan guru baru atau import dari file CSV/XLSX." />
@endif

{{-- Modal Tambah Guru --}}
<x-modal id="createModal" title="Tambah Data Guru" size="xl">
    <form method="POST" action="{{ route('admin.guru.store') }}" id="createForm">
        @csrf
        @include('admin.guru._form_tabs', ['mode' => 'create'])
    </form>
</x-modal>

{{-- Modal Edit Guru --}}
<x-modal id="editModal" title="Edit Data Guru" size="xl">
    <form method="POST" id="editForm">
        @csrf
        @method('PUT')
        <div id="editModalContent">
            <div style="text-align: center; padding: var(--space-8); color: var(--color-text-muted);">
                <div class="spinner" style="margin: 0 auto;"></div>
                <p style="margin-top: var(--space-3);">Memuat data...</p>
            </div>
        </div>
    </form>
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
<x-modal id="importModal" title="Import Guru">
    <form method="POST" action="{{ route('admin.guru.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="import_file" class="form-label">File CSV atau XLSX</label>
            <input type="file" id="import_file" name="csv_file" class="form-input" accept=".csv,.xlsx,text/csv" required>
        </div>
        <div style="background: var(--color-bg-secondary, var(--color-surface-muted)); padding: var(--space-3); border-radius: var(--radius-md); margin-bottom: var(--space-4);">
            <p style="font-size: var(--text-sm); font-weight: 500; margin-bottom: var(--space-2);">Format kolom (import export XLSX / data-guru CSV):</p>
            <code style="font-size: var(--text-xs); display: block; white-space: pre-wrap; word-break: break-word;">nama,nip,nipppk,nuptk,jenis_kelamin,agama,tempat_lahir,tanggal_lahir,status_kepegawaian,pangkat_golongan,jabatan,nik,alamat,no_hp,npwp,email,status,...</code>
            <p style="font-size: var(--text-xs); color: var(--color-text-muted); margin-top: var(--space-2);">
                Kolom opsional lain (TMT, KGB, sosial media, dll.) juga dikenali.
                Baris dengan NIP kosong akan memakai NIPPPK/NUPTK/NIK sebagai username.
                Password default: <code>password</code>. NIP yang sudah ada → data diupdate.
            </p>
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
    document.querySelectorAll('input[name="nip"]').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            if ([8, 9, 27, 13, 46, 37, 38, 39, 40, 35, 36].indexOf(e.keyCode) !== -1) return;
            if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].indexOf(e.keyCode) !== -1) return;
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    });
});

function openCreateModal() {
    document.getElementById('createModal').classList.add('active');
    setTimeout(() => switchTab('create', 'data_pribadi'), 100);
}

function openEditModal(id) {
    document.getElementById('editModal').classList.add('active');
    document.getElementById('editModalContent').innerHTML = '<div style="text-align:center;padding:var(--space-8);color:var(--color-text-muted);"><div class="spinner" style="margin:0 auto;"></div><p style="margin-top:var(--space-3);">Memuat data...</p></div>';

    fetch('/admin/guru/' + id + '/edit', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('editModalContent').innerHTML = data.html;
        document.getElementById('editForm').action = '/admin/guru/' + id;
        initEditFormTabs();
        switchTab('edit', 'data_pribadi');
    })
    .catch(() => {
        window.location.href = '/admin/guru/' + id + '/edit';
    });
}

function initEditFormTabs() {
    document.querySelectorAll('#editModal .guru-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            switchTab('edit', this.dataset.tab);
        });
    });
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

// Tab switching
function switchTab(prefix, tabName) {
    document.querySelectorAll('#' + prefix + 'Modal .guru-tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    document.querySelectorAll('#' + prefix + 'Modal .guru-tab-content').forEach(content => {
        content.style.display = content.dataset.tab === tabName ? 'block' : 'none';
    });
}

// Dynamic list management
let counters = { create_pendidikan: 0, create_tugas: 0, create_sertifikasi: 0, create_sk_pengangkatan: 0 };

function addListItem(prefix, type) {
    counters[prefix + '_' + type] = (counters[prefix + '_' + type] || 0) + 1;
    const idx = counters[prefix + '_' + type];
    const container = document.getElementById(prefix + '_' + type + '_list');
    const templates = getTemplates();
    const html = templates[type].replace(/__INDEX__/g, idx);
    container.insertAdjacentHTML('beforeend', html);
}

function addListItemWithData(prefix, type, data) {
    counters[prefix + '_' + type] = (counters[prefix + '_' + type] || 0) + 1;
    const idx = counters[prefix + '_' + type];
    const container = document.getElementById(prefix + '_' + type + '_list');
    const templates = getTemplates();
    let html = templates[type].replace(/__INDEX__/g, idx);

    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const fields = tempDiv.querySelectorAll('[data-field]');
    fields.forEach(field => {
        const fieldName = field.getAttribute('data-field');
        if (data[fieldName] !== undefined && data[fieldName] !== null) {
            field.value = data[fieldName];
        }
    });

    container.insertAdjacentHTML('beforeend', tempDiv.innerHTML);
}

function removeListItem(btn) {
    btn.closest('.guru-list-item').remove();
}

function getTemplates() {
    return {
        pendidikan: `
            <div class="guru-list-item guru-card-inner">
                <div class="guru-list-item-header">
                    <strong>Pendidikan #__INDEX__</strong>
                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Jenjang</label>
                        <select name="pendidikan[__INDEX__][jenjang]" class="form-select" data-field="jenjang">
                            <option value="">Pilih</option>
                            <option value="SD">SD</option>
                            <option value="SMP">SMP</option>
                            <option value="SMA/SMK">SMA/SMK</option>
                            <option value="D3">D3</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="pendidikan[__INDEX__][jurusan]" class="form-input" data-field="jurusan">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Perguruan Tinggi / Sekolah</label>
                        <input type="text" name="pendidikan[__INDEX__][perguruan_tinggi]" class="form-input" data-field="perguruan_tinggi">
                    </div>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Tahun Lulus</label>
                        <input type="text" name="pendidikan[__INDEX__][tahun_lulus]" class="form-input" data-field="tahun_lulus">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tempat</label>
                        <input type="text" name="pendidikan[__INDEX__][tempat]" class="form-input" data-field="tempat">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Ijazah</label>
                        <input type="text" name="pendidikan[__INDEX__][nomor_ijazah]" class="form-input" data-field="nomor_ijazah">
                    </div>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Tanggal Ijazah</label>
                        <input type="date" name="pendidikan[__INDEX__][tanggal_ijazah]" class="form-input" data-field="tanggal_ijazah">
                    </div>
                </div>
            </div>`,
        tugas: `
            <div class="guru-list-item guru-card-inner">
                <div class="guru-list-item-header">
                    <strong>Tugas #__INDEX__</strong>
                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Jenis</label>
                        <select name="tugas[__INDEX__][jenis]" class="form-select" data-field="jenis">
                            <option value="Tugas Tambahan">Tugas Tambahan</option>
                            <option value="Tugas Pokok">Tugas Pokok</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Uraian</label>
                        <input type="text" name="tugas[__INDEX__][uraian]" class="form-input" data-field="uraian">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jumlah Jam</label>
                        <input type="number" name="tugas[__INDEX__][jumlah_jam]" class="form-input" data-field="jumlah_jam">
                    </div>
                </div>
            </div>`,
        sertifikasi: `
            <div class="guru-list-item guru-card-inner">
                <div class="guru-list-item-header">
                    <strong>Sertifikasi #__INDEX__</strong>
                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="sertifikasi[__INDEX__][status]" class="form-select" data-field="status">
                            <option value="">Pilih</option>
                            <option value="Sudah Sertifikasi">Sudah Sertifikasi</option>
                            <option value="Belum Sertifikasi">Belum Sertifikasi</option>
                            <option value="Proses">Proses</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Sertifikat</label>
                        <input type="text" name="sertifikasi[__INDEX__][no_sertifikat]" class="form-input" data-field="no_sertifikat">
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Peserta</label>
                        <input type="text" name="sertifikasi[__INDEX__][no_peserta]" class="form-input" data-field="no_peserta">
                    </div>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">No. NRG</label>
                        <input type="text" name="sertifikasi[__INDEX__][no_nrg]" class="form-input" data-field="no_nrg">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bidang Studi</label>
                        <input type="text" name="sertifikasi[__INDEX__][bidang_studi]" class="form-input" data-field="bidang_studi">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Penyelenggara</label>
                        <input type="text" name="sertifikasi[__INDEX__][penyelenggara]" class="form-input" data-field="penyelenggara">
                    </div>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Tahun Lulus</label>
                        <input type="text" name="sertifikasi[__INDEX__][tahun_lulus]" class="form-input" data-field="tahun_lulus">
                    </div>
                </div>
            </div>`,
        sk_pengangkatan: `
            <div class="guru-list-item guru-card-inner">
                <div class="guru-list-item-header">
                    <strong>SK Pengangkatan #__INDEX__</strong>
                    <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="sk_pengangkatan[__INDEX__][kategori]" class="form-select" data-field="kategori">
                            <option value="SK Awal (Sekolah)">SK Awal (Sekolah)</option>
                            <option value="SK Kenaikan Pangkat">SK Kenaikan Pangkat</option>
                            <option value="SK Pensiun">SK Pensiun</option>
                            <option value="SK Mutasi">SK Mutasi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor SK</label>
                        <input type="text" name="sk_pengangkatan[__INDEX__][nomor_sk]" class="form-input" data-field="nomor_sk">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tanggal SK</label>
                        <input type="date" name="sk_pengangkatan[__INDEX__][tanggal_sk]" class="form-input" data-field="tanggal_sk">
                    </div>
                </div>
                <div class="guru-grid-3">
                    <div class="form-group">
                        <label class="form-label">Pejabat</label>
                        <input type="text" name="sk_pengangkatan[__INDEX__][pejabat]" class="form-input" data-field="pejabat">
                    </div>
                </div>
            </div>`
    };
}
</script>
@endpush
