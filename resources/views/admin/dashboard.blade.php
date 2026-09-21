@extends('layouts.admin')

@section('title', 'Dashboard - Admin')
@section('page-title', 'Dashboard')

@section('content')
<div class="stats-grid">
    <a href="{{ route('admin.attendance.index', ['status' => 'all']) }}" class="stat-card">
        <div class="stat-card-icon primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-card-label">Total Guru</div>
        <div class="stat-card-value">{{ $stats['total'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'hadir']) }}" class="stat-card">
        <div class="stat-card-icon success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div class="stat-card-label">Hadir</div>
        <div class="stat-card-value" data-stat="stat-hadir">{{ $stats['hadir'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'terlambat']) }}" class="stat-card">
        <div class="stat-card-icon warning">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="stat-card-label">Terlambat</div>
        <div class="stat-card-value" data-stat="stat-terlambat">{{ $stats['terlambat'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'izin']) }}" class="stat-card">
        <div class="stat-card-icon info">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div class="stat-card-label">Izin</div>
        <div class="stat-card-value">{{ $stats['izin'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'sakit']) }}" class="stat-card">
        <div class="stat-card-icon danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="stat-card-label">Sakit</div>
        <div class="stat-card-value">{{ $stats['sakit'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'tidak_ada_keterangan']) }}" class="stat-card">
        <div class="stat-card-icon danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="stat-card-label">Tidak Ada Keterangan</div>
        <div class="stat-card-value">{{ $stats['tak'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'belum']) }}" class="stat-card">
        <div class="stat-card-icon primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="stat-card-label">Belum Absen</div>
        <div class="stat-card-value">{{ $stats['belum'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'pulang']) }}" class="stat-card">
        <div class="stat-card-icon success">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div class="stat-card-label">Sudah Pulang</div>
        <div class="stat-card-value">{{ $stats['pulang'] ?? 0 }}</div>
    </a>

    <a href="{{ route('admin.attendance.index', ['status' => 'dinas_luar']) }}" class="stat-card">
        <div class="stat-card-icon info"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V5l7-3 7 3v16M9 9h6M9 13h6M9 17h6"/></svg></div>
        <div class="stat-card-label">Dinas Luar</div><div class="stat-card-value">{{ $stats['dinas_luar'] ?? 0 }}</div>
    </a>
</div>

