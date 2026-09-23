@extends('layouts.admin')

@section('title', 'Izin Absen - Admin')
@section('page-title', 'Izin Absen')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Kelola izin absensi guru</p>
    </div>
    <x-button onclick="document.getElementById('createIzinModal').classList.add('active')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Izin / Sakit / Dinas
    </x-button>
</div>

<div class="filter-bar">
    <input type="date" class="form-input" id="dateFilter" value="{{ date('Y-m-d') }}">
    <select class="form-select" id="guruFilter">
        <option value="">Semua Guru</option>
        @if(isset($gurus))
            @foreach($gurus as $guru)
                <option value="{{ $guru->id }}">{{ $guru->name }}</option>
            @endforeach
        @endif
    </select>
</div>

@if(isset($permissions) && count($permissions) > 0)
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Guru</th>
                    <th>NIP</th>
                    <th>Status</th>
                    <th>Alasan</th>
                    <th>Dibuat Oleh</th>
                    <th style="width: 80px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $perm)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($perm->tanggal)->format('d M Y') }}</td>
                        <td class="font-medium">{{ $perm->guru->name ?? '-' }}</td>
                        <td class="text-muted">{{ $perm->guru->username ?? '-' }}</td>
                        <td><x-badge variant="{{ $perm->status === 'sakit' ? 'danger' : ($perm->status === 'dinas_luar' ? 'info' : 'info') }}">{{ $perm->status === 'dinas_luar' ? 'Dinas Luar' : ucfirst($perm->status ?? 'izin') }}</x-badge></td>
                        <td class="text-sm">{{ $perm->alasan }}</td>
                        <td class="text-muted">{{ $perm->creator->name ?? '-' }}</td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-ghost btn-sm btn-icon text-danger" title="Cabut Izin" onclick="confirmRevoke({{ $perm->id }})">
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
        {{ $permissions->links() }}
    </div>
@else
    <x-empty-state title="Belum ada izin" text="Belum ada pengajuan izin absensi dari guru." />
@endif

<x-modal id="createIzinModal" title="Buat Izin, Sakit, atau Dinas Luar" size="lg">
    <form method="POST" action="{{ route('admin.permission.store') }}" enctype="multipart/form-data" data-upload-form>
        @csrf

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="status_izin" class="form-label">Jenis</label>
            <select id="status_izin" name="status" class="form-select" required>
                <option value="izin">Izin</option><option value="sakit">Sakit</option><option value="dinas_luar">Dinas Luar</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="guru_ids" class="form-label">Pilih Guru</label>
            <select id="guru_ids" name="guru_ids[]" class="form-select" multiple style="min-height: 120px;">
                @if(isset($gurus))
                    @foreach($gurus as $guru)
                        <option value="{{ $guru->id }}">{{ $guru->name }} ({{ $guru->username }})</option>
                    @endforeach
                @endif
            </select>
            <span class="form-help">Tahan Ctrl/Cmd untuk memilih beberapa guru</span>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" class="form-input" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="alasan" class="form-label">Keterangan / Keperluan</label>
            <textarea id="alasan" name="alasan" class="form-textarea" required placeholder="Masukkan keterangan..."></textarea>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);" id="lokasiDinasGroup">
            <label for="lokasi_dinas" class="form-label">Lokasi Dinas (wajib untuk Dinas Luar)</label>
            <input id="lokasi_dinas" name="lokasi_dinas" class="form-input" placeholder="Lokasi kegiatan dinas">
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="bukti_file" class="form-label">Lampiran (opsional)</label>
            <input id="bukti_file" name="bukti_file" type="file" class="form-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
            <span class="form-help">JPG, PNG, WebP, atau PDF maksimal 5 MB. Berlaku untuk izin, sakit, dan dinas luar.</span>
        </div>

        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('createIzinModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</x-modal>

<x-modal id="revokeModal" title="Cabut Izin">
    <p style="margin-bottom: var(--space-4);">Apakah Anda yakin ingin mencabut izin ini?</p>
    <form method="POST" id="revokeForm">
        @csrf
        @method('DELETE')
        <div class="modal-footer" style="padding: 0; border: none; margin-top: var(--space-4);">
            <x-button variant="secondary" onclick="document.getElementById('revokeModal').classList.remove('active')">Batal</x-button>
            <x-button variant="danger" type="submit">Cabut Izin</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
function confirmRevoke(id) {
    document.getElementById('revokeForm').action = `/admin/izin/${id}`;
    document.getElementById('revokeModal').classList.add('active');
}
const statusIzin = document.getElementById('status_izin');
const lokasiGroup = document.getElementById('lokasiDinasGroup');
const lokasiInput = document.getElementById('lokasi_dinas');
function syncDinasFields() {
    const isDinas = statusIzin.value === 'dinas_luar';
    lokasiGroup.style.display = isDinas ? '' : 'none';
    lokasiInput.required = isDinas;
}
statusIzin.addEventListener('change', syncDinasFields);
syncDinasFields();
// Cegah double submit saat upload lampiran.
document.querySelectorAll('[data-upload-form]').forEach(function(f) {
    f.addEventListener('submit', function() {
        const btn = f.querySelector('button[type=submit]');
        if (btn) btn.disabled = true;
    });
});
</script>
@endpush
