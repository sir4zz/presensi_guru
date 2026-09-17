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
});
</script>
<style>
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
@endpush
