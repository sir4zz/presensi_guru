<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SpreadsheetExportService
{
    /**
     * Unduh data sebagai file XLSX (single sheet, backward compat).
     *
     * @param array<int,string> $headings
     * @param array<int,array<int,mixed>> $rows
     */
    public function download(string $filename, array $headings, array $rows, string $sheetTitle = 'Data'): StreamedResponse
    {
        return $this->downloadSheets($filename, [
            ['title' => $sheetTitle, 'headings' => $headings, 'rows' => $rows],
        ]);
    }

    /**
     * Unduh multi-sheet dengan kop sekolah + periode + baris total + tanda tangan.
     *
     * Sheet format:
     * ['title'=>, 'headings'=>[], 'rows'=>[[]], 'kop'=>[lines], 'period'=>string,
     *  'totals'=>[]|null, 'totals_label'=>'TOTAL', 'signature'=>bool]
     */
    public function downloadSheets(string $filename, array $sheets, array $meta = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $first = true;

        foreach ($sheets as $def) {
            $sheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $first = false;
            $sheet->setTitle(mb_substr((string) ($def['title'] ?? 'Data'), 0, 31));

            $headings = array_values($def['headings'] ?? []);
            $rows = array_values($def['rows'] ?? []);
            $colCount = max(1, count($headings));
            $lastCol = $this->columnLetter($colCount);

            $r = 1;
            foreach ((array) ($def['kop'] ?? []) as $line) {
                if ($line === null || $line === '') {
                    $r++;
                    continue;
                }
                $sheet->setCellValue("A{$r}", $line);
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $r++;
            }
            if (!empty($def['kop'])) {
                $r++;
            }
            if (!empty($def['period'])) {
                $sheet->setCellValue("A{$r}", (string) $def['period']);
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $r++;
            }
            if (!empty($meta['printed_at'])) {
                $sheet->setCellValue("A{$r}", 'Dicetak: ' . $meta['printed_at']);
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                $sheet->getStyle("A{$r}")->getFont()->setSize(9)->setItalic(true);
                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $r++;
            }
            $r++; // spasi sebelum tabel

            $headerRow = $r;
            $sheet->fromArray([$headings], null, "A{$r}");
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $r++;

            if ($rows !== []) {
                $sheet->fromArray($rows, null, "A{$r}");
                $r += count($rows);
            }

            if (!empty($def['totals'])) {
                $totals = array_values($def['totals']);
                $totals[0] = $def['totals_label'] ?? $totals[0] ?? 'TOTAL';
                $sheet->fromArray([$totals], null, "A{$r}");
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFont()->setBold(true);
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF1F5F9');
                $r++;
            }

            if (!empty($def['signature'])) {
                $r++;
                $sheet->setCellValue("A{$r}", 'Mengetahui,');
                $sheet->setCellValue("{$lastCol}{$r}", 'Dicetak oleh: ' . ($meta['printed_by'] ?? 'Admin'));
                $r += 3;
                $sheet->setCellValue("A{$r}", '( Kepala Sekolah )');
                $sheet->setCellValue("{$lastCol}{$r}", '( ' . ($meta['printed_by'] ?? 'Admin') . ' )');
            }

            foreach (range(1, $colCount) as $c) {
                $sheet->getColumnDimension($this->columnLetter($c))->setAutoSize(true);
            }
            $sheet->freezePane('A' . ($headerRow + 1));
            $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");
        }

        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }
        return $letter ?: 'A';
    }
}
