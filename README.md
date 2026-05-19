[![PHP](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml/badge.svg)](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml)
# Life Vest Tracker — GMF AeroAsia
### *Pemantauan Real-Time Cerdas untuk Keselamatan Armada yang Unggul*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?style=flat-square&logo=vite)](https://vitejs.dev)
[![UI](https://img.shields.io/badge/UX-Premium_Glassmorphism-00AEEF?style=flat-square)](https://gmf-aeroasia.co.id)

---

## Mengenal Sistem Ini
**Life Vest Tracker** adalah ekosistem digital mutakhir yang dirancang khusus untuk tim Engineering dan Maintenance GMF AeroAsia. Sistem ini mengubah data keselamatan pesawat yang kompleks menjadi informasi visual yang siap ditindaklanjuti, memastikan setiap pesawat dalam armada dilengkapi dengan peralatan keselamatan yang patuh (compliant) dan aman. Menampilkan antarmuka *Dark Mode* modern berbasis *Glassmorphism*, aplikasi ini memastikan manajemen peralatan keselamatan menjadi lebih cepat, akurat, dan sangat memanjakan mata.

---

## Fitur Unggulan
1.  **Dashboard Visual Cerdas**: Menampilkan grafik kesehatan armada (Aman, Peringatan, atau Kritis) secara instan tanpa muat ulang halaman.
2.  **Smart PDF Scanner (Hybrid OCR AI)**: Ekstraksi data otomatis dari dokumen "Inventory Check" menggunakan gabungan *Google Cloud Vision* dan AI *Gemini/Claude* untuk akurasi tulisan tangan yang tinggi.
3.  **Peta Kursi Digital (Seat Map)**: Denah pesawat interaktif untuk pembaruan data pelampung secara massal (Multi-Select).
4.  **Keamanan Berbasis Peran (RBAC)**: Pembagian akses ketat antara Superadmin, Admin (TNP), dan User (Viewer).
5.  **Laporan & Formulir Lapangan**: Sekali klik untuk cetak laporan PDF atau Formulir Lapangan kosong yang disesuaikan dengan tipe pesawat.
6.  **Audit Trail (Global Log)**: Pencatatan permanen setiap aktivitas modifikasi data demi transparansi dan kebutuhan audit.

---

## Struktur Proyek (Core Structure)
Sistem ini diorganisir secara sistematis untuk memudahkan pemeliharaan:
*   app/Http/Controllers/ : Logika pengolah data, dashboard, manajemen user & log aktivitas.
*   database/seeders/ : Data awal registrasi pesawat, maskapai, & akses Role.
*   resources/views/aircraft/partials/ : **(Inti)** Konfigurasi teknis 16+ layout kursi (Seat Map).
*   resources/css/style.css : Sistem desain utama (Premium UI & Dark Mode).
*   resources/js/app.js : Interaksi peta kursi, optimasi zona waktu & navigasi SPA.

---

## Panduan Pengguna

### 1. Dashboard Utama (Command Center)
Dasbor menggunakan navigasi cerdas yang tidak memerlukan pemuatan ulang halaman (SPA). 
- **Airline Master Deck**: Menampilkan Kartu Maskapai dengan diagram profil kesehatan armada.
- **Airline Fleet Profile**: Menampilkan daftar pesawat yang dikelompokkan berdasarkan tipe (A320, B737, dll).

### 2. Smart PDF Scanner (Ekstraksi Dokumen AI)
- Buka menu **Smart PDF Scanner**.
- **Upload File**: Seret (*drag and drop*) dokumen PDF/Foto "Inventory Check" (GMF/Q-447) ke area kotak.
- **Uncertainty Flag**: AI akan otomatis membaca data. Jika ada coretan tinta pudar yang membingungkan AI, baris tersebut akan disorot dengan **Warna Kuning ⚠️** di tabel.
- **Pengurutan Cerdas (Smart Sorting)**: Sistem otomatis mengurutkan hasil *scan* secara hierarkis dari *Cockpit* hingga ke *Economy Class*, dan mengelompokkan data pelampung bayi/cadangan (*Infant/Spare*) ke bagian paling bawah tabel agar rapi.
- **Quick Navigation**: Gunakan tombol navigasi melayang (*Top/Mid/Bottom*) untuk menggulir tabel B777/A330 yang panjang secara instan.
- Klik **Download Excel** untuk mengekspor data yang siap masuk ke fitur *Bulk Import*.

### 3. Peta Kursi Digital & Pembaruan Data
**Cara Memilih Kursi (Multi-Select):**
- **Klik Biasa**: Memilih satu kursi.
- **Ctrl + Klik**: Menambah kursi ke pilihan yang ada.
- **Shift + Klik**: Memilih sederetan kursi dari titik awal ke titik akhir.
- **Klik Header Baris/Kolom**: Memilih seluruh baris atau kolom secara instan.

**Cara Mengisi Tanggal Kedaluwarsa:**
1. Pilih kursi-kursi yang ingin diperbarui.
2. Klik tombol "Set Date" di toolbar atas.
3. Pilih tanggal dan klik "Apply". Data akan tersimpan secara massal.

---

## Cara Kerja Fitur Bulk Import (Input Massal)
Fitur ini memungkinkan input data dalam jumlah masif menggunakan Excel.
1.  Buka menu **Bulk Import** di Sidebar.
2.  Unduh **Template Excel Resmi** yang disediakan sistem.
3.  Isi data Anda (Sistem otomatis mengabaikan baris contoh `[CONTOH (DIABAIKAN)]`).
4.  **Sistem Pengenalan Otomatis**: Anda bisa memasukkan data secara bebas (misal: menggunakan huruf kecil atau tanpa awalan), nanti sistem akan otomatis membacanya dan merapikannya sesuai standar. 
    *   *Contoh*: Jika Anda mengetik `6a` otomatis menjadi `6A`, atau ketik `d11-ll` otomatis disempurnakan menjadi `att/D11-LL`.
5.  Unggah kembali file dan klik **Mulai Proses Import Data**.

---

## Pintasan Keyboard (Shortcuts)
- **Ctrl + A**: Memilih SEMUA kursi di pesawat.
- **Enter**: Membuka kotak pengisian tanggal (jika ada kursi terpilih).
- **Escape (ESC)**: Menghapus semua pilihan kursi atau menutup modal.

---

## Legenda Status (Arti Warna)
| Warna | Status | Keterangan Kondisi |
| :--- | :--- | :--- |
| 🟢 **Hijau** | Aman (Safe) | Masa berlaku > 6 bulan |
| 🟡 **Kuning** | Peringatan (Warning) | Masa berlaku 3-6 bulan |
| 🔴 **Merah** | Kritis (Critical) | Masa berlaku < 3 bulan |
| 🟣 **Ungu** | Kedaluwarsa (Expired) | Sudah melewati batas tanggal |
| ⚪ **Abu-abu** | Kosong | Data belum dimasukkan ke sistem |

---

## Panduan Teknis (Developer)

### Prasyarat Sistem
- **PHP**: Versi 8.2+ | **Composer** | **Node.js & NPM** | **MySQL**

### Langkah Instalasi
```bash
# 1. Instal Dependensi
composer install && npm install

# 2. Pengaturan Lingkungan
cp .env.example .env && php artisan key:generate

# 3. Kredensial AI (Wajib untuk PDF Scanner)
# Buka file .env dan pastikan variabel ini terisi:
# GEMINI_API_KEY="xxx" atau ANTHROPIC_API_KEY="xxx"

# 4. Persiapan Database
php artisan migrate:fresh --seed

# 5. Kompilasi Aset & Jalankan
npm run build
php artisan serve
```

---

## Ekspor Laporan & Manajemen Armada
Sistem mendukung berbagai format dokumen untuk kebutuhan kantor maupun lapangan:
- **Laporan Digital (PDF & Excel)**: Cetak peta kursi berwarna (PDF) untuk arsip resmi, atau unduh log aktivitas (Excel) untuk kebutuhan audit data. File diekspor dengan format tanggal baku `d-m-Y`.
- **Akurasi Audit Trail (Log)**: Operasi modifikasi massal (*Bulk Import*) akan mencatat riwayat aktivitas (*Activity Log*) secara spesifik berdasarkan *Part Number* (P/N) kursi yang diubah saja, bukan keseluruhan isi pesawat, sehingga menghasilkan audit yang sangat akurat.
- **Formulir Lapangan (Blank Form)**: Desain khusus dengan kotak isian kosong untuk memudahkan teknisi mencatat data secara manual saat di pesawat.
- **Fleet Manager**: Pusat pengaturan untuk menambah atau mengubah data registrasi pesawat dan maskapai (Mendukung armada A320, A330, B737, hingga struktur kompleks B777).

---

## Akses Login Default
| Peran | Email | Password |
| :--- | :--- | :--- |
| **Superadmin** | `superadmin@tnp.com` | `superadmintnp` |
| **Admin** | `admin@tnp.com` | `admintnp` |
| **User (Viewer)** | `user@tnp.com` | `usertnp` |

---
*© 2026 GMF AeroAsia — Life Vest Tracker — Engineering Excellence*
