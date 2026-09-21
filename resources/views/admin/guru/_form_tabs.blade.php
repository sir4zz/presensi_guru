@php
    $profile = $guru->guruProfile ?? null;
    $p = isset($guru) ? $guru : null;
@endphp

<div class="guru-tabs-nav">
    <button type="button" class="guru-tab-btn active" data-tab="data_pribadi" onclick="switchTab('{{ $mode }}', 'data_pribadi')">Data Pribadi</button>
    <button type="button" class="guru-tab-btn" data-tab="kepegawaian" onclick="switchTab('{{ $mode }}', 'kepegawaian')">Kepegawaian</button>
    <button type="button" class="guru-tab-btn" data-tab="kontak" onclick="switchTab('{{ $mode }}', 'kontak')">Kontak</button>
    <button type="button" class="guru-tab-btn" data-tab="sosial_media" onclick="switchTab('{{ $mode }}', 'sosial_media')">Sosial Media</button>
    <button type="button" class="guru-tab-btn" data-tab="pendidikan" onclick="switchTab('{{ $mode }}', 'pendidikan')">Pendidikan</button>
    <button type="button" class="guru-tab-btn" data-tab="tugas" onclick="switchTab('{{ $mode }}', 'tugas')">Tugas</button>
    <button type="button" class="guru-tab-btn" data-tab="sertifikasi" onclick="switchTab('{{ $mode }}', 'sertifikasi')">Sertifikasi</button>
    <button type="button" class="guru-tab-btn" data-tab="kgb" onclick="switchTab('{{ $mode }}', 'kgb')">KGB</button>
    <button type="button" class="guru-tab-btn" data-tab="sk_pengangkatan" onclick="switchTab('{{ $mode }}', 'sk_pengangkatan')">SK Pengangkatan</button>
</div>

