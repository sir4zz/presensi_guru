@extends('layouts.admin')

@section('title', 'Data Absensi - Admin')
@section('page-title', 'Data Absensi')

@section('content')
<div class="admin-attendance-page">
<div class="page-header">
    <div>
        <p class="page-subtitle">Absensi guru — {{ \Carbon\Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>
</div>

<div class="alert alert-info" style="margin-bottom: var(--space-4);">
    Halaman ini menampilkan absensi per hari
    ({{ \Carbon\Carbon::parse($today)->locale('id')->translatedFormat('l, d F Y') }}).
    Untuk melihat rekap harian, bulanan, atau tahunan dan export Excel, gunakan menu
    <a href="{{ route('admin.report.index') }}" class="btn btn-sm btn-secondary" style="margin-left: var(--space-2);">Laporan</a>.
</div>

<div class="filter-bar">
    <input type="date" class="form-input" id="tanggalFilter" value="{{ $today }}" onchange="applyFilters()" style="max-width: 180px;">
    <div class="search-input">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" class="form-input" placeholder="Cari nama atau NIP..." id="searchInput" onkeyup="filterTable()">
    </div>
    <select class="form-select" id="guruFilter" onchange="applyFilters()">
        <option value="">Semua Guru</option>
        @if(isset($gurus))
            @foreach($gurus as $guru)
                <option value="{{ $guru->id }}" {{ (string) request('guru_id') === (string) $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
            @endforeach
        @endif
    </select>
    <select class="form-select" id="statusFilter" onchange="applyFilters()">
        <option value="">Semua Status</option>
        <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
        <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
        <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
        <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
        <option value="dinas_luar" {{ in_array(request('status'), ['dinas_luar', 'tugas_luar']) ? 'selected' : '' }}>Dinas Luar</option>
        <option value="tidak_ada_keterangan" {{ in_array(request('status'), ['tidak_ada_keterangan', 'alpha']) ? 'selected' : '' }}>TAK</option>
        <option value="pulang" {{ request('status') == 'pulang' ? 'selected' : '' }}>Sudah Pulang</option>
        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Absen</option>
    </select>
</div>

@if(isset($missingGurus) && count($missingGurus) > 0)
    <div class="card" style="margin-bottom: var(--space-4);">
        <div class="card-header">
            <h3 class="card-title">Belum Absen ({{ count($missingGurus) }} guru)</h3>
        </div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>NIP</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($missingGurus as $guru)
                        <tr>
                            <td class="font-medium">{{ $guru->name }}</td>
                            <td class="text-muted">{{ $guru->username }}</td>
                            <td><x-badge variant="neutral">Belum Absen</x-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@if(isset($attendances) && count($attendances) > 0)
    <div id="bulkBar" class="card" style="display: none; margin-bottom: var(--space-4); padding: var(--space-3); flex-direction: row; align-items: center; gap: var(--space-3);">
        <strong><span id="bulkCount">0</span> dipilih</strong>
        <form method="POST" action="{{ route('admin.attendance.bulk-delete') }}" id="bulkForm" onsubmit="return confirmBulkDelete()" style="display: inline;">
            @csrf
            <div id="bulkIds"></div>
            <button class="btn btn-danger btn-sm" type="submit">Hapus Terpilih</button>
        </form>
    </div>
    <div class="table-wrapper">
        <table class="table" id="attendanceTable">
            <thead>
                <tr>
                    <th style="width: 36px;"><input type="checkbox" id="checkAll" title="Pilih semua di halaman" aria-label="Pilih semua di halaman"></th>
                    <th>Guru</th>
                    <th>NIP</th>
                    <th>Status</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Keterangan</th>
                    <th>Lampiran</th>
                    <th style="width: 110px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $att)
                        <tr data-name="{{ strtolower($att->guru->name ?? '') }}" data-nip="{{ $att->guru->username ?? '' }}" data-status="{{ $att->status }}" data-guru-id="{{ $att->guru_id }}" data-pulang="{{ $att->jam_pulang ? '1' : '' }}">
                        <td><input type="checkbox" class="row-check" value="{{ $att->id }}" aria-label="Pilih absensi {{ $att->guru->name ?? '' }}"></td>
                        <td class="font-medium">{{ $att->guru->name ?? '-' }}</td>
                        <td class="text-muted">{{ $att->guru->username ?? '-' }}</td>
                        <td>
                            @if($att->status === 'hadir')
                                <x-badge variant="success">Hadir</x-badge>
                            @elseif($att->status === 'terlambat')
                                <x-badge variant="warning">Terlambat</x-badge>
                            @elseif($att->status === 'izin')
                                <x-badge variant="info">Izin</x-badge>
                            @elseif($att->status === 'sakit')
                                <x-badge variant="danger">Sakit</x-badge>
                            @elseif(in_array($att->status, ['tugas_luar', 'dinas_luar']))
                                <x-badge variant="info">{{ $att->status === 'dinas_luar' ? 'Dinas Luar' : 'Tugas Luar' }}</x-badge>
                            @else
                                <x-badge variant="danger">TAK</x-badge>
                            @endif
                        </td>
                        <td>{{ $att->jam_masuk ? \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') : '-' }}</td>
                        <td>{{ $att->jam_pulang ? \Carbon\Carbon::parse($att->jam_pulang)->format('H:i') : '-' }}</td>
                        <td class="text-muted text-sm">{{ $att->keterangan ?? '-' }}</td>
                        <td>
                            @if($att->bukti_file)
                                @if(\App\Services\FileUploadService::isPdfPath($att->bukti_file))
                                    <a href="{{ Storage::url($att->bukti_file) }}" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
                                @else
                                    <a href="{{ Storage::url($att->bukti_file) }}" target="_blank" title="Lihat lampiran">
                                        <img src="{{ Storage::url($att->bukti_thumb) }}" alt="Lampiran" loading="lazy" decoding="async" style="width:32px;height:32px;border-radius:var(--radius-md);object-fit:cover;">
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-ghost btn-sm btn-icon" title="Detail" onclick='openDetailModal(@json($att))'>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="btn btn-ghost btn-sm btn-icon" title="Koreksi" onclick='openEditModal(@json($att))'>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.attendance.delete', $att->id) }}" style="display: inline;" onsubmit="return confirm('Hapus data absensi {{ $att->guru->name ?? '' }} ({{ $att->tanggal }})? Foto/lampiran ikut dihapus dan tidak dapat dikembalikan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-sm btn-icon" style="color: var(--color-danger);" title="Hapus">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: var(--space-4);">
        {{ $attendances->links() }}
    </div>
@else
    <x-empty-state title="Belum ada absensi hari ini" text="Data akan muncul setelah guru melakukan absensi hari ini." />
@endif

{{-- Modal Detail --}}
<x-modal id="detailModal" title="Detail Absensi" size="lg">
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
                <span class="account-field-label">Status</span>
                <span id="detail_status">-</span>
            </div>
        </div>
        <div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Jam Masuk</span>
                <span class="account-field-value" id="detail_masuk">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Jam Pulang</span>
                <span class="account-field-value" id="detail_pulang">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Jarak Masuk</span>
                <span class="account-field-value" id="detail_jarak">-</span>
            </div>
            <div class="account-field" style="margin-bottom: var(--space-3);">
                <span class="account-field-label">Keterangan</span>
                <span class="account-field-value" id="detail_keterangan">-</span>
            </div>
            <div id="detail_dinas_wrap" style="display: none;">
                <div class="account-field" style="margin-bottom: var(--space-3);">
                    <span class="account-field-label">Lokasi Dinas</span>
                    <span class="account-field-value" id="detail_lokasi">-</span>
                </div>
                <div class="account-field" style="margin-bottom: var(--space-3);">
                    <span class="account-field-label">Keperluan Dinas</span>
                    <span class="account-field-value" id="detail_keperluan">-</span>
                </div>
                <div class="account-field" style="margin-bottom: var(--space-3);">
                    <span class="account-field-label">Verifikasi Dinas</span>
                    <span class="account-field-value" id="detail_verified">-</span>
                </div>
            </div>
        </div>
    </div>
    <div id="detail_foto_section" style="margin-top: var(--space-4); display: none;">
        <span class="account-field-label" style="display: block; margin-bottom: var(--space-2);">Foto Masuk</span>
        <a id="detail_foto_link" href="#" target="_blank" title="Lihat ukuran penuh">
            <img id="detail_foto" src="" alt="Foto absensi masuk" loading="lazy" decoding="async" style="max-width: 200px; border-radius: var(--radius-md);">
        </a>
    </div>
    <div id="detail_foto_pulang_section" style="margin-top: var(--space-4); display: none;">
        <span class="account-field-label" style="display: block; margin-bottom: var(--space-2);">Foto Pulang</span>
        <a id="detail_foto_pulang_link" href="#" target="_blank" title="Lihat ukuran penuh">
            <img id="detail_foto_pulang" src="" alt="Foto absensi pulang" loading="lazy" decoding="async" style="max-width: 200px; border-radius: var(--radius-md);">
        </a>
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

{{-- Modal Koreksi --}}
<x-modal id="editModal" title="Koreksi Absensi" size="lg">
    <form method="POST" id="editForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_status" class="form-label">Status</label>
            <select id="edit_status" name="status" class="form-select">
                <option value="hadir">Hadir</option>
                <option value="terlambat">Terlambat</option>
                <option value="izin">Izin</option>
                <option value="sakit">Sakit</option>
                <option value="dinas_luar">Dinas Luar</option>
                <option value="alpha">TAK</option>
            </select>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);" id="edit_jam_group">
            <div class="form-group">
                <label for="edit_jam_masuk" class="form-label">Jam Masuk</label>
                <input type="time" id="edit_jam_masuk" name="jam_masuk" class="form-input">
            </div>
            <div class="form-group">
                <label for="edit_jam_pulang" class="form-label">Jam Pulang</label>
                <input type="time" id="edit_jam_pulang" name="jam_pulang" class="form-input">
            </div>
        </div>
        <div id="edit_dinas_group" style="display: none;">
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="edit_keperluan" class="form-label">Keperluan Dinas</label>
                <textarea id="edit_keperluan" name="keperluan_dinas" class="form-textarea"></textarea>
            </div>
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="edit_lokasi_dinas" class="form-label">Lokasi Dinas</label>
                <input type="text" id="edit_lokasi_dinas" name="lokasi_dinas" class="form-input">
            </div>
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="edit_bukti" class="form-label">Ganti Lampiran (opsional)</label>
                <input type="file" id="edit_bukti" name="bukti_file" class="form-input" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <span class="form-help">Kosongkan bila lampiran tidak diganti.</span>
            </div>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="edit_keterangan" class="form-label">Keterangan</label>
            <textarea id="edit_keterangan" name="keterangan" class="form-textarea"></textarea>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="edit_alasan" class="form-label">Alasan Koreksi *</label>
            <textarea id="edit_alasan" name="alasan_koreksi" class="form-textarea" required placeholder="Masukkan alasan koreksi..."></textarea>
        </div>
        <div class="alert alert-danger" id="editError" style="display: none; margin-bottom: var(--space-4);"></div>
        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('editModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan Koreksi</x-button>
        </div>
    </form>
</x-modal>
</div>
@endsection

@push('scripts')
<script>
function openDetailModal(att) {
    document.getElementById('detail_guru').textContent = att.guru?.name || '-';
    document.getElementById('detail_nip').textContent = att.guru?.username || '-';
    document.getElementById('detail_tanggal').textContent = att.tanggal || '-';
    document.getElementById('detail_masuk').textContent = att.jam_masuk || '-';
    document.getElementById('detail_pulang').textContent = att.jam_pulang || '-';
    document.getElementById('detail_jarak').textContent = att.distance_masuk ? att.distance_masuk + ' meter' : '-';
    document.getElementById('detail_keterangan').textContent = att.keterangan || '-';

    const statusMap = { hadir: 'success', terlambat: 'warning', izin: 'info', sakit: 'danger', alpha: 'danger', tugas_luar: 'info', dinas_luar: 'info', tidak_ada_keterangan: 'danger' };
    const labelMap = { hadir: 'Hadir', terlambat: 'Terlambat', izin: 'Izin', sakit: 'Sakit', alpha: 'TAK', tugas_luar: 'Tugas Luar', dinas_luar: 'Dinas Luar', tidak_ada_keterangan: 'TAK' };
    document.getElementById('detail_status').innerHTML = `<span class="badge badge-${statusMap[att.status] || 'neutral'}">${labelMap[att.status] || att.status}</span>`;

    const fotoSection = document.getElementById('detail_foto_section');
    if (att.foto_masuk) {
        // Thumbnail kecil untuk daftar; full-size hanya saat link diklik.
        document.getElementById('detail_foto').src = '/storage/' + (att.foto_masuk_thumb || att.foto_masuk);
        document.getElementById('detail_foto_link').href = '/storage/' + att.foto_masuk;
        fotoSection.style.display = 'block';
    } else {
        fotoSection.style.display = 'none';
    }

    const fotoPulangSection = document.getElementById('detail_foto_pulang_section');
    if (att.foto_pulang) {
        document.getElementById('detail_foto_pulang').src = '/storage/' + (att.foto_pulang_thumb || att.foto_pulang);
        document.getElementById('detail_foto_pulang_link').href = '/storage/' + att.foto_pulang;
        fotoPulangSection.style.display = 'block';
    } else {
        fotoPulangSection.style.display = 'none';
    }

    const isDinas = att.status === 'dinas_luar';
    document.getElementById('detail_dinas_wrap').style.display = isDinas ? '' : 'none';
    if (isDinas) {
        document.getElementById('detail_lokasi').textContent = att.lokasi_dinas || '-';
        document.getElementById('detail_keperluan').textContent = att.keperluan_dinas || '-';
        document.getElementById('detail_verified').textContent = att.dinas_verified_at ? 'Terverifikasi' : 'Menunggu verifikasi';
    }

    const buktiSection = document.getElementById('detail_bukti_section');
    if (att.bukti_file) {
        document.getElementById('detail_bukti_link').href = '/storage/' + att.bukti_file;
        const isPdf = att.bukti_file.toLowerCase().endsWith('.pdf');
        document.getElementById('detail_bukti_img').style.display = isPdf ? 'none' : '';
        document.getElementById('detail_bukti_pdf').style.display = isPdf ? '' : 'none';
        if (!isPdf) document.getElementById('detail_bukti_img').src = '/storage/' + (att.bukti_thumb || att.bukti_file);
        buktiSection.style.display = 'block';
    } else {
        buktiSection.style.display = 'none';
    }

    document.getElementById('detailModal').classList.add('active');
}

function openEditModal(att) {
    document.getElementById('editForm').action = `/admin/absensi/${att.id}`;
    document.getElementById('edit_status').value = att.status;
    document.getElementById('edit_jam_masuk').value = att.jam_masuk || '';
    document.getElementById('edit_jam_pulang').value = att.jam_pulang || '';
    document.getElementById('edit_keterangan').value = att.keterangan || '';
    document.getElementById('edit_keperluan').value = att.keperluan_dinas || '';
    document.getElementById('edit_lokasi_dinas').value = att.lokasi_dinas || '';
    document.getElementById('edit_bukti').value = '';
    document.getElementById('edit_alasan').value = '';
    document.getElementById('editError').style.display = 'none';
    syncKoreksiFields();
    document.getElementById('editModal').classList.add('active');
}

function syncKoreksiFields() {
    const status = document.getElementById('edit_status').value;
    const isHadir = status === 'hadir' || status === 'terlambat';
    const isDinas = status === 'dinas_luar';
    const jamMasuk = document.getElementById('edit_jam_masuk');
    const jamPulang = document.getElementById('edit_jam_pulang');
    jamMasuk.disabled = !isHadir;
    jamPulang.disabled = !isHadir;
    if (!isHadir) {
        jamMasuk.value = '';
        jamPulang.value = '';
    }
    document.getElementById('edit_jam_group').style.opacity = isHadir ? '' : '0.45';
    document.getElementById('edit_dinas_group').style.display = isDinas ? '' : 'none';
}
document.getElementById('edit_status').addEventListener('change', syncKoreksiFields);

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

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#attendanceTable tbody tr');

    rows.forEach(row => {
        const name = row.dataset.name || '';
        const nip = (row.dataset.nip || '').toLowerCase();
        const matchSearch = name.includes(search) || nip.includes(search);
        row.style.display = matchSearch ? '' : 'none';
    });
}

function applyFilters() {
    const guruId = document.getElementById('guruFilter').value;
    const status = document.getElementById('statusFilter').value;
    const tanggal = document.getElementById('tanggalFilter').value;
    const params = new URLSearchParams();
    if (guruId) params.set('guru_id', guruId);
    if (status) params.set('status', status);
    if (tanggal) params.set('tanggal', tanggal);
    window.location.search = params.toString();
}

function syncBulkBar() {
    const checked = Array.from(document.querySelectorAll('.row-check:checked'));
    const bar = document.getElementById('bulkBar');
    document.getElementById('bulkCount').textContent = checked.length;
    bar.style.display = checked.length > 0 ? 'flex' : 'none';
    const holder = document.getElementById('bulkIds');
    holder.innerHTML = '';
    checked.forEach(function(box) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = box.value;
        holder.appendChild(input);
    });
    const checkAll = document.getElementById('checkAll');
    const visible = Array.from(document.querySelectorAll('.row-check')).filter(function(b) {
        return b.closest('tr').style.display !== 'none';
    });
    checkAll.checked = visible.length > 0 && visible.every(function(b) { return b.checked; });
}

function confirmBulkDelete() {
    const n = document.querySelectorAll('.row-check:checked').length;
    return confirm(`Hapus ${n} data absensi terpilih? Foto/lampiran ikut dihapus dan tidak dapat dikembalikan.`);
}

document.getElementById('checkAll').addEventListener('change', function() {
    const on = this.checked;
    document.querySelectorAll('.row-check').forEach(function(box) {
        if (box.closest('tr').style.display !== 'none') box.checked = on;
    });
    syncBulkBar();
});
document.querySelectorAll('.row-check').forEach(function(box) {
    box.addEventListener('change', syncBulkBar);
});
</script>
@endpush
