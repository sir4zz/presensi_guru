# PRD — Sistem Absensi Guru Laravel
## Redesign Modern • Soft Semantic Color • Admin Desktop First • Guru Mobile First

---

# 1. Ringkasan Produk

**Nama proyek:** Sistem Absensi Guru  
**Platform:** Web responsive  
**Arsitektur:** Laravel Monolith  
**Framework utama:** Laravel 13  
**Database:** MariaDB  
**Frontend:** Laravel Blade + CSS + Vanilla JavaScript  
**Interaksi dinamis:** Vanilla JavaScript / Fetch API bila diperlukan  
**Build tool:** Vite bawaan Laravel  
**Tidak menggunakan:** React, Vue, Next.js, Nuxt, Angular, Inertia, SPA terpisah, backend terpisah, atau framework frontend lain.  
**Timezone:** `Asia/Jakarta`  
**Bahasa UI:** Bahasa Indonesia

Sistem ini adalah redesign dan pengembangan ulang aplikasi PHP native `absensi_guru` menjadi aplikasi Laravel modern.

Semua fitur utama aplikasi lama dipertahankan, kemudian diperbaiki pada sisi:

- UI/UX.
- Struktur kode.
- keamanan.
- validasi.
- performa.
- responsivitas.
- dashboard.
- reporting.
- audit trail.
- konfigurasi.
- pengelolaan file.
- pengalaman absensi di perangkat mobile.

Prinsip utama:

> **Laravel menjadi satu-satunya framework aplikasi.**

Frontend tetap menggunakan Blade yang dirender Laravel, CSS biasa, dan Vanilla JavaScript. Tidak membuat frontend application terpisah.

---

# 2. Tujuan Produk

## 2.1 Tujuan utama

Membangun sistem absensi guru yang:

- cepat digunakan.
- sederhana.
- modern.
- profesional.
- tidak terlihat seperti template AI-generated.
- nyaman digunakan admin di desktop.
- nyaman digunakan guru di smartphone.
- memiliki validasi absensi yang kuat.
- mudah dipelihara oleh developer Laravel.
- memiliki laporan yang jelas.
- dapat dikembangkan di masa depan.

## 2.2 Prinsip UX

Desain harus terasa seperti **produk SaaS administrasi sekolah yang matang**, bukan dashboard template yang penuh card, gradient, dan warna.

Gunakan:

- whitespace yang cukup.
- typography yang jelas.
- hierarchy yang kuat.
- border tipis.
- radius moderat.
- shadow sangat ringan.
- ikon konsisten.
- micro-interaction seperlunya.
- layout sederhana.
- informasi penting diprioritaskan.

Hindari:

- gradient berlebihan.
- glassmorphism berlebihan.
- neon.
- terlalu banyak warna.
- card bertumpuk tanpa fungsi.
- emoji sebagai ikon UI.
- ilustrasi random.
- dekorasi yang tidak memiliki fungsi.
- efek animasi berlebihan.
- desain yang terlihat seperti hasil generator AI.

---

# 3. Prinsip Teknologi

## 3.1 Laravel Monolith

Semua bagian aplikasi berada dalam satu project Laravel:

```text
Laravel
├── Routing
├── Authentication
├── Authorization
├── Controllers
├── Models
├── Form Requests
├── Policies
├── Services
├── Jobs
├── Commands
├── Notifications
├── Blade Views
├── CSS
├── Vanilla JavaScript
├── Database
└── Storage
```

Tidak membuat:

```text
React frontend
Vue frontend
Next.js frontend
Node.js backend
Express API backend
separate REST API application
```

API endpoint hanya dibuat jika benar-benar dibutuhkan oleh fitur internal seperti AJAX/Fetch, bukan untuk membuat backend terpisah.

---

# 4. Design System

## 4.1 Filosofi warna

Gunakan **semantic color**, bukan warna berdasarkan komponen.

Warna utama aplikasi harus soft dan terbatas.

Palet dasar:

```text
Background
Surface
Surface Muted
Border
Text
Text Muted
Primary
Success
Warning
Danger
Info
```

Gunakan satu warna primary yang soft sebagai identitas aplikasi.

Contoh arah visual:

```text
Background  → off-white / soft neutral
Surface     → putih lembut
Text        → dark neutral
Muted       → gray neutral
Primary     → muted blue / slate-blue
Success     → soft green
Warning     → soft amber
Danger      → soft red
Info        → soft blue
```

Jangan menggunakan banyak warna primer sekaligus.

## 4.2 Semantic tokens

CSS variable wajib dibuat agar warna tidak tersebar di banyak file.

Contoh:

```css
:root {
    --color-bg: ...;
    --color-surface: ...;
    --color-surface-muted: ...;
    --color-border: ...;

    --color-text: ...;
    --color-text-muted: ...;

    --color-primary: ...;
    --color-primary-hover: ...;

    --color-success: ...;
    --color-warning: ...;
    --color-danger: ...;
    --color-info: ...;

    --radius-sm: ...;
    --radius-md: ...;
    --radius-lg: ...;

    --shadow-sm: ...;
}
```

Nilai warna final ditentukan saat implementasi berdasarkan konsistensi accessibility.

## 4.3 Status colors

Status absensi menggunakan semantic color:

| Status | Semantic |
|---|---|
| Hadir | success |
| Terlambat | warning |
| Izin | info |
| Sakit | info/danger-soft |
| Tidak Ada Keterangan | danger |
| Belum Absen | neutral |

Jangan memberikan warna berbeda-beda pada setiap card.

