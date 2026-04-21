# Life Vest Tracker — GMF AeroAsia
### *Pemantauan Real-Time Cerdas untuk Keselamatan Armada yang Unggul*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?style=flat-square&logo=vite)](https://vitejs.dev)
[![UI](https://img.shields.io/badge/UX-Premium_Glassmorphism-00AEEF?style=flat-square)](https://gmf-aeroasia.co.id)

---

## Panduan Teknis (Developer)

### Prasyarat
- **PHP**: 8.2 atau lebih tinggi
- **Composer**: Dependency manager
- **Node.js & NPM**: Asset bundling via Vite
- **Database**: MySQL / MariaDB

### Instalasi Langkah-Demi-Langkah
```bash
# 1. Clone & Instal Dependensi
composer install
npm install

# 2. Konfigurasi Lingkungan
cp .env.example .env
php artisan key:generate

# 3. Migrasi Database & Authentikasi
# Catatan: Pastikan DB_DATABASE sudah dikonfigurasi di file .env Anda
php artisan migrate --seed

# 4. Jalankan Lingkungan Pengembangan
# Jalankan kedua perintah ini di jendela terminal yang terpisah
php artisan serve
npm run dev
```

---

## Ringkasan Proyek

Life Vest Tracker adalah ekosistem digital mutakhir yang dirancang khusus untuk tim Engineering dan Maintenance GMF AeroAsia. Sistem ini mengubah data keselamatan pesawat yang kompleks menjadi informasi visual yang siap ditindaklanjuti, memastikan setiap pesawat dalam armada dilengkapi dengan peralatan keselamatan yang patuh (compliant) dan aman.

Dibangun dengan estetika Premium SaaS, platform ini memprioritaskan kejelasan, kecepatan, dan presisi—mencerminkan standar tinggi dalam industri penerbangan.

---

## Pilar Utama

| Presisi | Efisiensi | Pengalaman |
| :--- | :--- | :--- |
| Pemetaan konfigurasi LOPA pesawat (A320, B737, B777, A330) yang sangat akurat. | Operasi data massal (batch) dan ekspor instan PDF/Excel untuk kru lapangan. | UI Glassmorphism kelas atas dengan sinkronisasi Mode Gelap/Terang secara native. |

---

## Fitur Utama

### 1. Command Center (Dashboard Modern)
- **Pemantauan Kesehatan Armada**: Pelacakan status langsung untuk item yang Expired, Critical, dan Warning.
- **Mesin Prioritas Otomatis**: Secara otomatis menampilkan registrasi pesawat berisiko tinggi di urutan teratas.
- **Mikro-Interaksi**: Transisi halus dan navigasi glassmorphic untuk alur operasional yang mulus.

### 2. Digital LOPA Interaktif (Seat Map)
- **Sistem Grid Visual**: Representasi fidelitas tinggi dari tata letak kabin dengan kode warna status keselamatan.
- **Smart Selection Engine**: Mendukung Multi-Select, Shift-Click Range, dan seleksi Baris/Kolom untuk pembaruan cepat.
- **Pencarian Instan**: Temukan lokasi kursi tertentu secara instan dengan utilitas pencarian kursi terintegrasi.

### 3. Analitik Keselamatan Prediktif
- **Perencanaan Penggantian**: Peramalan otomatis untuk penggantian life vest (Mingguan, Bulanan, Tahunan).
- **Wawasan Part Number**: Rincian cerdas kebutuhan P/N spesifik untuk perencanaan pengadaan stok.
- **Suite Ekspor**: Hasilkan formulir kosong (Blank Form) PDF profesional untuk teknisi lapangan dan laporan Excel untuk logistik.

---

## Sorotan Arsitektur

- **Sistem UI**: Desain Sistem CSS kustom menggunakan token HSL (resources/css/style.css).
- **Lapisan Responsif**: Arsitektur desktop-first yang dioptimalkan untuk laptop maintenance penerbangan.
- **Konfigurasi Layout**: Konfigurasi dinamis kelas/baris pesawat dikelola melalui config/aircraft_class_rows.php.

---

## Pintasan & Interaksi

| Aksi | Fungsi |
| :--- | :--- |
| Ctrl + Klik | Tambahkan kursi individu ke pilihan |
| Shift + Klik | Pilih rentang kursi secara berkelanjutan |
| Header Baris/Kolom | Pilih seluruh baris atau kolom secara instan |
| Ctrl + A | Pilih SEMUA kursi di pesawat |
| Enter | Buka dialog modifikasi tanggal |
| Esc | Hapus seleksi atau tutup modal yang aktif |

---

## Roadmap & Status
- [x] Premium UI 2.0: Migrasi penuh ke desain Glassmorphism.
- [x] Split-Screen Login: Alur autentikasi perusahaan kelas atas.
- [x] Dukungan Peran Universal: Standarisasi tampilan administratif lintas peran (Admin/User).
- [ ] Portal Lapangan Mobile: Progressive Web App untuk entri data langsung di hangar (Direncanakan).

---

*© 2026 GMF AeroAsia — Fleet Management System | Engineering Excellence*
