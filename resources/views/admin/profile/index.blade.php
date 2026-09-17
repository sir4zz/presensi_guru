@extends('layouts.admin')

@section('title', 'Profil Admin - Admin')
@section('page-title', 'Profil Admin')

@section('content')
<div class="card" style="max-width: 480px;">
    <div class="card-header">
        <h3 class="card-title">Informasi Akun</h3>
    </div>
    <div class="account-info">
        <div class="account-field">
            <span class="account-field-label">Nama</span>
            <span class="account-field-value">{{ Auth::user()->name }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">Username</span>
            <span class="account-field-value">{{ Auth::user()->username }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">Role</span>
            <x-badge variant="info">{{ ucfirst(Auth::user()->role) }}</x-badge>
        </div>
    </div>
</div>

<div class="card" style="max-width: 480px; margin-top: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Ganti Password</h3>
    </div>
    <form method="POST" action="{{ route('admin.password.update') }}">
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
        <x-button>Ganti Password</x-button>
    </form>
</div>
@endsection