---

# 5. Typography

Gunakan typography yang bersih dan profesional.

Prioritas:

1. Readability.
2. Hierarchy.
3. Consistency.

Gunakan satu keluarga font utama.

Tidak menggunakan terlalu banyak font.

Heading:

- jelas.
- tidak terlalu besar.
- tidak terlalu bold.

Body:

- nyaman dibaca.
- line-height cukup.
- warna tidak terlalu hitam pekat.

---

# 6. Icon System

## 6.1 Requirement

Semua ikon UI menggunakan **SVG**.

Jangan menggunakan:

- emoji.
- icon berbasis teks.
- Unicode symbol sebagai ikon utama.
- gambar PNG untuk ikon navigasi.

## 6.2 SVG

Gunakan satu icon set SVG yang konsisten atau buat SVG inline sendiri.

SVG harus:

- memiliki `viewBox`.
- memiliki ukuran konsisten.
- mengikuti `currentColor`.
- dapat berubah warna melalui CSS.
- tidak memiliki style warna yang hard-coded jika tidak diperlukan.

Contoh:

```html
<svg viewBox="0 0 24 24" aria-hidden="true">
    ...
</svg>
```

Ikon harus memiliki makna yang jelas.

---

# 7. Admin UI — Desktop First

## 7.1 Layout

Admin dirancang **desktop first**.

Struktur:

```text
┌─────────────────────────────────────────────────────────┐
│ Sidebar │ Topbar                                         │
│         ├───────────────────────────────────────────────┤
│         │ Page Header                                    │
│         │                                                 │
│         │ Content                                         │
│         │                                                 │
│         │ Tables / Charts / Cards                        │
└─────────────────────────────────────────────────────────┘
```

## 7.2 Sidebar

Sidebar:

```text
Logo
Nama aplikasi
─────────────────
Dashboard
Absensi
Guru
Izin Absen
Laporan
Pengaturan
─────────────────
Profil Admin
Keluar
```

Sidebar:

- fixed/sticky.
- tidak terlalu lebar.
- icon SVG.
- active state halus.
- tidak menggunakan gradient.
- tidak penuh warna.

## 7.3 Topbar

Topbar menampilkan:

- breadcrumb/page title.
- tanggal hari ini jika diperlukan.
- notifikasi.
- profil admin.
- menu akun.

---

# 8. Admin Dashboard Interaktif

Dashboard admin tidak boleh hanya berisi angka.

## 8.1 Statistik utama

Tampilkan:

- Total Guru.
- Hadir.
- Terlambat.
- Izin.
- Sakit.
- Tidak Ada Keterangan.
- Belum Absen.
- Sudah Pulang.

## 8.2 Statistik harus interaktif

Card statistik dapat diklik untuk membuka data yang sesuai.

Contoh:

```text
Hadir
42 guru
↓ klik
Data Absensi → filter Hadir → hari ini
```

## 8.3 Grafik

Gunakan grafik secukupnya.

Minimal:

### Grafik tren absensi

Menampilkan tren:

```text
Hadir
Terlambat
Izin
Sakit
TAK
```

berdasarkan periode yang dipilih.

### Grafik distribusi

Menampilkan komposisi status absensi.

Jangan membuat dashboard penuh grafik.

## 8.4 Filter dashboard

Admin dapat memilih:

- Hari ini.
- 7 hari.
- Bulan ini.
- Bulan tertentu.

Jika periode diubah:

- statistik diperbarui.
- tabel diperbarui.
- grafik diperbarui.

Implementasi dapat menggunakan Vanilla JavaScript + Fetch ke route Laravel.

Tidak perlu membuat SPA.

## 8.5 Tabel status hari ini

Tampilkan:

```text
Guru
NIP
Status
Jam Masuk
Jam Pulang
Lokasi
Keterangan
```

Tambahkan pencarian dan filter.

---

# 9. Admin Data Guru

## 9.1 Table

Kolom:

```text
No
Nama
NIP
SK
SPMT
Status
Aksi
```

Aksi:

- Detail.
- Edit.
- Hapus.

## 9.2 Search

Search:

- Nama.
- NIP.

## 9.3 Filter

- Aktif.
- Nonaktif jika fitur status guru digunakan.

## 9.4 Detail Guru

Halaman detail guru menampilkan:

```text
Informasi Guru
Riwayat Absensi
Statistik Kehadiran
Dokumen
```

Tambahkan ringkasan:

```text
Total hadir
Terlambat
Izin
Sakit
TAK
```

---

# 10. Status Guru

Fitur ini ditambahkan karena diperlukan untuk data sekolah yang berubah.

Tambahkan:

```text
status
```

dengan minimal:

```text
aktif
nonaktif
```

Guru nonaktif:

- tidak dapat login.
- tidak muncul sebagai target absensi aktif.
- histori lama tetap tersimpan.

---

# 11. Admin Data Absensi

## 11.1 Table

Kolom:

```text
Tanggal
Guru
NIP
Status
Jam Masuk
Jam Pulang
Keterangan
Aksi
```

## 11.2 Filter

Minimal:

- tanggal.
- rentang tanggal.
- bulan.
- guru.
- status.
- belum pulang.
- belum absen.

## 11.3 Detail Absensi

Admin dapat melihat:

```text
Informasi Guru
Tanggal
Status
Jam Masuk
Jam Pulang
Foto Masuk
Foto Pulang
Koordinat Masuk
Koordinat Pulang
Jarak dari sekolah
Keterangan
Bukti
Created At
Updated At
```

