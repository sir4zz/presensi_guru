<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'nip' => 'required|string|numeric|digits_between:1,20|unique:users,username,' . $this->route('id'),
            'sk' => 'nullable|string|max:255',
            'spmt' => 'nullable|string|max:255',
            'status' => 'required|in:aktif,nonaktif',
            'password' => 'nullable|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'nip.numeric' => 'NIP hanya boleh berisi angka.',
            'nip.digits_between' => 'NIP maksimal 20 digit.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}
