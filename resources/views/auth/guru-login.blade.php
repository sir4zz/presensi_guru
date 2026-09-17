@extends('layouts.guest')

@section('title', 'Login Guru - Sistem Absensi Guru')
@section('subtitle', 'Masuk sebagai Guru')

@section('content')
<form method="POST" action="{{ route('guru.login') }}" class="guest-form">
    @csrf

    <input type="hidden" name="role" value="guru">

    <div class="form-group">
        <label for="nip" class="form-label">NIP</label>
        <input type="text" id="nip" name="username" class="form-input @error('username') form-input-error @enderror" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Masukkan NIP">
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
