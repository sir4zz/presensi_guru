@extends('layouts.admin')

@section('title', 'Tambah Guru - Admin')
@section('page-title', 'Tambah Guru')

@section('content')
@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: var(--space-4);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="page-header">
    <div>
        <p class="page-subtitle">Tambah data guru baru</p>
    </div>
    <x-button variant="secondary" href="{{ route('admin.guru.index') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </x-button>
</div>

<div class="card" style="max-width: 640px;">
    <form method="POST" action="{{ route('admin.guru.store') }}">
        @csrf

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input type="text" id="name" name="name" class="form-input @error('name') form-input-error @enderror" value="{{ old('name') }}" required pattern="[A-Za-z\s]+" placeholder="Masukkan nama lengkap">
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="nip" class="form-label">NIP</label>
            <input type="text" id="nip" name="nip" class="form-input @error('nip') form-input-error @enderror" value="{{ old('nip') }}" required pattern="[0-9]{1,20}" inputmode="numeric" placeholder="Masukkan NIP (angka saja)">
            @error('nip')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4); margin-bottom: var(--space-4);">
            <div class="form-group">
                <label for="sk" class="form-label">SK</label>
                <input type="text" id="sk" name="sk" class="form-input @error('sk') form-input-error @enderror" value="{{ old('sk') }}">
                @error('sk')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="spmt" class="form-label">SPMT</label>
                <input type="text" id="spmt" name="spmt" class="form-input @error('spmt') form-input-error @enderror" value="{{ old('spmt') }}">
                @error('spmt')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-input @error('password') form-input-error @enderror" required>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
        </div>

        <div class="flex gap-3">
            <x-button variant="secondary" href="{{ route('admin.guru.index') }}">Batal</x-button>
            <x-button type="submit">Simpan</x-button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var nipInput = document.getElementById('nip');
    nipInput.addEventListener('keydown', function(e) {
        if ([8, 9, 27, 13, 46, 37, 38, 39, 40, 35, 36].indexOf(e.keyCode) !== -1) return;
        if ((e.ctrlKey || e.metaKey) && [65, 67, 86, 88].indexOf(e.keyCode) !== -1) return;
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    nipInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
@endpush
