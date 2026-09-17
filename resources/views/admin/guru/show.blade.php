@extends('layouts.admin')

@section('title', 'Detail Guru - Admin')
@section('page-title', 'Detail Guru')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Informasi detail guru</p>
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
            <h3 class="card-title">Informasi Guru</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <div class="account-field">
                <span class="account-field-label">Nama</span>
                <span class="account-field-value">{{ $guru->name }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIP</span>
                <span class="account-field-value">{{ $guru->username }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">SK</span>
                <span class="account-field-value">{{ $guru->guruProfile?->sk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">SPMT</span>
                <span class="account-field-value">{{ $guru->guruProfile?->spmt ?? '-' }}</span>
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
                <div class="rekap-item-value" style="color: var(--color-danger);">{{ $stats['tak'] ?? 0 }}</div>
                <div class="rekap-item-label">TAK</div>
            </div>
        </div>
    </div>
</div>

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
                                    <x-badge variant="danger">TAK</x-badge>
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
