<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Multi-baris: tiap tanggal punya nama/jenis/keterangan sendiri.
            'items' => 'required|array|min:1|max:100',
            'items.*.date' => 'required|date|after_or_equal:2020-01-01',
            'items.*.name' => 'required|string|max:255',
            'items.*.type' => 'required|in:nasional,daerah,sekolah',
            'items.*.description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal satu tanggal wajib diisi.',
            'items.*.date.required' => 'Tanggal wajib diisi.',
            'items.*.date.date' => 'Format tanggal tidak valid.',
            'items.*.name.required' => 'Nama hari libur wajib diisi.',
            'items.*.type.in' => 'Jenis harus nasional, daerah, atau sekolah.',
        ];
    }
}
