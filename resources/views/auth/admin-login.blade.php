@extends('layouts.guest')

@section('title', 'Login Admin - Sistem Absensi Guru')
@section('subtitle', 'Masuk sebagai Administrator')

@section('content')
<form method="POST" action="{{ route('admin.login') }}" class="guest-form">
    @csrf

    <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-input @error('username') form-input-error @enderror" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Masukkan username">
        @error('username')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-input @error('password') form-input-error @enderror" required autocomplete="current-password" placeholder="Masukkan password">
        @error('password')
            <span class="form-error">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-full">Masuk</button>
</form>
@endsection
