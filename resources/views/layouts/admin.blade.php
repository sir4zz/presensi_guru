<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Sistem Absensi Guru'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="nprogress-bar"></div>
    <div id="page-skeleton">
        <div style="padding: var(--space-6);">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--space-4); margin-bottom: var(--space-6);">
                <div class="skel-card"><div class="skel-bar skel-bar-sm" style="margin-bottom:8px"></div><div class="skel-bar skel-bar-md" style="height:24px"></div></div>
                <div class="skel-card"><div class="skel-bar skel-bar-sm" style="margin-bottom:8px"></div><div class="skel-bar skel-bar-md" style="height:24px"></div></div>
                <div class="skel-card"><div class="skel-bar skel-bar-sm" style="margin-bottom:8px"></div><div class="skel-bar skel-bar-md" style="height:24px"></div></div>
                <div class="skel-card"><div class="skel-bar skel-bar-sm" style="margin-bottom:8px"></div><div class="skel-bar skel-bar-md" style="height:24px"></div></div>
            </div>
            <div class="skel-card" style="margin-bottom: var(--space-4);">
                <div class="skel-bar skel-bar-md" style="margin-bottom: var(--space-4); height: 18px;"></div>
                <div class="skel-row"><div class="skel-bar skel-bar-lg"></div><div class="skel-bar skel-bar-lg"></div></div>
                <div class="skel-row"><div class="skel-bar skel-bar-lg"></div><div class="skel-bar skel-bar-lg"></div></div>
                <div class="skel-row"><div class="skel-bar skel-bar-lg"></div><div class="skel-bar skel-bar-lg"></div></div>
            </div>
        </div>
    </div>

    <div class="admin-layout">
        <aside class="sidebar no-print" id="sidebar">
            <div class="sidebar-header">
                <svg class="sidebar-logo" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="32" height="32" rx="8" fill="var(--color-primary)"/>
                    <path d="M8 10h4v12H8V10zm6 0h4v12h-4V10zm6 0h4v12h-4V10z" fill="white" opacity="0.9"/>
                    <path d="M10 16h12" stroke="white" stroke-width="2"/>
                </svg>
                <span class="sidebar-brand">Absensi Guru</span>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-section">
                    <div class="sidebar-section-title">Menu Utama</div>
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.attendance.index') }}" class="sidebar-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Absensi
                    </a>
                    <a href="{{ route('admin.guru.index') }}" class="sidebar-link {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Guru
                    </a>
                    <a href="{{ route('admin.permission.index') }}" class="sidebar-link {{ request()->routeIs('admin.permission.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Izin Absen
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">Lainnya</div>
                    <a href="{{ route('admin.report.index') }}" class="sidebar-link {{ request()->routeIs('admin.report.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                        Laporan
                    </a>
                    <a href="{{ route('admin.setting.index') }}" class="sidebar-link {{ request()->routeIs('admin.setting.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Pengaturan
                    </a>
                    <a href="{{ route('admin.audit-log.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit-log.*') ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Audit Log
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="sidebar-user-name">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="sidebar-user-role">Administrator</div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="admin-main">
            <header class="topbar no-print">
                <div class="topbar-left">
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                    </button>
                    <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="topbar-right">
                    <span class="topbar-date">{{ now()->translatedFormat('l, d F Y') }}</span>
                    <div class="dropdown">
                        <button class="btn btn-ghost btn-icon" id="userDropdown" aria-label="Menu akun">
                            <div class="avatar avatar-sm">
                                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                            </div>
                        </button>
                        <div class="dropdown-menu" id="userDropdownMenu">
                            <a href="{{ route('admin.profile') }}" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Profil
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item danger">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="page-content">
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
        </main>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script>
        function initPage() {
            // Sidebar toggle
            const menuToggle = document.getElementById('menuToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (menuToggle) {
                menuToggle.onclick = function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                };
            }

            if (sidebarOverlay) {
                sidebarOverlay.onclick = function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                };
            }

            // User dropdown
            const userDropdown = document.getElementById('userDropdown');
            const userDropdownMenu = document.getElementById('userDropdownMenu');

            if (userDropdown && userDropdownMenu) {
                userDropdown.onclick = function(e) {
                    e.stopPropagation();
                    userDropdownMenu.classList.toggle('show');
                };

                document.addEventListener('click', function() {
                    userDropdownMenu.classList.remove('show');
                });
            }

            // Auto-dismiss alerts
            document.querySelectorAll('.alert').forEach(function(alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.3s';
                    alert.style.opacity = '0';
                    setTimeout(function() { alert.remove(); }, 300);
                }, 5000);
            });
        }

        initPage();
    </script>

    @stack('scripts')
</body>
</html>
