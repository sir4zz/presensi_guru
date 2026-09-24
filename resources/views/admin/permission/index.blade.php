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
                    <th>Lampiran</th>
                    <th>Dibuat Oleh</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $perm)
                    @php $det = $details[$perm->id] ?? []; @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($perm->tanggal)->format('d M Y') }}</td>
                        <td class="font-medium">{{ $perm->guru->name ?? '-' }}</td>
                        <td class="text-muted">{{ $perm->guru->username ?? '-' }}</td>
                        <td><x-badge variant="{{ $perm->status === 'sakit' ? 'danger' : ($perm->status === 'dinas_luar' ? 'info' : 'info') }}">{{ $perm->status === 'dinas_luar' ? 'Dinas Luar' : ucfirst($perm->status ?? 'izin') }}</x-badge></td>
                        <td class="text-sm">{{ $perm->alasan }}</td>
                        <td>
                            @if(!empty($det['bukti_file']))
                                @if(\App\Services\FileUploadService::isPdfPath($det['bukti_file']))
                                    <a href="{{ Storage::url($det['bukti_file']) }}" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
                                @else
                                    <a href="{{ Storage::url($det['bukti_file']) }}" target="_blank" title="Lihat lampiran">
                                        <img src="{{ Storage::url($det['bukti_thumb']) }}" alt="Lampiran" loading="lazy" decoding="async" style="width:32px;height:32px;border-radius:var(--radius-md);object-fit:cover;">
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $perm->creator->name ?? '-' }}</td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-ghost btn-sm btn-icon" title="Detail" onclick='openDetailModal(@json($det))'>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick='openEditModal(@json($det))'>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
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

<x-modal id="detailModal" title="Detail Izin" size="lg">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
        <div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Guru</span>
                <span class="account-field-value" id="detail_guru">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">NIP</span>
                <span class="account-field-value" id="detail_nip">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Tanggal</span>
                <span class="account-field-value" id="detail_tanggal">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Jenis</span>
                <span id="detail_status">-</span>
            </div>
        </div>
        <div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Alasan</span>
                <span class="account-field-value" id="detail_alasan">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);" id="detail_lokasi_wrap">
                <span class="account-field-label">Lokasi Dinas</span>
                <span class="account-field-value" id="detail_lokasi">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Dibuat Oleh</span>
                <span class="account-field-value" id="detail_creator">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Verifikasi Dinas</span>
                <span class="account-field-value" id="detail_verified">-</span>
            </div>
        </div>
    </div>
    <div id="detail_bukti_section" style="margin-top: var(--space-4); display: none;">
        <span class="account-field-label" style="display: block; margin-bottom: var(--space-2);">Lampiran</span>
        <a id="detail_bukti_link" href="#" target="_blank" title="Lihat / Download">
            <img id="detail_bukti_img" src="" alt="Lampiran" loading="lazy" decoding="async" style="max-width: 200px; border-radius: var(--radius-md); display: none;">
            <span id="detail_bukti_pdf" class="btn btn-secondary btn-sm" style="display: none;">Lihat / Download PDF</span>
        </a>
    </div>
    <div class="modal-footer" style="padding: 0; border: none; margin-top: var(--space-6);">
        <x-button variant="secondary" onclick="document.getElementById('detailModal').classList.remove('active')">Tutup</x-button>
    </div>
</x-modal>

<x-modal id="editModal" title="Edit Izin" size="lg">
    <form method="POST" id="editForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_status" class="form-label">Jenis</label>
            <select id="edit_status" name="status" class="form-select" required>
                <option value="izin">Izin</option>
                <option value="sakit">Sakit</option>
                <option value="dinas_luar">Dinas Luar</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_tanggal" class="form-label">Tanggal</label>
            <input type="date" id="edit_tanggal" name="tanggal" class="form-input" required>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_alasan" class="form-label">Alasan / Keperluan</label>
            <textarea id="edit_alasan" name="alasan" class="form-textarea" required></textarea>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);" id="editLokasiGroup">
            <label for="edit_lokasi" class="form-label">Lokasi Dinas (wajib untuk Dinas Luar)</label>
            <input id="edit_lokasi" name="lokasi_dinas" class="form-input">
        </div>
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="edit_bukti" class="form-label">Ganti Lampiran (opsional)</label>
            <input id="edit_bukti" name="bukti_file" type="file" class="form-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
            <span class="form-help">Kosongkan bila lampiran tidak diganti.</span>
        </div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('editModal').classList.remove('active')">Batal</x-button>
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

function openDetailModal(d) {
    document.getElementById('detail_guru').textContent = d.guru_name || '-';
    document.getElementById('detail_nip').textContent = d.nip || '-';
    document.getElementById('detail_tanggal').textContent = d.tanggal || '-';
    document.getElementById('detail_alasan').textContent = d.alasan || '-';
    document.getElementById('detail_creator').textContent = (d.creator_name || '-') + (d.created_at ? ' · ' + d.created_at : '');

    const labelMap = { izin: 'Izin', sakit: 'Sakit', dinas_luar: 'Dinas Luar' };
    const badgeMap = { izin: 'info', sakit: 'danger', dinas_luar: 'info' };
    document.getElementById('detail_status').innerHTML = `<span class="badge badge-${badgeMap[d.status] || 'neutral'}">${labelMap[d.status] || d.status || '-'}</span>`;

    const isDinas = d.status === 'dinas_luar';
    document.getElementById('detail_lokasi_wrap').style.display = isDinas ? '' : 'none';
    document.getElementById('detail_lokasi').textContent = d.lokasi_dinas || '-';
    document.getElementById('detail_verified').textContent = !isDinas ? '-' : (d.dinas_verified ? 'Terverifikasi' : 'Menunggu verifikasi');

    const buktiSection = document.getElementById('detail_bukti_section');
    if (d.bukti_file) {
        document.getElementById('detail_bukti_link').href = '/storage/' + d.bukti_file;
        const isPdf = d.bukti_file.toLowerCase().endsWith('.pdf');
        document.getElementById('detail_bukti_img').style.display = isPdf ? 'none' : '';
        document.getElementById('detail_bukti_pdf').style.display = isPdf ? '' : 'none';
        if (!isPdf) document.getElementById('detail_bukti_img').src = '/storage/' + (d.bukti_thumb || d.bukti_file);
        buktiSection.style.display = 'block';
    } else {
        buktiSection.style.display = 'none';
    }

    document.getElementById('detailModal').classList.add('active');
}

function openEditModal(d) {
    document.getElementById('editForm').action = `/admin/izin/${d.id}`;
    document.getElementById('edit_status').value = d.status || 'izin';
    document.getElementById('edit_tanggal').value = d.tanggal || '';
    document.getElementById('edit_alasan').value = d.alasan || '';
    document.getElementById('edit_lokasi').value = d.lokasi_dinas || '';
    syncEditDinasFields();
    document.getElementById('editModal').classList.add('active');
}

const editStatusIzin = document.getElementById('edit_status');
function syncEditDinasFields() {
    const isDinas = editStatusIzin.value === 'dinas_luar';
    document.getElementById('editLokasiGroup').style.display = isDinas ? '' : 'none';
    document.getElementById('edit_lokasi').required = isDinas;
}
editStatusIzin.addEventListener('change', syncEditDinasFields);
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
