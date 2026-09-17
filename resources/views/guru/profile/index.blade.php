@extends('layouts.guru')

@section('title', 'Akun - Guru')
@section('page-title', 'Akun')

@section('content')
<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Informasi Akun</h3>
    </div>
    <div class="account-info">
        <div class="account-field">
            <span class="account-field-label">Nama</span>
            <span class="account-field-value">{{ Auth::user()->name }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">NIP</span>
            <span class="account-field-value">{{ Auth::user()->username }}</span>
        </div>
        @if(Auth::user()->guruProfile)
            <div class="account-field">
                <span class="account-field-label">SK</span>
                <span class="account-field-value">{{ Auth::user()->guruProfile->sk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">SPMT</span>
                <span class="account-field-value">{{ Auth::user()->guruProfile->spmt ?? '-' }}</span>
            </div>
        @endif
    </div>
</div>

<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Ganti Password</h3>
    </div>
    <form method="POST" action="{{ route('guru.password.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="current_password" class="form-label">Password Lama</label>
            <input type="password" id="current_password" name="current_password" class="form-input @error('current_password') form-input-error @enderror" required>
            @error('current_password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" id="password" name="password" class="form-input @error('password') form-input-error @enderror" required>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
        </div>

        <x-button type="submit">Ganti Password</x-button>
    </form>
</div>

<div class="card">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-button variant="danger" type="submit" class="w-full">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
        </x-button>
    </form>
</div>
@endsection
