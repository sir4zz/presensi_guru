@extends('layouts.admin')

@section('title', 'Edit Guru - Admin')
@section('page-title', 'Edit Guru')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Edit data guru</p>
    </div>
    <x-button variant="secondary" href="{{ route('admin.guru.index') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </x-button>
</div>

<div class="card" style="max-width: 640px;">
    <form method="POST" action="{{ route('admin.guru.update', $guru) }}">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input type="text" id="name" name="name" class="form-input @error('name') form-input-error @enderror" value="{{ old('name', $guru->name) }}" required>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="nip" class="form-label">NIP</label>
            <input type="text" id="nip" name="nip" class="form-input @error('nip') form-input-error @enderror" value="{{ old('nip', $guru->username) }}" required>
            @error('nip')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
            <div class="form-group">
                <label for="sk" class="form-label">SK</label>
                <input type="text" id="sk" name="sk" class="form-input @error('sk') form-input-error @enderror" value="{{ old('sk', $guru->guruProfile?->sk) }}">
                @error('sk')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="spmt" class="form-label">SPMT</label>
                <input type="text" id="spmt" name="spmt" class="form-input @error('spmt') form-input-error @enderror" value="{{ old('spmt', $guru->guruProfile?->spmt) }}">
                @error('spmt')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select @error('status') form-input-error @enderror">
                <option value="aktif" {{ old('status', $guru->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ old('status', $guru->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="password" class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
            <input type="password" id="password" name="password" class="form-input @error('password') form-input-error @enderror">
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input">
        </div>

        <div class="flex gap-3">
            <x-button variant="secondary" href="{{ route('admin.guru.index') }}">Batal</x-button>
            <x-button type="submit">Simpan Perubahan</x-button>
        </div>
    </form>
</div>
@endsection
