@extends('layouts.guru')

@section('title', 'Beranda - Guru')
@section('page-title', 'Beranda')

@section('content')
<div style="margin-bottom: var(--space-5);">
    <h2 style="font-size: var(--text-xl); font-weight: 600;">Selamat {{ now()->hour < 12 ? 'Pagi' : (now()->hour < 17 ? 'Siang' : 'Sore') }},</h2>
    <p style="font-size: var(--text-sm); color: var(--color-text-muted);">{{ Auth::user()->name ?? 'Guru' }}</p>
</div>

<div class="status-card {{ $todayAttendance?->status ?? 'belum' }}" style="margin-bottom: var(--space-6);">
    <div class="status-card-date">{{ now()->translatedFormat('l, d F Y') }}</div>
    @if(isset($todayAttendance) && $todayAttendance)
        @if($todayAttendance->jam_masuk && ($todayAttendance->status === 'hadir' || $todayAttendance->status === 'terlambat'))
            <div class="status-card-status">{{ ucfirst($todayAttendance->status) }}</div>
            <div class="status-card-time">
                Masuk: {{ \Carbon\Carbon::parse($todayAttendance->jam_masuk)->format('H:i') }}
                @if($todayAttendance->jam_pulang)
                    | Pulang: {{ \Carbon\Carbon::parse($todayAttendance->jam_pulang)->format('H:i') }}
                @else
                    | Pulang: belum dilakukan
                @endif
            </div>
            @if(!$todayAttendance->jam_pulang)
                @if($canCheckout ?? false)
                    <a href="{{ route('guru.attendance.create') }}" class="btn btn-primary attendance-btn">Absen Pulang</a>
                    <p style="margin-top: var(--space-2); font-size: var(--text-sm); color: var(--color-text-muted);">Jendela pulang: {{ $checkoutStart ?? '15:00' }}–{{ $checkoutEnd ?? '17:00' }} WIB</p>
                @elseif(($checkoutStatus ?? '') === 'too_early')
                    <button class="btn btn-primary attendance-btn" disabled aria-disabled="true" title="Absensi pulang belum dibuka" style="opacity:.55; cursor:not-allowed;">Absen Pulang</button>
                    <p style="margin-top: var(--space-2); font-size: var(--text-sm); color: var(--color-text-muted);">Absensi pulang mulai pukul {{ $checkoutStart ?? '15:00' }} WIB.</p>
                @else
                    <button class="btn btn-primary attendance-btn" disabled aria-disabled="true" title="Waktu absensi sudah berakhir" style="opacity:.55; cursor:not-allowed;">Absen Pulang</button>
                    <p style="margin-top: var(--space-2); font-size: var(--text-sm); color: var(--color-text-muted);">Waktu absensi pulang sudah berakhir (batas {{ $checkoutEnd ?? '17:00' }} WIB).</p>
                @endif
            @endif
        @elseif($todayAttendance->status === 'izin')
            <div class="status-card-status">Izin</div>
            <div class="status-card-time">{{ $todayAttendance->keterangan ?? 'Dalam izin' }}</div>
        @elseif($todayAttendance->status === 'sakit')
            <div class="status-card-status">Sakit</div>
            <div class="status-card-time">{{ $todayAttendance->keterangan ?? 'Sakit' }}</div>
        @else
            <div class="status-card-status">Tidak Ada Keterangan</div>
            <div class="status-card-time">Anda tidak hadir hari ini</div>
        @endif
    @else
        <div class="status-card-status">Belum Absen</div>
        @if($redDate['is_red'] ?? false)
            <div class="status-card-time">Sistem absensi ditutup. Hari ini {{ $redDate['reason'] }}.</div>
            <button class="btn btn-primary attendance-btn" disabled aria-disabled="true" title="Absensi ditutup" style="opacity:.55; cursor:not-allowed;">Absen Sekarang</button>
        @else
            <div class="status-card-time">Anda belum melakukan absensi hari ini</div>
            <a href="{{ route('guru.attendance.create') }}" class="btn btn-primary attendance-btn">Absen Sekarang</a>
        @endif
    @endif
</div>

<div style="margin-bottom: var(--space-6);">
    <h3 style="font-size: var(--text-lg); font-weight: 600; margin-bottom: var(--space-4);">Ringkasan Bulan Ini</h3>
    <div class="rekap-grid">
        <div class="rekap-item">
            <div class="rekap-item-value" style="color: var(--color-success);">{{ $monthStats['hadir'] ?? 0 }}</div>
            <div class="rekap-item-label">Hadir</div>
        </div>
        <div class="rekap-item">
            <div class="rekap-item-value" style="color: var(--color-warning);">{{ $monthStats['terlambat'] ?? 0 }}</div>
            <div class="rekap-item-label">Terlambat</div>
        </div>
        <div class="rekap-item">
            <div class="rekap-item-value" style="color: var(--color-info);">{{ $monthStats['izin'] ?? 0 }}</div>
            <div class="rekap-item-label">Izin</div>
        </div>
        <div class="rekap-item">
            <div class="rekap-item-value" style="color: var(--color-danger);">{{ $monthStats['sakit'] ?? 0 }}</div>
            <div class="rekap-item-label">Sakit</div>
        </div>
    </div>
</div>

<div>
    <h3 style="font-size: var(--text-lg); font-weight: 600; margin-bottom: var(--space-4);">Riwayat Terbaru</h3>
    @if(isset($recentHistory) && count($recentHistory) > 0)
        <div class="history-list">
            @foreach($recentHistory as $item)
                <div class="history-item">
                    <div class="history-item-date">
                        <div class="history-item-day">{{ \Carbon\Carbon::parse($item->tanggal)->format('d') }}</div>
                        <div class="history-item-month">{{ \Carbon\Carbon::parse($item->tanggal)->format('M') }}</div>
                    </div>
                    <div class="history-item-info">
                        <div class="history-item-status">
                            @if($item->status === 'hadir')
                                <span style="color: var(--color-success);">Hadir</span>
                            @elseif($item->status === 'terlambat')
                                <span style="color: var(--color-warning);">Terlambat</span>
                            @elseif($item->status === 'izin')
                                <span style="color: var(--color-info);">Izin</span>
                            @elseif($item->status === 'sakit')
                                <span style="color: var(--color-danger);">Sakit</span>
                            @else
                                <span style="color: var(--color-danger);">TAK</span>
                            @endif
                        </div>
                        <div class="history-item-time">
                            Masuk: {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }}
                            @if($item->jam_pulang)
                                | Pulang: {{ \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state title="Belum ada riwayat" text="Riwayat kehadiran akan muncul di sini." />
    @endif
</div>
@endsection
