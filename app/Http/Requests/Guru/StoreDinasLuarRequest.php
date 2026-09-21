<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreDinasLuarRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->role === 'guru' && $this->user()?->status === 'aktif'; }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'keperluan_dinas' => ['required', 'string', 'max:2000'],
            'lokasi_dinas' => ['required', 'string', 'max:255'],
            'bukti_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
