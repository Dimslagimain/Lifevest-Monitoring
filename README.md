# 🛡️ Life Vest Tracker - GMF AeroAsia
### *Smart, Real-Time Monitoring for Fleet Safety Excellence*

[![PHP](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml/badge.svg)](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml)

Aplikasi **Life Vest Tracker** adalah solusi digital modern yang dirancang khusus untuk tim *Engineering* dan *Maintenance* GMF AeroAsia. Aplikasi ini memungkinkan pemantauan status kesehatan peralatan keselamatan (*life vest*) di seluruh armada pesawat secara akurat, cepat, dan visual.

---

## ⚙️ Panduan Teknis (Developer)

### Prasyarat
- PHP 8.1+ (Laravel 12)
- Composer
- Node.js & npm (Vite)
- MySQL/MariaDB

### Instalasi Langkah-demi-Langkah
```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Setup Database (sesuaikan DB_DATABASE di .env)
php artisan migrate --seed

# 4. Jalankan aplikasi (Gunakan 2 terminal)
php artisan serve
npm run dev
```

### Struktur File Penting
```
lifevest-laravel/
├── config/aircraft_class_rows.php   # Config baris bisnis/ekonomi
├── app/Http/Controllers/            # Logika utama sistem
├── resources/views/
│   ├── fleet/                       # View Fleet Manager
│   ├── aircraft/                    # Template wrapper seat map
│   └── aircraft/partials/           # ⭐ Konfigurasi layout kursi (16+ layout)
├── resources/css/
│   ├── style.css                    # Premium UI Design System tokens
│   └── dashboard.css                # Styling dashboard & navigasi
└── resources/js/app.js              # Logika interaksi seat map
```
---

## 🚀 Fitur Utama (Overview)

Kami merancang aplikasi ini agar mudah dipahami oleh siapa saja, mulai dari teknisi di lapangan hingga manajemen level atas.

### 📊 1. Command Center (Dashboard Utama)
Layar kendali utama yang memberikan gambaran "Kesehatan Armada" secara instan.
- **Visualisasi Donut Chart**: Melihat persentase pesawat yang aman (Safe), butuh perhatian (Warning), atau kritis (Critical/Expired).
- **Auto-Sorting**: Sistem secara otomatis menempatkan maskapai atau pesawat yang membutuhkan perhatian paling mendesak di urutan teratas.
- **SPA Experience**: Perpindahan antar-menu (via Sidebar) terjadi instan secara dinamis tanpa proses *reload* browser.
- **Dark/Light Mode**: Mendukung tema gelap dan terang dengan implementasi UI *Glassmorphism*.

### 💺 2. Digital Seat Map (Peta Kursi Interaktif)
Ucapkan selamat tinggal pada pendataan manual di kertas.
- **Grid Interaktif**: Visualisasi tata letak kursi (A320, B737, B777, dll.) dengan kode warna status.
- **Smart Selection**: Pilih banyak kursi sekaligus menggunakan klik, Shift+Klik, atau Ctrl+Klik.
- **Pencarian Cepat**: Cari kursi spesifik hanya dengan mengetikkan nomor kursinya.

### 📅 3. Predictive Replacement Planning
Perencanaan cerdas agar tidak ada *life vest* yang terlewat tanggal kedaluwarsanya.
- **Timeline Pintar**: Lihat jadwal penggantian dalam rentang Mingguan, Bulanan, hingga Tahunan.
- **Breakdown Part Number (P/N)**: Mengetahui secara spesifik stok *part number* apa yang harus disiapkan.
- **Export Excel**: Kirim laporan perencanaan langsung ke tim logistik atau pengadaan.

### 🛠️ 4. Operational Efficiency Tools
- **Batch Data Entry**: Fitur khusus untuk menyalin data dari Excel dan menempelnya langsung ke sistem.
- **Formulir Lapangan (Blank Form)**: Cetak peta kursi kosong untuk teknisi mencatat manual di lapangan.
- **Laporan PDF Profesional**: Cetak hasil inspeksi dalam format PDF resmi.

---

## ⌨️ Panduan Penggunaan & Shortcuts

### SELECT KURSI (Mouse & Keyboard)
| Aksi | Fungsi |
|------|--------|
| **Klik biasa** | Pilih 1 kursi (hapus selection sebelumnya) |
| **Ctrl + Klik** | Tambah kursi ke selection (multi-select) |
| **Shift + Klik** | Pilih range dari kursi terakhir ke kursi ini |
| **Klik nomor BARIS** | Pilih semua kursi di baris tersebut |
| **Klik huruf KOLOM** | Pilih semua kursi di kolom tersebut |
| **Ctrl + A** | Pilih **SEMUA** kursi |
| **Enter** | Buka dialog **Set Date** (jika ada kursi terpilih) |
| **Escape (ESC)** | Tutup dialog / Hapus selection |

### ARTI WARNA STATUS
| Warna | Status | Keterangan |
|-------|--------|------------|
| 🟢 **HIJAU** | Safe | Expiry > 6 bulan lagi |
| 🟡 **KUNING** | Warning | Expiry 3-6 bulan lagi |
| 🔴 **MERAH** | Critical | Expiry < 3 bulan lagi |
| 🟣 **UNGU** | Expired | Sudah melewati tanggal expiry |
| ⚪ **ABU-ABU** | No Data | Belum ada tanggal expiry |

---

## 📉 Fleet Overview

Berikut adalah daftar tipe pesawat dan konfigurasi layout yang didukung oleh sistem saat ini:

| Tipe | Registrasi | Layout Tersedia |
|------|------------|-----------------|
| **B737-800** | 40+ | e46, e47, e48, e49 |
| **B737 MAX 8** | 1 | e46 |
| **B777-300** | 8 | 2-Class, 3-Class |
| **A330-900** | 5 | 900a, 900b |
| **A330-300** | 14 | 300a, 300b, 300c, Cargo |
| **A330-341** | 2 | 300c |
| **A330-200** | 5 | 200a, 200b |
| **A320-200** | 50 | a320a |
| **ATR72-600** | 2 | atr72 |

### Maskapai (Airlines)
Sistem mendukung **Multi-Airline Management**:
1. **Garuda Indonesia (GA)**
2. **Citilink (QG)**

---

## 🛠️ Detail Fitur Operasional

### 1. Ekspor & Laporan
- **Export PDF**: Klik tombol **"Export PDF"** di toolbar seat map untuk laporan berwarna.
- **Blank Form**: Klik tombol **"Blank Form"** untuk formulir inspeksi lapangan.
- **Spare Buffer**: Kotak spare (PAX & INF) otomatis muncul di form sesuai tipe pesawat:
    - A320: 15 PAX, 20 INF
    - A330: 15 PAX, 40 INF
    - B737: 10 PAX, 25 INF
    - B777: 35 PAX, 40 INF

### 2. Fleet Manager
Pusat kontrol data pesawat melalui rute `/fleet` (Akses Admin/Superadmin):
- **Tab Aircraft**: Kelola registrasi, tipe, dan layout pesawat.
- **Tab Airlines**: Kelola daftar maskapai dan kode IATA.

---

## 📖 Dokumentasi Lengkap

| Dokumen | Deskripsi |
|---------|-----------|
| [USER_MANUAL.md](dokumentasi/USER_MANUAL.md) | Panduan mendalam untuk end-user & teknisi |
| [DEVELOPER_MANUAL.md](dokumentasi/DEVELOPER_MANUAL.md) | Dokumentasi teknis & arsitektur sistem |

---
*© 2026 GMF AeroAsia - Life Vest Tracking System*
