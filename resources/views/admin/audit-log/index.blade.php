@extends('layouts.admin')

@section('title', 'Audit Log - Admin')
@section('page-title', 'Audit Log')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">Riwayat aktivitas sistem</p>
    </div>
</div>

<div class="filter-bar">
    <div class="search-input">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" class="form-input" placeholder="Cari aktivitas..." id="searchInput">
    </div>
    <select class="form-select" id="moduleFilter">
        <option value="">Semua Modul</option>
        <option value="auth">Autentikasi</option>
        <option value="guru">Guru</option>
        <option value="attendance">Absensi</option>
        <option value="permission">Izin</option>
        <option value="setting">Pengaturan</option>
    </select>
    <input type="date" class="form-input" id="dateFilter">
</div>

@if(isset($logs) && count($logs) > 0)
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Modul</th>
                    <th>Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td class="text-muted text-sm">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="font-medium">{{ $log->user->name ?? '-' }}</td>
                        <td>
                            <x-badge variant="{{ $log->action === 'delete' ? 'danger' : ($log->action === 'create' ? 'success' : 'info') }}">
                                {{ ucfirst($log->action) }}
                            </x-badge>
                        </td>
                        <td class="text-muted">{{ $log->module }}</td>
                        <td class="text-sm">{{ $log->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: var(--space-4); display: flex; justify-content: center;">
        {{ $logs->links() }}
    </div>
@else
    <x-empty-state title="Belum ada audit log" text="Aktivitas sistem akan tercatat di sini." />
@endif
@endsection
