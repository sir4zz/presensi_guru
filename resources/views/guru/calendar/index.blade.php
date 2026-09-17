@extends('layouts.guru')

@section('title', 'Kalender - Guru')
@section('page-title', 'Kalender')

@section('content')
<div class="calendar-nav">
    <a href="{{ $prevUrl }}" class="btn btn-ghost btn-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div style="display:flex; align-items:center; gap:var(--space-3);">
        <div class="calendar-nav-title">{{ $monthName }}</div>
        <a href="{{ $todayUrl }}" class="btn btn-sm btn-secondary">Hari Ini</a>
    </div>
    <a href="{{ $nextUrl }}" class="btn btn-ghost btn-icon">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
</div>

<div class="calendar-grid" id="calendarGrid">
    <div class="calendar-header">Min</div>
    <div class="calendar-header">Sen</div>
    <div class="calendar-header">Sel</div>
    <div class="calendar-header">Rab</div>
    <div class="calendar-header">Kam</div>
    <div class="calendar-header">Jum</div>
    <div class="calendar-header">Sab</div>
</div>

<div class="card" style="margin-top: var(--space-4); display: none;" id="dayDetail">
    <div class="card-header">
        <h3 class="card-title" id="dayDetailTitle">Detail Hari</h3>
        <button class="btn btn-ghost btn-sm btn-icon" onclick="document.getElementById('dayDetail').style.display='none'">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div id="dayDetailContent">
        <p style="color: var(--color-text-muted); font-size: var(--text-sm);">Pilih tanggal pada kalender untuk melihat detail.</p>
    </div>
</div>

<div style="margin-top: var(--space-6);">
    <h3 style="font-size: var(--text-lg); font-weight: 600; margin-bottom: var(--space-4);">Rekap Bulan Ini</h3>
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

    @if(isset($monthStats['total_hari_kerja']) && $monthStats['total_hari_kerja'] > 0)
        <div class="rekap-percentage">
            <div class="rekap-percentage-value">{{ round((($monthStats['hadir'] ?? 0) + ($monthStats['terlambat'] ?? 0)) / $monthStats['total_hari_kerja'] * 100, 1) }}%</div>
            <div class="rekap-percentage-label">Persentase Kehadiran</div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    var attendanceData = @json($attendanceData ?? []);
    var holidayMap = @json($holidayMap ?? []);

    var calendarGrid = document.getElementById('calendarGrid');
    var dayDetail = document.getElementById('dayDetail');
    var dayDetailTitle = document.getElementById('dayDetailTitle');
    var dayDetailContent = document.getElementById('dayDetailContent');

    var month = {{ $month }};
    var year = {{ $year }};

    function renderCalendar() {
        var existingDays = calendarGrid.querySelectorAll('.calendar-day');
        existingDays.forEach(function(el) { el.remove(); });

        var firstDay = new Date(year, month - 1, 1).getDay();
        var daysInMonth = new Date(year, month, 0).getDate();
        var today = new Date();

        for (var i = 0; i < firstDay; i++) {
            var emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var dayEl = document.createElement('button');
            dayEl.type = 'button';
            dayEl.className = 'calendar-day';

            var dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            var dateObj = new Date(year, month - 1, day);
            var dayOfWeek = dateObj.getDay();
            var isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            var isHoliday = holidayMap[dateStr] !== undefined;

            if (isWeekend) dayEl.classList.add('weekend');
            if (isHoliday) dayEl.classList.add('has-holiday');

            if (today.getDate() === day && today.getMonth() + 1 === month && today.getFullYear() === year) {
                dayEl.classList.add('today');
            }

            var html = '<span>' + day + '</span>';

            if (isHoliday) {
                html += '<span class="calendar-holiday-dot"></span>';
            }

            var att = attendanceData.find(function(a) { return a.tanggal === dateStr; });
            if (att) {
                html += '<div class="calendar-dot ' + att.status + '"></div>';
            }

            dayEl.innerHTML = html;

            dayEl.addEventListener('click', (function(d, dd, hh) {
                return function() { showDayDetail(d, dd, hh); };
            })(dateStr, att, isHoliday ? holidayMap[dateStr] : null));

            calendarGrid.appendChild(dayEl);
        }
    }

    function showDayDetail(dateStr, att, holiday) {
        var date = new Date(dateStr + 'T00:00:00');
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dayDetailTitle.textContent = date.toLocaleDateString('id-ID', options);

        var html = '';

        if (holiday) {
            html += '<div class="holiday-banner">';
            html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;color:var(--color-danger);"><path d="M10 9l5 3-5 3v-6z"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
            html += '<span><strong>' + holiday.name + '</strong>';
            if (holiday.description) html += ' - ' + holiday.description;
            html += '</span></div>';
        }

        if (att) {
            var statusBadge = { hadir: 'success', terlambat: 'warning', izin: 'info', sakit: 'danger' };
            var statusLabel = { hadir: 'Hadir', terlambat: 'Terlambat', izin: 'Izin', sakit: 'Sakit', alpha: 'TAK', tidak_ada_keterangan: 'TAK' };
            html += '<div style="display: flex; flex-direction: column; gap: var(--space-3);">';
            html += '<div style="display: flex; align-items: center; gap: var(--space-2);">';
            html += '<span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Status</span>';
            html += '<span class="badge badge-' + (statusBadge[att.status] || 'danger') + '">' + (statusLabel[att.status] || att.status) + '</span></div>';
            html += '<div style="display: flex; align-items: center; gap: var(--space-2);">';
            html += '<span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Masuk</span>';
            html += '<span style="font-size: var(--text-sm); font-weight: 500;">' + (att.jam_masuk || '-') + '</span></div>';
            html += '<div style="display: flex; align-items: center; gap: var(--space-2);">';
            html += '<span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Pulang</span>';
            html += '<span style="font-size: var(--text-sm); font-weight: 500;">' + (att.jam_pulang || '-') + '</span></div>';
            if (att.foto_masuk) {
                html += '<div style="display: flex; align-items: center; gap: var(--space-2);">';
                html += '<span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Foto</span>';
                html += '<img src="/storage/' + att.foto_masuk + '" alt="Foto" style="width:48px;height:48px;border-radius:var(--radius-md);object-fit:cover;"></div>';
            }
            if (att.distance_masuk) {
                html += '<div style="display: flex; align-items: center; gap: var(--space-2);">';
                html += '<span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Jarak</span>';
                html += '<span style="font-size: var(--text-sm);">' + att.distance_masuk + ' meter</span></div>';
            }
            html += '</div>';
        } else {
            html += '<p style="color: var(--color-text-muted); font-size: var(--text-sm);">Tidak ada data absensi pada tanggal ini.</p>';
        }

        dayDetailContent.innerHTML = html;
        dayDetail.style.display = 'block';
    }

    renderCalendar();
})();
</script>
@endpush
