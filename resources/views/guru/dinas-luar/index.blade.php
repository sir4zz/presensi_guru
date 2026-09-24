@extends('layouts.guru')
@section('title', 'Dinas Luar - Guru')
@section('page-title', 'Dinas Luar')
@section('content')
<div class="page-header"><div><p class="page-subtitle">Ajukan absensi untuk tugas di luar sekolah</p></div></div>
<div class="card" style="margin-bottom:var(--space-5)">
    <form method="POST" action="{{ route('guru.kedinasan.store') }}" enctype="multipart/form-data" data-upload-form>
        @csrf
        <div class="form-group" style="margin-bottom:var(--space-4)"><label class="form-label" for="tanggal">Tanggal</label><input class="form-input" type="date" name="tanggal" id="tanggal" min="{{ now()->toDateString() }}" value="{{ old('tanggal', now()->toDateString()) }}" required></div>
        <div class="form-group" style="margin-bottom:var(--space-4)"><label class="form-label" for="lokasi_dinas">Lokasi Dinas</label><input class="form-input" type="text" name="lokasi_dinas" id="lokasi_dinas" value="{{ old('lokasi_dinas') }}" required></div>
        <div class="form-group" style="margin-bottom:var(--space-4)"><label class="form-label" for="keperluan_dinas">Keperluan</label><textarea class="form-textarea" name="keperluan_dinas" id="keperluan_dinas" required>{{ old('keperluan_dinas') }}</textarea></div>
        <div class="form-group" style="margin-bottom:var(--space-5)"><label class="form-label" for="bukti_file">Bukti Lampiran *</label><input class="form-input" type="file" name="bukti_file" id="bukti_file" accept=".jpg,.jpeg,.png,.webp,.pdf" required><span class="form-help" id="fileInfo">JPG, PNG, WebP, atau PDF. Maksimal 5 MB.</span></div>
        @if($errors->any())<div class="alert alert-danger" style="margin-bottom:var(--space-4)">{{ $errors->first() }}</div>@endif
        <button class="btn btn-primary" type="submit">Kirim Pengajuan</button>
    </form>
</div>
<div class="card"><div class="card-header"><h3 class="card-title">Pengajuan Saya</h3></div>
@forelse($requests as $item)<div style="padding:var(--space-3) 0;border-bottom:1px solid var(--color-border-light);display:flex;justify-content:space-between;gap:var(--space-3);align-items:center"><div><strong>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</strong><div class="text-muted">{{ $item->lokasi_dinas }} · {{ $item->keperluan_dinas }}</div>@if($item->bukti_file)@if(\App\Services\FileUploadService::isPdfPath($item->bukti_file))<a href="{{ Storage::url($item->bukti_file) }}" target="_blank" class="btn btn-secondary btn-sm" style="margin-top:4px;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> Lihat PDF</a>@else<a href="{{ Storage::url($item->bukti_file) }}" target="_blank"><img src="{{ \App\Services\FileUploadService::thumbUrl($item->bukti_file) }}" alt="Bukti" loading="lazy" decoding="async" style="width:48px;height:48px;border-radius:var(--radius-md);object-fit:cover;margin-top:4px;"></a>@endif@endif</div><form method="POST" action="{{ route('guru.kedinasan.destroy', $item) }}" onsubmit="return confirm('Hapus pengajuan ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Hapus</button></form></div>@empty<p class="text-muted">Belum ada pengajuan.</p>@endforelse
</div>
@endsection
@push('scripts')<script>document.getElementById('bukti_file')?.addEventListener('change',function(){const f=this.files[0],i=document.getElementById('fileInfo');if(f)i.textContent=f.name+' ('+(f.size/1024/1024).toFixed(2)+' MB)';});document.querySelectorAll('[data-upload-form]').forEach(f=>f.addEventListener('submit',()=>f.querySelector('button[type=submit]').disabled=true));</script>@endpush
