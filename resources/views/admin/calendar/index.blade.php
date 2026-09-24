@extends('layouts.admin')

@section('title', 'Kalender Absensi - Admin')
@section('page-title', 'Kalender Absensi')

@section('content')
<div class="filter-bar">
    <select class="form-select" id="monthFilter" style="max-width: 200px;">
        @foreach(range(1, 12) as $m)
            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
        @endforeach
    </select>
    <select class="form-select" id="yearFilter" style="max-width: 120px;">
        @foreach(range(date('Y') - 2, date('Y') + 1) as $y)
            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>
</div>

<div class="calendar-admin-layout">
    <div class="calendar-main">
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

        <div class="calendar-grid admin-calendar" id="calendarGrid">
            <div class="calendar-header sunday">Min</div>
            <div class="calendar-header">Sen</div>
            <div class="calendar-header">Sel</div>
            <div class="calendar-header">Rab</div>
            <div class="calendar-header">Kam</div>
            <div class="calendar-header">Jum</div>
            <div class="calendar-header">Sab</div>
        </div>

        <div class="calendar-legend" style="margin-top: var(--space-4);">
            <div class="calendar-legend-item"><span class="calendar-dot-legend" style="background:var(--color-danger);"></span> Akhir Pekan/Libur</div>
            <div class="calendar-legend-item"><span class="calendar-dot-legend" style="background:var(--color-success);"></span> Hadir</div>
            <div class="calendar-legend-item"><span class="calendar-dot-legend" style="background:var(--color-warning);"></span> Terlambat</div>
            <div class="calendar-legend-item"><span class="calendar-dot-legend" style="background:var(--color-info);"></span> Izin</div>
            <div class="calendar-legend-item"><span class="calendar-dot-legend" style="background:var(--color-danger);"></span> Sakit/TAK</div>
        </div>

        <div class="card" style="margin-top: var(--space-5); display:none;" id="dayDetailCard">
            <div class="card-header">
                <h3 class="card-title" id="dayDetailTitle">Detail Hari</h3>
                <button class="btn btn-ghost btn-sm btn-icon" onclick="document.getElementById('dayDetailCard').style.display='none'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div id="dayDetailContent"></div>
        </div>
    </div>

    <div class="calendar-sidebar">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rekap Bulan Ini</h3>
            </div>
            <div class="rekap-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="rekap-item">
                    <div class="rekap-item-value" style="color:var(--color-success);">{{ $monthStats['hadir'] }}</div>
                    <div class="rekap-item-label">Hadir</div>
                </div>
                <div class="rekap-item">
                    <div class="rekap-item-value" style="color:var(--color-warning);">{{ $monthStats['terlambat'] }}</div>
                    <div class="rekap-item-label">Terlambat</div>
                </div>
                <div class="rekap-item">
                    <div class="rekap-item-value" style="color:var(--color-info);">{{ $monthStats['izin'] }}</div>
                    <div class="rekap-item-label">Izin</div>
                </div>
                <div class="rekap-item">
                    <div class="rekap-item-value" style="color:var(--color-danger);">{{ $monthStats['sakit'] + $monthStats['alpha'] }}</div>
                    <div class="rekap-item-label">Sakit/TAK</div>
                </div>
            </div>
            <div style="text-align:center; margin-top:var(--space-3); font-size:var(--text-sm); color:var(--color-text-muted);">
                {{ $monthStats['total_guru'] }} guru aktif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hari Libur</h3>
                <div style="display:flex; gap:var(--space-2);">
                    <button class="btn btn-sm btn-secondary" id="syncHolidayBtn" title="Ambil libur nasional otomatis dari API">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                        Sinkron
                    </button>
                    <button class="btn btn-sm btn-primary" id="addHolidayBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah
                    </button>
                </div>
            </div>
            <div id="holidayList">
                @forelse($holidayMap as $date => $holiday)
                    <div class="holiday-item" data-id="{{ $holiday['id'] }}">
                        <div>
                            <div style="font-size:var(--text-sm); font-weight:500;">{{ $holiday['name'] }}</div>
                            <div style="font-size:var(--text-xs); color:var(--color-text-muted);">
                                {{ \Carbon\Carbon::parse($holiday['date'])->format('d M Y') }}
                                <span class="badge badge-sm" style="margin-left:var(--space-1);">{{ $holiday['type'] }}</span>
                            </div>
                        </div>
                        <div style="display:flex; gap:var(--space-1);">
                            <button class="btn btn-ghost btn-sm btn-icon" title="Edit" onclick='editHoliday(@json($holiday))'>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="btn btn-ghost btn-sm btn-icon" title="Hapus" onclick="deleteHoliday({{ $holiday['id'] }}, '{{ addslashes($holiday['name']) }}')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p style="font-size:var(--text-sm); color:var(--color-text-muted); text-align:center; padding:var(--space-4);">Belum ada hari libur.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<x-modal id="holidayModal" title="Tambah Hari Libur" size="lg">
    <form id="holidayForm" action="{{ route('admin.holiday.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="holiday_method" value="POST">
        <div class="form-group" id="holidayDateSingle" style="margin-bottom:var(--space-4); display:none;">
            <label class="form-label">Tanggal *</label>
            <input type="date" id="holiday_date" name="date" class="form-input">
        </div>
        <div class="form-group" id="holidayDatesMulti" style="margin-bottom:var(--space-4);">
            <label class="form-label">Daftar Libur *</label>
            <div style="display:grid;grid-template-columns:150px 1fr 120px 32px;gap:var(--space-2);margin-bottom:var(--space-2);font-size:var(--text-xs);color:var(--color-text-muted);">
                <span>Tanggal</span><span>Nama Libur</span><span>Jenis</span><span></span>
            </div>
            <div id="holidayDatesList" style="display:flex;flex-direction:column;gap:var(--space-2);"></div>
            <button type="button" class="btn btn-secondary btn-sm" id="addHolidayDateBtn" style="margin-top:var(--space-2); align-self:flex-start;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah tanggal
            </button>
            <span class="form-help">Tiap baris punya nama &amp; jenis sendiri. Keterangan bisa diisi per baris (opsional). Tanggal yang sudah ada dilewati otomatis.</span>
        </div>
        <div id="holidaySharedFields">
        <div class="form-group" style="margin-bottom:var(--space-4);">
            <label class="form-label">Nama Hari Libur *</label>
            <input type="text" id="holiday_name" name="name" class="form-input" required placeholder="Contoh: Hari Raya Idul Fitri">
        </div>
        <div class="form-group" style="margin-bottom:var(--space-4);">
            <label class="form-label">Keterangan</label>
            <textarea id="holiday_description" name="description" class="form-textarea" rows="2" placeholder="Keterangan tambahan (opsional)"></textarea>
        </div>
        <div class="form-group" style="margin-bottom:var(--space-6);">
            <label class="form-label">Jenis *</label>
            <select id="holiday_type" name="type" class="form-select" required>
                <option value="nasional">Nasional</option>
                <option value="daerah">Daerah</option>
                <option value="sekolah">Sekolah</option>
            </select>
        </div>
        </div>
        <div class="modal-footer" style="padding:0; border:none;">
            <button type="button" class="btn btn-secondary btn-md" onclick="document.getElementById('holidayModal').classList.remove('active')">Batal</button>
            <button type="submit" class="btn btn-primary btn-md">Simpan</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
