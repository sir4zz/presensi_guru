@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md',
])

@php
    $sizeStyles = match($size) {
        'sm' => 'max-width: 360px;',
        'lg' => 'max-width: 640px;',
        'xl' => 'max-width: 900px;',
        default => '',
    };
@endphp

<div class="modal-overlay" id="{{ $id }}">
    <div class="modal" style="{{ $sizeStyles }}">
        <div class="modal-header">
            <h3 class="modal-title">{{ $title }}</h3>
            <button class="modal-close" onclick="document.getElementById('{{ $id }}').classList.remove('active')" aria-label="Tutup">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            {{ $slot }}
        </div>
    </div>
</div>
