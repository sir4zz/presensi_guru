@extends('layouts.guru')

@section('title', 'Absensi - Guru')
@section('page-title', 'Absensi')

@section('content')
<div class="attendance-container">
    <div class="status-card {{ $todayAttendance?->status ?? 'belum' }}">
        <div class="status-card-date">{{ now()->translatedFormat('l, d F Y') }}</div>
        @if(isset($todayAttendance) && $todayAttendance)
            @if($todayAttendance->status === 'hadir' || $todayAttendance->status === 'terlambat')
                <div class="status-card-status">{{ ucfirst($todayAttendance->status) }}</div>
                <div class="status-card-time">Masuk: {{ \Carbon\Carbon::parse($todayAttendance->jam_masuk)->format('H:i') }}</div>
                @if(!$todayAttendance->jam_pulang)
                    <p style="margin-top: var(--space-3); font-size: var(--text-sm); color: var(--color-text-muted);">Anda sudah absen masuk. Silakan absen pulang.</p>
                @endif
            @else
                <div class="status-card-status">{{ ucfirst($todayAttendance->status) }}</div>
            @endif
        @else
            <div class="status-card-status">Belum Absen</div>
            <div class="status-card-time">Silakan absen masuk</div>
        @endif
    </div>

    <div class="attendance-camera" id="cameraContainer">
        <div class="attendance-camera-placeholder" id="cameraPlaceholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            <span style="font-size: var(--text-sm);">Ketuk untuk mengaktifkan kamera</span>
        </div>
        <video id="cameraPreview" style="display: none;" autoplay playsinline></video>
        <canvas id="cameraCanvas" style="display: none;"></canvas>
        <img id="capturedImage" style="display: none;" alt="Foto selfie">
    </div>

    <div class="attendance-info">
        <div class="attendance-info-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span class="label">Jam Server</span>
            <span class="value" id="serverTime">--:--</span>
        </div>
        <div class="attendance-info-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span class="label">Lokasi</span>
            <span class="value" id="locationStatus">Mengecek lokasi...</span>
        </div>
        <div class="attendance-info-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span class="label">Jarak</span>
            <span class="value" id="distanceStatus">Menghitung jarak...</span>
        </div>
    </div>

    <input type="hidden" id="latitude" name="latitude">
    <input type="hidden" id="longitude" name="longitude">
    <input type="hidden" id="distance" name="distance">

    @if(!isset($todayAttendance) || !$todayAttendance)
        <button class="btn btn-primary btn-lg attendance-btn" id="checkInBtn" disabled>
            Absen Masuk
        </button>
    @elseif(!isset($todayAttendance->jam_pulang) || !$todayAttendance->jam_pulang)
        <button class="btn btn-primary btn-lg attendance-btn" id="checkOutBtn" disabled>
            Absen Pulang
        </button>
    @endif

    <div id="statusMessage" style="display: none;" class="alert"></div>
</div>

