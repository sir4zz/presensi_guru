@extends('layouts.admin')

@section('title', 'Laporan - Admin')
@section('page-title', 'Laporan')

@php
    $statusLabels = ['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'TAK', 'dinas_luar' => 'Dinas Luar', 'tugas_luar' => 'Dinas Luar', 'tidak_ada_keterangan' => 'TAK'];
    $rawStatus = request('status', '');
@endphp

@section('content')
<style>
    .report-kop { text-align: center; margin-bottom: var(--space-4); }
    .report-kop h2 { font-size: 1.05rem; margin: 0; }
    .report-kop h3 { font-size: 0.95rem; font-weight: 600; margin: 2px 0; }
    .report-kop p { margin: 2px 0; color: var(--color-text-muted); font-size: 0.85rem; }
    .report-sign { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-top: var(--space-6); font-size: 0.9rem; }
    .report-sign .right { text-align: right; }
    @media print {
        .no-print, .filter-bar, .page-header .flex { display: none !important; }
        .page-content { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
        .table-wrapper { overflow: visible !important; }
        body { background: #fff !important; }
    }
</style>

<div class="page-header">
    <div>
        <p class="page-subtitle">Laporan kehadiran guru</p>
    </div>
    <div class="flex gap-3 no-print">
        <x-button variant="secondary" onclick="window.print()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak
        </x-button>
        <x-button variant="secondary" id="reportExportBtn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Rekap XLSX
        </x-button>
    </div>
</div>

<div class="filter-bar no-print">
    <select class="form-select" id="periodFilter" onchange="applyReportFilters()">
        <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Harian</option>
        <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Bulanan</option>
        <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Tahunan</option>
    </select>
    <input type="date" class="form-input" id="dateFilter" value="{{ request('date', now()->toDateString()) }}" onchange="applyReportFilters()" style="{{ $period === 'daily' ? '' : 'display:none;' }}">
    <select class="form-select" id="monthFilter" onchange="applyReportFilters()" style="{{ $period === 'monthly' ? '' : 'display:none;' }}">
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
        @endforeach
    </select>
    <select class="form-select" id="yearFilter" onchange="applyReportFilters()" style="{{ $period === 'daily' ? 'display:none;' : '' }}">
        @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>
    <select class="form-select" id="guruFilter" onchange="applyReportFilters()">
        <option value="">Semua Guru</option>
        @if(isset($allGurus))
            @foreach($allGurus as $guru)
                <option value="{{ $guru->id }}" {{ request('guru_id') == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
            @endforeach
        @endif
    </select>
    <select class="form-select" id="statusFilter" onchange="applyReportFilters()">
        <option value="">Semua Status</option>
        <option value="hadir" {{ $rawStatus == 'hadir' ? 'selected' : '' }}>Hadir</option>
        <option value="terlambat" {{ $rawStatus == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
        <option value="izin" {{ $rawStatus == 'izin' ? 'selected' : '' }}>Izin</option>
        <option value="sakit" {{ $rawStatus == 'sakit' ? 'selected' : '' }}>Sakit</option>
        <option value="dinas_luar" {{ $rawStatus == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
        <option value="tidak_ada_keterangan" {{ in_array($rawStatus, ['tidak_ada_keterangan', 'alpha']) ? 'selected' : '' }}>TAK</option>
    </select>
</div>

{{-- Kop laporan --}}
<div class="card" style="margin-bottom: var(--space-4);">
    <div class="report-kop">
        @foreach($kop as $i => $line)
            @if($i === 0)
                <p>{{ $line }}</p>
            @elseif($i === 1)
                <h2>{{ $line }}</h2>
            @else
                <p>{{ $line }}</p>
            @endif
        @endforeach
        <h3>{{ $reportTitle }}</h3>
        <p>Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB oleh {{ auth()->user()?->name ?? 'Admin' }}</p>
    </div>
</div>

@if($period === 'daily' && isset($daily))
    @if(!$daily['is_workday'])
        <div class="alert alert-info" style="margin-bottom: var(--space-4);">
            Tanggal ini bukan hari kerja
            @if($daily['holiday']) (libur: {{ $daily['holiday']->name }})@elseif($daily['is_sunday']) (hari Minggu)@endif.
            Data yang tampil hanya record yang sudah ada.
        </div>
    @endif

    <div class="stats-grid" style="margin-bottom: var(--space-6);">
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Hadir</div><div class="stat-card-value">{{ $daily['stats']['hadir'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Terlambat</div><div class="stat-card-value">{{ $daily['stats']['terlambat'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Izin</div><div class="stat-card-value">{{ $daily['stats']['izin'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Sakit</div><div class="stat-card-value">{{ $daily['stats']['sakit'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Dinas Luar</div><div class="stat-card-value">{{ $daily['stats']['dinas_luar'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">TAK</div><div class="stat-card-value">{{ $daily['stats']['tak'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Belum Absen</div><div class="stat-card-value">{{ $daily['missing_count'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Sudah Pulang</div><div class="stat-card-value">{{ $daily['stats']['pulang'] }}</div></div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">{{ $reportTitle }} — Detail per guru</h3></div>
        @if(count($daily['attendances']) > 0)
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr><th>No</th><th>Nama</th><th>NIP</th><th>Status</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Keterangan</th></tr>
                    </thead>
                    <tbody>
                        @foreach($daily['attendances'] as $i => $att)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-medium">{{ $att->guru->name ?? '-' }}</td>
                                <td class="text-muted">{{ $att->guru->guruProfile?->nip ?? $att->guru->username ?? '-' }}</td>
                                <td><x-badge variant="{{ $att->status === 'hadir' ? 'success' : ($att->status === 'terlambat' ? 'warning' : ($att->status === 'alpha' ? 'danger' : 'info')) }}">{{ $statusLabels[$att->status] ?? $att->status }}</x-badge></td>
                                <td>{{ $att->jam_masuk ? substr($att->jam_masuk, 0, 5) : '-' }}</td>
                                <td>{{ $att->jam_pulang ? substr($att->jam_pulang, 0, 5) : '-' }}</td>
                                <td class="text-muted text-sm">{{ $att->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state title="Belum ada data absensi" text="Tidak ada record absensi pada tanggal ini untuk filter yang dipilih." />
        @endif

        @if(count($daily['missing']) > 0)
            <div class="card-header" style="margin-top: var(--space-4);"><h3 class="card-title">Belum Absen ({{ count($daily['missing']) }} guru)</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>No</th><th>Nama</th><th>NIP</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($daily['missing'] as $i => $g)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td class="font-medium">{{ $g->name }}</td>
                                <td class="text-muted">{{ $g->guruProfile?->nip ?? $g->username }}</td>
                                <td><x-badge variant="neutral">Belum Absen</x-badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="report-sign">
            <div>Mengetahui,<br>Kepala Sekolah<br><br><br><br>(..............)</div>
            <div class="right">Dicetak oleh:<br>{{ auth()->user()?->name ?? 'Admin' }}<br><br><br><br>(..............)</div>
        </div>
    </div>
@else
    @php $t = $summary['totals'] ?? ['hadir'=>0,'terlambat'=>0,'izin'=>0,'sakit'=>0,'dinas_luar'=>0,'tak'=>0,'apel'=>0]; @endphp
    <div class="stats-grid" style="margin-bottom: var(--space-6);">
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Hari Kerja</div><div class="stat-card-value">{{ $summary['hari_kerja'] ?? 0 }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Hadir</div><div class="stat-card-value">{{ $t['hadir'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Terlambat</div><div class="stat-card-value">{{ $t['terlambat'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Izin</div><div class="stat-card-value">{{ $t['izin'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Sakit</div><div class="stat-card-value">{{ $t['sakit'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">Dinas Luar</div><div class="stat-card-value">{{ $t['dinas_luar'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">TAK</div><div class="stat-card-value">{{ $t['tak'] }}</div></div>
        <div class="stat-card" style="cursor: default;"><div class="stat-card-label">APEL</div><div class="stat-card-value">{{ $t['apel'] }}</div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title">{{ $reportTitle }}</h3>
                <p class="page-subtitle">Hari kerja: {{ $summary['hari_kerja'] ?? 0 }} hari
                    @if(($summary['libur_count'] ?? 0) > 0) (libur: {{ $summary['libur_count'] }} hari) @endif
                    • 7 jam keterlambatan = 1 hari • DL tidak masuk APEL/%</p>
            </div>
        </div>

        @if(isset($summary['rows']) && count($summary['rows']) > 0)
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th><th>Nama</th><th>NIP</th>
                            <th style="text-align:center;" title="Hadir">H</th>
                            <th style="text-align:center;" title="Terlambat">TL</th>
                            <th style="text-align:center;" title="Izin">I</th>
                            <th style="text-align:center;" title="Sakit">S</th>
                            <th style="text-align:center;" title="Dinas Luar">DL</th>
                            <th style="text-align:center;" title="Tanpa Keterangan">TAK</th>
                            <th style="text-align:center;" title="Apel = Hadir + Terlambat">APEL</th>
                            <th style="text-align:center;" title="Terlambat menit">TM</th>
                            <th style="text-align:center;" title="Pulang awal menit">PS</th>
                            <th style="text-align:center;" title="Konversi hari">Konv</th>
                            <th style="text-align:center;" title="TMTB = TAK">TMTB</th>
                            <th style="text-align:center;" title="Total = Konversi + TMTB">Total</th>
                            <th style="text-align:center;" title="% Kehadiran = APEL / Hari Kerja">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary['rows'] as $data)
                            <tr>
                                <td>{{ $data['no'] }}</td>
                                <td class="font-medium">{{ $data['name'] }}</td>
                                <td class="text-muted">{{ $data['nip'] }}</td>
                                <td class="text-center">{{ $data['hadir'] }}</td>
                                <td class="text-center">{{ $data['terlambat'] }}</td>
                                <td class="text-center">{{ $data['izin'] }}</td>
                                <td class="text-center">{{ $data['sakit'] }}</td>
                                <td class="text-center">{{ $data['dinas_luar'] }}</td>
                                <td class="text-center">{{ $data['tak'] }}</td>
                                <td class="text-center font-medium">{{ $data['apel'] }}</td>
                                <td class="text-center">{{ $data['tm'] }}</td>
                                <td class="text-center">{{ $data['ps'] }}</td>
                                <td class="text-center">{{ $data['konversi_hari'] }}</td>
                                <td class="text-center">{{ $data['tmtb'] }}</td>
                                <td class="text-center font-medium">{{ $data['total'] }}</td>
                                <td class="text-center">{{ $data['persentase'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="font-medium">TOTAL</td>
                            <td class="text-center font-medium">{{ $t['hadir'] }}</td>
                            <td class="text-center font-medium">{{ $t['terlambat'] }}</td>
                            <td class="text-center font-medium">{{ $t['izin'] }}</td>
                            <td class="text-center font-medium">{{ $t['sakit'] }}</td>
                            <td class="text-center font-medium">{{ $t['dinas_luar'] }}</td>
                            <td class="text-center font-medium">{{ $t['tak'] }}</td>
                            <td class="text-center font-medium">{{ $t['apel'] }}</td>
                            <td class="text-center font-medium">{{ $t['tm'] }}</td>
                            <td class="text-center font-medium">{{ $t['ps'] }}</td>
                            <td class="text-center">-</td>
                            <td class="text-center font-medium">{{ $t['tmtb'] }}</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <x-empty-state title="Belum ada data laporan" text="Pilih periode untuk melihat laporan kehadiran." />
        @endif

        @if($period === 'yearly' && isset($summary['per_month']))
            <div class="card-header" style="margin-top: var(--space-6);"><h3 class="card-title">Agregat per bulan — {{ $year }}</h3></div>
            <div class="table-wrapper">
                <table class="table">
                    <thead><tr><th>No</th><th>Bulan</th><th style="text-align:center;">H</th><th style="text-align:center;">TL</th><th style="text-align:center;">I</th><th style="text-align:center;">S</th><th style="text-align:center;">DL</th><th style="text-align:center;">TAK</th><th style="text-align:center;">APEL</th></tr></thead>
                    <tbody>
                        @foreach($summary['per_month'] as $i => $m)
                            <tr>
                                <td>{{ $i + 1 }}</td><td class="font-medium">{{ $m['bulan'] }}</td>
                                <td class="text-center">{{ $m['hadir'] }}</td><td class="text-center">{{ $m['terlambat'] }}</td>
                                <td class="text-center">{{ $m['izin'] }}</td><td class="text-center">{{ $m['sakit'] }}</td>
                                <td class="text-center">{{ $m['dinas_luar'] }}</td><td class="text-center">{{ $m['tak'] }}</td>
                                <td class="text-center font-medium">{{ $m['apel'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="report-sign">
            <div>Mengetahui,<br>Kepala Sekolah<br><br><br><br>(..............)</div>
            <div class="right">Dicetak oleh:<br>{{ auth()->user()?->name ?? 'Admin' }}<br><br><br><br>(..............)</div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function applyReportFilters() {
    const params = new URLSearchParams();
    const period = document.getElementById('periodFilter').value;
    params.set('period', period);
    if (period === 'daily') {
        params.set('date', document.getElementById('dateFilter').value);
    } else if (period === 'monthly') {
        params.set('month', document.getElementById('monthFilter').value);
        params.set('year', document.getElementById('yearFilter').value);
    } else {
        params.set('year', document.getElementById('yearFilter').value);
    }
    const guruId = document.getElementById('guruFilter').value;
    const status = document.getElementById('statusFilter').value;
    if (guruId) params.set('guru_id', guruId);
    if (status) params.set('status', status);
    window.location.href = '{{ route("admin.report.index") }}?' + params.toString();
}

document.getElementById('reportExportBtn').addEventListener('click', function() {
    const params = new URLSearchParams(window.location.search);
    if (!params.get('period')) params.set('period', document.getElementById('periodFilter').value);
    if (params.get('period') === 'daily' && !params.get('date')) params.set('date', document.getElementById('dateFilter').value);
    if (params.get('period') === 'monthly') {
        if (!params.get('month')) params.set('month', document.getElementById('monthFilter').value);
        if (!params.get('year')) params.set('year', document.getElementById('yearFilter').value);
    }
    if (params.get('period') === 'yearly' && !params.get('year')) params.set('year', document.getElementById('yearFilter').value);
    window.location.href = '{{ route("admin.report.export") }}?' + params.toString();
});
</script>
@endpush