<div class="chart-row">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tren Absensi</h3>
            <select class="form-select" id="chartPeriod" style="max-width: 150px;">
                <option value="week">7 Hari</option>
                <option value="month" selected>Bulan Ini</option>
            </select>
        </div>
        <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted);">
            <div id="chartContainer" style="width: 100%; height: 100%;"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Distribusi Status</h3>
        </div>
        <div id="distributionChart" style="height: 250px; display: flex; align-items: center; justify-content: center;">
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Data Absensi Hari Ini</h3>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
    </div>

    @if(isset($todayAttendance) && count($todayAttendance) > 0)
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Guru</th>
                        <th>NIP</th>
                        <th>Status</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todayAttendance as $att)
                        <tr>
                            <td>{{ $att->guru->name ?? '-' }}</td>
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
        <x-empty-state title="Belum ada data absensi pada hari ini" text="Data akan muncul setelah guru melakukan absensi." />
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh dashboard stats every 30 seconds
    let previousStats = {
        hadir: {{ $stats['hadir'] ?? 0 }},
        terlambat: {{ $stats['terlambat'] ?? 0 }}
    };

    function refreshDashboard() {
        fetch('/admin/dashboard/data')
            .then(response => response.json())
            .then(data => {
                if (data.stats) {
                    const newHadir = data.stats.hadir || 0;
                    const newTerlambat = data.stats.terlambat || 0;

                    // Update stat values
                    updateStatValue('stat-hadir', newHadir);
                    updateStatValue('stat-terlambat', newTerlambat);

                    // Show notification if new check-in
                    if (newHadir > previousStats.hadir) {
                        showNotification('Ada guru baru absen hadir! (' + newHadir + ' guru)');
                    } else if (newTerlambat > previousStats.terlambat) {
                        showNotification('Ada guru baru absen terlambat! (' + newTerlambat + ' guru)');
                    }

                    previousStats = { hadir: newHadir, terlambat: newTerlambat };
                }
            })
            .catch(() => {});
    }

    function updateStatValue(id, value) {
        const el = document.querySelector('[data-stat="' + id + '"]');
        if (el) el.textContent = value;
    }

    function showNotification(message) {
        // Create toast notification
        const toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:20px;right:20px;background:var(--color-primary);color:white;padding:12px 20px;border-radius:8px;z-index:9999;animation:slideIn 0.3s ease;font-size:14px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Poll every 30 seconds
    setInterval(refreshDashboard, 30000);

    // ===== Charts (SVG, tanpa library tambahan) =====
    const chartPeriod = document.getElementById('chartPeriod');
    const chartContainer = document.getElementById('chartContainer');
    const distributionChart = document.getElementById('distributionChart');

    const SERIES = [
        { key: 'hadir', label: 'Hadir', color: 'var(--color-success)' },
        { key: 'terlambat', label: 'Terlambat', color: 'var(--color-warning)' },
        { key: 'izin', label: 'Izin', color: 'var(--color-info)' },
        { key: 'sakit', label: 'Sakit', color: 'var(--color-danger)' },
        { key: 'alpha', label: 'TAK', color: 'var(--color-danger)' }
    ];

    function emptyState(text) {
        return '<p style="color:var(--color-text-muted); font-size:var(--text-sm); text-align:center;">' + text + '</p>';
    }

    function renderTrend(trend) {
        const totals = trend.map(d => SERIES.reduce((s, x) => s + (d[x.key] || 0), 0));
        const max = Math.max(1, ...totals);
        const W = 620, H = 220, padL = 28, padB = 24, padT = 8;
        const chartW = W - padL - 8, chartH = H - padT - padB;
        const n = trend.length;
        const slot = chartW / Math.max(n, 1);
        const barW = Math.min(26, Math.max(6, slot * 0.55));

        let svg = '<svg viewBox="0 0 ' + W + ' ' + H + '" style="width:100%; height:100%;" role="img" aria-label="Tren absensi">';

        [0, 0.5, 1].forEach(f => {
            const y = padT + chartH - chartH * f;
            const val = Math.round(max * f);
            svg += '<line x1="' + padL + '" y1="' + y + '" x2="' + W + '" y2="' + y + '" style="stroke:var(--color-border-light);stroke-width:1;"/>';
            svg += '<text x="' + (padL - 5) + '" y="' + (y + 4) + '" text-anchor="end" style="font-size:10px;fill:var(--color-text-muted);">' + val + '</text>';
        });

        const step = Math.ceil(n / 10);
        trend.forEach((d, i) => {
            const total = totals[i];
            const x = padL + slot * i + (slot - barW) / 2;
            let y = padT + chartH;
            if (total > 0) {
                SERIES.forEach(s => {
                    const v = d[s.key] || 0;
                    if (v <= 0) return;
                    const h = Math.max(2, chartH * v / max);
                    y -= h;
                    svg += '<rect x="' + x.toFixed(1) + '" y="' + y.toFixed(1) + '" width="' + barW.toFixed(1) + '" height="' + h.toFixed(1) + '" rx="2" style="fill:' + s.color + ';">'
                        + '<title>' + d.label + ' — ' + s.label + ': ' + v + '</title></rect>';
                });
            }
            if (i % step === 0 || i === n - 1) {
                svg += '<text x="' + (x + barW / 2).toFixed(1) + '" y="' + (H - 8) + '" text-anchor="middle" style="font-size:10px;fill:var(--color-text-muted);">' + d.label + '</text>';
            }
        });

        svg += '</svg>';

        let legend = '<div style="display:flex; gap:var(--space-3); flex-wrap:wrap; font-size:var(--text-xs); color:var(--color-text-muted); margin-bottom:var(--space-2);">';
        SERIES.forEach(s => {
            legend += '<span style="display:inline-flex; align-items:center; gap:4px;"><span style="width:8px; height:8px; border-radius:50%; background:' + s.color + '; display:inline-block;"></span>' + s.label + '</span>';
        });
        legend += '</div>';

        chartContainer.innerHTML = legend + svg;
    }

    function renderDistribution(dist) {
        const total = SERIES.reduce((s, x) => s + (dist[x.key] || 0), 0);
        if (total <= 0) {
            distributionChart.innerHTML = emptyState('Belum ada data pada periode ini.');
            return;
        }

        const R = 64, C = 2 * Math.PI * R;
        let offset = 0;
        let svg = '<svg viewBox="0 0 180 180" style="width:170px; height:170px;" role="img" aria-label="Distribusi status">';
        svg += '<circle cx="90" cy="90" r="' + R + '" fill="none" style="stroke:var(--color-border-light);stroke-width:18;"/>';
        SERIES.forEach(s => {
            const v = dist[s.key] || 0;
            if (v <= 0) return;
            const len = C * v / total;
            svg += '<circle cx="90" cy="90" r="' + R + '" fill="none" style="stroke:' + s.color + ';stroke-width:18;'
                + 'stroke-dasharray:' + len.toFixed(1) + ' ' + (C - len).toFixed(1) + ';'
                + 'stroke-dashoffset:' + (-offset).toFixed(1) + ';transform:rotate(-90deg);transform-origin:90px 90px;">'
                + '<title>' + s.label + ': ' + v + '</title></circle>';
            offset += len;
        });
        svg += '<text x="90" y="86" text-anchor="middle" style="font-size:22px; font-weight:700; fill:var(--color-text);">' + total + '</text>';
        svg += '<text x="90" y="104" text-anchor="middle" style="font-size:11px; fill:var(--color-text-muted);">Absensi</text>';
        svg += '</svg>';

        let legend = '<div style="display:flex; flex-direction:column; gap:var(--space-2); font-size:var(--text-sm);">';
        SERIES.forEach(s => {
            const v = dist[s.key] || 0;
            const pct = total > 0 ? Math.round(v / total * 100) : 0;
            legend += '<div style="display:flex; align-items:center; gap:var(--space-2);">'
                + '<span style="width:10px; height:10px; border-radius:50%; background:' + s.color + '; display:inline-block;"></span>'
                + '<span style="min-width:70px;">' + s.label + '</span>'
                + '<strong>' + v + '</strong>'
                + '<span style="color:var(--color-text-muted);">(' + pct + '%)</span></div>';
        });
        legend += '</div>';

        distributionChart.innerHTML = '<div style="display:flex; align-items:center; gap:var(--space-5); flex-wrap:wrap; justify-content:center;">' + svg + legend + '</div>';
    }

    function loadCharts() {
        const period = chartPeriod ? chartPeriod.value : 'month';
        chartContainer.innerHTML = '<p style="color:var(--color-text-muted); font-size:var(--text-sm);">Memuat...</p>';
        fetch('/admin/dashboard/data?period=' + period, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                renderTrend(data.trend || []);
                renderDistribution(data.distribution || {});
            })
            .catch(() => {
                chartContainer.innerHTML = emptyState('Grafik tidak dapat dimuat.');
            });
    }

    if (chartPeriod) {
        chartPeriod.addEventListener('change', loadCharts);
    }
    loadCharts();
});
</script>
<style>
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
@endpush