## 11.4 Peta

Jika diperlukan, detail absensi dapat menampilkan posisi:

```text
Lokasi Sekolah
Lokasi Guru
Jarak
```

Tidak perlu membuat peta pada setiap tabel karena akan memperberat halaman.

---

# 12. Koreksi Absensi oleh Admin

Admin dapat mengoreksi:

- status.
- jam masuk.
- jam pulang.
- keterangan.
- bukti.

Namun setiap perubahan penting harus masuk ke **audit log**.

Contoh:

```text
Admin A mengubah status:
Tidak Ada Keterangan → Izin

Waktu:
16 September 2026 10:22

Alasan:
Surat izin diterima
```

Admin wajib mengisi alasan ketika melakukan koreksi manual terhadap data penting.

---

# 13. Audit Log

Fitur ini ditambahkan sebagai kebutuhan sistem administrasi.

Simpan aktivitas penting:

```text
login admin
logout
tambah guru
edit guru
hapus guru
ubah password
ubah status absensi
hapus absensi
upload bukti
hapus bukti
memberi izin
mencabut izin
import guru
export laporan
ubah pengaturan
```

Data:

```text
id
user/admin
action
module
target_type
target_id
description
old_values
new_values
ip_address
user_agent
created_at
```

Audit log hanya dapat dilihat admin yang memiliki izin.

---

# 14. Izin Absen Massal

Admin dapat:

- memilih beberapa guru.
- memilih tanggal.
- memberi izin.
- mencabut izin.

Tambahkan alasan:

```text
Alasan izin
```

Jangan hanya menyimpan flag izin tanpa konteks.

---

# 15. Pengaturan Sekolah

Fitur ini ditambahkan agar sistem tidak hard-code.

Admin dapat mengatur:

```text
Nama sekolah
Alamat sekolah
Logo sekolah
Latitude sekolah
Longitude sekolah
Radius absensi
Jam mulai
Batas Hadir
Batas Terlambat
Batas Absensi Masuk
Jam mulai pulang
```

Contoh:

```text
Batas Hadir       08:30
Batas Terlambat   09:00
Radius            200 meter
```

Semua aturan dibaca dari database/configuration terpusat.

---

# 16. Aturan Jam Absensi

Berdasarkan aplikasi PHP native:

| Waktu | Status |
|---|---|
| 07:00–08:30 | Hadir |
| 08:31–09:00 | Terlambat |
| Setelah 09:00 | Tidak dapat absen masuk |

Namun sistem Laravel harus membuat jam tersebut **configurable**.

## Validasi

Keputusan final harus dilakukan oleh backend.

JavaScript hanya membantu UI.

---

# 17. Absensi Masuk Guru

Guru melakukan:

```text
Buka halaman Absensi
↓
Cek status
↓
Aktifkan kamera
↓
Ambil selfie
↓
Ambil lokasi GPS
↓
Backend validasi
↓
Simpan absensi
↓
Tampilkan hasil
```

Backend memvalidasi:

- user authenticated.
- role guru.
- guru aktif.
- tanggal.
- waktu server.
- belum absen masuk.
- GPS.
- radius.
- foto.
- izin khusus.
- kondisi absensi.

---

# 18. Absensi Pulang

Syarat:

- sudah absen masuk.
- belum absen pulang.

Validasi:

- GPS.
- selfie.
- waktu server.
- ownership.
- record absensi hari ini.

---

# 19. GPS

Konfigurasi default:

```text
Latitude sekolah : -6.2011
Longitude sekolah: 106.393
Radius           : 200 meter
```

Nilai ini harus menjadi data konfigurasi, bukan hard-code.

## Perhitungan

Frontend:

```text
getCurrentPosition()
```

Backend:

```text
hitung jarak
```

Jika:

```text
distance <= radius
```

maka valid.

Jika:

```text
distance > radius
```

maka ditolak.

Simpan:

```text
latitude
longitude
distance
```

agar admin dapat mengetahui jarak ketika absensi dilakukan.

---

# 20. Selfie

Guru wajib melakukan selfie.

Simpan:

```text
foto_masuk
foto_pulang
```

Requirement:

- image only.
- MIME validation.
- max size configurable.
- random filename.
- Storage Laravel.
- tidak overwrite.
- metadata file yang diperlukan.
- file aman dari direct upload execution.

---

# 21. Pengelolaan File

Gunakan:

```text
Laravel Storage
```

Kategori:

```text
attendance/selfie
attendance/evidence
school/logo
```

Bukti izin/sakit:

```text
JPG
JPEG
PNG
PDF
```

Default max:

```text
5 MB
```

Buat validasi server.

Tambahkan cleanup:

- file lama ketika diganti.
- file terkait ketika record dihapus jika memang tidak diperlukan.
- jangan menghapus file yang masih direferensikan record lain.

---

# 22. Halaman Guru — Mobile First

Guru lebih sering menggunakan smartphone.

Desain harus dimulai dari:

```text
320px+
```

dan berkembang ke tablet/desktop.

## 22.1 Bottom Navigation

Gunakan bottom navigation modern.

Contoh:

```text
┌──────────────────────────────────┐
│                                  │
│          PAGE CONTENT            │
│                                  │
├──────────────────────────────────┤
│  Home   Absensi   Riwayat   Akun │
└──────────────────────────────────┘
```

Gunakan SVG.

Menu maksimal sekitar 4–5 item.

Active state:

- icon.
- label.
- semantic primary color.
- tidak menggunakan bubble/gradient berlebihan.

