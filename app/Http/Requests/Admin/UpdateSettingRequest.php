<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_name' => 'nullable|string|max:255',
            'school_address' => 'nullable|string|max:500',
            'school_logo' => 'nullable|image|max:2048',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'attendance_radius' => 'nullable|integer|min:50|max:1000',
            'work_start_time' => 'nullable|date_format:H:i',
            'present_until' => 'nullable|date_format:H:i',
            'late_until' => 'nullable|date_format:H:i',
            'checkout_start_time' => 'nullable|date_format:H:i',
        ];
    }
}
