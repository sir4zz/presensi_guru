@extends('layouts.admin')

@section('title', 'Tambah Data Guru - Admin')
@section('page-title', 'Tambah Data Guru')

@section('content')
@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom: var(--space-4);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="page-header">
    <div>
        <p class="page-subtitle">Tambah data guru baru</p>
    </div>
    <x-button variant="secondary" href="{{ route('admin.guru.index') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </x-button>
</div>

<div class="card" style="max-width: 900px;">
    <form method="POST" action="{{ route('admin.guru.store') }}">
        @csrf
        @php $guru = null; @endphp
        @include('admin.guru._form_tabs', ['mode' => 'create'])
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.guru-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            switchTab('create', this.dataset.tab);
        });
    });
    switchTab('create', 'data_pribadi');
});
</script>
@endsection
