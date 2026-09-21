<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\AttendanceReportService;
use App\Services\AuditLogService;
use App\Services\SpreadsheetExportService;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(protected AttendanceReportService $reports)
    {
    }

    public function index()
    {
        $period = in_array(request('period', 'monthly'), ['daily', 'monthly', 'yearly'], true) ? request('period', 'monthly') : 'monthly';
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $guruId = request('guru_id') ? (int) request('guru_id') : null;
        $rawStatus = request('status') ?: null;
        $status = AttendanceReportService::normalizeStatus($rawStatus);
        [$startDate, $endDate, $reportTitle] = $this->periodRange($period, $month, $year, request('date'));

        $gurus = $this->reports->resolveGurus($guruId);
        $allGurus = $this->reports->resolveGurus();

        $daily = null;
        $summary = null;
        if ($period === 'daily') {
            $daily = $this->reports->dailyReport($startDate, $guruId, $rawStatus);
        } elseif ($period === 'yearly') {
            $summary = $this->reports->yearlyReport($year, $guruId, $rawStatus);
        } else {
            $summary = $this->reports->monthlyReport($month, $year, $guruId, $rawStatus);
        }

        $kop = $this->kopLines();

        return view('admin.report.index', compact(
            'gurus', 'allGurus', 'period', 'month', 'year', 'reportTitle',
            'startDate', 'endDate', 'daily', 'summary', 'kop', 'status'
        ));
    }

    public function export(SpreadsheetExportService $excel)
    {
        $period = in_array(request('period', 'monthly'), ['daily', 'monthly', 'yearly'], true) ? request('period', 'monthly') : 'monthly';
        $month = min(12, max(1, (int) request('month', now()->month)));
        $year = min(2100, max(2000, (int) request('year', now()->year)));
        $guruId = request('guru_id') ? (int) request('guru_id') : null;
        $rawStatus = request('status') ?: null;
        [$startDate, $endDate, $periodLabel] = $this->periodRange($period, $month, $year, request('date'));

        $kop = $this->kopLines();
        $meta = [
            'printed_at' => now()->translatedFormat('d F Y H:i') . ' WIB',
            'printed_by' => auth()->user()?->name ?? 'Admin',
        ];
        $statusLabels = ['hadir' => 'Hadir', 'terlambat' => 'Terlambat', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'TAK', 'dinas_luar' => 'Dinas Luar'];

        if ($period === 'daily') {
            $daily = $this->reports->dailyReport($startDate, $guruId, $rawStatus);
            $rekapRows = [];
            foreach ($daily['attendances']->values() as $i => $att) {
                $rekapRows[] = [
                    $i + 1,
                    AttendanceReportService::nipOf($att->guru),
                    $att->guru->name ?? '-',
                    $statusLabels[$att->status] ?? $att->status,
                    $att->jam_masuk ? substr((string) $att->jam_masuk, 0, 5) : '-',
                    $att->jam_pulang ? substr((string) $att->jam_pulang, 0, 5) : '-',
                    $att->keterangan ?? '-',
                ];
            }
            $missingRows = [];
            foreach ($daily['missing']->values() as $i => $g) {
                $missingRows[] = [$i + 1, AttendanceReportService::nipOf($g), $g->name, 'Belum Absen'];
            }
            AuditLogService::log('export', 'laporan', "Export laporan harian {$startDate} ke XLSX");

            return $excel->downloadSheets("rekap_harian_{$startDate}.xlsx", [
                [
                    'title' => 'Harian', 'kop' => $kop, 'period' => 'REKAP HARIAN — ' . Carbon::parse($startDate)->locale('id')->translatedFormat('l, d F Y'),
                    'headings' => ['NO', 'NIP', 'NAMA', 'STATUS', 'MASUK', 'PULANG', 'KETERANGAN'],
                    'rows' => $rekapRows, 'signature' => true,
                ],
                [
                    'title' => 'Belum Absen', 'kop' => $kop, 'period' => 'BELUM ABSEN — ' . $startDate,
                    'headings' => ['NO', 'NIP', 'NAMA', 'STATUS'],
                    'rows' => $missingRows, 'signature' => false,
                ],
            ], $meta);
        }

        $summary = $period === 'yearly'
            ? $this->reports->yearlyReport($year, $guruId, $rawStatus)
            : $this->reports->monthlyReport($month, $year, $guruId, $rawStatus);

        $rekapHeadings = ['NO', 'NIP', 'NAMA', 'H', 'TL', 'I', 'S', 'DL', 'TAK', 'APEL', 'TM', 'PS', 'KONV.HARI', 'TMTB', 'TOTAL', '%'];
        $rekapRows = [];
        foreach ($summary['rows'] as $row) {
            $rekapRows[] = [
                $row['no'], $row['nip'], $row['name'], $row['hadir'], $row['terlambat'],
                $row['izin'], $row['sakit'], $row['dinas_luar'], $row['tak'], $row['apel'],
                $row['tm'], $row['ps'], $row['konversi_hari'], $row['tmtb'], $row['total'], $row['persentase'],
            ];
        }
        $t = $summary['totals'];
        $totalsRow = ['', '', 'TOTAL', $t['hadir'], $t['terlambat'], $t['izin'], $t['sakit'], $t['dinas_luar'], $t['tak'], $t['apel'], $t['tm'], $t['ps'], '', $t['tmtb'], '', ''];

        $detail = $this->reports->detailRows($startDate, $endDate, $guruId, $rawStatus);
        $detailRows = [];
        foreach ($detail->values() as $i => $att) {
            $detailRows[] = [
                $i + 1,
                AttendanceReportService::nipOf($att->guru),
                $att->guru->name ?? '-',
                $att->tanggal,
                Carbon::parse($att->tanggal)->locale('id')->translatedFormat('l'),
                $statusLabels[$att->status] ?? $att->status,
                $att->jam_masuk ? substr((string) $att->jam_masuk, 0, 5) : '-',
                $att->jam_pulang ? substr((string) $att->jam_pulang, 0, 5) : '-',
                $att->keterangan ?? '-',
            ];
        }

        $sheets = [
            [
                'title' => 'Rekap', 'kop' => $kop, 'period' => 'REKAP ' . strtoupper($periodLabel) . " — Hari kerja: {$summary['hari_kerja']}",
                'headings' => $rekapHeadings, 'rows' => $rekapRows, 'totals' => $totalsRow, 'signature' => true,
            ],
            [
                'title' => 'Detail', 'kop' => $kop, 'period' => 'DETAIL ' . strtoupper($periodLabel),
                'headings' => ['NO', 'NIP', 'NAMA', 'TANGGAL', 'HARI', 'STATUS', 'MASUK', 'PULANG', 'KETERANGAN'],
                'rows' => $detailRows, 'signature' => false,
            ],
        ];

        if ($period === 'yearly') {
            $monthRows = [];
            foreach ($summary['per_month'] as $i => $m) {
                $monthRows[] = [$i + 1, $m['bulan'], $m['hadir'], $m['terlambat'], $m['izin'], $m['sakit'], $m['dinas_luar'], $m['tak'], $m['apel']];
            }
            $sheets[] = [
                'title' => 'Per Bulan', 'kop' => $kop, 'period' => 'AGREGAT PER BULAN — TAHUN ' . $year,
                'headings' => ['NO', 'BULAN', 'H', 'TL', 'I', 'S', 'DL', 'TAK', 'APEL'],
                'rows' => $monthRows, 'signature' => false,
            ];
            AuditLogService::log('export', 'laporan', "Export laporan tahunan {$year} ke XLSX");
            return $excel->downloadSheets("rekap_tahunan_{$year}.xlsx", $sheets, $meta);
        }

        AuditLogService::log('export', 'laporan', "Export laporan bulanan {$month}-{$year} ke XLSX");
        return $excel->downloadSheets(sprintf('rekap_bulanan_%02d_%04d.xlsx', $month, $year), $sheets, $meta);
    }

    /** Baris kop dari pengaturan sekolah. */
    protected function kopLines(): array
    {
        $saved = SchoolSetting::allAsArray();
        $name = $saved['school_name'] ?? 'SMKN 11 KABUPATEN TANGERANG';
        $address = $saved['school_address'] ?? '';
        $lines = ['REKAP KEHADIRAN GURU', mb_strtoupper((string) $name)];
        if ($address !== '') {
            $lines[] = $address;
        }
        return $lines;
    }

    protected function periodRange(string $period, int $month, int $year, ?string $date = null): array
    {
        if ($period === 'daily') {
            $day = Carbon::parse($date ?: now()->toDateString());
            return [$day->toDateString(), $day->toDateString(), 'Harian ' . $day->locale('id')->translatedFormat('l, d F Y')];
        }
        if ($period === 'yearly') {
            return ["{$year}-01-01", "{$year}-12-31", 'Tahunan ' . $year];
        }
        $start = Carbon::create($year, $month, 1);
        return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString(), 'Bulanan ' . $start->locale('id')->translatedFormat('F Y')];
    }
}
