<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guru_ids' => 'required|array',
            'guru_ids.*' => 'exists:users,id',
            'tanggal' => 'required|date',
            'alasan' => 'required|string|max:500',
            'status' => 'required|in:izin,sakit,dinas_luar',
            'lokasi_dinas' => 'required_if:status,dinas_luar|nullable|string|max:255',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];
    }
}
