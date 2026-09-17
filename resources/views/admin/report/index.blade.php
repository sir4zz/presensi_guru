@extends('layouts.admin')

@section('title', 'Laporan - Admin')
@section('page-title', 'Laporan')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Laporan kehadiran guru</p>
    </div>
    <div class="flex gap-3">
        <x-button variant="secondary" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </x-button>
        <x-button variant="secondary" id="reportExportBtn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export CSV
        </x-button>
    </div>
</div>

<div class="filter-bar">
    <select class="form-select" id="monthFilter">
        @foreach(range(1, 12) as $month)
            <option value="{{ $month }}" {{ $month == date('m') ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>
        @endforeach
    </select>
    <select class="form-select" id="yearFilter">
        @foreach(range(date('Y') - 2, date('Y') + 1) as $year)
            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
        @endforeach
    </select>
    <select class="form-select" id="guruFilter">
        <option value="">Semua Guru</option>
        @if(isset($gurus))
            @foreach($gurus as $guru)
                <option value="{{ $guru->id }}" {{ request('guru_id') == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
            @endforeach
        @endif
    </select>
    <select class="form-select" id="statusFilter">
        <option value="">Semua Status</option>
        <option value="hadir">Hadir</option>
        <option value="terlambat">Terlambat</option>
        <option value="izin">Izin</option>
        <option value="sakit">Sakit</option>
        <option value="tidak_ada_keterangan">TAK</option>
    </select>
</div>

<div class="stats-grid" style="margin-bottom: var(--space-6);">
    <div class="stat-card" style="cursor: default;">
        <div class="stat-card-label">Total Hari Kerja</div>
        <div class="stat-card-value">{{ $report['total_hari_kerja'] ?? 0 }}</div>
    </div>
    <div class="stat-card" style="cursor: default;">
        <div class="stat-card-icon success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-card-label">Hadir</div>
        <div class="stat-card-value">{{ $report['hadir'] ?? 0 }}</div>
    </div>
    <div class="stat-card" style="cursor: default;">
        <div class="stat-card-icon warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-card-label">Terlambat</div>
        <div class="stat-card-value">{{ $report['terlambat'] ?? 0 }}</div>
    </div>
    <div class="stat-card" style="cursor: default;">
        <div class="stat-card-icon info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div class="stat-card-label">Izin</div>
        <div class="stat-card-value">{{ $report['izin'] ?? 0 }}</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rekap Kehadiran per Guru</h3>
    </div>

    @if(isset($report['guru_data']) && count($report['guru_data']) > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th style="text-align: center;">H</th>
                        <th style="text-align: center;">TL</th>
                        <th style="text-align: center;">I</th>
                        <th style="text-align: center;">S</th>
                        <th style="text-align: center;">TAK</th>
                        <th style="text-align: center;">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['guru_data'] as $index => $data)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="font-medium">{{ $data['name'] }}</td>
                            <td class="text-muted">{{ $data['nip'] }}</td>
                            <td class="text-center">{{ $data['hadir'] }}</td>
                            <td class="text-center">{{ $data['terlambat'] }}</td>
                            <td class="text-center">{{ $data['izin'] }}</td>
                            <td class="text-center">{{ $data['sakit'] }}</td>
                            <td class="text-center">{{ $data['tak'] }}</td>
                            <td class="text-center font-medium">{{ $data['persentase'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <x-empty-state title="Belum ada data laporan" text="Pilih periode untuk melihat laporan kehadiran." />
    @endif
</div>
@endsection

@push('scripts')
<script>
document.getElementById('reportExportBtn').addEventListener('click', function() {
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    window.location.href = '{{ route("admin.report.export") }}?month=' + month + '&year=' + year;
});
</script>
@endpush
