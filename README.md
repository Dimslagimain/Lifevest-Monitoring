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
- **Basis Data**: MySQL atau MariaDB

### Langkah Instalasi
```bash
# 1. Unduh dan Instal Dependensi
composer install
npm install

# 2. Pengaturan Lingkungan
cp .env.example .env
php artisan key:generate

# 3. Persiapan Basis Data
# Pastikan nama DB_DATABASE sudah sesuai di file .env Anda
php artisan migrate --seed

# 4. Menjalankan Aplikasi
# Buka dua terminal berbeda untuk menjalankan perintah berikut:
php artisan serve
npm run dev
```

---

## Struktur Inti Proyek (Core Structure)

Berikut adalah folder dan file utama yang mengelola logika dan tampilan sistem ini:

- **config/aircraft_class_rows.php**: Pusat pengaturan baris bisnis dan ekonomi untuk setiap tipe pesawat.
- **app/Http/Controllers/**: Berisi logika utama sistem untuk pengolahan data dan laporan.
- **resources/views/fleet/**: Halaman untuk mengelola data armada dan maskapai (Fleet Manager).
- **resources/views/aircraft/partials/**: Konfigurasi khusus untuk lebih dari 16 layout kursi pesawat yang berbeda.
- **resources/css/style.css**: Sistem desain utama (Design System) yang mengelola warna dan gaya visual.
- **resources/js/app.js**: Logika untuk interaksi seat map seperti pemilihan kursi dan pencarian.

---

## Ringkasan Proyek

Life Vest Tracker adalah ekosistem digital mutakhir yang dirancang khusus untuk tim Engineering dan Maintenance GMF AeroAsia. Sistem ini mengubah data keselamatan pesawat yang kompleks menjadi informasi visual yang siap ditindaklanjuti, memastikan setiap pesawat dalam armada dilengkapi dengan peralatan keselamatan yang patuh (compliant) dan aman.

Aplikasi ini menggantikan proses pencatatan manual di kertas yang memakan waktu dan rentan kesalahan, beralih ke sistem digital yang rapi, otomatis, dan dapat dipantau secara langsung oleh manajemen.

---

## Penjelasan Fitur Lengkap

### 1. Dashboard Utama (Fleet Health)
Layar pertama yang memberikan gambaran "Kesehatan Armada" secara instan tanpa perlu membuka data satu per satu.
- **Sorting Otomatis**: Pesawat yang membutuhkan perhatian paling mendesak (paling dekat kedaluwarsa) akan otomatis berada di urutan teratas.
- **Navigasi Cepat**: Perpindahan antar-menu terjadi secara instan tanpa proses muat ulang (reload) browser.
- **Sinkronisasi Tema**: Mendukung Mode Terang dan Gelap yang bisa diganti secara manual melalui tombol di pojok layar.

### 2. Peta Kursi Digital (Interactive Seat Map)
Visualisasi tata letak kursi pesawat dengan fidelitas tinggi untuk memudahkan teknisi melakukan pembaruan data.
- **Pencarian Kursi**: Pengguna bisa mencari kursi spesifik secara instan hanya dengan mengetik nomor kursinya.
- **Multi-Selection**: Memungkinkan pengguna memilih banyak kursi sekaligus untuk memperbarui tanggal secara massal.
- **Informasi Detil**: Mengarahkan kursor ke atas kursi akan menampilkan informasi lengkap mengenai masa berlaku life vest tersebut.

### 3. Analitik dan Laporan Otomatis
Membantu pengguna dalam perencanaan pekerjaan di masa depan sehingga tidak ada peralatan yang terlewat.
- **Ramalan Penggantian**: Melihat jadwal penggantian dalam rentang Mingguan, Bulanan, hingga Tahunan.
- **Wawasan Part Number (P/N)**: Mengetahui secara spesifik jenis barang apa saja yang harus disiapkan tim logistik.
- **Ekspor Data**: Hasilkan laporan Excel profesional atau cetak dokumen PDF resmi dalam satu kali klik.

---

## Arti Warna Status Pelampung

Kami menggunakan sistem warna yang standar agar memudahkan identifikasi status setiap kursi di pesawat:

| Warna | Status | Keterangan Waktu |
| :--- | :--- | :--- |
| **Hijau** | Aman (Safe) | Masa berlaku masih lebih dari 6 bulan lagi |
| **Kuning** | Peringatan (Warning) | Masa berlaku tersisa 3 sampai 6 bulan lagi |
| **Merah** | Kritis (Critical) | Masa berlaku kurang dari 3 bulan lagi |
| **Ungu** | Kedaluwarsa (Expired) | Masa berlaku sudah habis |
| **Abu-abu** | Tidak Ada Data | Data tanggal kedaluwarsa belum diisi ke sistem |

---

## Pintasan dan Interaksi (Shortcuts)

Gunakan kombinasi keyboard dan mouse berikut untuk bekerja lebih efisien pada peta kursi:

| Aksi | Fungsi |
| :--- | :--- |
| **Klik biasa** | Memilih satu kursi (menghapus pilihan sebelumnya) |
| **Ctrl + Klik** | Menambahkan kursi ke pilihan yang sudah ada (Multi-select) |
| **Shift + Klik** | Memilih rentang kursi dari titik awal ke titik akhir |
| **Klik Nama Baris** | Memilih seluruh kursi dalam baris tersebut |
| **Klik Nama Kolom** | Memilih seluruh kursi dalam kolom tersebut |
| **Ctrl + A** | Memilih SEMUA kursi di dalam pesawat sekaligus |
| **Tombol Enter** | Membuka kotak pengisian tanggal (ketika ada kursi dipilih) |
| **Tombol Esc** | Mengosongkan pilihan atau menutup kotak pesan yang aktif |

---

## Daftar Armada dan Maskapai

Sistem ini mendukung pengelolaan armada untuk beberapa maskapai besar:

1. **Garuda Indonesia (GA)**
2. **Citilink (QG)**

### Kapabilitas Tipe Pesawat
| Tipe Pesawat | Registrasi | Kapasitas Terpasang |
| :--- | :--- | :--- |
| B737-800 | 40+ Pesawat | Hingga 174 Kursi |
| B737 MAX 8 | Khusus | Konfigurasi Modern |
| B777-300 | 8 Pesawat | 3-Class Configuration |
| A330-900 | 5 Pesawat | New Generation Layout |
| A330-200/300 | 25+ Pesawat | Long Haul Configuration |
| A320-200 | 50+ Pesawat | Narrow Body Configuration |
| ATR72-600 | Spesifik | Regional Configuration |

---

## Detail Operasional Tambahan

- **Formulir Lapangan (Blank Form)**: Cetak denah kursi kosong untuk membantu teknisi mencatat manual saat tidak membawa laptop.
- **Kelola Fleet**: Admin dapat menambah atau mengubah data maskapai dan registrasi pesawat melalui rute /fleet.
- **Buffer Spare**: Sistem otomatis menghitung kebutuhan pelampung cadangan (Spare PAX & INF) sesuai jenis pesawat.

---

*© 2026 GMF AeroAsia — Sistem Pemantauan Life Vest | Engineering Excellence*
