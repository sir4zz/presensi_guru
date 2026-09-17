<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistem Absensi Guru'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .guest-layout {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--space-5);
            background-color: var(--color-bg);
        }
        .guest-card {
            width: 100%;
            max-width: 400px;
            background-color: var(--color-surface);
            border: 1px solid var(--color-border-light);
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            box-shadow: var(--shadow-md);
        }
        .guest-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: var(--space-8);
        }
        .guest-logo svg {
            width: 48px;
            height: 48px;
            margin-bottom: var(--space-3);
        }
        .guest-logo h1 {
            font-size: var(--text-xl);
            font-weight: 600;
        }
        .guest-logo p {
            font-size: var(--text-sm);
            color: var(--color-text-muted);
            margin-top: var(--space-1);
        }
        .guest-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }
        .guest-footer {
            text-align: center;
            margin-top: var(--space-6);
            font-size: var(--text-sm);
            color: var(--color-text-muted);
        }
        .guest-footer a {
            color: var(--color-primary);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="guest-layout">
        <div class="guest-card">
            <div class="guest-logo">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="12" fill="var(--color-primary)"/>
                    <path d="M12 15h6v18h-6V15zm9 0h6v18h-6V15zm9 0h6v18h-6V15z" fill="white" opacity="0.9"/>
                    <path d="M15 24h18" stroke="white" stroke-width="2.5"/>
                </svg>
                <h1>Sistem Absensi Guru</h1>
                <p>@yield('subtitle', 'Silakan masuk ke akun Anda')</p>
            </div>

            @if(session('error'))
                <div class="alert alert-danger" style="margin-bottom: var(--space-4);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</body>
</html>
