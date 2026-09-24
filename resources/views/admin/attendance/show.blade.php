@extends('layouts.admin')

@section('title', 'Detail Absensi - Admin')
@section('page-title', 'Detail Absensi')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Detail data absensi</p>
    </div>
    <div class="flex gap-3">
        <x-button variant="secondary" href="{{ route('admin.attendance.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </x-button>
        <x-button onclick="document.getElementById('editModal').classList.add('active')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Koreksi
        </x-button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Absensi</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <div class="account-field">
                <span class="account-field-label">Guru</span>
                <span class="account-field-value">{{ $attendance->guru->name ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIP</span>
                <span class="account-field-value">{{ $attendance->guru->username ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Tanggal</span>
                <span class="account-field-value">{{ \Carbon\Carbon::parse($attendance->tanggal)->format('d F Y') }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Status</span>
                @if($attendance->status === 'hadir')
                    <x-badge variant="success">Hadir</x-badge>
                @elseif($attendance->status === 'terlambat')
                    <x-badge variant="warning">Terlambat</x-badge>
                @elseif($attendance->status === 'izin')
                    <x-badge variant="info">Izin</x-badge>
                @elseif($attendance->status === 'sakit')
                    <x-badge variant="danger">Sakit</x-badge>
                @elseif($attendance->status === 'dinas_luar')
                    <x-badge variant="info">Dinas Luar</x-badge>
                @else
                    <x-badge variant="danger">TAK</x-badge>
                @endif
            </div>
            <div class="account-field">
                <span class="account-field-label">Jam Masuk</span>
                <span class="account-field-value">{{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jam Pulang</span>
                <span class="account-field-value">{{ $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i') : '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Keterangan</span>
                <span class="account-field-value">{{ $attendance->keterangan ?? '-' }}</span>
            </div>
            @if($attendance->status === 'dinas_luar')
                <div class="account-field"><span class="account-field-label">Lokasi Dinas</span><span class="account-field-value">{{ $attendance->lokasi_dinas ?? '-' }}</span></div>
                <div class="account-field"><span class="account-field-label">Keperluan</span><span class="account-field-value">{{ $attendance->keperluan_dinas ?? '-' }}</span></div>
                <div class="account-field"><span class="account-field-label">Verifikasi</span><span class="account-field-value">{{ $attendance->dinas_verified_at ? 'Terverifikasi' : 'Menunggu verifikasi' }}</span></div>
                @if($attendance->bukti_file)<div class="account-field"><span class="account-field-label">Bukti Lampiran</span><a href="{{ Storage::url($attendance->bukti_file) }}" target="_blank" class="btn btn-secondary btn-sm">Lihat / Download</a></div>@endif
                <div style="display:flex;gap:var(--space-2)"><form method="POST" action="{{ route('admin.attendance.verify-dinas', $attendance) }}">@csrf<button class="btn btn-success btn-sm">Verifikasi</button></form><form method="POST" action="{{ route('admin.attendance.reject-dinas', $attendance) }}">@csrf<button class="btn btn-danger btn-sm">Tolak</button></form></div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lokasi & Foto</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <div class="account-field">
                <span class="account-field-label">Latitude Masuk</span>
                <span class="account-field-value">{{ $attendance->lat_masuk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Longitude Masuk</span>
                <span class="account-field-value">{{ $attendance->lng_masuk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jarak Masuk</span>
                <span class="account-field-value">{{ $attendance->distance_masuk ? number_format($attendance->distance_masuk, 0) . ' meter' : '-' }}</span>
            </div>
            @if($attendance->foto_masuk)
                <div class="account-field">
                    <span class="account-field-label">Foto Masuk</span>
                    <a href="{{ Storage::url($attendance->foto_masuk) }}" target="_blank" title="Lihat ukuran penuh">
                        <img src="{{ Storage::url($attendance->foto_masuk_thumb) }}" alt="Foto Masuk" loading="lazy" decoding="async" style="max-width: 200px; border-radius: var(--radius-md);">
                    </a>
                </div>
            @endif
            @if($attendance->foto_pulang)
                <div class="account-field">
                    <span class="account-field-label">Foto Pulang</span>
                    <a href="{{ Storage::url($attendance->foto_pulang) }}" target="_blank" title="Lihat ukuran penuh">
                        <img src="{{ Storage::url($attendance->foto_pulang_thumb) }}" alt="Foto Pulang" loading="lazy" decoding="async" style="max-width: 200px; border-radius: var(--radius-md);">
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<x-modal id="editModal" title="Koreksi Absensi" size="lg">
    <form method="POST" action="{{ route('admin.attendance.update', $attendance) }}" id="editForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="hadir" {{ $attendance->status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                <option value="terlambat" {{ $attendance->status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="izin" {{ $attendance->status === 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ $attendance->status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="dinas_luar" {{ $attendance->status === 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                <option value="alpha" {{ $attendance->status === 'alpha' ? 'selected' : '' }}>TAK</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);" id="edit_jam_group">
            <div class="form-group">
                <label for="jam_masuk" class="form-label">Jam Masuk</label>
                <input type="time" id="jam_masuk" name="jam_masuk" class="form-input" value="{{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '' }}">
            </div>
            <div class="form-group">
                <label for="jam_pulang" class="form-label">Jam Pulang</label>
                <input type="time" id="jam_pulang" name="jam_pulang" class="form-input" value="{{ $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i') : '' }}">
            </div>
        </div>

        <div id="edit_dinas_group" style="display: none;">
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="keperluan_dinas" class="form-label">Keperluan Dinas</label>
                <textarea id="keperluan_dinas" name="keperluan_dinas" class="form-textarea">{{ $attendance->keperluan_dinas }}</textarea>
            </div>
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="lokasi_dinas" class="form-label">Lokasi Dinas</label>
                <input type="text" id="lokasi_dinas" name="lokasi_dinas" class="form-input" value="{{ $attendance->lokasi_dinas }}">
            </div>
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="bukti_file" class="form-label">Ganti Lampiran (opsional)</label>
                <input type="file" id="bukti_file" name="bukti_file" class="form-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <span class="form-help">Kosongkan bila lampiran tidak diganti.</span>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea id="keterangan" name="keterangan" class="form-textarea">{{ $attendance->keterangan }}</textarea>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="alasan_koreksi" class="form-label">Alasan Koreksi *</label>
            <textarea id="alasan_koreksi" name="alasan_koreksi" class="form-textarea" required placeholder="Masukkan alasan koreksi..."></textarea>
        </div>

        <div class="alert alert-danger" id="editError" style="display: none; margin-bottom: var(--space-4);"></div>

        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('editModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan Koreksi</x-button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
(function() {
    const statusSel = document.getElementById('status');
    const jamMasuk = document.getElementById('jam_masuk');
    const jamPulang = document.getElementById('jam_pulang');

    function syncKoreksiFields() {
        const isHadir = statusSel.value === 'hadir' || statusSel.value === 'terlambat';
        const isDinas = statusSel.value === 'dinas_luar';
        jamMasuk.disabled = !isHadir;
        jamPulang.disabled = !isHadir;
        if (!isHadir) {
            jamMasuk.value = '';
            jamPulang.value = '';
        }
        document.getElementById('edit_jam_group').style.opacity = isHadir ? '' : '0.45';
        document.getElementById('edit_dinas_group').style.display = isDinas ? '' : 'none';
    }
    statusSel.addEventListener('change', syncKoreksiFields);
    syncKoreksiFields();

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const errBox = document.getElementById('editError');
        errBox.style.display = 'none';
        const btn = form.querySelector('button[type=submit]');
        btn.disabled = true;
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: new FormData(form)
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(result) {
            btn.disabled = false;
            if (result.ok && result.data.success) {
                window.location.reload();
            } else {
                const msgs = [];
                if (result.data.errors) {
                    for (const f in result.data.errors) { result.data.errors[f].forEach(function(m) { msgs.push(m); }); }
                }
                errBox.textContent = msgs.join(' ') || result.data.message || 'Koreksi gagal disimpan.';
                errBox.style.display = 'block';
            }
        })
        .catch(function() {
            btn.disabled = false;
            errBox.textContent = 'Terjadi kesalahan jaringan.';
            errBox.style.display = 'block';
        });
    });
})();
</script>
@endpush
