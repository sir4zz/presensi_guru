<?php

namespace App\Http\Requests\Guru;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'selfie' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Lokasi GPS diperlukan.',
            'latitude.between' => 'Latitude tidak valid.',
            'longitude.required' => 'Lokasi GPS diperlukan.',
            'longitude.between' => 'Longitude tidak valid.',
            'selfie.required' => 'Foto selfie wajib diambil.',
            'selfie.image' => 'File harus berupa gambar.',
            'selfie.mimes' => 'Format gambar harus JPEG atau PNG.',
            'selfie.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
