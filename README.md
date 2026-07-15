[![PHP](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml/badge.svg)](https://github.com/ragepanz/lifevest-laravel/actions/workflows/php.yml)
# Life Vest Tracker - GMF AeroAsia
### *Pemantauan Real-Time Cerdas untuk Keselamatan Armada yang Unggul*

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Vite](https://img.shields.io/badge/Frontend-Vite-646CFF?style=flat-square&logo=vite)](https://vitejs.dev)
[![UI](https://img.shields.io/badge/UX-Premium_Prominent_Design-00AEEF?style=flat-square)](https://gmf-aeroasia.co.id)

---

## Mengenal Sistem Ini
**Life Vest Tracker** adalah ekosistem digital mutakhir yang dirancang khusus untuk tim Engineering dan Maintenance GMF AeroAsia. Sistem ini mengubah data keselamatan pesawat yang kompleks menjadi informasi visual yang siap ditindaklanjuti, memastikan setiap pesawat dalam armada dilengkapi dengan peralatan keselamatan yang patuh (*compliant*) dan aman. Menampilkan antarmuka *Dark Mode* dan *Light Mode* modern dengan desain border prominent dan kontras tinggi, aplikasi ini memastikan manajemen peralatan keselamatan menjadi lebih cepat, akurat, dan efisien.

---

## Fitur Utama & Fungsionalitas Sistem

Sistem ini memiliki serangkaian fitur canggih yang dirancang untuk mendukung operasional pemeliharaan peralatan keselamatan udara secara *end-to-end*:

### 1. Dashboard Visual Cerdas (Command Center)
Dasbor interaktif yang bertindak sebagai pusat kendali untuk memantau kesehatan seluruh armada secara *real-time*:
*   **Fleet Overview (Kartu Ringkasan)**: Menampilkan statistik total life vest berdasarkan status keselamatan (Green/Safe, Yellow/Warning, Red/Critical, Purple/Expired) secara dinamis.
*   **Filter Fleet Cerdas**: Dropdown interaktif dengan multi-select *checkbox* tipe pesawat (A320, A330, ATR72, B737, B777). Memungkinkan tim memfilter seluruh dasbor berdasarkan tipe secara instan.
*   **Filter Lanjutan**: Mempermudah penyaringan data pesawat berdasarkan *Airline*, *Type*, *Status* (Active/Prolong), *Health* (Safe/Warning/Critical), dan pencarian registrasi (misalnya: PK-GFD).
*   **Life Vest Replacement Summary**: Ringkasan per *Part Number* (Adult, Crew, Infant) yang menunjukkan jumlah total dan jumlah yang telah kedaluwarsa beserta *breakdown* registrasi pesawat yang memerlukannya. Dilengkapi indikator kartu merah jika terdapat item kedaluwarsa.
*   **Quick Stats**: Ringkasan performa armada seperti *Health Score* persentase keselamatan, total kursi yang dilacak, jumlah pesawat/maskapai aktif, serta item yang memerlukan perhatian.

### 2. Smart PDF Scanner (Hybrid AI OCR & Multi-Stage Refinement)
Fitur unggulan untuk mempercepat input data dari lembar LOPA ("Inventory Check" (formulir GMF/Q-447) berbasis tulisan tangan atau stempel secara instan:
*   **Pipeline Ekstraksi Multi-Tahap**:
    1.  **Ghostscript Integration**: PDF diubah menjadi gambar beresolusi tinggi (300 DPI) untuk kejelasan penulisan tangan.
    2.  **Pre-processing OpenCV & Tiling**: Gambar dipertajam menggunakan filter OpenCV dan diputar otomatis (*auto-rotate*). Gambar dipecah menjadi **3 horizontal strips (tiling)** dengan overlap 80px untuk menghindari masalah *row drift* (pergeseran baris data) pada AI.
    3.  **Stage 1 AI OCR**: Mengirimkan pecahan potongan gambar ke model AI Vision canggih (seperti Claude 3.5 Sonnet via Flaz API) untuk mendeteksi stempel dan tulisan tangan awal secara terstruktur.
    4.  **Stage 2 AI Refinement (GPT-5)**: Hasil ekstraksi awal divalidasi dan disempurnakan menggunakan model penalaran GPT-5 dengan membandingkannya kembali ke visual asli untuk mengoreksi ketidakcocokan data, format tanggal, maupun kesalahan baca (*refinement*).
*   **Penyaringan Layout per Pesawat (Per-Aircraft Validation)**:
    *   Sistem melakukan pencarian registrasi di database untuk mengetahui jenis *layout* (contoh: layout `a330-900a` dengan Business Class vs `a330-900b` Economy Only).
    *   `VerificationService` mencocokkan baris yang diekstrak dengan konfigurasi baris minimum/maksimum pesawat tersebut.
    *   Jika terdeteksi baris yang tidak sesuai (misal: stempel di baris 6 untuk armada `a330-900b` yang seharusnya mulai dari baris 21), baris tersebut akan ditandai dengan bendera peringatan (**Uncertainty Flag** [Warning]).
*   **Smart Sorting & Grouping**: Hasil ekstraksi otomatis diurutkan dari kabin paling depan (Cockpit), Business, Economy, dan menempatkan semua life vest cadangan (*Infant/Spare*) di bagian paling bawah tabel secara rapi.
*   **Review Page & Eksport**: Halaman review menampilkan visual file asli di samping tabel ekstraksi. Pengguna dapat mengunduh hasilnya langsung ke Excel atau melakukan ekspor ke template *Bulk Import*.

### 3. Peta Kursi Digital & Interaksi Massal (Interactive Seat Map)
Representasi grafis 2D dari tata letak kursi pesawat yang sesungguhnya:
*   **Multi-Select Lanjutan**:
    *   *Klik biasa*: Memilih satu kursi tunggal.
    *   *Ctrl + Klik*: Menambah/menghapus kursi ke dalam pilihan yang sudah ada.
    *   *Shift + Klik*: Memilih deretan kursi dari titik awal ke titik akhir secara runtut.
    *   *Klik Nomor Baris (Kiri)*: Memilih seluruh baris kursi sekaligus.
    *   *Klik Huruf Kolom (Atas)*: Memilih seluruh kolom kursi secara vertikal.
    *   *Ctrl + A*: Memilih seluruh kursi di dalam pesawat.
*   **Part Number Info Bar**: Menampilkan tipe part number yang digunakan pada pesawat tersebut (Adult, Crew, Infant), jumlah total pelampung terpasang, dan indikator merah jika terdeteksi ada yang kedaluwarsa.
*   **Bulk Update & Expiry Setter**: Klik tombol [Set Date] (atau tekan **Enter**) untuk menetapkan tanggal kedaluwarsa secara massal bagi seluruh kursi terpilih. Data langsung tersimpan secara asinkron ke database.

### 4. Ekspor PDF Berwarna & Blank Form Inspeksi
*   **Export PDF (Colored Seat Map)**: Menghasilkan dokumen PDF layout pesawat berwarna yang menyajikan warna status visual (Hijau, Kuning, Merah, Ungu) beserta tanggal kedaluwarsa yang tertera di setiap kotak kursi. Siap di-print atau diarsipkan.
*   **Blank Form (Formulir Lapangan Kosong)**: Digunakan untuk mencatat data secara manual selama inspeksi fisik di pesawat. Kotak kursi dicetak kosong dengan nomor kursi saja.
*   **Dynamic Spare Buffers**: Jumlah kotak kosong cadangan (*spare*) pada formulir lapangan otomatis disesuaikan dengan kapasitas tipe pesawat:
    *   **A320**: 15 PAX / 20 INF
    *   **A330**: 15 PAX / 40 INF
    *   **ATR72**: 10 PAX / 10 INF
    *   **B737**: 10 PAX / 25 INF
    *   **B777**: 35 PAX / 40 INF

### 5. Input Massal Cepat (Batch Input)
*   Memungkinkan penyalinan data tanggal kedaluwarsa langsung dari kolom Microsoft Excel.
*   Disediakan kotak input teks area terpisah untuk masing-masing kolom kursi kabin (A, B, C, D, E, F, dll) dan bagian Spare.
*   **Flexible Date Parser**: Sistem secara cerdas mampu membaca berbagai format tanggal inputan seperti `Oct-25`, `24-Jan-25`, `01/03/2030` dan mengubahnya menjadi format database standar (`Y-m-d`).

### 6. CRUD Manager (Fleet & Airline Management)
*   **Tab Aircraft**: Memungkinkan Admin mengelola registrasi pesawat, model tipe, menetapkan file layout visual, dan mengubah Part Number (Adult, Crew, Infant) yang digunakan.
*   **Tab Airlines**: Pengelolaan maskapai terdaftar (Garuda Indonesia, Citilink, dsb) lengkap dengan kode IATA dan jumlah armada terintegrasi.

### 7. Keamanan Akses (RBAC & Suspend User)
Penerapan otentikasi ketat menggunakan Role-Based Access Control (RBAC):
*   **Superadmin**: Memiliki hak akses penuh, termasuk mengelola data pengguna (menambah, mengubah, suspensi/unsuspensi pengguna), menjalankan Smart PDF Scanner, dan melakukan *Bulk Import* database.
*   **Admin (TNP)**: Dapat mengubah status keselamatan kursi pesawat, menggunakan Batch Input, dan mengelola armada di Fleet Manager.
*   **User (Viewer)**: Hanya memiliki hak akses membaca dasbor, melihat peta kursi, dan mengunduh laporan PDF/Excel.

### 8. Audit Trail (Global Log Aktivitas)
Setiap modifikasi data expiry pada kursi direkam secara otomatis oleh sistem ke dalam tabel aktivitas audit:
*   Merekam nama pengguna yang melakukan perubahan, tipe aksi, pesawat yang dimodifikasi, tanggal pengerjaan, dan detail data sebelum/sesudah perubahan.
*   Pencatatan riwayat modifikasi massal dikelompokkan secara spesifik berdasarkan nomor kursi (*Part Number*) yang diubah saja, menjaga detail log tetap bersih dan berakurasi tinggi.
*   Log aktivitas dapat diekspor ke format spreadsheet Excel untuk pemenuhan kebutuhan kepatuhan regulator keselamatan penerbangan.

---

## Struktur Proyek & Folder Penting

Sistem ini diorganisasikan secara sistematis untuk mempermudah pengembangan dan pemeliharaan struktur LOPA:

```text
lifevest-laravel/
├── app/
│   ├── Http/Controllers/              # Pengendali logika (Dashboard, Seat Map, PDF Scan, Bulk Import)
│   ├── Models/                        # Model Eloquent (Airline, Aircraft, Seat, User)
│   └── Services/                      # Layanan bisnis (OcrPreprocessService, PdfParserService, VerificationService)
├── config/
│   ├── aircraft_class_rows.php        # Pemetaan Cabin Class per layout pesawat
│   ├── aircraft_columns.php           # Konfigurasi kolom kursi per layout pesawat
│   ├── aircraft_economy_sections.php  # Konfigurasi bagian batch input per layout pesawat
│   └── ocr_corrections.php            # Aturan koreksi otomatis OCR (substitusi digit/huruf, bulan)
├── database/
│   ├── migrations/                    # Skema tabel database (airlines, aircraft, seats, users)
│   └── seeders/                       # Data awal (seeder maskapai, registrasi pesawat, user default)
├── dokumentasi/                       # Panduan manual resmi & spesifikasi teknis (User/Developer Manual, LOPA Layout Mapping, dll)
├── resources/
│   ├── css/
│   │   ├── style.css                  # Desain tema utama (Premium UI & Dark/Light Mode)
│   │   └── dashboard.css              # Gaya visual khusus dashboard
│   ├── js/
│   │   └── app.js                     # Logika interaksi frontend & shortcut seat map
│   └── views/
│       ├── aircraft/                  # Template visual detail seat map per layout pesawat
│       │   └── partials/              # Struktur grid layout kursi pesawat (A320, A330, B737, B777, ATR)
│       ├── components/                # Komponen Blade reusable (toolbar, seat-cell, modal)
│       └── reports/                   # Template cetak PDF dan Blank Form lapangan
├── routes/
│   └── web.php                        # Seluruh routing aplikasi (RBAC protected)
```

---

## Legenda Status (Arti Warna)

Di bawah ini adalah status masa berlaku pelampung beserta representasi warna pada antarmuka sistem (dengan border dan kontras tinggi untuk kejelasan):

*   ![Safe](https://img.shields.io/badge/Status-Safe-10b981) - Masa berlaku pelampung > 6 bulan dari sekarang.
*   ![Warning](https://img.shields.io/badge/Status-Warning-f59e0b) - Masa berlaku tersisa 3 sampai 6 bulan.
*   ![Critical](https://img.shields.io/badge/Status-Critical-ef4444) - Masa berlaku tersisa kurang dari 3 bulan.
*   ![Expired](https://img.shields.io/badge/Status-Expired-8b5cf6) - Tanggal masa berlaku telah terlewati (perlu diganti segera).
*   ![No Data](https://img.shields.io/badge/Status-No_Data-64748b) - Data tanggal kedaluwarsa belum dimasukkan ke sistem.

---

## Pintasan Keyboard (Shortcuts)
Gunakan pintasan berikut di halaman **Seat Map** untuk mempercepat alur kerja:
*   **Ctrl + A**: Memilih seluruh kursi dalam peta visual.
*   **Enter**: Membuka dialog pemilih tanggal kedaluwarsa (Set Date) ketika ada kursi yang sedang dipilih.
*   **Escape (ESC)**: Menghapus semua pilihan kursi aktif atau menutup jendela modal yang terbuka.

---

## Konfigurasi Layout Pesawat (Developer Reference)

Konfigurasi layout kursi pesawat didasarkan pada empat file pengaturan utama di dalam folder `config/`:
1.  **`config/aircraft_class_rows.php`**: Mendefinisikan baris mana saja yang termasuk ke dalam Cabin Class tertentu untuk setiap layout. Contoh:
    ```php
    'a330-900a' => [
        'business' => range(6, 11),
        'economy'  => array_diff(range(21, 58), [24]), // baris 21-58, skip galley baris 24
    ]
    ```
2.  **`config/aircraft_columns.php`**: Daftar kolom yang tersedia untuk layout tertentu (contoh: `['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']` untuk armada berbadan lebar A330).
3.  **`config/aircraft_economy_sections.php`**: Penentuan rentang baris per bagian kabin ekonomi untuk mempermudah form Batch Input.
4.  **`config/ocr_corrections.php`**: Kamus koreksi otomatis pembacaan teks OCR. Berisi aturan substitusi digit-ke-huruf atau huruf-ke-digit (seperti `O` menjadi `0`, atau `2O25` menjadi `2025`), normalisasi singkatan bulan bahasa Indonesia ke bahasa Inggris, format registrasi PK-XXX, dan tingkat batas keyakinan (*confidence threshold*) model AI.

---

## Panduan Teknis & Instalasi

### Prasyarat Sistem
*   **PHP**: Versi 8.2 atau lebih tinggi.
*   **Composer** (Manajer Dependensi PHP).
*   **Node.js & NPM** (Untuk membangun aset frontend).
*   **MySQL / MariaDB** (Sebagai server database).
*   **Ghostscript**: Diperlukan oleh PDF Scanner untuk mengubah lembaran PDF LOPA menjadi file PNG beresolusi tinggi.

### Langkah Instalasi
1.  **Clone dan Masuk ke Direktori Proyek**:
    ```bash
    git clone <github.com/ragepanz/lifevest-laravel>
    cd lifevest-laravel
    ```
2.  **Instal Dependensi Backend & Frontend**:
    ```bash
    composer install
    npm install
    ```
3.  **Salin Pengaturan Lingkungan (.env)**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Konfigurasi Database di file `.env`**:
    Sesuaikan variabel `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai dengan konfigurasi lokal Anda.
5.  **Konfigurasi Jalur Ghostscript & Kredensial AI (.env)**:
    Untuk mengaktifkan fitur Smart PDF Scanner, pastikan variabel berikut terisi di file `.env` Anda:
    ```env
    GHOSTSCRIPT_PATH="C:/Program Files/gs/gs10.07.0/bin/gswin64c.exe"  # Sesuaikan folder instalasi GS
    FLAZ_API_KEY="your-flaz-api-key"
    FLAZ_MODEL="claude-sonnet-4-6"          # Model utama OCR (Stage 1)
    FLAZ_REFINEMENT_ENABLED=true            # Set true untuk mengaktifkan perbaikan AI
    FLAZ_REFINEMENT_MODEL="gpt-5"           # Model perbaikan hasil (Stage 2)
    ```
6.  **Migrasi Database & Isi Data Seed**:
    ```bash
    php artisan migrate:fresh --seed
    ```
7.  **Jalankan Server Lokal**:
    Jalankan perintah berikut pada terminal yang berbeda:
    *   **Terminal 1 (Laravel Server)**:
        ```bash
        php artisan serve
        ```
    *   **Terminal 2 (Vite Development Compiler)**:
        ```bash
        npm run dev
        ```
    Aplikasi dapat dibuka melalui peramban web di alamat `http://localhost:8000`.

---

## Kredensial Akses Default (Login)

Tersedia tiga akun default hasil dari proses database seeder untuk melakukan pengujian fungsionalitas sistem:

| Peran (Role) | Alamat Surel (Email) | Kata Sandi (Password) |
| :--- | :--- | :--- |
| **Superadmin** | `superadmin@tnp.com` | `superadmintnp` |
| **Admin (TNP)** | `admin@tnp.com` | `admintnp` |
| **User (Viewer)** | `user@tnp.com` | `usertnp` |

---
*(c) 2026 GMF AeroAsia - Life Vest Tracker - Engineering Excellence*