Bottom navigation:

- fixed.
- safe-area aware.
- tidak menutupi konten.
- nyaman disentuh.
- touch target minimal sekitar 44px.

---

# 23. Guru Dashboard

Dashboard mobile menampilkan informasi paling penting terlebih dahulu.

Urutan:

```text
Salam / nama guru
Tanggal
Status absensi hari ini
Aksi absensi
Ringkasan bulan
Riwayat terbaru
```

Jangan membuat dashboard terlalu panjang.

---

# 24. Kartu Status Hari Ini

Contoh informasi:

```text
Hari ini

Hadir
07:12
Pulang belum dilakukan

[Absen Pulang]
```

Jika belum absen:

```text
Belum Absen

[Absen Masuk]
```

Jika terlambat:

```text
Terlambat
08:47
```

---

# 25. Halaman Absensi Guru

Ini adalah halaman paling penting untuk mobile.

Tampilkan:

```text
Tanggal
Jam server
Status lokasi
Jarak sekolah
Preview kamera
Status selfie
Status absensi
```

Aksi utama dibuat sangat jelas.

Jangan menggunakan terlalu banyak tombol.

---

# 26. UX GPS dan Kamera

Saat proses berlangsung:

```text
Mengecek lokasi...
Mengambil foto...
Memvalidasi absensi...
Menyimpan absensi...
```

Jika gagal:

```text
GPS ditolak
Kamera tidak tersedia
Di luar radius
Sudah melakukan absensi
Waktu absensi sudah ditutup
```

Pesan harus:

- jelas.
- singkat.
- memberi solusi.

Contoh:

```text
Lokasi berada 350 meter dari sekolah.
Anda harus berada maksimal 200 meter dari lokasi sekolah.
```

---

# 27. Riwayat Guru

Mobile-first.

Filter:

```text
Bulan
```

Tampilan dapat berupa list/card compact:

```text
16 Sep 2026
Hadir
Masuk 07:12
Pulang 15:20
```

Pada desktop dapat berubah menjadi table.

---

# 28. Kalender Kehadiran

Fitur tambahan yang dibutuhkan untuk UX guru.

Tampilkan kalender bulanan dengan status:

```text
Hadir
Terlambat
Izin
Sakit
TAK
Belum ada data
```

Klik tanggal:

```text
detail absensi
```

Jangan hanya mengandalkan tabel.

---

# 29. Rekap Guru

Guru dapat melihat:

```text
Hadir
Terlambat
Izin
Sakit
TAK
Hari kerja
Persentase kehadiran
```

Persentase harus memiliki definisi yang jelas dan konsisten.

Contoh:

```text
Kehadiran =
(Hadir + Terlambat) / Hari Kerja × 100
```

Hari izin/sakit tidak dimasukkan sebagai hadir.

---

# 30. Ganti Password

Form:

```text
Password lama
Password baru
Konfirmasi password
```

Gunakan Laravel Hash.

Tidak pernah menyimpan password plaintext.

---

# 31. Notifikasi Sistem

Tambahkan notifikasi internal untuk event penting:

Admin:

- import selesai.
- export selesai jika proses async.
- koreksi absensi berhasil.
- error proses tertentu.

Guru:

- absensi berhasil.
- absensi gagal.
- lokasi di luar radius.
- absensi sudah dilakukan.
- waktu absensi sudah ditutup.

Notifikasi tidak perlu memakai layanan pihak ketiga pada MVP.

---

# 32. Empty State

Semua halaman data harus mempunyai empty state.

Contoh:

```text
Belum ada data absensi pada periode ini.
```

Jangan menampilkan tabel kosong tanpa penjelasan.

---

# 33. Loading State

Untuk operasi AJAX:

```text
Loading...
```

Button harus dinonaktifkan ketika request berlangsung untuk mencegah double submit.

---

# 34. Error State

Buat error UI yang konsisten:

```text
Validasi gagal
Tidak memiliki akses
Data tidak ditemukan
Terjadi kesalahan server
Upload gagal
GPS gagal
Kamera gagal
```

Jangan menampilkan exception Laravel kepada user production.

---

# 35. Confirmation

Operasi berbahaya harus memiliki konfirmasi:

- hapus guru.
- hapus absensi.
- hapus bukti.
- cabut izin.
- perubahan status penting.

Confirmation harus menjelaskan akibatnya.

---

# 36. Accessibility

Wajib:

- semantic HTML.
- label form.
- keyboard accessible.
- focus state.
- kontras warna yang memadai.
- aria-label untuk ikon-only button.
- status tidak hanya dibedakan berdasarkan warna.
- touch target nyaman pada mobile.

---

# 37. Database

## 37.1 Admin/User

Gunakan arsitektur authentication Laravel yang sederhana.

Direkomendasikan satu tabel:

```text
users
```

Field:

```text
id
name
username
password
role
status
remember_token
created_at
updated_at
```

Role:

```text
admin
guru
```

Untuk data khusus guru:

```text
guru_profiles
```

Field:

```text
id
user_id
nip
sk
spmt
created_at
updated_at
```

NIP unique.

---

# 38. Tabel absensis

```text
id
guru_id
tanggal
status
jam_masuk
jam_pulang

foto_masuk
foto_pulang

lat_masuk
lng_masuk
distance_masuk

lat_pulang
lng_pulang
distance_pulang

keterangan
bukti_file

created_at
updated_at
```

Constraint:

```text
UNIQUE(guru_id, tanggal)
```

---

# 39. Tabel izin_absens

