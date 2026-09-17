@props([
    'title' => 'Belum ada data',
    'text' => '',
    'icon' => 'inbox',
])

<div class="empty-state">
    @if($icon === 'inbox')
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
    @elseif($icon === 'search')
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    @endif
    <div class="empty-state-title">{{ $title }}</div>
    @if($text)
        <div class="empty-state-text">{{ $text }}</div>
    @endif
</div>
