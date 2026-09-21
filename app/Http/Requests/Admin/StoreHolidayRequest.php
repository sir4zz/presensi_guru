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
            'date' => 'required|date|after_or_equal:2020-01-01|unique:holidays,date',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:nasional,daerah,sekolah',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Tanggal wajib diisi.',
            'date.unique' => 'Tanggal tersebut sudah ada sebagai hari libur.',
            'name.required' => 'Nama hari libur wajib diisi.',
            'type.required' => 'Jenis hari libur wajib dipilih.',
            'type.in' => 'Jenis harus nasional, daerah, atau sekolah.',
        ];
    }
}
