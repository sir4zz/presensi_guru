@extends('layouts.admin')

@section('title', 'Edit Data Guru - Admin')
@section('page-title', 'Edit Data Guru')

@section('content')
<div class="page-header">
    <div>
        <p class="page-subtitle">{{ $guru->name }}</p>
    </div>
    <x-button variant="secondary" href="{{ route('admin.guru.index') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </x-button>
</div>

<div class="card" style="max-width: 900px;">
    <form method="POST" action="{{ route('admin.guru.update', $guru) }}">
        @csrf
        @method('PUT')
        @include('admin.guru._form_tabs', ['mode' => 'edit'])
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.guru-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            switchTab('edit', this.dataset.tab);
        });
    });
    switchTab('edit', 'data_pribadi');
});
</script>
@endsection
