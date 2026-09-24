<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\SchoolSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Layanan rekap laporan kehadiran terpusat.
 *
 * Menggantikan logika duplikat di ReportController & AttendanceController:
 * - definisi hari kerja tunggal (Senin-Jumat minus libur, Sabtu/Minggu selalu libur)
 * - agregasi 2-3 query (tanpa N+1 per guru, sesuai PRD #64)
 * - status legacy 'tidak_ada_keterangan' dinormalisasi ke 'alpha'
 * - Dinas Luar kolom terpisah, tidak masuk APEL/% (keputusan produk)
 * - konversi keterlambatan: 7 jam (420 menit) = 1 hari
 */
class AttendanceReportService
{
    /** 7 jam = 1 hari hukuman keterlambatan (menit). */
    public const MINUTES_PER_CONVERSION_DAY = 420;

    protected array $settings;

    public function __construct()
    {
        $saved = SchoolSetting::allAsArray();
        $this->settings = array_merge([
            'present_until' => '08:30',
            'checkout_start_time' => '15:00',
        ], $saved);
    }

    /** Normalisasi status legacy dari filter UI ke nilai DB. */
    public static function normalizeStatus(?string $status): ?string
    {
        if ($status === null || $status === '' || $status === 'all') {
            return null;
        }
        if ($status === 'tidak_ada_keterangan') {
            return AttendanceStatus::Alpha->value;
        }
        // Legacy: 'tugas_luar' diperlakukan sama dengan 'dinas_luar'.
        if ($status === 'tugas_luar') {
            return AttendanceStatus::DinasLuar->value;
        }
        $valid = ['hadir', 'terlambat', 'izin', 'sakit', 'alpha', 'dinas_luar'];
        return in_array($status, $valid, true) ? $status : null;
    }

    public static function nipOf(User $guru): string
    {
        return $guru->guruProfile?->nip ?? $guru->username ?? '-';
    }

    /**
     * Daftar hari kerja pada rentang (Y-m-d), plus peta libur.
     * @return array{days: array<int,string>, count: int, holidays: array<string,string>, libur_count: int}
     */
    public function workingDays(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($h) => Carbon::parse($h->date)->toDateString());

        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $isWeekend = $cursor->dayOfWeek === Carbon::SATURDAY || $cursor->dayOfWeek === Carbon::SUNDAY;
            $isHoliday = $holidays->has($cursor->toDateString());
            if (!$isWeekend && !$isHoliday) {
                $days[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return [
            'days' => $days,
            'count' => count($days),
            'holidays' => $holidays->map(fn ($h) => $h->name)->toArray(),
            'libur_count' => $holidays->count(),
        ];
    }

    /** Ambil guru terurut nama, default hanya aktif. */
    public function resolveGurus(?int $guruId = null, bool $includeNonaktif = false): Collection
    {
        $query = User::where('role', 'guru')->with('guruProfile')->orderBy('name');
        if (!$includeNonaktif) {
            $query->where('status', 'aktif');
        }
        if ($guruId) {
            $query->where('id', $guruId);
        }
        return $query->get();
    }

    protected function minutesBetween(string $from, string $to): int
    {
        [$fh, $fm] = array_map('intval', explode(':', substr($from, 0, 5)));
        [$th, $tm] = array_map('intval', explode(':', substr($to, 0, 5)));
        return (($th * 60) + $tm) - (($fh * 60) + $fm);
    }

    /** Hitung TM & PS dari koleksi attendance milik 1 guru. */
    protected function disciplineMinutes(Collection $rows): array
    {
        $presentUntil = substr((string) ($this->settings['present_until'] ?? '08:30'), 0, 5);
        $checkoutStart = substr((string) ($this->settings['checkout_start_time'] ?? '15:00'), 0, 5);
        $late = 0;
        $early = 0;
        foreach ($rows as $row) {
            if ($row->status === AttendanceStatus::Terlambat->value && $row->jam_masuk) {
                $late += max(0, $this->minutesBetween($presentUntil, substr((string) $row->jam_masuk, 0, 5)));
            }
            if ($row->jam_pulang) {
                $early += max(0, $this->minutesBetween(substr((string) $row->jam_pulang, 0, 5), $checkoutStart));
            }
        }
        $total = $late + $early;
        return [
            'terlambat_menit' => $late,
            'pulang_awal_menit' => $early,
            'konversi_jam' => round($total / 60, 2),
            'konversi_hari' => round($total / 60 / (self::MINUTES_PER_CONVERSION_DAY / 60), 2),
            'total_menit' => $total,
        ];
    }

    /**
     * Agregasi per guru untuk rentang tanggal. 1 query attendance + workingDays.
     * @return array{rows: array, totals: array, hari_kerja: int, libur: array}
     */
    public function aggregateForRange(string $startDate, string $endDate, ?int $guruId = null, ?string $statusFilter = null): array
    {
        $status = self::normalizeStatus($statusFilter);
        $work = $this->workingDays($startDate, $endDate);
        $workSet = array_flip($work['days']);

        $gurus = $this->resolveGurus($guruId);

        $attendances = Attendance::whereBetween('tanggal', [$startDate, $endDate])
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->get();

        $byGuru = $attendances->groupBy('guru_id');

        $rows = [];
        $totals = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'dinas_luar' => 0, 'tak' => 0, 'apel' => 0, 'tm' => 0, 'ps' => 0, 'tmtb' => 0];

        foreach ($gurus as $index => $guru) {
            $gAtt = $byGuru->get($guru->id, collect());
            // Hanya record di hari kerja untuk hitung belum-absen & %.
            $onWorkdays = $gAtt->filter(fn ($a) => isset($workSet[$a->tanggal]));
            $hadir = $gAtt->where('status', 'hadir')->count();
            $terlambat = $gAtt->where('status', 'terlambat')->count();
            $izin = $gAtt->where('status', 'izin')->count();
            $sakit = $gAtt->where('status', 'sakit')->count();
            $dl = $gAtt->whereIn('status', ['dinas_luar', 'tugas_luar'])->count();
            $tak = $gAtt->where('status', 'alpha')->count();
            $apel = $hadir + $terlambat;
            $disc = $this->disciplineMinutes($gAtt);
            $konversiHari = $disc['konversi_hari'];
            $totalHukuman = round($konversiHari + $tak, 2);
            $tercatat = $onWorkdays->count();
            $belum = max(0, $work['count'] - $tercatat);
            $persentase = $work['count'] > 0 ? round(min(100, ($apel / $work['count']) * 100)) : 0;

            $rows[] = [
                'no' => $index + 1,
                'id' => $guru->id,
                'name' => $guru->name,
                'nip' => self::nipOf($guru),
                'status_aktif' => $guru->status,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'sakit' => $sakit,
                'dinas_luar' => $dl,
                'tak' => $tak,
                'belum' => $belum,
                'apel' => $apel,
                'tm' => $disc['terlambat_menit'],
                'ps' => $disc['pulang_awal_menit'],
                'konversi_jam' => $disc['konversi_jam'],
                'konversi_hari' => $konversiHari,
                'tmtb' => $tak,
                'total' => $totalHukuman,
                'persentase' => $persentase,
            ];

            $totals['hadir'] += $hadir;
            $totals['terlambat'] += $terlambat;
            $totals['izin'] += $izin;
            $totals['sakit'] += $sakit;
            $totals['dinas_luar'] += $dl;
            $totals['tak'] += $tak;
            $totals['apel'] += $apel;
            $totals['tm'] += $disc['terlambat_menit'];
            $totals['ps'] += $disc['pulang_awal_menit'];
            $totals['tmtb'] += $tak;
        }

        return [
            'rows' => $rows,
            'totals' => $totals,
            'hari_kerja' => $work['count'],
            'libur' => $work['holidays'],
            'libur_count' => $work['libur_count'],
        ];
    }

    /** Laporan harian: detail per guru + daftar belum absen. */
    public function dailyReport(string $date, ?int $guruId = null, ?string $statusFilter = null): array
    {
        $status = self::normalizeStatus($statusFilter);
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $isSunday = $dayOfWeek === Carbon::SUNDAY;
        $isSaturday = $dayOfWeek === Carbon::SATURDAY;
        $holiday = Holiday::whereDate('date', $date)->first();
        $isWorkday = !$isSunday && !$isSaturday && !$holiday;

        $attendances = Attendance::with('guru.guruProfile')
            ->whereDate('tanggal', $date)
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('jam_masuk')
            ->get();

        $presentIds = Attendance::whereDate('tanggal', $date)->pluck('guru_id')->all();
        $missing = User::where('role', 'guru')->where('status', 'aktif')
            ->with('guruProfile')
            ->when($guruId, fn ($q) => $q->where('id', $guruId))
            ->whereNotIn('id', $presentIds)
            ->orderBy('name')
            ->get();

        $count = fn ($s) => $s === 'dinas_luar'
            ? $attendances->whereIn('status', ['dinas_luar', 'tugas_luar'])->count()
            : $attendances->where('status', $s)->count();

        return [
            'date' => $date,
            'is_workday' => $isWorkday,
            'is_sunday' => $isSunday,
            'is_saturday' => $isSaturday,
            'holiday' => $holiday,
            'attendances' => $attendances,
            'missing' => ($status || !$isWorkday) ? collect() : $missing,
            'missing_count' => ($status || !$isWorkday) ? 0 : $missing->count(),
            'stats' => [
                'hadir' => $count('hadir'),
                'terlambat' => $count('terlambat'),
                'izin' => $count('izin'),
                'sakit' => $count('sakit'),
                'dinas_luar' => $count('dinas_luar'),
                'tak' => $count('alpha'),
                'pulang' => $attendances->whereNotNull('jam_pulang')->count(),
                'total' => $attendances->count(),
            ],
        ];
    }

    public function monthlyReport(int $month, int $year, ?int $guruId = null, ?string $statusFilter = null): array
    {
        $start = Carbon::create($year, $month, 1);
        $agg = $this->aggregateForRange($start->toDateString(), $start->copy()->endOfMonth()->toDateString(), $guruId, $statusFilter);
        $agg['month'] = $month;
        $agg['year'] = $year;
        $agg['title'] = 'Bulanan ' . $start->locale('id')->translatedFormat('F Y');
        return $agg;
    }

    /** Laporan tahunan: agregasi + breakdown per bulan (1 query). */
    public function yearlyReport(int $year, ?int $guruId = null, ?string $statusFilter = null): array
    {
        $agg = $this->aggregateForRange("{$year}-01-01", "{$year}-12-31", $guruId, $statusFilter);

        $rows = Attendance::whereBetween('tanggal', ["{$year}-01-01", "{$year}-12-31"])
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->get();

        $perMonth = [];
        for ($m = 1; $m <= 12; $m++) {
            $prefix = sprintf('%04d-%02d', $year, $m);
            $mr = $rows->filter(fn ($a) => str_starts_with((string) $a->tanggal, $prefix));
            $hadir = $mr->where('status', 'hadir')->count();
            $terlambat = $mr->where('status', 'terlambat')->count();
            $perMonth[] = [
                'bulan' => Carbon::create($year, $m, 1)->locale('id')->translatedFormat('F'),
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $mr->where('status', 'izin')->count(),
                'sakit' => $mr->where('status', 'sakit')->count(),
                'dinas_luar' => $mr->whereIn('status', ['dinas_luar', 'tugas_luar'])->count(),
                'tak' => $mr->where('status', 'alpha')->count(),
                'apel' => $hadir + $terlambat,
            ];
        }

        $agg['year'] = $year;
        $agg['title'] = 'Tahunan ' . $year;
        $agg['per_month'] = $perMonth;
        return $agg;
    }

    /** Detail mentah untuk Sheet Detail export (chunk-friendly, terurut tanggal). */
    public function detailRows(string $startDate, string $endDate, ?int $guruId = null, ?string $statusFilter = null, int $limit = 5000): Collection
    {
        return Attendance::with('guru.guruProfile')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->when(self::normalizeStatus($statusFilter), fn ($q, $s) => $q->where('status', $s))
            ->orderBy('tanggal')
            ->orderBy('jam_masuk')
            ->limit($limit)
            ->get();
    }
}
