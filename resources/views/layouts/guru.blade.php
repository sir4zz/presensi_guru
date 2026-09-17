<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistem Absensi Guru'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="nprogress-bar"></div>
    <div id="page-skeleton">
        <div style="padding: var(--space-4);">
            <div class="skel-card" style="margin-bottom: var(--space-4);">
                <div class="skel-bar skel-bar-md" style="margin-bottom: var(--space-4); height: 18px;"></div>
                <div class="skel-row"><div class="skel-bar skel-bar-lg" style="height:48px"></div></div>
            </div>
            <div class="skel-card" style="margin-bottom: var(--space-4);">
                <div class="skel-row">
                    <div><div class="skel-bar skel-bar-sm" style="margin-bottom:8px"></div><div class="skel-bar skel-bar-lg" style="height:32px"></div></div>
                    <div><div class="skel-bar skel-bar-sm" style="margin-bottom:8px"></div><div class="skel-bar skel-bar-lg" style="height:32px"></div></div>
                </div>
            </div>
            <div class="skel-card">
                <div class="skel-bar skel-bar-lg" style="height: 120px;"></div>
            </div>
        </div>
    </div>

    <div class="guru-layout">
        <header class="guru-topbar no-print">
            <h1 class="guru-topbar-title">@yield('page-title', 'Beranda')</h1>
            @yield('topbar-right')
        </header>

        <div class="guru-page-content">
            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: var(--space-4);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" style="margin-bottom: var(--space-4);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>

        <nav class="bottom-nav no-print">
            <a href="{{ route('guru.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Beranda
            </a>
            <a href="{{ route('guru.attendance.create') }}" class="bottom-nav-item {{ request()->routeIs('guru.attendance.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Absensi
            </a>
            <a href="{{ route('guru.history.index') }}" class="bottom-nav-item {{ request()->routeIs('guru.history.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Riwayat
            </a>
            <a href="{{ route('guru.calendar.index') }}" class="bottom-nav-item {{ request()->routeIs('guru.calendar.*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Kalender
            </a>
            <a href="{{ route('guru.profile') }}" class="bottom-nav-item {{ request()->routeIs('guru.profile') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Akun
            </a>
        </nav>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    @stack('scripts')
</body>
</html>
