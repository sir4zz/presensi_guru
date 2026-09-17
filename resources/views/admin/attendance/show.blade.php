@extends('layouts.admin')

@section('title', 'Detail Absensi - Admin')
@section('page-title', 'Detail Absensi')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Detail data absensi</p>
    </div>
    <div class="flex gap-3">
        <x-button variant="secondary" href="{{ route('admin.attendance.index') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Kembali
        </x-button>
        <x-button onclick="document.getElementById('editModal').classList.add('active')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Koreksi
        </x-button>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Informasi Absensi</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <div class="account-field">
                <span class="account-field-label">Guru</span>
                <span class="account-field-value">{{ $attendance->guru->name ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIP</span>
                <span class="account-field-value">{{ $attendance->guru->username ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Tanggal</span>
                <span class="account-field-value">{{ \Carbon\Carbon::parse($attendance->tanggal)->format('d F Y') }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Status</span>
                @if($attendance->status === 'hadir')
                    <x-badge variant="success">Hadir</x-badge>
                @elseif($attendance->status === 'terlambat')
                    <x-badge variant="warning">Terlambat</x-badge>
                @elseif($attendance->status === 'izin')
                    <x-badge variant="info">Izin</x-badge>
                @elseif($attendance->status === 'sakit')
                    <x-badge variant="danger">Sakit</x-badge>
                @else
                    <x-badge variant="danger">TAK</x-badge>
                @endif
            </div>
            <div class="account-field">
                <span class="account-field-label">Jam Masuk</span>
                <span class="account-field-value">{{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jam Pulang</span>
                <span class="account-field-value">{{ $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i') : '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Keterangan</span>
                <span class="account-field-value">{{ $attendance->keterangan ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lokasi & Foto</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-4);">
            <div class="account-field">
                <span class="account-field-label">Latitude Masuk</span>
                <span class="account-field-value">{{ $attendance->lat_masuk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Longitude Masuk</span>
                <span class="account-field-value">{{ $attendance->lng_masuk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jarak Masuk</span>
                <span class="account-field-value">{{ $attendance->distance_masuk ? number_format($attendance->distance_masuk, 0) . ' meter' : '-' }}</span>
            </div>
            @if($attendance->foto_masuk)
                <div class="account-field">
                    <span class="account-field-label">Foto Masuk</span>
                    <img src="{{ Storage::url($attendance->foto_masuk) }}" alt="Foto Masuk" style="max-width: 200px; border-radius: var(--radius-md);">
                </div>
            @endif
            @if($attendance->foto_pulang)
                <div class="account-field">
                    <span class="account-field-label">Foto Pulang</span>
                    <img src="{{ Storage::url($attendance->foto_pulang) }}" alt="Foto Pulang" style="max-width: 200px; border-radius: var(--radius-md);">
                </div>
            @endif
        </div>
    </div>
</div>

<x-modal id="editModal" title="Koreksi Absensi" size="lg">
    <form method="POST" action="{{ route('admin.attendance.update', $attendance) }}">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select">
                <option value="hadir" {{ $attendance->status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                <option value="terlambat" {{ $attendance->status === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                <option value="izin" {{ $attendance->status === 'izin' ? 'selected' : '' }}>Izin</option>
                <option value="sakit" {{ $attendance->status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                <option value="tidak_ada_keterangan" {{ $attendance->status === 'tidak_ada_keterangan' ? 'selected' : '' }}>TAK</option>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
            <div class="form-group">
                <label for="jam_masuk" class="form-label">Jam Masuk</label>
                <input type="time" id="jam_masuk" name="jam_masuk" class="form-input" value="{{ $attendance->jam_masuk ? \Carbon\Carbon::parse($attendance->jam_masuk)->format('H:i') : '' }}">
            </div>
            <div class="form-group">
                <label for="jam_pulang" class="form-label">Jam Pulang</label>
                <input type="time" id="jam_pulang" name="jam_pulang" class="form-input" value="{{ $attendance->jam_pulang ? \Carbon\Carbon::parse($attendance->jam_pulang)->format('H:i') : '' }}">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea id="keterangan" name="keterangan" class="form-textarea">{{ $attendance->keterangan }}</textarea>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="alasan_koreksi" class="form-label">Alasan Koreksi *</label>
            <textarea id="alasan_koreksi" name="alasan_koreksi" class="form-textarea" required placeholder="Masukkan alasan koreksi..."></textarea>
        </div>

        <div class="modal-footer" style="padding: 0; border: none;">
            <x-button variant="secondary" onclick="document.getElementById('editModal').classList.remove('active')">Batal</x-button>
            <x-button type="submit">Simpan Koreksi</x-button>
        </div>
    </form>
</x-modal>
@endsection
