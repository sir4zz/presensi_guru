@extends('layouts.admin')

@section('title', 'Pengaturan Sekolah - Admin')
@section('page-title', 'Pengaturan Sekolah')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Konfigurasi sistem absensi</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.setting.update') }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informasi Sekolah</h3>
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="school_name" class="form-label">Nama Sekolah</label>
                <input type="text" id="school_name" name="school_name" class="form-input" value="{{ old('school_name', $settings->school_name ?? '') }}">
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="school_address" class="form-label">Alamat Sekolah</label>
                <textarea id="school_address" name="school_address" class="form-textarea">{{ old('school_address', $settings->school_address ?? '') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="school_logo" class="form-label">Logo Sekolah</label>
                <input type="file" id="school_logo" name="school_logo" class="form-input" accept="image/*">
                @if($settings->school_logo ?? null)
                    <img src="{{ Storage::url($settings->school_logo) }}" alt="Logo" style="max-width: 80px; margin-top: var(--space-2); border-radius: var(--radius-md);">
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Lokasi & Radius</h3>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
                <div class="form-group">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="text" id="latitude" name="latitude" class="form-input" value="{{ old('latitude', $settings->latitude ?? '') }}">
                </div>
                <div class="form-group">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="text" id="longitude" name="longitude" class="form-input" value="{{ old('longitude', $settings->longitude ?? '') }}">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="attendance_radius" class="form-label">Radius Absensi (meter)</label>
                <input type="number" id="attendance_radius" name="attendance_radius" class="form-input" value="{{ old('attendance_radius', $settings->attendance_radius ?? 200) }}">
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jam Kerja</h3>
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="work_start_time" class="form-label">Jam Mulai Kerja</label>
                <input type="time" id="work_start_time" name="work_start_time" class="form-input" value="{{ old('work_start_time', $settings->work_start_time ?? '07:00') }}">
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="present_until" class="form-label">Batas Hadir</label>
                <input type="time" id="present_until" name="present_until" class="form-input" value="{{ old('present_until', $settings->present_until ?? '08:30') }}">
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="late_until" class="form-label">Batas Terlambat</label>
                <input type="time" id="late_until" name="late_until" class="form-input" value="{{ old('late_until', $settings->late_until ?? '09:00') }}">
            </div>

            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label for="checkout_start_time" class="form-label">Jam Mulai Pulang</label>
                <input type="time" id="checkout_start_time" name="checkout_start_time" class="form-input" value="{{ old('checkout_start_time', $settings->checkout_start_time ?? '15:00') }}">
            </div>
        </div>
    </div>

    <div style="margin-top: var(--space-6);">
        <x-button type="submit">Simpan Pengaturan</x-button>
    </div>
</form>
@endsection
