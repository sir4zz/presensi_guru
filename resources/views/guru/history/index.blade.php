@extends('layouts.guru')

@section('title', 'Riwayat - Guru')
@section('page-title', 'Riwayat')

@section('content')
<div class="filter-bar" style="padding: var(--space-3); margin-bottom: var(--space-4);">
    <select class="form-select" id="monthFilter" style="max-width: 200px;">
        @foreach(range(1, 12) as $month)
            <option value="{{ $month }}" {{ $month == request('month', date('m')) ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($month)->translatedFormat('F') }}</option>
        @endforeach
    </select>
    <select class="form-select" id="yearFilter" style="max-width: 120px;">
        @foreach(range(date('Y') - 2, date('Y') + 1) as $year)
            <option value="{{ $year }}" {{ $year == request('year', date('Y')) ? 'selected' : '' }}>{{ $year }}</option>
        @endforeach
    </select>
</div>

@if(isset($history) && count($history) > 0)
    <div class="history-list">
        @foreach($history as $item)
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
                    @if($item->keterangan)
                        <div style="font-size: var(--text-xs); color: var(--color-text-muted); margin-top: 2px;">{{ $item->keterangan }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <x-empty-state title="Belum ada riwayat" text="Riwayat kehadiran akan muncul di sini." />
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');

    function updateFilter() {
        const month = monthFilter.value;
        const year = yearFilter.value;
        window.location.href = `{{ route("guru.history.index") }}?month=${month}&year=${year}`;
    }

    monthFilter.addEventListener('change', updateFilter);
    yearFilter.addEventListener('change', updateFilter);
});
</script>
@endpush