<div class="guru-tabs-content">
    {{-- Tab: Data Pribadi --}}
    <div class="guru-tab-content" data-tab="data_pribadi" style="display: block;">
        <div class="guru-card-inner">
            <div class="form-group" style="margin-bottom: var(--space-4);">
                <label class="form-label">Foto (opsional)</label>
                <div class="guru-photo-upload">
                    <div class="guru-photo-preview" id="{{ $mode }}_photo_preview">
                        @if($mode === 'edit' && $profile?->foto)
                            <img src="{{ $profile->foto }}" alt="Foto">
                        @else
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-text-light);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        @endif
                    </div>
                    <div style="display: flex; flex-direction: column; gap: var(--space-2); flex: 1;">
                        <input type="text" name="foto" class="form-input" placeholder="...atau tempel URL gambar" value="{{ $profile?->foto ?? '' }}" oninput="document.getElementById('{{ $mode }}_photo_preview').innerHTML = this.value ? '<img src=&quot;'+this.value+'&quot; alt=&quot;Foto&quot;>' : '<svg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 24 24&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; stroke-width=&quot;1.5&quot; style=&quot;color:var(--color-text-light);&quot;><rect x=&quot;3&quot; y=&quot;3&quot; width=&quot;18&quot; height=&quot;18&quot; rx=&quot;2&quot; ry=&quot;2&quot;/><circle cx=&quot;8.5&quot; cy=&quot;8.5&quot; r=&quot;1.5&quot;/><polyline points=&quot;21 15 16 10 5 21&quot;/></svg>'">
                        <p style="font-size: var(--text-xs); color: var(--color-text-muted);">JPG/PNG/WEBP maks. 2 MB, direkomendasikan persegi (1:1).</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Nama Lengkap *</label>
                <input type="text" name="name" class="form-input" value="{{ $p->name ?? '' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">NIP *</label>
                <input type="text" name="nip" class="form-input" value="{{ $p->username ?? '' }}" required inputmode="numeric">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">NIPPPK</label>
                <input type="text" name="nipppk" class="form-input" value="{{ $profile?->nipppk ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">NUPTK</label>
                <input type="text" name="nuptk" class="form-input" value="{{ $profile?->nuptk ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-select">
                    <option value="">Pilih</option>
                    <option value="laki-laki" {{ ($profile?->jenis_kelamin ?? '') === 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="perempuan" {{ ($profile?->jenis_kelamin ?? '') === 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Agama</label>
                <input type="text" name="agama" class="form-input" value="{{ $profile?->agama ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" class="form-input" value="{{ $profile?->tempat_lahir ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" class="form-input" value="{{ $profile?->tanggal_lahir?->format('Y-m-d') ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">NIK</label>
                <input type="text" name="nik" class="form-input" value="{{ $profile?->nik ?? '' }}" inputmode="numeric">
            </div>
            @if($mode === 'create')
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" required>
            </div>
            @endif
        </div>
        @if($mode === 'create')
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Konfirmasi Password *</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>
            <div></div>
        </div>
        @endif
        @if($mode === 'edit')
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" class="form-input">
            </div>
        </div>
        @endif
    </div>

    {{-- Tab: Kepegawaian --}}
    <div class="guru-tab-content" data-tab="kepegawaian" style="display: none;">
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Status Kepegawaian</label>
                <select name="status_kepegawaian" class="form-select">
                    <option value="">Pilih</option>
                    <option value="PNS" {{ ($profile?->status_kepegawaian ?? '') === 'PNS' ? 'selected' : '' }}>PNS</option>
                    <option value="PPPK" {{ ($profile?->status_kepegawaian ?? '') === 'PPPK' ? 'selected' : '' }}>PPPK</option>
                    <option value="Honorer" {{ ($profile?->status_kepegawaian ?? '') === 'Honorer' ? 'selected' : '' }}>Honorer</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Pangkat / Golongan</label>
                <input type="text" name="pangkat_golongan" class="form-input" value="{{ $profile?->pangkat_golongan ?? '' }}" placeholder="cth. Pembina Tk. I / IV/b">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Jabatan</label>
                <input type="text" name="jabatan" class="form-input" value="{{ $profile?->jabatan ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">TMT Golongan</label>
                <input type="date" name="tmt_golongan" class="form-input" value="{{ $profile?->tmt_golongan?->format('Y-m-d') ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">TMT CPNS</label>
                <input type="date" name="tmt_cpns" class="form-input" value="{{ $profile?->tmt_cpns?->format('Y-m-d') ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">TMT PNS / PPPK</label>
                <input type="date" name="tmt_pns_pppk" class="form-input" value="{{ $profile?->tmt_pns_pppk?->format('Y-m-d') ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">TMT SK Sekolah</label>
                <input type="date" name="tmt_sk_sekolah" class="form-input" value="{{ $profile?->tmt_sk_sekolah?->format('Y-m-d') ?? '' }}">
            </div>
            <div class="form-group" style="justify-content: flex-end;">
                <label style="display: flex; align-items: center; gap: var(--space-2); cursor: pointer; min-height: 36px;">
                    <input type="checkbox" name="aktif_ditampilkan" value="1" {{ ($profile?->aktif_ditampilkan ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                    <span class="form-label" style="margin: 0;">Aktif ditampilkan</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Tab: Kontak --}}
    <div class="guru-tab-content" data-tab="kontak" style="display: none;">
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-textarea" rows="3">{{ $profile?->alamat ?? '' }}</textarea>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">No. HP</label>
                <input type="text" name="no_hp" class="form-input" value="{{ $profile?->no_hp ?? '' }}" inputmode="numeric">
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" value="{{ $profile?->email ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">NPWP</label>
                <input type="text" name="npwp" class="form-input" value="{{ $profile?->npwp ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">No. Akta Lahir</label>
                <input type="text" name="no_akta_lahir" class="form-input" value="{{ $profile?->no_akta_lahir ?? '' }}">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: var(--space-4);">
            <label class="form-label">No. BPJS</label>
            <input type="text" name="no_bpjs" class="form-input" value="{{ $profile?->no_bpjs ?? '' }}">
        </div>
        <div class="form-group">
            <label class="form-label">Profil Singkat</label>
            <textarea name="profil_singkat" class="form-textarea" rows="3">{{ $profile?->profil_singkat ?? '' }}</textarea>
        </div>
    </div>

    {{-- Tab: Sosial Media --}}
    <div class="guru-tab-content" data-tab="sosial_media" style="display: none;">
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Instagram</label>
                <input type="text" name="social_instagram" class="form-input" value="{{ $profile?->social_instagram ?? '' }}" placeholder="username atau URL">
            </div>
            <div class="form-group">
                <label class="form-label">Facebook</label>
                <input type="text" name="social_facebook" class="form-input" value="{{ $profile?->social_facebook ?? '' }}" placeholder="username atau URL">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">X / Twitter</label>
                <input type="text" name="social_twitter" class="form-input" value="{{ $profile?->social_twitter ?? '' }}" placeholder="username atau URL">
            </div>
            <div class="form-group">
                <label class="form-label">TikTok</label>
                <input type="text" name="social_tiktok" class="form-input" value="{{ $profile?->social_tiktok ?? '' }}" placeholder="username atau URL">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">YouTube</label>
                <input type="text" name="social_youtube" class="form-input" value="{{ $profile?->social_youtube ?? '' }}" placeholder="URL">
            </div>
            <div class="form-group">
                <label class="form-label">LinkedIn</label>
                <input type="text" name="social_linkedin" class="form-input" value="{{ $profile?->social_linkedin ?? '' }}" placeholder="username atau URL">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Website</label>
                <input type="text" name="social_website" class="form-input" value="{{ $profile?->social_website ?? '' }}" placeholder="URL">
            </div>
            <div class="form-group">
                <label class="form-label">GitHub</label>
                <input type="text" name="social_github" class="form-input" value="{{ $profile?->social_github ?? '' }}" placeholder="username atau URL">
            </div>
        </div>
        <p style="font-size: var(--text-xs); color: var(--color-text-muted); margin-top: var(--space-3);">Cukup isi username tanpa tanda @. Akan tampil sebagai ikon media sosial di profil publik.</p>
    </div>

    {{-- Tab: Pendidikan --}}
    <div class="guru-tab-content" data-tab="pendidikan" style="display: none;">
        <div id="{{ $mode }}_pendidikan_list">
            @if($mode === 'edit' && $profile?->pendidikan)
                @foreach($profile->pendidikan as $idx => $pend)
                    <div class="guru-list-item guru-card-inner">
                        <div class="guru-list-item-header">
                            <strong>Pendidikan #{{ $idx + 1 }}</strong>
                            <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <input type="hidden" name="pendidikan[{{ $idx }}][id]" value="{{ $pend->id }}">
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Jenjang</label>
                                <select name="pendidikan[{{ $idx }}][jenjang]" class="form-select">
                                    <option value="">Pilih</option>
                                    @foreach(['SD','SMP','SMA/SMK','D3','S1','S2','S3'] as $j)
                                        <option value="{{ $j }}" {{ ($pend->jenjang ?? '') === $j ? 'selected' : '' }}>{{ $j }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jurusan</label>
                                <input type="text" name="pendidikan[{{ $idx }}][jurusan]" class="form-input" value="{{ $pend->jurusan ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Perguruan Tinggi / Sekolah</label>
                                <input type="text" name="pendidikan[{{ $idx }}][perguruan_tinggi]" class="form-input" value="{{ $pend->perguruan_tinggi ?? '' }}">
                            </div>
                        </div>
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Tahun Lulus</label>
                                <input type="text" name="pendidikan[{{ $idx }}][tahun_lulus]" class="form-input" value="{{ $pend->tahun_lulus ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tempat</label>
                                <input type="text" name="pendidikan[{{ $idx }}][tempat]" class="form-input" value="{{ $pend->tempat ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor Ijazah</label>
                                <input type="text" name="pendidikan[{{ $idx }}][nomor_ijazah]" class="form-input" value="{{ $pend->nomor_ijazah ?? '' }}">
                            </div>
                        </div>
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Tanggal Ijazah</label>
                                <input type="date" name="pendidikan[{{ $idx }}][tanggal_ijazah]" class="form-input" value="{{ $pend->tanggal_ijazah?->format('Y-m-d') ?? '' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-outline-add" onclick="addListItem('{{ $mode }}', 'pendidikan')">
            + Tambah Pendidikan
        </button>
    </div>

    {{-- Tab: Tugas --}}
    <div class="guru-tab-content" data-tab="tugas" style="display: none;">
        <div id="{{ $mode }}_tugas_list">
            @if($mode === 'edit' && $profile?->tugas)
                @foreach($profile->tugas as $idx => $t)
                    <div class="guru-list-item guru-card-inner">
                        <div class="guru-list-item-header">
                            <strong>Tugas #{{ $idx + 1 }}</strong>
                            <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <input type="hidden" name="tugas[{{ $idx }}][id]" value="{{ $t->id }}">
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Jenis</label>
                                <select name="tugas[{{ $idx }}][jenis]" class="form-select">
                                    <option value="Tugas Tambahan" {{ ($t->jenis ?? '') === 'Tugas Tambahan' ? 'selected' : '' }}>Tugas Tambahan</option>
                                    <option value="Tugas Pokok" {{ ($t->jenis ?? '') === 'Tugas Pokok' ? 'selected' : '' }}>Tugas Pokok</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Uraian</label>
                                <input type="text" name="tugas[{{ $idx }}][uraian]" class="form-input" value="{{ $t->uraian ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Jumlah Jam</label>
                                <input type="number" name="tugas[{{ $idx }}][jumlah_jam]" class="form-input" value="{{ $t->jumlah_jam ?? '' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-outline-add" onclick="addListItem('{{ $mode }}', 'tugas')">
            + Tambah Tugas
        </button>
    </div>

    {{-- Tab: Sertifikasi --}}
    <div class="guru-tab-content" data-tab="sertifikasi" style="display: none;">
        <div id="{{ $mode }}_sertifikasi_list">
            @if($mode === 'edit' && $profile?->sertifikasi)
                @foreach($profile->sertifikasi as $idx => $s)
                    <div class="guru-list-item guru-card-inner">
                        <div class="guru-list-item-header">
                            <strong>Sertifikasi #{{ $idx + 1 }}</strong>
                            <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <input type="hidden" name="sertifikasi[{{ $idx }}][id]" value="{{ $s->id }}">
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="sertifikasi[{{ $idx }}][status]" class="form-select">
                                    <option value="">Pilih</option>
                                    @foreach(['Sudah Sertifikasi','Belum Sertifikasi','Proses'] as $st)
                                        <option value="{{ $st }}" {{ ($s->status ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">No. Sertifikat</label>
                                <input type="text" name="sertifikasi[{{ $idx }}][no_sertifikat]" class="form-input" value="{{ $s->no_sertifikat ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">No. Peserta</label>
                                <input type="text" name="sertifikasi[{{ $idx }}][no_peserta]" class="form-input" value="{{ $s->no_peserta ?? '' }}">
                            </div>
                        </div>
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">No. NRG</label>
                                <input type="text" name="sertifikasi[{{ $idx }}][no_nrg]" class="form-input" value="{{ $s->no_nrg ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Bidang Studi</label>
                                <input type="text" name="sertifikasi[{{ $idx }}][bidang_studi]" class="form-input" value="{{ $s->bidang_studi ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Penyelenggara</label>
                                <input type="text" name="sertifikasi[{{ $idx }}][penyelenggara]" class="form-input" value="{{ $s->penyelenggara ?? '' }}">
                            </div>
                        </div>
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Tahun Lulus</label>
                                <input type="text" name="sertifikasi[{{ $idx }}][tahun_lulus]" class="form-input" value="{{ $s->tahun_lulus ?? '' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-outline-add" onclick="addListItem('{{ $mode }}', 'sertifikasi')">
            + Tambah Sertifikasi
        </button>
    </div>

    {{-- Tab: KGB --}}
    <div class="guru-tab-content" data-tab="kgb" style="display: none;">
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">No. SK KGB</label>
                <input type="text" name="no_sk_kgb" class="form-input" value="{{ $profile?->no_sk_kgb ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal SK</label>
                <input type="date" name="tanggal_sk_kgb" class="form-input" value="{{ $profile?->tanggal_sk_kgb?->format('Y-m-d') ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">Gaji Pokok</label>
                <input type="number" name="gaji_pokok" class="form-input" value="{{ $profile?->gaji_pokok ?? '' }}" min="0" step="any">
            </div>
            <div class="form-group">
                <label class="form-label">MKG</label>
                <input type="text" name="mkg" class="form-input" value="{{ $profile?->mkg ?? '' }}">
            </div>
        </div>
        <div class="guru-grid-2">
            <div class="form-group">
                <label class="form-label">TMT KGB Akhir</label>
                <input type="date" name="tmt_kgb_akhir" class="form-input" value="{{ $profile?->tmt_kgb_akhir?->format('Y-m-d') ?? '' }}">
            </div>
            <div class="form-group">
                <label class="form-label">TMT KGB Berikutnya</label>
                <input type="date" name="tmt_kgb_berikutnya" class="form-input" value="{{ $profile?->tmt_kgb_berikutnya?->format('Y-m-d') ?? '' }}">
            </div>
        </div>
    </div>

    {{-- Tab: SK Pengangkatan --}}
    <div class="guru-tab-content" data-tab="sk_pengangkatan" style="display: none;">
        <div id="{{ $mode }}_sk_pengangkatan_list">
            @if($mode === 'edit' && $profile?->skPengangkatan)
                @foreach($profile->skPengangkatan as $idx => $sk)
                    <div class="guru-list-item guru-card-inner">
                        <div class="guru-list-item-header">
                            <strong>SK Pengangkatan #{{ $idx + 1 }}</strong>
                            <button type="button" class="btn btn-ghost btn-sm btn-icon text-danger" title="Hapus" onclick="removeListItem(this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </div>
                        <input type="hidden" name="sk_pengangkatan[{{ $idx }}][id]" value="{{ $sk->id }}">
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Kategori</label>
                                <select name="sk_pengangkatan[{{ $idx }}][kategori]" class="form-select">
                                    @foreach(['SK Awal (Sekolah)','SK Kenaikan Pangkat','SK Pensiun','SK Mutasi'] as $kat)
                                        <option value="{{ $kat }}" {{ ($sk->kategori ?? '') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Nomor SK</label>
                                <input type="text" name="sk_pengangkatan[{{ $idx }}][nomor_sk]" class="form-input" value="{{ $sk->nomor_sk ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tanggal SK</label>
                                <input type="date" name="sk_pengangkatan[{{ $idx }}][tanggal_sk]" class="form-input" value="{{ $sk->tanggal_sk?->format('Y-m-d') ?? '' }}">
                            </div>
                        </div>
                        <div class="guru-grid-3">
                            <div class="form-group">
                                <label class="form-label">Pejabat</label>
                                <input type="text" name="sk_pengangkatan[{{ $idx }}][pejabat]" class="form-input" value="{{ $sk->pejabat ?? '' }}">
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" class="btn btn-outline-add" onclick="addListItem('{{ $mode }}', 'sk_pengangkatan')">
            + Tambah SK Pengangkatan
        </button>
    </div>
</div>

<div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6); padding-top: var(--space-4); border-top: 1px solid var(--color-border-light);">
    <x-button variant="secondary" onclick="document.getElementById('{{ $mode }}Modal').classList.remove('active')">Batal</x-button>
    <x-button type="submit">+ Simpan</x-button>
</div>
