@extends('layouts.guru')

@section('title', 'Akun - Guru')
@section('page-title', 'Akun')

@php $profile = $guru->guruProfile; @endphp

@section('content')
<div class="card" style="margin-bottom: var(--space-6);">
    <div style="display: flex; align-items: center; gap: var(--space-4);">
        <div class="avatar avatar-lg">
            @if($profile?->foto)
                <img src="{{ $profile->foto }}" alt="{{ $guru->name }}">
            @else
                {{ substr($guru->name, 0, 1) }}
            @endif
        </div>
        <div>
            <h2 style="font-size: var(--text-xl); font-weight: 600;">{{ $guru->name }}</h2>
            <p class="page-subtitle">NIP: {{ $guru->username }}{{ $profile?->jabatan ? ' - ' . $profile->jabatan : '' }}</p>
        </div>
    </div>
</div>

<div class="profile-grid" style="margin-bottom: var(--space-6);">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Pribadi</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
            <div class="account-field">
                <span class="account-field-label">Nama Lengkap</span>
                <span class="account-field-value">{{ $guru->name }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIP</span>
                <span class="account-field-value">{{ $guru->username }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIPPPK</span>
                <span class="account-field-value">{{ $profile?->nipppk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NUPTK</span>
                <span class="account-field-value">{{ $profile?->nuptk ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jenis Kelamin</span>
                <span class="account-field-value">{{ $profile?->jenis_kelamin === 'laki-laki' ? 'Laki-laki' : ($profile?->jenis_kelamin === 'perempuan' ? 'Perempuan' : '-') }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Agama</span>
                <span class="account-field-value">{{ $profile?->agama ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Tempat, Tanggal Lahir</span>
                <span class="account-field-value">{{ $profile?->tempat_lahir ?? '-' }}{{ $profile?->tanggal_lahir ? ', ' . $profile->tanggal_lahir->format('d M Y') : '' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">NIK</span>
                <span class="account-field-value">{{ $profile?->nik ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Status</span>
                @if($guru->status === 'aktif')
                    <x-badge variant="success">Aktif</x-badge>
                @else
                    <x-badge variant="danger">Nonaktif</x-badge>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Kepegawaian</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: var(--space-3);">
            <div class="account-field">
                <span class="account-field-label">Status Kepegawaian</span>
                <span class="account-field-value">{{ $profile?->status_kepegawaian ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Pangkat / Golongan</span>
                <span class="account-field-value">{{ $profile?->pangkat_golongan ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">Jabatan</span>
                <span class="account-field-value">{{ $profile?->jabatan ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT Golongan</span>
                <span class="account-field-value">{{ $profile?->tmt_golongan?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT CPNS</span>
                <span class="account-field-value">{{ $profile?->tmt_cpns?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT PNS / PPPK</span>
                <span class="account-field-value">{{ $profile?->tmt_pns_pppk?->format('d M Y') ?? '-' }}</span>
            </div>
            <div class="account-field">
                <span class="account-field-label">TMT SK Sekolah</span>
                <span class="account-field-value">{{ $profile?->tmt_sk_sekolah?->format('d M Y') ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Kontak</h3>
    </div>
    <div style="display: flex; flex-direction: column; gap: var(--space-3);">
        <div class="account-field">
            <span class="account-field-label">Alamat</span>
            <span class="account-field-value">{{ $profile?->alamat ?? '-' }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">No. HP</span>
            <span class="account-field-value">{{ $profile?->no_hp ?? '-' }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">Email</span>
            <span class="account-field-value">{{ $profile?->email ?? '-' }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">NPWP</span>
            <span class="account-field-value">{{ $profile?->npwp ?? '-' }}</span>
        </div>
        <div class="account-field">
            <span class="account-field-label">No. BPJS</span>
            <span class="account-field-value">{{ $profile?->no_bpjs ?? '-' }}</span>
        </div>
    </div>
</div>

@if($profile?->pendidikan && count($profile->pendidikan) > 0)
<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Pendidikan</h3>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Jenjang</th>
                    <th>Jurusan</th>
                    <th>Perguruan Tinggi / Sekolah</th>
                    <th>Tahun Lulus</th>
                    <th>Nomor Ijazah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profile->pendidikan as $p)
                    <tr>
                        <td>{{ $p->jenjang ?? '-' }}</td>
                        <td>{{ $p->jurusan ?? '-' }}</td>
                        <td>{{ $p->perguruan_tinggi ?? '-' }}</td>
                        <td>{{ $p->tahun_lulus ?? '-' }}</td>
                        <td>{{ $p->nomor_ijazah ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($profile?->sertifikasi && count($profile->sertifikasi) > 0)
<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Sertifikasi</h3>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>No. Sertifikat</th>
                    <th>No. NRG</th>
                    <th>Bidang Studi</th>
                    <th>Penyelenggara</th>
                </tr>
            </thead>
            <tbody>
                @foreach($profile->sertifikasi as $s)
                    <tr>
                        <td>{{ $s->status ?? '-' }}</td>
                        <td>{{ $s->no_sertifikat ?? '-' }}</td>
                        <td>{{ $s->no_nrg ?? '-' }}</td>
                        <td>{{ $s->bidang_studi ?? '-' }}</td>
                        <td>{{ $s->penyelenggara ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card" style="margin-bottom: var(--space-6);">
    <div class="card-header">
        <h3 class="card-title">Ganti Password</h3>
    </div>
    <form method="POST" action="{{ route('guru.password.update') }}">
        @csrf
        @method('PUT')

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="current_password" class="form-label">Password Lama</label>
            <input type="password" id="current_password" name="current_password" class="form-input @error('current_password') form-input-error @enderror" required>
            @error('current_password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" id="password" name="password" class="form-input @error('password') form-input-error @enderror" required>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: var(--space-6);">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
        </div>

        <x-button type="submit">Ganti Password</x-button>
    </form>
</div>

<div class="card">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-button variant="danger" type="submit" class="w-full">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Keluar
        </x-button>
    </form>
</div>
@endsection