```text
id
guru_id
tanggal
alasan
dibuat_oleh
created_at
updated_at
```

Constraint:

```text
UNIQUE(guru_id, tanggal)
```

---

# 40. Tabel school_settings

Contoh:

```text
id
school_name
school_address
school_logo
latitude
longitude
attendance_radius

work_start_time
present_until
late_until
checkout_start_time

created_at
updated_at
```

---

# 41. Tabel audit_logs

```text
id
user_id
action
module
target_type
target_id
description
old_values
new_values
ip_address
user_agent
created_at
```

---

# 42. Tabel holidays

Fitur tambahan yang penting untuk sistem absensi.

```text
id
tanggal
nama
jenis
is_active
created_at
updated_at
```

Contoh:

```text
Libur Nasional
Libur Sekolah
Kegiatan Sekolah
```

Jika tanggal merupakan hari libur:

- sistem tidak membuat TAK otomatis.
- absensi dapat dinonaktifkan.
- dashboard dapat menampilkan status libur.

---

# 43. Tabel work_schedules

Jika sekolah membutuhkan jadwal yang berbeda:

```text
id
nama
hari
jam_masuk
batas_hadir
batas_terlambat
jam_pulang
is_active
created_at
updated_at
```

Untuk MVP dapat menggunakan satu jadwal global.

Struktur tetap disiapkan agar sistem dapat dikembangkan.

---

# 44. Status Absensi

Gunakan PHP Enum:

```text
AttendanceStatus
```

Nilai:

```text
hadir
terlambat
izin
sakit
tidak_ada_keterangan
```

Database menggunakan string agar lebih fleksibel.

---

# 45. Tidak Ada Keterangan

Setelah cutoff:

```text
09:00
```

system menjalankan command:

```text
attendance:mark-missing
```

Proses:

```text
ambil semua guru aktif
↓
cek hari kerja
↓
cek hari libur
↓
cek izin
↓
cek absensi
↓
jika belum ada
↓
buat TAK
```

Tidak bergantung pada guru membuka halaman.

---

# 46. Laravel Scheduler

Gunakan scheduler Laravel.

Contoh konsep:

```text
Setiap hari setelah cutoff:
attendance:mark-missing
```

Timezone:

```text
Asia/Jakarta
```

---

# 47. Race Condition

Proses absensi harus aman dari double click/request ganda.

Gunakan:

- database transaction.
- unique constraint `(guru_id, tanggal)`.
- validasi backend.
- lock/query sesuai kebutuhan.

Jika dua request masuk bersamaan, database tetap harus mencegah duplikasi.

---

# 48. Security

Wajib:

- CSRF.
- Laravel authentication.
- authorization.
- middleware role.
- Policy.
- Form Request.
- password hashing.
- session regeneration.
- logout session invalidation.
- rate limiting.
- upload validation.
- file name random.
- ownership validation.
- server-side time validation.
- server-side GPS validation.
- mass assignment protection.
- SQL injection prevention melalui Eloquent/Query Builder.
- production error handling.

Jangan mempercayai:

```text
guru_id
status
jam
latitude
longitude
```

yang berasal dari browser.

Semua harus diverifikasi backend.

---

# 49. Anti Manipulasi Absensi

Sistem tidak dapat menjamin GPS spoofing 100%, tetapi harus mengurangi manipulasi.

Minimum:

- server time.
- backend radius validation.
- selfie.
- unique attendance.
- audit log.
- user ownership.
- IP logging.
- user-agent logging.

Jika ingin dikembangkan:

- device fingerprint ringan.
- anomaly detection.
- terlalu banyak perubahan lokasi.
- terlalu banyak percobaan gagal.

Fitur tersebut bukan MVP.

---

# 50. Import Excel

Admin dapat import guru.

Kolom:

```text
Nama
NIP
SK
SPMT
Password
```

Requirement:

- template.
- validasi.
- duplicate detection.
- password hash.
- import result.
- error per row.
- transaction/batch strategy.
- maximum file size.

Jika menggunakan package Excel, pilih package yang kompatibel dengan Laravel 13.

Package eksternal hanya digunakan bila benar-benar diperlukan.

---

# 51. Export Excel

Admin dapat memilih:

```text
Bulan
Tahun
```

Output minimal:

### Sheet Rekap

```text
No
Nama
NIP
1
2
3
...
H
TL
I
S
TAK
```

### Sheet Detail

```text
Nama
NIP
Tanggal
Status
Jam Masuk
Jam Pulang
Keterangan
```

### Sheet Lokasi

```text
Nama
Tanggal
Lat Masuk
Lng Masuk
Jarak Masuk
Lat Pulang
Lng Pulang
Jarak Pulang
Foto Masuk
Foto Pulang
```

---

# 52. Laporan Admin

Tambahkan halaman laporan terpisah.

Filter:

```text
periode
guru
status
```

Statistik:

```text
Total hari kerja
Hadir
Terlambat
Izin
Sakit
TAK
```

Admin dapat:

- melihat.
- print.
- export Excel.

---

# 53. Print Friendly

Halaman laporan memiliki mode print.

Saat print:

- sidebar hilang.
- navigation hilang.
- tombol hilang.
- hanya laporan yang dicetak.

Tidak perlu library PDF pada MVP jika browser print sudah cukup.

---

# 54. Dashboard Admin Responsive

Walaupun desktop-first, admin tetap harus usable pada:

- tablet.
- laptop kecil.
- mobile.

Pada layar kecil:

- sidebar menjadi drawer.
- table dapat horizontal scroll.
- card statistik menjadi grid.
- filter menjadi stacked.

---

# 55. Dashboard Guru Responsive

Mobile-first.

Pada desktop:

- bottom navigation dapat tetap menjadi navigation bawah atau berubah menjadi layout yang lebih nyaman.
- jangan merusak pola navigasi mobile.

Prioritas tetap mobile.

---

# 56. Navigation Structure

## Admin

```text
Dashboard
Absensi
Guru
Izin Absen
Laporan
Pengaturan
Audit Log
Profil
Keluar
```

## Guru

Bottom navigation:

```text
Beranda
Absensi
Riwayat
Akun
```

---

# 57. Struktur Laravel

```text
app/
├── Enums/
│   └── AttendanceStatus.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── GuruController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── PermissionController.php
│   │   │   ├── ReportController.php
│   │   │   ├── ImportController.php
│   │   │   ├── ExportController.php
│   │   │   ├── SettingController.php
│   │   │   └── AuditLogController.php
│   │   │
│   │   └── Guru/
│   │       ├── DashboardController.php
│   │       ├── AttendanceController.php
│   │       ├── HistoryController.php
│   │       └── PasswordController.php
│   │
│   ├── Requests/
│   │   ├── Admin/
│   │   └── Guru/
│   │
│   └── Middleware/
│       └── RoleMiddleware.php
│
├── Models/
│   ├── User.php
│   ├── GuruProfile.php
│   ├── Attendance.php
│   ├── AttendancePermission.php
│   ├── SchoolSetting.php
│   ├── Holiday.php
│   ├── WorkSchedule.php
│   └── AuditLog.php
│
├── Services/
│   ├── AttendanceService.php
│   ├── AttendanceReportService.php
│   ├── GuruImportService.php
│   └── AuditLogService.php
│
└── Console/
    └── Commands/
        └── MarkMissingAttendance.php
```

---

# 58. Blade Structure

```text
resources/views/

layouts/
├── admin.blade.php
├── guru.blade.php
└── guest.blade.php

components/
├── button.blade.php
├── badge.blade.php
├── modal.blade.php
├── table.blade.php
├── empty-state.blade.php
├── alert.blade.php
└── bottom-navigation.blade.php

admin/
├── dashboard.blade.php
├── guru/
├── absensi/
├── izin/
├── laporan/
├── pengaturan/
└── audit-log/

guru/
├── dashboard.blade.php
├── absensi.blade.php
├── riwayat.blade.php
├── kalender.blade.php
└── akun.blade.php

auth/
```

---

# 59. CSS Architecture

Jangan menaruh semua CSS di satu file besar.

Gunakan:

```text
resources/css/
├── app.css
├── tokens.css
├── base.css
├── components.css
├── admin.css
└── guru.css
```

Tetap sederhana.

Tidak menggunakan CSS framework frontend tambahan jika requirement proyek adalah Laravel + Blade + CSS.

---

# 60. JavaScript Architecture

Gunakan Vanilla JavaScript.

```text
resources/js/
├── app.js
├── admin/
│   ├── dashboard.js
│   ├── attendance.js
│   └── guru.js
└── guru/
    ├── attendance.js
    └── dashboard.js
```

JavaScript hanya digunakan untuk:

- kamera.
- GPS.
- modal.
- filter interaktif.
- dashboard AJAX.
- countdown.
- UI state.
- form enhancement.

Business rule tetap di Laravel.

---

# 61. Kamera

Gunakan browser API:

```text
navigator.mediaDevices.getUserMedia()
```

Fallback:

```text
<input type="file" accept="image/*" capture="user">
```

Kamera hanya dipakai ketika diperlukan.

Jangan menjalankan kamera terus menerus di background.

---

# 62. GPS

Gunakan:

```text
navigator.geolocation
```

Frontend:

```text
request location
```

Backend:

```text
calculate distance
validate radius
```

Simpan distance agar dapat diaudit.

---

# 63. Dashboard Data Loading

Dashboard dapat menggunakan:

```text
Blade initial render
```

dan Fetch API untuk filter dinamis.

Contoh:

```text
GET /admin/dashboard/data?period=month
```

Response JSON sederhana.

Tidak membangun API platform terpisah.

---

# 64. Performance

Wajib:

- eager loading.
- pagination.
- database indexes.
- query optimization.
- lazy loading gambar jika diperlukan.
- kompres gambar selfie.
- resize gambar sebelum disimpan jika memungkinkan.
- cache settings.
- jangan load seluruh absensi ke browser.
- jangan membuat query per guru pada loop.

Index:

```text
users.username
guru_profiles.nip
attendance.guru_id
attendance.tanggal
attendance.status
(guru_id, tanggal)
attendance_permission.(guru_id, tanggal)
holidays.tanggal
```

---

# 65. Image Optimization

Karena selfie dapat membuat storage dan bandwidth besar:

Sistem dapat:

```text
upload
↓
validate
↓
resize
↓
compress
↓
store
```

Kualitas tetap harus cukup untuk kebutuhan verifikasi.

Simpan metadata jika diperlukan.

---

# 66. Pagination

Semua data besar menggunakan pagination:

- guru.
- absensi.
- audit log.
- laporan detail.

Jangan menampilkan ribuan record sekaligus.

---

# 67. Caching

Data yang cocok di-cache:

- school settings.
- work schedule.
- daftar holiday tertentu.

Setelah admin mengubah settings:

```text
cache invalidation
```

---

# 68. Testing

Minimal:

