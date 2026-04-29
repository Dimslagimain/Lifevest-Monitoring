[![PHP](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml/badge.svg)](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml)
# Life Vest Tracker — GMF AeroAsia
### *Pemantauan Real-Time Cerdas untuk Keselamatan Armada yang Unggul*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?style=flat-square&logo=vite)](https://vitejs.dev)
[![UI](https://img.shields.io/badge/UX-Premium_Glassmorphism-00AEEF?style=flat-square)](https://gmf-aeroasia.co.id)

---

## Panduan Teknis (Developer)

### Prasyarat Sistem
- **PHP**: Versi 8.2 atau lebih tinggi
- **Composer**: Untuk mengelola paket ketergantungan (dependencies)
- **Node.js & NPM**: Untuk mengompilasi tampilan antarmuka (Vite)
- **Basis Data**: MySQL atau MariaDB (via Laragon, XAMPP, atau lainnya)

### Langkah Instalasi
```bash
# 1. Unduh dan Instal Dependensi
composer install
npm install

# 2. Pengaturan Lingkungan
cp .env.example .env
php artisan key:generate

# 3. Persiapan Basis Data (Sistem & Data Dummy)
php artisan migrate:fresh --seed

# 4. Kompilasi Aset Visual (Wajib sebelum production)
npm run build

# 5. Menjalankan Aplikasi
# Buka dua jendela terminal berbeda dan jalankan:
# Terminal 1:
php artisan serve

# Terminal 2 (Hanya untuk Development):
npm run dev
```
Akses aplikasi melalui: `http://localhost:8000`

---

## Struktur Inti Proyek (Core Structure)

Sistem ini diorganisir secara sistematis untuk memudahkan pemeliharaan jangka panjang:

```
lifevest-laravel/
├── app/Http/Controllers/              # Logika pengolah data, dashboard, manajemen user & log aktivitas
├── app/Models/                        # Skema database (Aircraft, Seat, User, ActivityLog)
├── config/aircraft_class_rows.php      # Pusat pengaturan baris bisnis/ekonomi layout
├── database/seeders/                  # Data awal registrasi pesawat, maskapai, & akses Role
├── resources/css/style.css            # Sistem desain utama (Premium UI & Dark Mode)
├── resources/js/app.js                # Interaksi peta kursi, optimasi zona waktu & navigasi SPA
├── resources/views/
│   ├── aircraft/                      # Template pembungkus tiap layout pesawat
│   ├── aircraft/partials/             # Konfigurasi teknis 16+ layout kursi (Seat Map)
│   ├── reports/                       # Template untuk Cetak PDF & Formulir Lapangan
│   └── components/                    # Komponen visual (Toolbar, Modal, Legend, Global Log)
└── dokumentasi/                       # Panduan manual lengkap (User & Developer)
```

---

## Ringkasan Proyek

Life Vest Tracker adalah ekosistem digital mutakhir yang dirancang khusus untuk tim Engineering dan Maintenance GMF AeroAsia. Sistem ini mengubah data keselamatan pesawat yang kompleks menjadi informasi visual yang siap ditindaklanjuti, memastikan setiap pesawat dalam armada dilengkapi dengan peralatan keselamatan yang patuh (compliant) dan aman.

Pembaruan terbaru telah menghadirkan **Keamanan Berbasis Peran (RBAC)**, **Integritas Data Log Global**, dan **Input Massal (Batch Input)** yang mampu menangani ratusan data kursi secara instan dengan optimasi *query* tinggi, menggantikan proses pencatatan manual yang rentan kesalahan.

---

## Panduan Penggunaan Lengkap

Penggunaan aplikasi ini dirancang sesederhana mungkin agar mudah dipahami oleh staf operasional dari berbagai kalangan usia.

### 1. Dashboard Utama (Command Center)
Dasbor menggunakan navigasi cerdas yang tidak memerlukan pemuatan ulang halaman (SPA). Segala informasi mengalir secara instan.

**Airline Master Deck (Level 1):**
Layar pertama menampilkan Kartu Maskapai dalam ukuran besar. Setiap kartu dilengkapi dengan diagram lingkaran (Donut Chart) yang menunjukkan profil kesehatan armada maskapai (Safe, Warning, Critical). Terdapat fitur "Smart Sorting" untuk mendahulukan maskapai dengan tingkat kerusakan tertinggi ke urutan paling atas.

**Airline Fleet Profile (Level 2):**
Mengklik kartu maskapai akan membawa Anda masuk ke daftar pesawat yang dimiliki maskapai tersebut. Pesawat dikelompokkan berdasarkan tipenya (A320, B737, dll.) dalam bentuk daftar yang bisa diciutkan (accordion) untuk kenyamanan pandangan.

### 2. Manajemen Akses & Aktivitas (RBAC & Logs)
Sistem dilengkapi dengan hierarki akses yang ketat:
- **Super Administrator**: Memiliki akses penuh, termasuk mengelola akun pengguna (menambah, membekukan/suspend, menghapus).
- **Admin (TNP)**: Dapat memodifikasi data pesawat, mengatur tanggal kursi, dan menggunakan fitur cetak/ekspor.
- **User (Viewer)**: Hanya dapat melihat status dan membaca laporan (akses *Read-Only*).

Semua tindakan modifikasi (Update, Delete, Suspend User) dicatat secara permanen di **Global Activity Log** demi transparansi dan kebutuhan audit (*Audit Trail*).

### 3. Peta Kursi Digital & Pembaruan Data
Fitur ini adalah tempat utama untuk melihat dan memperbarui data pelampung di pesawat.

**Cara Memilih Kursi (Multi-Select):**
- **Klik Biasa**: Memilih satu kursi (menghilangkan pilihan sebelumnya).
- **Ctrl + Klik**: Menambah kursi ke pilihan yang sudah ada (untuk banyak kursi sekaligus).
- **Shift + Klik**: Memilih sederetan kursi dari titik awal ke titik akhir.
- **Klik Header Baris/Kolom**: Memilih seluruh baris atau kolom secara instan.

**Cara Mengisi Tanggal Kedaluwarsa (Set Date):**
1. Pilih kursi-kursi yang ingin diperbarui.
2. Klik tombol "Set Date" di toolbar atas.
3. Pilih tanggal dari kalender yang muncul (wajib memiliki Role Admin/Superadmin).
4. Klik "Apply". Data akan tersimpan secara massal secara merata ke seluruh kursi terpilih (menimpa data lama).

**Input Massal (Bulk Import Data):**
Fitur ini tersedia di menu samping (Sidebar) khusus untuk mengunggah file Excel dalam format besar. Sangat berguna saat inisialisasi awal atau pembaruan database secara masif.

*Cara Menggunakan Bulk Import:*
1. Buka menu **Bulk Import** di Sidebar.
2. Pilih Kategori Data yang ingin diimpor (Aircraft, Seat/Life Vest, atau User Account).
3. Unduh **Template Excel Resmi** yang disediakan sistem (tombol akan muncul otomatis).
4. Isi data Anda mulai dari baris berikutnya. *Catatan: Baris contoh yang di-highlight merah bertuliskan `[CONTOH (DIABAIKAN)]` tidak perlu Anda hapus, karena sistem akan secara otomatis mengabaikannya.*
5. Unggah kembali file tersebut ke area Dropzone dan klik **Mulai Proses Import Data**.

*Penulisan Seat ID Fleksibel (Case-Insensitive & Auto-Format):*
Khusus untuk import template **Seat/Life Vest**, sistem dilengkapi dengan kecerdasan pembersihan data. Anda **Bebas** menggunakan gaya huruf besar/kecil sesukanya, karena sistem akan merapikannya secara otomatis sebelum disimpan ke database:
- **Kursi Biasa**: Tulis `6a` atau `6A` bebas saja, sistem otomatis menyimpannya dengan rapi menjadi huruf besar `6A`.
- **PAX / INF**: Mau tulis `PAX-1`, `Pax-1`, atau `pax-1` bebas, akan otomatis menjadi standar huruf kecil `pax-1`.
- **Attendant (Pramugari)**: Tidak perlu repot mengetik awalan `att/`.
  - Tulis `d11-ll` otomatis disempurnakan jadi `att/D11-LL`
  - Tulis `ATT/D11-LL` otomatis dirapikan jadi `att/D11-LL`
  - Tulis `d11-L` otomatis jadi `att/D11-L`

### 4. Pintasan Keyboard (Shortcuts)
Untuk mempercepat pekerjaan, gunakan tombol keyboard berikut:
- **Ctrl + A**: Memilih SEMUA kursi di pesawat tersebut.
- **Enter**: Membuka kotak pengisian tanggal (jika ada kursi yang sedang dipilih).
- **Escape (ESC)**: Menghapus semua pilihan kursi atau menutup kotak pesan yang aktif.

---

## Referensi Visual

### Arti Warna Status
| Warna | Status | Keterangan Kondisi |
| :--- | :--- | :--- |
| **Hijau** | Aman (Safe) | Masa berlaku masih di atas 6 bulan lagi |
| **Kuning** | Peringatan (Warning) | Masa berlaku tersisa antara 3 sampai 6 bulan |
| **Merah** | Kritis (Critical) | Masa berlaku tinggal kurang dari 3 bulan |
| **Ungu** | Kedaluwarsa (Expired) | Sudah melewati batas tanggal masa berlaku |
| **Abu-abu** | Tidak Ada Data | Tanggal masa berlaku belum dimasukkan ke sistem |

---

## Cetak Laporan dan Formulir Lapangan

Aplikasi mendukung pengunduhan laporan dalam berbagai format resmi perusahaan:

- **Export PDF**: Menghasilkan laporan peta kursi berwarna yang menunjukkan posisi pelampung dan tanggal kadaluwarsanya. Sangat berguna untuk arsip resmi.
- **Formulir Kosong (Blank Form)**: Desain khusus dengan kotak yang lebih besar untuk pencatatan manual oleh teknisi di lapangan. Sistem secara otomatis menyesuaikan jumlah "Spare Boxes" (Cadangan PAX & INF) sesuai tipe pesawat.
- **Export Excel**: Tersedia di log aktivitas global untuk pengunduhan log audit atau perencanaan suku cadang massal.

---

## Manajemen Armada dan Maskapai

Kelola data dasar melalui rute `/fleet` yang dibagi menjadi dua tab utama:

- **Tab Aircraft**: Melihat, memfilter (berdasarkan maskapai atau tipe), menambah, mengubah, dan menghapus registrasi pesawat.
- **Tab Airlines**: Mengelola daftar maskapai. Anda bisa menambah maskapai baru dengan mengisi Nama dan Kode IATA-nya.

### Penambahan Pesawat/Layout Baru
1. **Cara Utama**: Melalui Fleet Manager, pilih Maskapai, Registrasi, Tipe, dan Layout yang sudah tersedia di daftar pilihan.
2. **Layout Baru (Teknis)**: Jika ada konfigurasi kursi baru, admin dapat menyalin file template Blade di folder `aircraft/` dan menyesuaikan konfigurasi kursinya di folder `aircraft/partials/`. Sistem akan mendeteksi file baru tersebut secara otomatis di menu pilihan layout.

---

## Ikhtisar Armada Terpasang

Sistem saat ini mendukung berbagai tipe pesawat dengan cakupan luas:
- **B737-800 & MAX 8**: (40+ pesawat, layout e46-e49)
- **B777-300**: (8 pesawat, layout 2-Class & 3-Class)
- **A330-200/300/900**: (30+ pesawat, berbagai variasi layout & Cargo)
- **A320-200**: (50+ pesawat, layout a320a)
- **ATR72-600**: (layout atr72)

---

*© 2026 GMF AeroAsia — Life Vest Tracker — Engineering Excellence*