<input type="file" id="fileInput" accept="image/*" capture="user" style="display: none;">
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cameraPlaceholder = document.getElementById('cameraPlaceholder');
    const cameraPreview = document.getElementById('cameraPreview');
    const cameraCanvas = document.getElementById('cameraCanvas');
    const capturedImage = document.getElementById('capturedImage');
    const fileInput = document.getElementById('fileInput');
    const checkInBtn = document.getElementById('checkInBtn');
    const checkOutBtn = document.getElementById('checkOutBtn');
    const statusMessage = document.getElementById('statusMessage');
    const serverTimeEl = document.getElementById('serverTime');
    const locationStatus = document.getElementById('locationStatus');
    const distanceStatus = document.getElementById('distanceStatus');

    let stream = null;
    let capturedBlob = null;
    let userLat = null;
    let userLng = null;

    // Update server time
    function updateServerTime() {
        const now = new Date();
        serverTimeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    setInterval(updateServerTime, 1000);
    updateServerTime();

    // Start camera
    cameraPlaceholder.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
            });
            cameraPreview.srcObject = stream;
            cameraPreview.style.display = 'block';
            cameraPlaceholder.style.display = 'none';
        } catch (err) {
            // Fallback to file input
            fileInput.click();
        }
    });

    // Handle file input fallback
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            capturedBlob = file;
            const reader = new FileReader();
            reader.onload = function(event) {
                capturedImage.src = event.target.result;
                capturedImage.style.display = 'block';
                cameraPreview.style.display = 'none';
                cameraPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });

    // Capture photo from video
    function capturePhoto() {
        if (stream) {
            cameraCanvas.width = cameraPreview.videoWidth;
            cameraCanvas.height = cameraPreview.videoHeight;
            const ctx = cameraCanvas.getContext('2d');
            ctx.drawImage(cameraPreview, 0, 0);
            cameraCanvas.toBlob(function(blob) {
                capturedBlob = blob;
                const url = URL.createObjectURL(blob);
                capturedImage.src = url;
                capturedImage.style.display = 'block';
                cameraPreview.style.display = 'none';
                stream.getTracks().forEach(track => track.stop());
            }, 'image/jpeg', 0.8);
        }
    }

    // Get location
    function getLocation() {
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    userLat = position.coords.latitude;
                    userLng = position.coords.longitude;
                    document.getElementById('latitude').value = userLat;
                    document.getElementById('longitude').value = userLng;
                    locationStatus.textContent = 'Lokasi ditemukan';

                    // Calculate distance to school (default: -6.2011, 106.393)
                    const schoolLat = {{ $schoolSettings->latitude ?? -6.2011 }};
                    const schoolLng = {{ $schoolSettings->longitude ?? 106.393 }};
                    const radius = {{ $schoolSettings->attendance_radius ?? 200 }};

                    const R = 6371000;
                    const dLat = (userLat - schoolLat) * Math.PI / 180;
                    const dLng = (userLng - schoolLng) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(schoolLat * Math.PI / 180) * Math.cos(userLat * Math.PI / 180) * Math.sin(dLng/2) * Math.sin(dLng/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    const distance = R * c;

                    document.getElementById('distance').value = Math.round(distance);
                    distanceStatus.textContent = Math.round(distance) + ' meter dari sekolah';

                    if (distance <= radius) {
                        distanceStatus.style.color = 'var(--color-success)';
                        if (checkInBtn) checkInBtn.disabled = false;
                        if (checkOutBtn) checkOutBtn.disabled = false;
                    } else {
                        distanceStatus.style.color = 'var(--color-danger)';
                        showStatus('danger', 'Anda berada di luar radius sekolah (' + Math.round(distance) + ' meter). Maksimal ' + radius + ' meter.');
                    }
                },
                function(err) {
                    locationStatus.textContent = 'GPS ditolak';
                    locationStatus.style.color = 'var(--color-danger)';
                    showStatus('danger', 'Aktifkan GPS untuk melakukan absensi.');
                }
            );
        } else {
            locationStatus.textContent = 'GPS tidak tersedia';
        }
    }

    getLocation();

    // Show status message
    function showStatus(type, message) {
        statusMessage.className = 'alert alert-' + type;
        statusMessage.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' + message;
        statusMessage.style.display = 'flex';
    }

    // Submit attendance
    function submitAttendance(type) {
        if (!capturedBlob) {
            showStatus('warning', 'Silakan ambil foto selfie terlebih dahulu.');
            return;
        }

        if (!userLat || !userLng) {
            showStatus('danger', 'Lokasi GPS belum tersedia. Aktifkan GPS Anda.');
            return;
        }

        const formData = new FormData();
        formData.append('latitude', userLat);
        formData.append('longitude', userLng);
        formData.append('distance', document.getElementById('distance').value);
        formData.append('selfie', capturedBlob, 'selfie.jpg');

        const url = type === 'checkin' ? '{{ route("guru.attendance.store") }}' : '{{ route("guru.attendance.checkout") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatus('success', data.message || 'Absensi berhasil disimpan.');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                showStatus('danger', data.message || 'Absensi gagal.');
            }
        })
        .catch(err => {
            showStatus('danger', 'Terjadi kesalahan. Silakan coba lagi.');
        });
    }

    if (checkInBtn) {
        checkInBtn.addEventListener('click', function() {
            capturePhoto();
            setTimeout(() => submitAttendance('checkin'), 500);
        });
    }

    if (checkOutBtn) {
        checkOutBtn.addEventListener('click', function() {
            capturePhoto();
            setTimeout(() => submitAttendance('checkout'), 500);
        });
    }
});
</script>
@endpush
