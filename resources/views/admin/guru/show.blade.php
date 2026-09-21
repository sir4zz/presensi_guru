@extends('layouts.admin')

@section('title', 'Detail Guru - Admin')
@section('page-title', 'Detail Guru')

@php $profile = $guru->guruProfile; @endphp

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; gap: var(--space-4);">
        <div class="avatar avatar-lg">
            @if($profile?->foto)
                <img src="{{ $profile->foto }}" alt="{{ $guru->name }}">
            @else
                {{ substr($guru->name, 0, 1) }}
            @endif
        </div>
        <div>
            <h2 style="font-size: var(--text-xl); font-weight: 600;">{{ $guru->name }}</h2>
            <p class="page-subtitle">NIP: {{ $guru->username }}{{ $profile?->jabatan ? ' - ' . $profile->jabatan : '' }}</p>
        </div>
    </div>
    <div class="flex gap-3">
        <x-button variant="secondary" href="{{ route('admin.guru.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </x-button>
        <x-button href="{{ route('admin.guru.edit', $guru) }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
        </x-button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Pribadi</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
            <div class="account-field">
                <span class="account-field-label">Nama Lengkap</span>
                <span class="account-field-value">{{ $guru->name }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIP</span>
                <span class="account-field-value">{{ $guru->username }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIPPPK</span>
                <span class="account-field-value">{{ $profile?->nipppk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NUPTK</span>
                <span class="account-field-value">{{ $profile?->nuptk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jenis Kelamin</span>
                <span class="account-field-value">{{ $profile?->jenis_kelamin === 'laki-laki' ? 'Laki-laki' : ($profile?->jenis_kelamin === 'perempuan' ? 'Perempuan' : '-') }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Agama</span>
                <span class="account-field-value">{{ $profile?->agama ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Tempat, Tanggal Lahir</span>
                <span class="account-field-value">{{ $profile?->tempat_lahir ?? '-' }}{{ $profile?->tanggal_lahir ? ', ' . $profile->tanggal_lahir->format('d M Y') : '' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIK</span>
                <span class="account-field-value">{{ $profile?->nik ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Status</span>
                @if($guru->status === 'aktif')
                    <x-badge variant="success">Aktif</x-badge>
                @else
                    <x-badge variant="danger">Nonaktif</x-badge>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kepegawaian</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
            <div class="account-field">
                <span class="account-field-label">Status Kepegawaian</span>
                <span class="account-field-value">{{ $profile?->status_kepegawaian ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Pangkat / Golongan</span>
                <span class="account-field-value">{{ $profile?->pangkat_golongan ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jabatan</span>
                <span class="account-field-value">{{ $profile?->jabatan ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT Golongan</span>
                <span class="account-field-value">{{ $profile?->tmt_golongan?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT CPNS</span>
                <span class="account-field-value">{{ $profile?->tmt_cpns?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT PNS / PPPK</span>
                <span class="account-field-value">{{ $profile?->tmt_pns_pppk?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT SK Sekolah</span>
                <span class="account-field-value">{{ $profile?->tmt_sk_sekolah?->format('d M Y') ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6); margin-top: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kontak</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
            <div class="account-field">
                <span class="account-field-label">Alamat</span>
                <span class="account-field-value">{{ $profile?->alamat ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">No. HP</span>
                <span class="account-field-value">{{ $profile?->no_hp ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Email</span>
                <span class="account-field-value">{{ $profile?->email ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NPWP</span>
                <span class="account-field-value">{{ $profile?->npwp ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">No. BPJS</span>
                <span class="account-field-value">{{ $profile?->no_bpjs ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Statistik Kehadiran</h3>
        </div>
        <div class="rekap-grid">
            <div class="rekap-item">
                <div class="rekap-item-value" style="color: var(--color-success);">{{ $stats['hadir'] ?? 0 }}</div>
                <div class="rekap-item-label">Hadir</div>
            </div>
            <div class="rekap-item">
                <div class="rekap-item-value" style="color: var(--color-warning);">{{ $stats['terlambat'] ?? 0 }}</div>
                <div class="rekap-item-label">Terlambat</div>
            </div>
            <div class="rekap-item">
                <div class="rekap-item-value" style="color: var(--color-info);">{{ $stats['izin'] ?? 0 }}</div>
                <div class="rekap-item-label">Izin</div>
            </div>
            <div class="rekap-item">
                <div class="rekap-item-value" style="color: var(--color-danger);">{{ $stats['sakit'] ?? 0 }}</div>
                <div class="rekap-item-label">Sakit</div>
            </div>
            <div class="rekap-item">
                <div class="rekap-item-value" style="color: var(--color-danger);">{{ $stats['alpha'] ?? 0 }}</div>
                <div class="rekap-item-label">Alpha</div>
            </div>
        </div>
    </div>
</div>

@if($profile?->pendidikan && count($profile->pendidikan) > 0)
<div class="card" style="margin-top: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Pendidikan</h3>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Jenjang</th>
                    <th>Jurusan</th>
                    <th>Perguruan Tinggi / Sekolah</th>
                    <th>Tahun Lulus</th>
                    <th>Nomor Ijazah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profile->pendidikan as $p)
                    <tr>
                        <td>{{ $p->jenjang ?? '-' }}</td>
                        <td>{{ $p->jurusan ?? '-' }}</td>
                        <td>{{ $p->perguruan_tinggi ?? '-' }}</td>
                        <td>{{ $p->tahun_lulus ?? '-' }}</td>
                        <td>{{ $p->nomor_ijazah ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($profile?->sertifikasi && count($profile->sertifikasi) > 0)
<div class="card" style="margin-top: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Sertifikasi</h3>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>No. Sertifikat</th>
                    <th>No. NRG</th>
                    <th>Bidang Studi</th>
                    <th>Penyelenggara</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profile->sertifikasi as $s)
                    <tr>
                        <td>{{ $s->status ?? '-' }}</td>
                        <td>{{ $s->no_sertifikat ?? '-' }}</td>
                        <td>{{ $s->no_nrg ?? '-' }}</td>
                        <td>{{ $s->bidang_studi ?? '-' }}</td>
                        <td>{{ $s->penyelenggara ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card" style="margin-top: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Riwayat Absensi Terbaru</h3>
    </div>

    @if(isset($recentAttendance) && count($recentAttendance) > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentAttendance as $att)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($att->tanggal)->format('d M Y') }}</td>
                            <td>
                                @if($att->status === 'hadir')
                                    <x-badge variant="success">Hadir</x-badge>
                                @elseif($att->status === 'terlambat')
                                    <x-badge variant="warning">Terlambat</x-badge>
                                @elseif($att->status === 'izin')
                                    <x-badge variant="info">Izin</x-badge>
                                @elseif($att->status === 'sakit')
                                    <x-badge variant="danger">Sakit</x-badge>
                                @else
                                    <x-badge variant="danger">Alpha</x-badge>
                                @endif
                            </td>
                            <td>{{ $att->jam_masuk ? \Carbon\Carbon::parse($att->jam_masuk)->format('H:i') : '-' }}</td>
                            <td>{{ $att->jam_pulang ? \Carbon\Carbon::parse($att->jam_pulang)->format('H:i') : '-' }}</td>
                            <td class="text-muted text-sm">{{ $att->keterangan ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-empty-state title="Belum ada riwayat absensi" />
    @endif
</div>
@endsection
