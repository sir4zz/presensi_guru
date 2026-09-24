<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required|date',
            'status' => 'required|in:izin,sakit,dinas_luar',
            'alasan' => 'required|string|max:500',
            'lokasi_dinas' => 'required_if:status,dinas_luar|nullable|string|max:255',
            'bukti_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'lokasi_dinas.required_if' => 'Lokasi dinas wajib diisi untuk Dinas Luar.',
            'bukti_file.mimes' => 'Lampiran harus JPG, PNG, WebP, atau PDF.',
            'bukti_file.max' => 'Ukuran lampiran maksimal 5 MB.',
        ];
    }
}
