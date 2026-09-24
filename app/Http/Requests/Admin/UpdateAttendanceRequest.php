<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Matriks koreksi: jam hanya untuk Hadir/Terlambat; field dinas
        // hanya untuk Dinas Luar. Frontend mengunci input, backend menolak
        // bila dilanggar (jangan diam-diam mengabaikan).
        $status = $this->input('status');
        $isHadir = in_array($status, ['hadir', 'terlambat'], true);
        $isDinas = $status === 'dinas_luar';

        return [
            'status' => 'required|in:hadir,terlambat,izin,sakit,alpha,tugas_luar,dinas_luar',
            'jam_masuk' => [Rule::prohibitedIf(! $isHadir), 'nullable', 'date_format:H:i'],
            'jam_pulang' => [Rule::prohibitedIf(! $isHadir), 'nullable', 'date_format:H:i'],
            'keterangan' => 'nullable|string|max:500',
            'keperluan_dinas' => [Rule::prohibitedIf(! $isDinas), 'nullable', 'string', 'max:2000'],
            'lokasi_dinas' => [Rule::prohibitedIf(! $isDinas), 'nullable', 'string', 'max:255'],
            'bukti_file' => [Rule::prohibitedIf(! $isDinas), 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'alasan_koreksi' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'jam_masuk.prohibited' => 'Jam masuk hanya untuk status Hadir/Terlambat.',
            'jam_pulang.prohibited' => 'Jam pulang hanya untuk status Hadir/Terlambat.',
            'keperluan_dinas.prohibited' => 'Keperluan dinas hanya untuk Dinas Luar.',
            'lokasi_dinas.prohibited' => 'Lokasi dinas hanya untuk Dinas Luar.',
            'bukti_file.prohibited' => 'Lampiran koreksi hanya untuk Dinas Luar.',
            'alasan_koreksi.required' => 'Alasan koreksi wajib diisi.',
        ];
    }
}