```text
AuthenticationTest
AuthorizationTest
GuruManagementTest
AttendanceCheckInTest
AttendanceCheckOutTest
AttendanceCutoffTest
AttendanceLocationTest
AttendanceDuplicateTest
AttendancePermissionTest
AttendanceEvidenceTest
MissingAttendanceTest
ImportGuruTest
ExportAttendanceTest
AuditLogTest
```

## Boundary test

```text
08:30 → Hadir
08:31 → Terlambat
09:00 → Terlambat
09:01 → ditolak
```

## Radius

```text
200m → valid
201m → ditolak
```

---

# 69. Test Ownership

Wajib:

```text
Guru A tidak dapat melihat absensi Guru B.
Guru A tidak dapat mengubah absensi Guru B.
Guru tidak dapat mengubah status sendiri.
Guru tidak dapat mengakses halaman admin.
Admin dapat mengakses data administrasi.
```

---

# 70. Test Hari Libur

Jika tanggal masuk `holidays`:

- tidak membuat TAK.
- absensi dapat dinonaktifkan.
- dashboard menunjukkan hari libur.

---

# 71. Test Scheduler

Command:

```text
attendance:mark-missing
```

harus:

- hanya memproses guru aktif.
- hanya memproses hari kerja.
- mengecualikan hari libur.
- mengecualikan guru dengan izin.
- mengecualikan guru yang sudah punya record.
- tidak membuat duplikasi.

---

# 72. Backup dan Recovery

Fitur operasional yang dibutuhkan untuk production:

- backup database berkala.
- backup file selfie/bukti.
- dokumentasi restore.
- environment secret tidak masuk Git.
- `.env.example` tersedia.

Backup dapat ditangani di server/hosting, bukan harus menjadi fitur UI admin pada MVP.

---

# 73. Logging

Gunakan Laravel logging.

Log event penting:

```text
attendance failed
upload failed
import failed
export failed
authentication failure
unexpected exception
```

Jangan menyimpan password di log.

---

# 74. Rate Limiting

Berikan rate limit untuk:

- login.
- endpoint absensi.
- upload.
- operasi sensitif.

Tujuan:

- mencegah brute force.
- mencegah spam request.
- mencegah double submission.

---

# 75. Session

Login:

```text
authenticate
↓
regenerate session
```

Logout:

```text
logout
↓
invalidate session
↓
regenerate token
```

---

# 76. UX Login

Login harus sederhana.

Admin:

```text
Username
Password
Masuk
```

Guru:

```text
NIP
Password
Masuk
```

Jangan membuat login terlalu dekoratif.

---

# 77. Logo

Logo aplikasi harus dibuat sebagai SVG.

Requirement:

- sederhana.
- recognizable.
- satu bentuk utama.
- cocok pada light/dark jika dibutuhkan.
- tidak menggunakan icon random.
- tidak menggunakan emoji.
- tidak menggunakan banyak warna.

Logo dapat berupa simbol yang berkaitan dengan:

```text
attendance + school
```

Nama aplikasi ditampilkan sebagai wordmark terpisah.

---

# 78. Tidak Ada AI Slop

UI secara eksplisit dilarang menggunakan pola:

```text
gradient background
huge hero heading
glass cards everywhere
floating blobs
excessive shadows
random statistics
AI generated illustrations
too many rounded cards
rainbow status colors
```

Sebaliknya:

```text
clear hierarchy
functional layout
restrained colors
real data
consistent spacing
consistent icons
purposeful animation
```

---

# 79. Animation

Gunakan micro-animation saja:

- button hover.
- modal.
- drawer.
- tab.
- toast.
- loading.
- success state.

Durasi pendek.

Hindari:

- parallax.
- floating animation.
- bouncing cards.
- infinite decorative animation.

---

# 80. Toast / Feedback

Gunakan satu sistem feedback.

Contoh:

```text
✓ Absensi masuk berhasil disimpan.
```

atau:

```text
× Absensi gagal karena Anda berada di luar radius sekolah.
```

Tidak membuat alert dengan banyak style berbeda.

---

# 81. PWA

PWA bukan bagian MVP.

Jika nanti diperlukan:

- installable.
- offline shell.
- push notification.

Namun **absensi tidak boleh dianggap berhasil ketika offline**.

GPS/selfie/validasi final tetap harus dilakukan server.

---

# 82. Fitur Tambahan yang Ditambahkan dari Analisis

Selain fitur aplikasi PHP native, sistem Laravel ini menambahkan:

1. Pengaturan sekolah.
2. Status aktif/nonaktif guru.
3. Detail guru.
4. Audit log.
5. Alasan koreksi absensi.
6. Kalender absensi.
7. Hari libur.
8. Jadwal kerja.
9. Dashboard interaktif.
10. Statistik periode.
11. Jarak GPS yang disimpan.
12. Image optimization.
13. Empty/loading/error state.
14. Accessibility.
15. Rate limiting.
16. Race-condition protection.
17. Print-friendly report.
18. Backup/recovery planning.
19. Centralized design tokens.
20. Responsive navigation khusus admin dan guru.

Fitur tersebut diperlukan agar aplikasi tidak hanya menjadi clone PHP native, tetapi menjadi sistem yang lebih siap digunakan dalam jangka panjang.

---

# 83. MVP

## Admin

- Login.
- Dashboard interaktif.
- CRUD guru.
- Detail guru.
- Data absensi.
- Filter.
- Koreksi absensi.
- Upload bukti.
- Izin massal.
- Laporan.
- Export Excel.
- Pengaturan sekolah.