(function() {
    var calendarData = @json($calendarData);
    var holidayMap = @json($holidayMap);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var calendarGrid = document.getElementById('calendarGrid');
    var dayDetailCard = document.getElementById('dayDetailCard');
    var dayDetailTitle = document.getElementById('dayDetailTitle');
    var dayDetailContent = document.getElementById('dayDetailContent');
    var holidayList = document.getElementById('holidayList');
    var holidayModal = document.getElementById('holidayModal');
    var holidayForm = document.getElementById('holidayForm');
    var holidayMethod = document.getElementById('holiday_method');

    var month = {{ $month }};
    var year = {{ $year }};
    var calendarBaseUrl = '{{ route("admin.calendar.index") }}';

    document.getElementById('monthFilter').addEventListener('change', applyMonthYearFilter);
    document.getElementById('yearFilter').addEventListener('change', applyMonthYearFilter);

    function applyMonthYearFilter() {
        var m = document.getElementById('monthFilter').value;
        var y = document.getElementById('yearFilter').value;
        window.location.href = calendarBaseUrl + '?month=' + m + '&year=' + y;
    }

    function renderCalendar() {
        var existingDays = calendarGrid.querySelectorAll('.calendar-day');
        existingDays.forEach(function(el) { el.remove(); });

        var firstDay = new Date(year, month - 1, 1).getDay();
        var daysInMonth = new Date(year, month, 0).getDate();
        var today = new Date();

        // Kalender dinding Indonesia: minggu dimulai hari Minggu (Min).
        // getDay(): 0=Minggu..6=Sabtu, jadi offset = firstDay.
        var offset = firstDay;

        for (var i = 0; i < offset; i++) {
            var emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            var dateObj = new Date(year, month - 1, day);
            var dayOfWeek = dateObj.getDay();
            var isSunday = dayOfWeek === 0;
            var isSaturday = dayOfWeek === 6;
            var isHoliday = holidayMap[dateStr] !== undefined;
            var isToday = today.getDate() === day && today.getMonth() + 1 === month && today.getFullYear() === year;

            var dayEl = document.createElement('button');
            dayEl.type = 'button';
            dayEl.className = 'calendar-day';
            if (isSunday || isSaturday) dayEl.classList.add('sunday');
            if (isHoliday) dayEl.classList.add('has-holiday');
            if (isToday) dayEl.classList.add('today');

            var html = '<span class="calendar-day-num">' + day + '</span>';

            if (isHoliday) {
                html += '<span class="calendar-holiday-dot"></span>';
            }

            var att = calendarData[dateStr];
            if (att) {
                html += '<div class="calendar-summary">';
                if (att.hadir > 0) html += '<span class="cs cs-hadir">' + att.hadir + '</span>';
                if (att.terlambat > 0) html += '<span class="cs cs-terlambat">' + att.terlambat + '</span>';
                if (att.izin > 0) html += '<span class="cs cs-izin">' + att.izin + '</span>';
                if (att.sakit > 0) html += '<span class="cs cs-sakit">' + att.sakit + '</span>';
                if (att.alpha > 0) html += '<span class="cs cs-alpha">' + att.alpha + '</span>';
                if (att.tugas_luar > 0) html += '<span class="cs cs-tugas-luar">' + att.tugas_luar + '</span>';
                html += '</div>';
            }

            dayEl.innerHTML = html;

            dayEl.addEventListener('click', (function(d, dd) {
                return function() { showDayDetail(d, dd); };
            })(dateStr, att));

            calendarGrid.appendChild(dayEl);
        }
    }

    function showDayDetail(dateStr, att) {
        var date = new Date(dateStr + 'T00:00:00');
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dayDetailTitle.textContent = date.toLocaleDateString('id-ID', options);

        var html = '';
        if (holidayMap[dateStr]) {
            var h = holidayMap[dateStr];
            html += '<div class="holiday-banner">';
            html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M10 9l5 3-5 3v-6z"/><rect x="3" y="3" width="18" height="18" rx="2"/></svg>';
            html += '<span><strong>' + h.name + '</strong> (Hari Libur ' + h.type + ')</span>';
            html += '</div>';
        }

        fetch('/admin/kalender/' + dateStr)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.attendances.length === 0 && !data.holiday && !data.is_weekend) {
                    html += '<p style="color:var(--color-text-muted); font-size:var(--text-sm); padding:var(--space-4);">Tidak ada data absensi pada tanggal ini.</p>';
                } else if (data.attendances.length > 0) {
                    html += '<div class="table-wrapper"><table class="table"><thead><tr>';
                    html += '<th>Guru</th><th>NIP</th><th>Status</th><th>Masuk</th><th>Pulang</th><th>Jarak</th><th>Foto</th>';
                    html += '</tr></thead><tbody>';
                    data.attendances.forEach(function(a) {
                        var statusBadge = { hadir: 'success', terlambat: 'warning', izin: 'info', sakit: 'danger', alpha: 'danger', tugas_luar: 'info', tidak_ada_keterangan: 'danger' };
                        var statusLabel = { hadir: 'Hadir', terlambat: 'Terlambat', izin: 'Izin', sakit: 'Sakit', alpha: 'TAK', tugas_luar: 'Tugas Luar', tidak_ada_keterangan: 'TAK' };
                        html += '<tr>';
                        html += '<td class="font-medium">' + (a.guru_name || '-') + '</td>';
                        html += '<td class="text-muted">' + (a.nip || '-') + '</td>';
                        html += '<td><span class="badge badge-' + (statusBadge[a.status] || 'neutral') + '">' + (statusLabel[a.status] || a.status) + '</span></td>';
                        html += '<td>' + (a.jam_masuk || '-') + '</td>';
                        html += '<td>' + (a.jam_pulang || '-') + '</td>';
                        html += '<td>' + (a.distance_masuk ? a.distance_masuk + 'm' : '-') + '</td>';
                        html += '<td>' + (a.foto_masuk || a.foto_pulang ? '<span style="display:inline-flex;gap:4px;">' + (a.foto_masuk ? '<a href="/storage/' + a.foto_masuk + '" target="_blank" title="Foto masuk"><img src="/storage/' + (a.foto_masuk_thumb || a.foto_masuk) + '" alt="Foto masuk" loading="lazy" decoding="async" style="width:32px;height:32px;border-radius:var(--radius-md);object-fit:cover;"></a>' : '') + (a.foto_pulang ? '<a href="/storage/' + a.foto_pulang + '" target="_blank" title="Foto pulang"><img src="/storage/' + (a.foto_pulang_thumb || a.foto_pulang) + '" alt="Foto pulang" loading="lazy" decoding="async" style="width:32px;height:32px;border-radius:var(--radius-md);object-fit:cover;"></a>' : '') + '</span>' : '-') + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                }

                dayDetailContent.innerHTML = html;
                dayDetailCard.style.display = 'block';
                dayDetailCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
    }

    document.getElementById('addHolidayBtn').addEventListener('click', function() {
        holidayForm.reset();
        holidayForm.action = '{{ route("admin.holiday.store") }}';
        holidayMethod.value = 'POST';
        document.getElementById('holidayDateSingle').style.display = 'none';
        document.getElementById('holiday_date').removeAttribute('required');
        document.getElementById('holidayDatesMulti').style.display = '';
        document.getElementById('holidaySharedFields').style.display = 'none';
        document.getElementById('holidaySharedFields').style.display = 'none';
        document.getElementById('holiday_name').removeAttribute('required');
        document.getElementById('holiday_type').removeAttribute('required');
        document.getElementById('holidayDatesList').innerHTML = '';
        holidayItemIdx = 0;
        addHolidayDateRow('', '', 'nasional', '');
        holidayModal.querySelector('.card-title, .modal-title, h3').textContent = 'Tambah Hari Libur';
        holidayModal.classList.add('active');
    });

    var holidayItemIdx = 0;

    function addHolidayDateRow(date, name, type, description) {
        var list = document.getElementById('holidayDatesList');
        var idx = holidayItemIdx++;
        var wrap = document.createElement('div');
        wrap.style.display = 'flex';
        wrap.style.flexDirection = 'column';
        wrap.style.gap = 'var(--space-1)';
        wrap.style.padding = 'var(--space-2)';
        wrap.style.border = '1px solid var(--color-border-light)';
        wrap.style.borderRadius = 'var(--radius-md)';

        var row = document.createElement('div');
        row.style.display = 'grid';
        row.style.gridTemplateColumns = '150px 1fr 120px 32px';
        row.style.gap = 'var(--space-2)';
        row.style.alignItems = 'center';

        var dateInput = document.createElement('input');
        dateInput.type = 'date';
        dateInput.className = 'form-input';
        dateInput.name = 'items[' + idx + '][date]';
        dateInput.min = '2020-01-01';
        dateInput.value = date || '';

        var nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.className = 'form-input';
        nameInput.name = 'items[' + idx + '][name]';
        nameInput.placeholder = 'Nama libur';
        nameInput.value = name || '';

        var typeSelect = document.createElement('select');
        typeSelect.className = 'form-select';
        typeSelect.name = 'items[' + idx + '][type]';
        ['nasional', 'daerah', 'sekolah'].forEach(function(t) {
            var opt = document.createElement('option');
            opt.value = t;
            opt.textContent = t.charAt(0).toUpperCase() + t.slice(1);
            if (t === (type || 'nasional')) opt.selected = true;
            typeSelect.appendChild(opt);
        });

        var del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-ghost btn-sm btn-icon';
        del.title = 'Hapus baris ini';
        del.setAttribute('aria-label', 'Hapus baris ini');
        del.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
        del.addEventListener('click', function() { wrap.remove(); });

        var descInput = document.createElement('input');
        descInput.type = 'text';
        descInput.className = 'form-input';
        descInput.name = 'items[' + idx + '][description]';
        descInput.placeholder = 'Keterangan (opsional)';
        descInput.value = description || '';

        row.appendChild(dateInput);
        row.appendChild(nameInput);
        row.appendChild(typeSelect);
        row.appendChild(del);
        wrap.appendChild(row);
        wrap.appendChild(descInput);
        list.appendChild(wrap);
    }

    document.getElementById('addHolidayDateBtn').addEventListener('click', function() {
        addHolidayDateRow('');
    });

    var syncBtn = document.getElementById('syncHolidayBtn');
    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            if (!confirm('Sinkronkan libur nasional tahun ' + year + ' dari API? Data manual (daerah/sekolah) tidak akan ditimpa.')) return;
            syncBtn.disabled = true;
            var originalText = syncBtn.innerHTML;
            syncBtn.innerHTML = 'Menyinkron...';
            fetch('{{ route("admin.holiday.sync") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ year: year })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    alert(data.message || 'Sinkronisasi berhasil.');
                    window.location.reload();
                } else {
                    alert(data.message || 'Sinkronisasi gagal.');
                    syncBtn.disabled = false;
                    syncBtn.innerHTML = originalText;
                }
            })
            .catch(function(err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
                syncBtn.disabled = false;
                syncBtn.innerHTML = originalText;
            });
        });
    }

    window.editHoliday = function(h) {
        holidayForm.action = '{{ route("admin.holiday.index") }}/' + h.id;
        holidayMethod.value = 'PUT';
        document.getElementById('holidayDateSingle').style.display = '';
        document.getElementById('holiday_date').setAttribute('required', 'required');
        document.getElementById('holidayDatesMulti').style.display = 'none';
        document.getElementById('holidayDatesList').innerHTML = '';
        document.getElementById('holidaySharedFields').style.display = '';
        document.getElementById('holiday_name').setAttribute('required', 'required');
        document.getElementById('holiday_type').setAttribute('required', 'required');
        document.getElementById('holiday_date').value = h.date ? h.date.split('T')[0] : '';
        document.getElementById('holiday_name').value = h.name || '';
        document.getElementById('holiday_description').value = h.description || '';
        document.getElementById('holiday_type').value = h.type || 'nasional';
        holidayModal.classList.add('active');
    };

    window.deleteHoliday = function(id, name) {
        if (!confirm('Hapus hari libur "' + name + '"?')) return;
        fetch('{{ route("admin.holiday.index") }}/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var item = holidayList.querySelector('[data-id="' + id + '"]');
                if (item) item.remove();
                var dateKey = Object.keys(holidayMap).find(function(k) { return holidayMap[k].id === id; });
                if (dateKey) delete holidayMap[dateKey];
                renderCalendar();
            } else {
                alert(data.message || 'Gagal menghapus.');
            }
        });
    };

    holidayForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Mode tambah: buang baris yang tanggalnya kosong agar tidak gagal validasi.
        if (holidayMethod.value === 'POST') {
            var rows = document.querySelectorAll('#holidayDatesList > div');
            var filled = 0;
            rows.forEach(function(wrap) {
                var dateInput = wrap.querySelector('input[type=date]');
                if (dateInput && dateInput.value) {
                    filled++;
                } else {
                    wrap.remove();
                }
            });
            if (filled === 0) {
                alert('Isi minimal satu baris (tanggal + nama libur).');
                return;
            }
        }
        var formData = new FormData(holidayForm);
        var formAction = holidayForm.action || '{{ route("admin.holiday.store") }}';

        fetch(formAction, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(function(r) {
            if (!r.ok) {
                return r.json().catch(function() { return { message: 'Terjadi kesalahan server.' }; });
            }
            return r.json();
        })
        .then(function(data) {
            if (data.success) {
                holidayModal.classList.remove('active');
                window.location.reload();
            } else {
                var msgs = [];
                if (data.errors) {
                    for (var f in data.errors) { data.errors[f].forEach(function(m) { msgs.push(m); }); }
                }
                alert(msgs.join('\n') || data.message || 'Gagal menyimpan.');
            }
        })
        .catch(function(err) {
            alert('Terjadi kesalahan jaringan: ' + err.message);
        });
    });

    renderCalendar();
})();
</script>
@endpush
