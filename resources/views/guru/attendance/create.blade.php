@extends('layouts.guru')

@section('title', 'Absensi - Guru')
@section('page-title', 'Absensi')

@section('content')
<div class="attendance-container">

    <div class="attendance-day-card">
        <div><span class="attendance-day-label">Tanggal</span><strong>{{ now()->format('d-m-Y') }}</strong></div>
        <div><span class="attendance-day-label">Status Hari</span><strong class="{{ $redDate['is_red'] ? 'day-holiday' : 'day-work' }}">{{ $redDate['is_red'] ? 'LIBUR' : 'HARI KERJA' }}</strong></div>
    </div>

    {{-- STEP 0: Status Hari Ini --}}
    <div id="stepStatus">
        <div class="status-card {{ $todayAttendance?->status ?? 'belum' }}">
            <div class="status-card-date">{{ now()->translatedFormat('l, d F Y') }}</div>
            @if(isset($todayAttendance) && $todayAttendance && $todayAttendance->jam_masuk)
                @if($todayAttendance->status === 'hadir' || $todayAttendance->status === 'terlambat')
                    <div class="status-card-status">{{ ucfirst($todayAttendance->status) }}</div>
                    <div class="status-card-time">Masuk: {{ \Carbon\Carbon::parse($todayAttendance->jam_masuk)->format('H:i') }}
                        @if($todayAttendance->jam_pulang)
                            | Pulang: {{ \Carbon\Carbon::parse($todayAttendance->jam_pulang)->format('H:i') }}
                        @endif
                    </div>
                    @if(!$todayAttendance->jam_pulang)
                        <p style="margin-top: var(--space-3); font-size: var(--text-sm); color: var(--color-text-muted);">Absensi pulang: {{ $checkoutStart }}–{{ $checkoutEnd }} WIB (waktu server).</p>
                    @endif
                @else
                    <div class="status-card-status">{{ ucfirst($todayAttendance->status) }}</div>
                @endif
            @elseif(isset($todayAttendance) && $todayAttendance)
                <div class="status-card-status">{{ ucfirst($todayAttendance->status) }}</div>
            @else
                <div class="status-card-status">Belum Absen</div>
                <div class="status-card-time">Silakan absen masuk</div>
            @endif
        </div>

        @if(($redDate['is_red'] ?? false) && (!$todayAttendance || !$todayAttendance->jam_masuk))
            <div class="alert alert-danger" style="margin-top: var(--space-4);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Sistem absensi ditutup. Hari ini {{ $redDate['reason'] }}.
            </div>
        @elseif(!$todayAttendance || !$todayAttendance->jam_masuk)
            @if($canCheckIn)
                <button class="btn btn-primary btn-lg attendance-btn" id="startBtn" style="margin-top: var(--space-4);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    Mulai Absensi
                </button>
            @else
                <div class="alert alert-warning" style="margin-top: var(--space-4);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Absensi masuk sudah ditutup. Batas: {{ $lateUntil }} WIB
                </div>
            @endif
        @elseif(!$todayAttendance->jam_pulang)
            @if($canCheckout)
                <button class="btn btn-primary btn-lg attendance-btn" id="startBtn" style="margin-top: var(--space-4);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><polyline points="7 1 3 5 7 9"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Absen Pulang
                </button>
                <p style="margin-top: var(--space-2); font-size: var(--text-sm); color: var(--color-text-muted); text-align:center;">Jendela absensi pulang: {{ $checkoutStart }}–{{ $checkoutEnd }} WIB</p>
            @elseif($checkoutStatus === 'too_early')
                <button class="btn btn-primary btn-lg attendance-btn" id="startBtn" disabled aria-disabled="true" title="Absensi pulang belum dibuka" style="margin-top: var(--space-4); opacity:.55; cursor:not-allowed;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><polyline points="7 1 3 5 7 9"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Absen Pulang
                </button>
                <div class="alert alert-warning" style="margin-top: var(--space-3);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Absensi pulang belum dibuka. Mulai pukul {{ $checkoutStart }} WIB.
                </div>
            @else
                <button class="btn btn-primary btn-lg attendance-btn" id="startBtn" disabled aria-disabled="true" title="Waktu absensi sudah berakhir" style="margin-top: var(--space-4); opacity:.55; cursor:not-allowed;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><polyline points="7 1 3 5 7 9"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                    Absen Pulang
                </button>
                <div class="alert alert-danger" style="margin-top: var(--space-3);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    Waktu absensi pulang sudah berakhir (batas {{ $checkoutEnd }} WIB).
                </div>
            @endif
        @else
            <div class="alert alert-success" style="margin-top: var(--space-4);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Absensi pulang sudah dilakukan pukul {{ \Carbon\Carbon::parse($todayAttendance->jam_pulang)->format('H:i') }} WIB.
            </div>
            <div style="display:flex; gap:var(--space-2); margin-top:var(--space-4);">
                <a href="{{ route('guru.dashboard') }}" class="btn btn-secondary" style="flex:1; text-align:center;">Kembali ke Dashboard</a>
                <a href="{{ route('guru.history.index') }}" class="btn btn-primary" style="flex:1; text-align:center;">Lihat Riwayat</a>
            </div>
        @endif
    </div>

    {{-- STEP 1: Camera --}}
    <div id="stepCamera" style="display:none;">
        <div class="step-progress">
            <div class="step-progress-item active">
                <div class="step-progress-dot">1</div>
                <span>Selfie</span>
            </div>
            <div class="step-progress-line"></div>
            <div class="step-progress-item">
                <div class="step-progress-dot">2</div>
                <span>Lokasi</span>
            </div>
            <div class="step-progress-line"></div>
            <div class="step-progress-item">
                <div class="step-progress-dot">3</div>
                <span>Kirim</span>
            </div>
        </div>

        <div class="attendance-camera" id="cameraContainer">
            <video id="cameraPreview" autoplay playsinline muted></video>
            <canvas id="cameraCanvas" style="display:none;"></canvas>
            <img id="capturedImage" style="display:none;" alt="Selfie">
            <div class="attendance-camera-overlay" id="cameraOverlay">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:40px;height:40px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>Mengaktifkan kamera...</span>
            </div>
        </div>

        <div id="cameraLoading" class="step-loading">
            <span class="gps-spinner"></span> Mengaktifkan kamera...
        </div>

        <div id="cameraError" class="attendance-camera-error" style="display:none;"></div>

        <div id="cameraBtnCapture" style="display:none; margin-top: var(--space-3);">
            <button type="button" class="btn btn-primary btn-lg attendance-btn" id="captureBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                Ambil Foto
            </button>
        </div>

        <div id="cameraBtnConfirm" style="display:none; margin-top: var(--space-3); display:none; flex-direction:column; gap:var(--space-2);">
            <div class="step-status-text" style="color:var(--color-success); text-align:center;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;display:inline;vertical-align:middle;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Foto sudah diambil
            </div>
            <div style="display:flex; gap:var(--space-2);">
                <button type="button" class="btn btn-secondary" id="retakeBtn" style="flex:1;">Ambil Ulang</button>
                <button type="button" class="btn btn-primary" id="confirmPhotoBtn" style="flex:1;">Gunakan Foto</button>
            </div>
        </div>

        <input type="file" id="fileInput" accept="image/*" style="display:none;">
        <div id="fileFallback" style="display:none; margin-top:var(--space-3);">
            <button type="button" class="btn btn-secondary btn-lg attendance-btn" id="pickFileBtn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                Pilih Foto dari Galeri
            </button>
        </div>
    </div>

    {{-- STEP 2: GPS --}}
    <div id="stepGps" style="display:none;">
        <div class="step-progress">
            <div class="step-progress-item done">
                <div class="step-progress-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span>Selfie</span>
            </div>
            <div class="step-progress-line active"></div>
            <div class="step-progress-item active">
                <div class="step-progress-dot">2</div>
                <span>Lokasi</span>
            </div>
            <div class="step-progress-line"></div>
            <div class="step-progress-item">
                <div class="step-progress-dot">3</div>
                <span>Kirim</span>
            </div>
        </div>

        <div class="gps-status-card" id="gpsStatusCard">
            <div class="gps-status-row">
                <span class="gps-status-label" id="gpsStatusLabel">Mencari lokasi Anda...</span>
                <span class="gps-status-indicator" id="gpsStatusIndicator"><span class="gps-spinner"></span></span>
            </div>
            <div class="gps-status-row">
                <span class="gps-status-label">Akurasi GPS</span>
                <span class="gps-distance" id="gpsAccuracy">--</span>
            </div>
            <div class="gps-status-row">
                <span class="gps-status-label">Jarak dari sekolah</span>
                <span class="gps-distance" id="gpsDistance">--</span>
            </div>
            <div class="gps-status-row">
                <span class="gps-status-label">Radius sekolah</span>
                <span class="gps-radius">{{ $schoolSettings->attendance_radius }} meter</span>
            </div>
        </div>

        <div id="gpsError" class="attendance-camera-error" style="display:none;"></div>

        <div id="gpsNote" class="step-loading" style="font-size:var(--text-sm); color:var(--color-text-muted);">
            Pastikan GPS aktif dan berada di area terbuka.
        </div>
    </div>

    {{-- STEP 3: Ready to submit --}}
    <div id="stepReady" style="display:none;">
        <div class="step-progress">
            <div class="step-progress-item done">
                <div class="step-progress-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span>Selfie</span>
            </div>
            <div class="step-progress-line done"></div>
            <div class="step-progress-item done">
                <div class="step-progress-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span>Lokasi</span>
            </div>
            <div class="step-progress-line active"></div>
            <div class="step-progress-item active">
                <div class="step-progress-dot">3</div>
                <span>Kirim</span>
            </div>
        </div>

        <div class="step-ready-card">
            <div class="step-ready-title">Siap melakukan absensi</div>
            <div class="step-ready-row">
                <span>Selfie</span>
                <span class="step-ready-ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;color:var(--color-success);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    OK
                </span>
            </div>
            <div class="step-ready-row">
                <span>Lokasi</span>
                <span class="step-ready-ok">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;color:var(--color-success);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    OK
                </span>
            </div>
            <div class="step-ready-row">
                <span>Jarak</span>
                <span class="step-ready-value" id="readyDistance">--</span>
            </div>
            <div class="step-ready-row">
                <span>Akurasi</span>
                <span class="step-ready-value" id="readyAccuracy">--</span>
            </div>
        </div>

        <input type="hidden" id="latitude">
        <input type="hidden" id="longitude">
        <input type="hidden" id="accuracy">

        <button class="btn btn-primary btn-lg attendance-btn" id="submitBtn" style="margin-top:var(--space-4);">
            @if($isCheckIn)
                Absen Masuk
            @else
                Absen Pulang
            @endif
        </button>
    </div>

    {{-- STEP 4: Result --}}
    <div id="stepResult" style="display:none;">
        <div class="step-progress">
            <div class="step-progress-item done">
                <div class="step-progress-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span>Selfie</span>
            </div>
            <div class="step-progress-line done"></div>
            <div class="step-progress-item done">
                <div class="step-progress-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span>Lokasi</span>
            </div>
            <div class="step-progress-line done"></div>
            <div class="step-progress-item done">
                <div class="step-progress-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width:12px;height:12px;"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <span>Kirim</span>
            </div>
        </div>

        <div class="status-card" id="resultCard">
            <div class="status-card-status" id="resultStatus" style="color:var(--color-success);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px;display:block;margin:0 auto var(--space-3);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span id="resultTitle">Absensi Berhasil</span>
            </div>
            <div class="status-card-time" id="resultTime"></div>
            <div id="resultDetail" style="margin-top:var(--space-4); text-align:left; font-size:var(--text-sm); color:var(--color-text-muted);"></div>
            <div id="resultActions" style="display:flex; gap:var(--space-2); margin-top:var(--space-4);">
                <a href="{{ route('guru.dashboard') }}" class="btn btn-secondary" style="flex:1; text-align:center;">Kembali ke Dashboard</a>
                <a href="{{ route('guru.history.index') }}" class="btn btn-primary" style="flex:1; text-align:center;">Lihat Riwayat</a>
            </div>
        </div>
    </div>

    <div id="statusMessage" style="display:none;" class="alert"></div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var stepStatus = document.getElementById('stepStatus');
    var stepCamera = document.getElementById('stepCamera');
    var stepGps = document.getElementById('stepGps');
    var stepReady = document.getElementById('stepReady');
    var stepResult = document.getElementById('stepResult');

    var startBtn = document.getElementById('startBtn');
    var captureBtn = document.getElementById('captureBtn');
    var retakeBtn = document.getElementById('retakeBtn');
    var confirmPhotoBtn = document.getElementById('confirmPhotoBtn');
    var pickFileBtn = document.getElementById('pickFileBtn');
    var submitBtn = document.getElementById('submitBtn');

    var cameraPreview = document.getElementById('cameraPreview');
    var cameraCanvas = document.getElementById('cameraCanvas');
    var capturedImage = document.getElementById('capturedImage');
    var cameraOverlay = document.getElementById('cameraOverlay');
    var cameraLoading = document.getElementById('cameraLoading');
    var cameraError = document.getElementById('cameraError');
    var cameraBtnCapture = document.getElementById('cameraBtnCapture');
    var cameraBtnConfirm = document.getElementById('cameraBtnConfirm');
    var fileInput = document.getElementById('fileInput');
    var fileFallback = document.getElementById('fileFallback');

    var gpsStatusLabel = document.getElementById('gpsStatusLabel');
    var gpsStatusIndicator = document.getElementById('gpsStatusIndicator');
    var gpsAccuracy = document.getElementById('gpsAccuracy');
    var gpsDistance = document.getElementById('gpsDistance');
    var gpsError = document.getElementById('gpsError');
    var gpsNote = document.getElementById('gpsNote');

    var statusMessage = document.getElementById('statusMessage');

    var stream = null;
    var capturedBlob = null;
    var userLat = null;
    var userLng = null;
    var userAccuracy = null;

    var SCHOOL_LAT = {{ $schoolSettings->latitude }};
    var SCHOOL_LNG = {{ $schoolSettings->longitude }};
    var RADIUS = {{ $schoolSettings->attendance_radius }};
    var MAX_ACCURACY = {{ $schoolSettings->max_accuracy }};

    var isCheckIn = {{ $isCheckIn ? 'true' : 'false' }};
    var canCheckout = {{ $canCheckout ? 'true' : 'false' }};
    var checkoutStart = '{{ $checkoutStart }}';
    var checkoutEnd = '{{ $checkoutEnd }}';

    function showStep(step) {
        stepStatus.style.display = 'none';
        stepCamera.style.display = 'none';
        stepGps.style.display = 'none';
        stepReady.style.display = 'none';
        stepResult.style.display = 'none';
        step.style.display = 'block';
    }

    function showStatus(type, message) {
        statusMessage.className = 'alert alert-' + type;
        statusMessage.textContent = message;
        statusMessage.style.display = 'flex';
        clearTimeout(showStatus._timer);
        showStatus._timer = setTimeout(function() { statusMessage.style.display = 'none'; }, 8000);
    }

    function showCameraError(msg) {
        cameraError.textContent = msg;
        cameraError.style.display = 'block';
        cameraLoading.style.display = 'none';
    }

    // ===== START =====
    if (startBtn) {
        startBtn.addEventListener('click', function() {
            if (startBtn.disabled) {
                return;
            }
            if (!isCheckIn && !canCheckout) {
                showStatus('warning', 'Absensi pulang hanya ' + checkoutStart + '–' + checkoutEnd + ' WIB.');
                return;
            }
            showStep(stepCamera);
            startCamera();
        });
    }

    // ===== CAMERA =====
    function startCamera() {
        cameraError.style.display = 'none';
        cameraLoading.style.display = 'flex';
        cameraOverlay.querySelector('span').textContent = 'Mengaktifkan kamera...';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraError('Browser tidak mendukung kamera. Gunakan browser terbaru.');
            fileFallback.style.display = 'block';
            cameraLoading.style.display = 'none';
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false
        })
        .then(function(s) {
            stream = s;
            cameraPreview.srcObject = stream;
            cameraPreview.style.display = 'block';
            cameraOverlay.style.display = 'none';
            capturedImage.style.display = 'none';
            cameraLoading.style.display = 'none';
            cameraBtnCapture.style.display = 'block';
            cameraBtnConfirm.style.display = 'none';
        })
        .catch(function(err) {
            cameraLoading.style.display = 'none';
            if (err.name === 'NotAllowedError') {
                showCameraError('Akses kamera ditolak. Izinkan kamera pada browser HP Anda.');
            } else if (err.name === 'NotFoundError') {
                showCameraError('Tidak ada kamera ditemukan.');
            } else {
                showCameraError('Kamera tidak dapat diakses: ' + err.message);
            }
            fileFallback.style.display = 'block';
        });
    }

    captureBtn.addEventListener('click', function() {
        if (!stream) return;
        cameraCanvas.width = cameraPreview.videoWidth;
        cameraCanvas.height = cameraPreview.videoHeight;
        var ctx = cameraCanvas.getContext('2d');
        // Balik horizontal: stream kamera depan ter-mirror, dan preview
        // CSS sudah dibalik agar normal — samakan hasil simpanan dengan
        // yang dilihat pengguna (tidak mirror). Jalur galeri tidak
        // lewat sini sehingga foto galeri tetap apa adanya.
        ctx.translate(cameraCanvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(cameraPreview, 0, 0);
        cameraCanvas.toBlob(function(blob) {
            capturedBlob = blob;
            var url = URL.createObjectURL(blob);
            capturedImage.src = url;
            capturedImage.style.display = 'block';
            cameraPreview.style.display = 'none';
            stream.getTracks().forEach(function(t) { t.stop(); });
            stream = null;
            cameraBtnCapture.style.display = 'none';
            cameraBtnConfirm.style.display = 'flex';
        }, 'image/jpeg', 0.8);
    });

    retakeBtn.addEventListener('click', function() {
        capturedBlob = null;
        capturedImage.style.display = 'none';
        cameraBtnCapture.style.display = 'none';
        cameraBtnConfirm.style.display = 'none';
        cameraOverlay.style.display = 'flex';
        startCamera();
    });

    confirmPhotoBtn.addEventListener('click', function() {
        if (!capturedBlob) return;
        showStep(stepGps);
        startGPS();
    });

    pickFileBtn.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            var file = e.target.files[0];
            capturedBlob = file;
            var reader = new FileReader();
            reader.onload = function(ev) {
                capturedImage.src = ev.target.result;
                capturedImage.style.display = 'block';
                cameraPreview.style.display = 'none';
                cameraOverlay.style.display = 'none';
                cameraBtnCapture.style.display = 'none';
                cameraBtnConfirm.style.display = 'flex';
                cameraLoading.style.display = 'none';
                fileFallback.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    // ===== GPS =====
    function calculateDistance(lat1, lng1, lat2, lng2) {
        var R = 6371000;
        var dLat = (lat2 - lat1) * Math.PI / 180;
        var dLng = (lng2 - lng1) * Math.PI / 180;
        var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLng/2) * Math.sin(dLng/2);
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    function setGPSIcon(type) {
        if (type === 'ok') {
            gpsStatusIndicator.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;color:var(--color-success);"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
        } else if (type === 'fail') {
            gpsStatusIndicator.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;color:var(--color-danger);"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
        } else {
            gpsStatusIndicator.innerHTML = '<span class="gps-spinner"></span>';
        }
    }

    function startGPS() {
        if (!navigator.geolocation) {
            gpsError.textContent = 'Browser tidak mendukung GPS.';
            gpsError.style.display = 'block';
            setGPSIcon('fail');
            gpsStatusLabel.textContent = 'GPS tidak tersedia';
            return;
        }

        gpsStatusLabel.textContent = 'Mencari lokasi Anda...';
        gpsNote.style.display = 'block';
        setGPSIcon('loading');

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                userLat = pos.coords.latitude;
                userLng = pos.coords.longitude;
                userAccuracy = pos.coords.accuracy;

                var distance = calculateDistance(userLat, userLng, SCHOOL_LAT, SCHOOL_LNG);
                var dist = Math.round(distance);
                var acc = Math.round(userAccuracy);

                gpsAccuracy.textContent = '\u00b1' + acc + ' meter';
                gpsDistance.textContent = dist + ' meter';
                gpsNote.style.display = 'none';

                if (acc > MAX_ACCURACY) {
                    setGPSIcon('fail');
                    gpsStatusLabel.textContent = 'Akurasi GPS rendah';
                    gpsDistance.style.color = 'var(--color-warning)';
                    gpsAccuracy.style.color = 'var(--color-warning)';
                    gpsError.textContent = 'Akurasi GPS terlalu rendah (\u00b1' + acc + 'm). Aktifkan GPS akurasi tinggi dan pindah ke area terbuka.';
                    gpsError.style.display = 'block';
                    return;
                }

                if (dist <= RADIUS) {
                    setGPSIcon('ok');
                    gpsStatusLabel.textContent = 'Lokasi valid';
                    gpsDistance.style.color = 'var(--color-success)';
                    gpsAccuracy.style.color = 'var(--color-text)';
                    gpsError.style.display = 'none';

                    document.getElementById('latitude').value = userLat;
                    document.getElementById('longitude').value = userLng;
                    document.getElementById('accuracy').value = userAccuracy;
                    document.getElementById('readyDistance').textContent = dist + ' meter';
                    document.getElementById('readyAccuracy').textContent = '\u00b1' + acc + ' meter';

                    setTimeout(function() { showStep(stepReady); }, 800);
                } else {
                    setGPSIcon('fail');
                    gpsStatusLabel.textContent = 'Di luar area absensi';
                    gpsDistance.style.color = 'var(--color-danger)';
                    gpsAccuracy.style.color = 'var(--color-text)';
                    gpsError.textContent = 'Anda berada di luar area absensi. Jarak: ' + dist + ' meter (maks: ' + RADIUS + ' meter).';
                    gpsError.style.display = 'block';
                }
            },
            function(err) {
                gpsNote.style.display = 'none';
                setGPSIcon('fail');
                if (err.code === 1) {
                    gpsStatusLabel.textContent = 'Izin lokasi ditolak';
                    gpsError.textContent = 'Akses lokasi diperlukan. Izinkan browser mengakses lokasi Anda, kemudian coba lagi.';
                } else if (err.code === 2) {
                    gpsStatusLabel.textContent = 'Lokasi tidak tersedia';
                    gpsError.textContent = 'Lokasi belum ditemukan. Pastikan GPS aktif dan coba lagi.';
                } else {
                    gpsStatusLabel.textContent = 'Timeout';
                    gpsError.textContent = 'Lokasi belum ditemukan. Pastikan GPS aktif dan coba lagi.';
                }
                gpsError.style.display = 'block';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    // ===== SUBMIT =====
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            if (!capturedBlob || !userLat || !userLng) {
                showStatus('warning', 'Data belum lengkap. Silakan ulangi proses.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan absensi...';

            var formData = new FormData();
            formData.append('latitude', userLat);
            formData.append('longitude', userLng);
            formData.append('accuracy', userAccuracy || '');
            formData.append('selfie', capturedBlob, 'selfie.jpg');

            var url = isCheckIn ? '{{ route("guru.attendance.store") }}' : '{{ route("guru.attendance.checkout") }}';

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
            .then(function(result) {
                if (result.data.success) {
                    var detail = '';
                    if (isCheckIn && result.data.status_label) {
                        detail = '<div style="margin-top:var(--space-3);"><strong>Status:</strong> ' + result.data.status_label + '</div>';
                        document.getElementById('resultTitle').textContent = 'Absensi Masuk Berhasil';
                    }
                    if (!isCheckIn) {
                        var jamPulang = result.data.jam_pulang ? result.data.jam_pulang.substring(0, 5) : new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
                        detail = '<div style="margin-top:var(--space-3);"><strong>Absensi pulang:</strong> ' + jamPulang + ' WIB</div>';
                        document.getElementById('resultTitle').textContent = 'Absensi Pulang Berhasil';
                    }
                    document.getElementById('resultTime').textContent = 'Waktu: ' + new Date().toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) + ' WIB';
                    document.getElementById('resultDetail').innerHTML = detail;
                    // Kunci tombol agar tidak bisa absen dua kali.
                    submitBtn.disabled = true;
                    if (startBtn) { startBtn.disabled = true; }
                    showStep(stepResult);
                } else {
                    showStatus('danger', result.data.message || 'Absensi gagal.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = isCheckIn ? 'Absen Masuk' : 'Absen Pulang';
                }
            })
            .catch(function() {
                showStatus('danger', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
                submitBtn.disabled = false;
                submitBtn.textContent = isCheckIn ? 'Absen Masuk' : 'Absen Pulang';
            });
        });
    }
})();
</script>
@endpush
