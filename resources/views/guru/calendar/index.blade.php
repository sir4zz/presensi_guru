@extends('layouts.guru')

@section('title', 'Kalender - Guru')
@section('page-title', 'Kalender')

@section('content')
<div class="calendar-nav">
    <button class="btn btn-ghost btn-icon" id="prevMonth">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
    <div class="calendar-nav-title" id="calendarTitle">{{ Carbon\Carbon::now()->translatedFormat('F Y') }}</div>
    <button class="btn btn-ghost btn-icon" id="nextMonth">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
    </button>
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
document.addEventListener('DOMContentLoaded', function() {
    let currentMonth = parseInt('{{ request("month", date("m")) }}');
    let currentYear = parseInt('{{ request("year", date("Y")) }}');
    const attendanceData = @json($attendanceData ?? []);

    const calendarGrid = document.getElementById('calendarGrid');
    const calendarTitle = document.getElementById('calendarTitle');
    const dayDetail = document.getElementById('dayDetail');
    const dayDetailTitle = document.getElementById('dayDetailTitle');
    const dayDetailContent = document.getElementById('dayDetailContent');

    function renderCalendar() {
        // Clear existing days
        const existingDays = calendarGrid.querySelectorAll('.calendar-day');
        existingDays.forEach(el => el.remove());

        const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        calendarTitle.textContent = monthNames[currentMonth - 1] + ' ' + currentYear;

        const firstDay = new Date(currentYear, currentMonth - 1, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
        const today = new Date();

        // Empty days
        for (let i = 0; i < firstDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        // Days
        for (let day = 1; day <= daysInMonth; day++) {
            const dayEl = document.createElement('button');
            dayEl.className = 'calendar-day';
            dayEl.innerHTML = `<span>${day}</span>`;

            const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            if (today.getDate() === day && today.getMonth() + 1 === currentMonth && today.getFullYear() === currentYear) {
                dayEl.classList.add('today');
            }

            // Check attendance data
            const att = attendanceData.find(a => a.tanggal === dateStr);
            if (att) {
                const dot = document.createElement('div');
                dot.className = `calendar-dot ${att.status}`;
                dayEl.appendChild(dot);
            }

            dayEl.addEventListener('click', function() {
                showDayDetail(dateStr, att);
            });

            calendarGrid.appendChild(dayEl);
        }
    }

    function showDayDetail(dateStr, att) {
        const date = new Date(dateStr);
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dayDetailTitle.textContent = date.toLocaleDateString('id-ID', options);

        if (att) {
            let statusBadge = '';
            if (att.status === 'hadir') statusBadge = '<span class="badge badge-success">Hadir</span>';
            else if (att.status === 'terlambat') statusBadge = '<span class="badge badge-warning">Terlambat</span>';
            else if (att.status === 'izin') statusBadge = '<span class="badge badge-info">Izin</span>';
            else if (att.status === 'sakit') statusBadge = '<span class="badge badge-danger">Sakit</span>';
            else statusBadge = '<span class="badge badge-danger">TAK</span>';

            dayDetailContent.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Status</span>
                        ${statusBadge}
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Masuk</span>
                        <span style="font-size: var(--text-sm); font-weight: 500;">${att.jam_masuk || '-'}</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: var(--space-2);">
                        <span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Pulang</span>
                        <span style="font-size: var(--text-sm); font-weight: 500;">${att.jam_pulang || '-'}</span>
                    </div>
                    ${att.keterangan ? `<div style="display: flex; align-items: center; gap: var(--space-2);"><span style="font-size: var(--text-sm); color: var(--color-text-muted); min-width: 80px;">Keterangan</span><span style="font-size: var(--text-sm);">${att.keterangan}</span></div>` : ''}
                </div>
            `;
        } else {
            dayDetailContent.innerHTML = '<p style="color: var(--color-text-muted); font-size: var(--text-sm);">Tidak ada data absensi pada tanggal ini.</p>';
        }

        dayDetail.style.display = 'block';
    }

    document.getElementById('prevMonth').addEventListener('click', function() {
        currentMonth--;
        if (currentMonth < 1) {
            currentMonth = 12;
            currentYear--;
        }
        window.location.href = '{{ route("guru.calendar.index") }}?month=' + currentMonth + '&year=' + currentYear;
    });

    document.getElementById('nextMonth').addEventListener('click', function() {
        currentMonth++;
        if (currentMonth > 12) {
            currentMonth = 1;
            currentYear++;
        }
        window.location.href = '{{ route("guru.calendar.index") }}?month=' + currentMonth + '&year=' + currentYear;
    });

    renderCalendar();
});
</script>
@endpush
