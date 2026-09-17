@props([
    'emptyTitle' => 'Belum ada data',
    'emptyText' => '',
])

<div class="table-wrapper">
    <table class="table">
        {{ $slot }}
    </table>
</div>
