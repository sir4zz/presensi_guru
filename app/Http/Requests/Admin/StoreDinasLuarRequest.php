<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDinasLuarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'guru_ids' => ['required', 'array', 'min:1'],
            'guru_ids.*' => ['integer', 'exists:users,id'],
            'tanggal' => ['required', 'date'],
            'keperluan_dinas' => ['required', 'string', 'max:2000'],
            'lokasi_dinas' => ['required', 'string', 'max:255'],
            'bukti_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