## Guru

- Login.
- Dashboard.
- Absen masuk.
- GPS.
- Selfie.
- Absen pulang.
- Riwayat.
- Kalender.
- Rekap.
- Ganti password.

## System

- Authentication.
- Authorization.
- Scheduler.
- Audit log.
- Storage.
- Security.
- Responsive UI.

---

# 84. Phase Pengembangan

## Phase 1 — Foundation

```text
Laravel 13
MariaDB
Authentication
Database
Models
Migration
Seeder
Role
Design tokens
Blade layouts
```

## Phase 2 — Admin Core

```text
Dashboard
Guru
Absensi
Izin
Settings
```

## Phase 3 — Guru Core

```text
Dashboard
GPS
Selfie
Masuk
Pulang
Riwayat
Kalender
```

## Phase 4 — Reporting

```text
Import
Export
Report
Print
```

## Phase 5 — System Reliability

```text
Audit log
Scheduler
Holiday
Work schedule
Logging
Rate limiting
Optimization
```

## Phase 6 — Testing

```text
Feature test
Security test
Mobile test
Desktop test
Browser test
Boundary test
```

## Phase 7 — Production

```text
Environment
Database
Storage
Scheduler
Queue jika diperlukan
Backup
SSL
Monitoring
```

---

# 85. Definition of Done

Proyek dianggap selesai apabila:

### Architecture

- [ ] Laravel 13.
- [ ] Satu project Laravel.
- [ ] Blade.
- [ ] CSS.
- [ ] Vanilla JavaScript.
- [ ] Tidak ada frontend framework terpisah.

### Design

- [ ] Soft color.
- [ ] Semantic color.
- [ ] Warna terbatas.
- [ ] Admin desktop-first.
- [ ] Guru mobile-first.
- [ ] Bottom navigation guru.
- [ ] SVG icon.
- [ ] SVG logo.
- [ ] Tidak AI-slop.
- [ ] Responsive.
- [ ] Accessible.

### Admin

- [ ] Login.
- [ ] Dashboard interaktif.
- [ ] CRUD guru.
- [ ] Search.
- [ ] Filter.
- [ ] Detail guru.
- [ ] Data absensi.
- [ ] Koreksi.
- [ ] Bukti.
- [ ] Izin massal.
- [ ] Laporan.
- [ ] Import.
- [ ] Export.
- [ ] Settings.
- [ ] Audit log.

### Guru

- [ ] Login NIP.
- [ ] Dashboard.
- [ ] Absensi masuk.
- [ ] GPS.
- [ ] Selfie.
- [ ] Validasi radius.
- [ ] Absensi pulang.
- [ ] Riwayat.
- [ ] Kalender.
- [ ] Rekap.
- [ ] Password.

### Attendance

- [ ] Server time.
- [ ] Hadir.
- [ ] Terlambat.
- [ ] Cutoff.
- [ ] TAK otomatis.
- [ ] Izin.
- [ ] Sakit.
- [ ] Bukti.
- [ ] GPS.
- [ ] Selfie.
- [ ] Anti duplicate.
- [ ] Audit koreksi.

### System

- [ ] Hari libur.
- [ ] Jadwal kerja.
- [ ] Scheduler.
- [ ] Logging.
- [ ] Rate limiting.
- [ ] Image optimization.
- [ ] Pagination.
- [ ] Database indexes.
- [ ] Backup strategy.

---

# 86. Prinsip Implementasi Final

Developer/AI agent yang mengerjakan proyek ini harus mengikuti urutan:

```text
Database
↓
Models & Relations
↓
Enums
↓
Authentication
↓
Authorization
↓
Form Requests
↓
Services
↓
Controllers
↓
Routes
↓
Blade Layout
↓
Design System
↓
Admin UI
↓
Guru UI
↓
GPS & Camera
↓
Reporting
↓
Scheduler
↓
Audit Log
↓
Testing
↓
Optimization
```

Jangan langsung membuat UI sebelum struktur database dan business logic jelas.

Jangan menaruh business logic absensi di Blade atau JavaScript.

Semua aturan penting harus dapat diuji dari backend Laravel.

---

# 87. Prinsip Produk

Sistem harus terasa:

```text
Simple
Calm
Professional
Fast
Reliable
Consistent
```

Bukan:

```text
Colorful
Over-designed
Template-like
AI-generated
```

Prioritas:

```text
Keamanan
↓
Ketepatan absensi
↓
Kemudahan penggunaan
↓
Kejelasan data
↓
Performa
↓
Visual
```

---

# 88. Target Akhir

Hasil akhir adalah aplikasi absensi guru Laravel monolith dengan:

```text
             LARAVEL 13
                  │
       ┌──────────┴──────────┐
       │                     │
     ADMIN                  GURU
 Desktop First           Mobile First
       │                     │
 Sidebar + Topbar       Bottom Navigation
       │                     │
 Dashboard               Dashboard
 Interaktif              Absensi
 Guru                    GPS
 Absensi                 Selfie
 Laporan                 Riwayat
 Settings                Kalender
 Audit Log               Akun
       │                     │
       └──────────┬──────────┘
                  │
             MariaDB
                  │
             Laravel
              Storage
```

Tidak ada aplikasi frontend terpisah.

Tidak ada backend terpisah.

Tidak ada React/Vue/Next.js.

Seluruh aplikasi dikembangkan sebagai satu project **Laravel 13 + Blade + CSS + Vanilla JavaScript + MariaDB**.
