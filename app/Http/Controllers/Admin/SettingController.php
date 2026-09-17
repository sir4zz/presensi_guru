<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\SchoolSetting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $defaults = [
            'school_name' => '', 'school_address' => '', 'school_logo' => null,
            'latitude' => '-6.2011', 'longitude' => '106.393', 'attendance_radius' => 200,
            'work_start_time' => '07:00', 'present_until' => '08:30',
            'late_until' => '09:00', 'checkout_start_time' => '15:00',
        ];
        $saved = SchoolSetting::allAsArray();
        $settings = (object) array_merge($defaults, $saved);

        return view('admin.setting.index', compact('settings'));
    }

    public function update(UpdateSettingRequest $request)
    {
        $fields = ['school_name', 'school_address', 'latitude', 'longitude', 'attendance_radius',
            'work_start_time', 'present_until', 'late_until', 'checkout_start_time'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                SchoolSetting::set($field, $request->input($field));
            }
        }

        if ($request->hasFile('school_logo')) {
            if ($request->user()->school_logo) {
                Storage::disk('public')->delete($request->user()->school_logo);
            }
            $path = $request->file('school_logo')->store('logos', 'public');
            SchoolSetting::set('school_logo', $path);
        }

        return redirect()->route('admin.setting.index')->with('success', 'Pengaturan berhasil disimpan.');
    }
}
