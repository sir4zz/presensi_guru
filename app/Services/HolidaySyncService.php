<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidaySyncService
{
    /**
     * Sumber API hari libur nasional Indonesia (primer + cadangan).
     * Format primer: {status, code, data: [{date: Y-m-d, description}]}
     * Format cadangan: [{date: Y-m-d, description}]
     */
    protected string $primaryUrl = 'https://api-hari-libur.vercel.app/api';

    protected string $fallbackUrl = 'https://raw.githubusercontent.com/andifahruddinakas/api-hari-libur/main/data';

    /**
     * Sinkronkan libur nasional satu tahun ke tabel holidays yang sudah ada.
     * Entri manual (daerah/sekolah) tidak akan ditimpa.
     *
     * @return array{success:bool,message:string,year:int,created:int,updated:int,skipped:int}
     */
    public function sync(int $year): array
    {
        if ($year < 2020 || $year > 2100) {
            return $this->result(false, 'Tahun tidak valid.', $year, 0, 0, 0);
        }

        $items = $this->fetch($year);

        if ($items === null) {
            return $this->result(false, 'Gagal mengambil data libur nasional. Periksa koneksi internet lalu coba lagi.', $year, 0, 0, 0);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($items as $item) {
            $date = $item['date'] ?? null;
            $name = trim((string) ($item['description'] ?? ''));

            if (!$this->validDate($date, $year) || $name === '') {
                $skipped++;
                continue;
            }

            $existing = Holiday::whereDate('date', $date)->first();

            // Jangan timpa entri manual (daerah/sekolah).
            if ($existing && $existing->type !== 'nasional') {
                $skipped++;
                continue;
            }

            $attributes = [
                'name' => $name,
                'description' => $this->isCutiBersama($name)
                    ? 'Cuti bersama (sinkronisasi otomatis libur nasional).'
                    : 'Libur nasional (sinkronisasi otomatis).',
                'type' => 'nasional',
            ];

            if ($existing) {
                if ($existing->name !== $attributes['name'] || $existing->description !== $attributes['description']) {
                    $existing->update($attributes);
                    $updated++;
                } else {
                    $skipped++;
                }
                continue;
            }

            Holiday::create(array_merge(['date' => $date], $attributes));
            $created++;
        }

        return $this->result(
            true,
            "Sinkronisasi libur nasional {$year} selesai: {$created} ditambah, {$updated} diperbarui, {$skipped} dilewati.",
            $year, $created, $updated, $skipped
        );
    }

    /**
     * @return array<int, array{date:string,description:string}>|null
     */
    protected function fetch(int $year): ?array
    {
        // Sumber primer: API dinamis per tahun.
        try {
            $response = Http::timeout(15)->acceptJson()->get($this->primaryUrl, ['year' => $year]);

            if ($response->successful()) {
                $items = $response->json('data');
                if (is_array($items) && $items !== []) {
                    return $items;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Sinkron libur nasional: sumber primer gagal.', ['year' => $year, 'error' => $e->getMessage()]);
        }

        // Sumber cadangan: file JSON statis per tahun.
        try {
            $response = Http::timeout(15)->acceptJson()->get("{$this->fallbackUrl}/{$year}.json");

            if ($response->successful()) {
                $items = $response->json();
                if (is_array($items) && $items !== []) {
                    return $items;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Sinkron libur nasional: sumber cadangan gagal.', ['year' => $year, 'error' => $e->getMessage()]);
        }

        return null;
    }

    protected function validDate(mixed $date, int $year): bool
    {
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $date));

        return $y === $year && checkdate($m, $d, $y);
    }

    protected function isCutiBersama(string $name): bool
    {
        return stripos($name, 'cuti bersama') !== false;
    }

    protected function result(bool $success, string $message, int $year, int $created, int $updated, int $skipped): array
    {
        return compact('success', 'message', 'year', 'created', 'updated', 'skipped');
    }
}
