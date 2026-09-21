# Prompt - Ubah Format Absensi + Dinas Luar + Bukti Lampiran

> Copy-paste seluruh isi blok di bawah ini ke AI coding agent.

```text
Konteks: Proyek Laravel Monolith (Laravel 13, Blade + CSS + Vanilla JS, MariaDB, timezone Asia/Jakarta) di folder guru_presensi. Patuhi PRD.md: jangan tambah React/Vue/Inertia, business logic di backend, validasi via Form Request, file via Laravel Storage.

PENTING: JANGAN ubah bottom navigation guru. Tetap 5 item seperti sekarang di resources/views/layouts/guru.blade.php: [Beranda, Absensi, Riwayat, Kalender, Akun]. Jangan kurangi, jangan ganti jadi [Beranda, Absensi, Kedinasan, Laporan] seperti di foto.

Tugas: 1) Ubah format tampilan halaman Absensi seperti foto, 2) Tambah Dinas Luar, 3) Guru bisa kirim bukti lampiran file.

1. FORMAT HALAMAN ABSENSI (ikut foto, tapi nav tetap):
File acuan: resources/views/guru/attendance/create.blade.php
- Header tetap judul "Absensi".
- Tambah card atas 2 kolom: [DD-MM-YYYY] | [STATUS HARI: LIBUR / HARI KERJA]. Ambil dari holidays + AttendanceService::isRedDate().
- Tambah section "DISIPLIN PEGAWAI" (periode bulan berjalan + filter bulan):
  * Terlambat Datang (TD) : X Menit = sum(jam_masuk - present_until) hanya saat status=terlambat
  * Pulang Sebelum Waktu (PS) : X Menit = sum(checkout_start - jam_pulang) hanya jika pulang lebih awal
  * Konversi Jam (TD + PS / 60) : X Jam = (TD+PS)/60
  * Konversi Hari (TD + PS / 60 / 7) : X Hari = (TD+PS)/60/7
  * TMTB (Tidak Masuk Tanpa Berita) : X Hari = count status=alpha
  * Total (Konversi Hari + TMTB) : X Hari
- Style persis foto: label kiri, ":" tengah, nilai + satuan kanan, border tipis. Tanpa gradient.
- Flow Absen Masuk/Pulang (Selfie + GPS + radius + server-time + UNIQUE(guru_id,tanggal)) JANGAN dihapus, taruh di bawah kartu disiplin.
- Buat method AttendanceService::getDisciplineSummary(guru_id, bulan, tahun) agar reusable Dashboard/Laporan.

2. DINAS LUAR (tanpa ubah nav):
- Tambah enum AttendanceStatus::DinasLuar = 'dinas_luar', label "Dinas Luar", badge 'info'.
- Migration attendances: tambah keperluan_dinas TEXT nullable, surat_tugas_file VARCHAR nullable, dinas_verified_by INT nullable, dinas_verified_at TIMESTAMP nullable. Jika mau minimal, reuse kolom bukti_file + keterangan.
- Halaman Kedinasan sebagai halaman biasa (bukan menu bottom nav). Akses via tombol/card "Ajukan Dinas Luar" di halaman Absensi + Dashboard. Route: GET/POST /guru/kedinasan (name guru.kedinasan.*). Jangan tambah item bottom nav.
- Form: tanggal, keperluan, lokasi dinas, upload bukti (wajib). Tanpa validasi radius GPS (karena di luar), tapi tetap validasi login, role guru, guru aktif, waktu server, belum ada absensi di tanggal itu, selfie opsional. Simpan status=dinas_luar.
- Update attendance:mark-missing agar tidak buat TAK jika sudah ada dinas_luar/izin/sakit/libur.
- Admin: tampilkan DL di filter Dashboard, Data Absensi, Laporan, Export. Bisa verifikasi/tolak + audit log.

3. BUKTI LAMPIRAN FILE OLEH GURU:
- Di form Kedinasan + form Izin/Sakit: <input type="file" accept=".jpg,.jpeg,.png,.pdf">.
- Validasi backend: mime jpg,jpeg,png,pdf, max 5MB, random filename, simpan di storage/app/public/attendance/evidence via Laravel Storage. Wajib untuk izin/sakit/dinas_luar. Hapus file lama saat diganti/dihapus.
- Frontend Vanilla JS only: tampilkan nama file + ukuran, error jelas, disable tombol saat upload cegah double submit.
- Guru bisa lihat/hapus miliknya (dengan konfirmasi). Admin bisa lihat/download di admin/attendance/show.blade.php.
- Audit log: upload_bukti, hapus_bukti, verifikasi_dinas.

Acceptance:
- Nav tetap 5 item, tidak berubah.
- /guru/absensi tampil card tanggal + LIBUR/KERJA + hitungan TD/PS/Konversi/TMTB/Total benar.
- Guru bisa ajukan DL + upload PDF/JPG max 5MB, tersimpan di attendance/evidence.
- Admin lihat DL + bukti di tabel/detail/export, TAK tidak dibuat dobel.
- Mobile 320px rapi.
```
