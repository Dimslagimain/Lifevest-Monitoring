# 📕 Developer Manual — Life Vest Tracker

---

## 📋 Daftar Isi

1. [Tech Stack](#1--tech-stack)
2. [Setup Development](#2--setup-development)
3. [Struktur Folder](#3--struktur-folder)
4. [Database Schema](#4--database-schema)
5. [Routes & Controllers](#5--routes--controllers)
6. [Membuat Layout Baru](#6--membuat-layout-baru-step-by-step)
7. [Menambah Tipe Pesawat Baru](#7--menambah-tipe-pesawat-baru)
8. [Menambah Airline Baru](#8--menambah-airline-baru)
9. [CSS & Theming](#9--css--theming)
10. [Sistem PDF](#10--sistem-pdf)
11. [Alur Request](#11--alur-request)
12. [Smart PDF Scanner dan Multi-Stage OCR Refinement](#12-smart-pdf-scanner-dan-multi-stage-ocr-refinement)

---

## 1. 🛠️ Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12 (PHP 8.1+) |
| Frontend | Vanilla CSS & JavaScript |
| Database | MySQL / MariaDB |
| Build Tool | Vite |
| PDF | Barryvdh/DomPDF |
| Timezone | Asia/Jakarta (GMT+7) |

---

## 2. 🚀 Setup Development

### Prasyarat

- PHP 8.1+
- Composer
- Node.js & npm
- MySQL/MariaDB (via Laragon/XAMPP)

### Langkah Instalasi

```bash
# 1. Clone & install
git clone <repo-url>
cd lifevest-laravel
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Konfigurasi database di .env
DB_DATABASE=lifevest_tracker
DB_USERNAME=root
DB_PASSWORD=

# 4. Migrasi database
php artisan migrate

# 5. (Opsional) Seed data awal
php artisan db:seed

# 6. Jalankan server (buka 2 terminal)
# Terminal 1:
php artisan serve

# Terminal 2:
npm run dev
```

Buka `http://localhost:8000`

---

## 3. 📁 Struktur Folder

```
lifevest-laravel/
├── config/
│   └── aircraft_class_rows.php       # Konfigurasi baris per class per layout
├── database/
│   ├── migrations/
│   │   ├── create_aircraft_table      # Tabel pesawat
│   │   ├── create_seats_table         # Tabel kursi & expiry
│   │   └── create_airlines_table      # Tabel maskapai
│   └── seeders/
│       ├── AircraftSeeder.php         # Data awal pesawat
│       └── AirlineSeeder.php          # Data awal airline
├── app/
│   ├── Models/
│   │   ├── Aircraft.php               # belongsTo(Airline)
│   │   ├── Airline.php                # hasMany(Aircraft)
│   │   └── Seat.php                   # Data kursi & expiry date
│   └── Http/Controllers/
│       ├── DashboardController.php    # Dashboard & Fleet Overview
│       ├── AircraftController.php     # Seat map, update, batch input
│       ├── FleetController.php        # CRUD pesawat & airline
│       └── ReportController.php       # PDF Export & Blank Form
├── resources/
│   ├── views/
│   │   ├── layouts/app.blade.php      # Master layout + Navbar
│   │   ├── dashboard.blade.php        # Halaman dashboard
│   │   ├── fleet/                     # Fleet Manager views
│   │   │   ├── index.blade.php        # Daftar pesawat & airline
│   │   │   ├── create.blade.php       # Form tambah pesawat
│   │   │   └── edit.blade.php         # Form edit pesawat
│   │   ├── aircraft/                  # ⭐ Template wrapper per layout
│   │   │   ├── b737-e46.blade.php     # Wrapper B737 layout e46
│   │   │   ├── b777-3class.blade.php  # Wrapper B777 3-class
│   │   │   └── ... (16 file)
│   │   ├── aircraft/partials/         # ⭐ Seat map layout (inti)
│   │   │   ├── b737-e46.blade.php     # Konfigurasi kursi B737
│   │   │   ├── b777-3class.blade.php  # Konfigurasi kursi B777
│   │   │   └── ... (16 file)
│   │   ├── reports/                   # Template PDF
│   │   │   ├── seat-map.blade.php     # PDF Export (berwarna)
│   │   │   └── blank-form.blade.php   # Blank Form (kosong)
│   │   └── components/               # Blade components
│   │       ├── toolbar.blade.php      # Toolbar + tombol Export
│   │       ├── seat-cell.blade.php    # Komponen satu sel kursi
│   │       ├── cockpit-section.blade.php
│   │       ├── status-legend.blade.php
│   │       ├── aircraft-header-info.blade.php
│   │       └── date-modal.blade.php
│   ├── css/
│   │   ├── style.css                  # CSS utama + Dark/Light mode
│   │   └── dashboard.css              # CSS khusus dashboard
│   └── js/
│       └── app.js                     # JavaScript interaksi kursi
└── routes/
    └── web.php                        # Semua route
```

---

## 4. 🗄️ Database Schema

### Tabel `aircraft`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | int (PK) | Auto increment |
| `registration` | varchar (unique) | Nomor registrasi (PK-GFD) |
| `type` | varchar | Tipe pesawat (B737-800) |
| `icon` | varchar | Emoji ikon (default: ✈️) |
| `layout` | varchar | Kode layout blade (b737-e46) |
| `status` | enum | `active` / `prolong` |
| `airline_id` | int (FK) | Foreign key ke tabel airlines |

### Tabel `seats`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | int (PK) | Auto increment |
| `registration` | varchar | FK ke aircraft registration |
| `seat_id` | varchar | ID kursi: `21A`, `captain`, `pax-1`, `inf-3` |
| `row` | int (nullable) | Nomor baris (null untuk cockpit) |
| `col` | varchar (nullable) | Huruf kolom (A, B, C, ...) |
| `class_type` | varchar | `cockpit`, `first`, `business`, `economy` |
| `expiry_date` | date (nullable) | Tanggal kadaluarsa life vest |

**Unique constraint**: `(registration, seat_id)`

### Tabel `airlines`

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | int (PK) | Auto increment |
| `name` | varchar | Nama maskapai |
| `code` | varchar (nullable) | Kode IATA (GA, QG) |
| `icon` | varchar | Emoji (default: 🏢) |

### Relasi

```
airlines (1) ──── (N) aircraft
aircraft.registration (1) ──── (N) seats
```

---

## 5. 🛤️ Routes & Controllers

### Routes (`routes/web.php`)

| Method | URL | Controller/Method | Name | Deskripsi |
|--------|-----|-------------------|------|-----------|
| GET | `/` | `DashboardController@__invoke` | `dashboard` | Halaman dashboard |
| GET | `/aircraft/{reg}` | `AircraftController@show` | `aircraft.show` | Seat map |
| POST | `/aircraft/{reg}/update-seats` | `AircraftController@updateSeats` | `aircraft.updateSeats` | Update expiry |
| DELETE | `/aircraft/{reg}/delete-seat` | `AircraftController@deleteSeat` | `aircraft.deleteSeat` | Hapus spare seat |
| GET | `/aircraft/{reg}/report` | `ReportController@exportPdf` | `reports.pdf` | Export PDF |
| GET | `/aircraft/{reg}/blank-form` | `ReportController@exportBlankForm` | `reports.blank` | Blank Form |
| GET | `/aircraft/{reg}/batch-input` | `AircraftController@batchInput` | `aircraft.batchInput` | Batch Input form |
| POST | `/aircraft/{reg}/batch-input` | `AircraftController@storeBatchInput` | `aircraft.storeBatchInput` | Simpan batch |
| Resource | `/fleet` | `FleetController` | `fleet.*` | CRUD pesawat |
| GET | `/fleet/airlines/create` | `FleetController@createAirline` | `airlines.create` | Form tambah airline |
| POST | `/fleet/airlines` | `FleetController@storeAirline` | `airlines.store` | Simpan airline |
| GET | `/fleet/airlines/{id}/edit` | `FleetController@editAirline` | `airlines.edit` | Form edit airline |
| PUT | `/fleet/airlines/{id}` | `FleetController@updateAirline` | `airlines.update` | Update airline |
| DELETE | `/fleet/airlines/{id}` | `FleetController@destroyAirline` | `airlines.destroy` | Hapus airline |

### Controllers

#### `DashboardController`
- Menghitung status semua seat per pesawat
- Mengelompokkan per airline → per tipe
- Menghitung `$perFleetStats` untuk dropdown filter fleet
- Menghitung P/N summary

#### `AircraftController`
- `show()` — Memuat seat map; return view `aircraft/{layout}`.
- `updateSeats()` — Update tanggal expiry untuk satu atau banyak kursi.
- `deleteSeat()` — Hapus spare seat (PAX/INF) dari database.
- `batchInput()` — Render form batch input dengan sections berdasarkan `config/aircraft_class_rows.php`.
- `storeBatchInput()` — Proses input massal; membuat/update record `Seat` per kolom per baris.
- `parseFlexibleDate()` — Parse berbagai format tanggal (`Oct-25`, `24-Jan-25`, `01/03/2030`).

#### `ReportController`
- `exportPdf()` — Render PDF lengkap dengan warna status dan tanggal.
- `exportBlankForm()` — Render PDF form kosong; menentukan `maxPax`/`maxInf` berdasarkan tipe pesawat.

#### `FleetController`
- Standard Laravel Resource Controller untuk CRUD pesawat.
- Tambahan method untuk CRUD airline (`createAirline`, `storeAirline`, dll).

---

## 6. ✈️ Membuat Layout Baru (Step-by-Step)

Jika ada pesawat dengan konfigurasi kursi yang belum pernah ada, ikuti langkah berikut:

### Step 1: Buat Partial (Seat Map)

File ini berisi konfigurasi kursi sesungguhnya.

**Lokasi**: `resources/views/aircraft/partials/`

1. Copy file yang paling mirip. Contoh untuk A330 layout baru:
   ```bash
   cp resources/views/aircraft/partials/a330-300a.blade.php resources/views/aircraft/partials/a330-300d.blade.php
   ```

2. Edit file baru tersebut. Struktur partial:

   ```blade
   <!-- Cabin Section -->
   <section class="cabin-section">
       <h2>Economy</h2>
       <div class="cabin-grid">
           <!-- Header kolom -->
           <div class="header-row">
               <div class="row-number"></div>
               <div class="col-header" data-col="A">A</div>
               <div class="col-header" data-col="B">B</div>
               <div class="col-header" data-col="C">C</div>
               <div class="aisle"></div>
               <div class="col-header" data-col="D">D</div>
               <div class="col-header" data-col="E">E</div>
               <div class="col-header" data-col="F">F</div>
           </div>

           <!-- Baris kursi -->
           @foreach(range(21, 46) as $row)
               <div class="seat-row" data-row="{{ $row }}">
                   <div class="row-number" data-row="{{ $row }}">{{ $row }}</div>
                   @foreach(['A','B','C'] as $col)
                       <x-seat-cell :row="$row" :col="$col" :seats="$seats" />
                   @endforeach
                   <div class="aisle"></div>
                   @foreach(['D','E','F'] as $col)
                       <x-seat-cell :row="$row" :col="$col" :seats="$seats" />
                   @endforeach
               </div>
           @endforeach
       </div>
   </section>

   <!-- Spare Section: PAX & INF -->
   <section class="cabin-section">
       <h2>Spare</h2>
       <div class="spare-grid">
           <!-- PAX Column -->
           <div class="spare-column" id="pax-column">
               <h3>PAX</h3>
               <div class="spare-items" id="pax-items">
                   @php
                       $paxSeats = collect($seats)->filter(fn($s, $id) => str_starts_with($id, 'pax-'))
                           ->sortBy(fn($s, $id) => (int) str_replace('pax-', '', $id));
                   @endphp
                   @forelse($paxSeats as $seatId => $seat)
                       @php
                           $num = str_replace('pax-', '', $seatId);
                           $status = $seat?->status ?? 'no-data';
                           $expiryDate = $seat?->expiry_date?->format($dateFormat) ?? '-';
                       @endphp
                       <div class="seat-card spare-card status-{{ $status }}" data-seat="{{ $seatId }}">
                           <button type="button" class="btn-delete-spare" title="Delete">&times;</button>
                           <div class="seat-id">{{ $num }}</div>
                           <div class="seat-date" data-date="{{ $seat?->expiry_date?->format('Y-m-d') ?? '' }}">
                               {{ $expiryDate }}
                           </div>
                       </div>
                   @empty
                       <p class="empty-message">Belum ada data PAX</p>
                   @endforelse
               </div>
               <button type="button" class="btn btn-add-spare" data-type="pax">+ Add PAX</button>
           </div>

           <!-- INF Column (sama structurenya, ganti pax → inf) -->
           ...
       </div>
   </section>
   ```

3. **Sesuaikan**:
   - Kolom header (A–F untuk narrow body, A–K untuk wide body)
   - Range baris (`range(21, 46)`)
   - Konfigurasi aisle (lorong di tengah)
   - Jika ada baris yang di-skip, gunakan: `@if(!in_array($row, [13, 24]))`

### Step 2: Buat Template Wrapper

File ini membungkus partial dengan layout utama.

**Lokasi**: `resources/views/aircraft/`

1. Copy template yang mirip:
   ```bash
   cp resources/views/aircraft/a330-300a.blade.php resources/views/aircraft/a330-300d.blade.php
   ```

2. Edit baris `@include` agar menunjuk ke partial yang baru:

   ```blade
   @extends('layouts.app')

   @section('header-right')
       <x-aircraft-header-info :aircraft="$aircraft" :registration="$registration" />
   @endsection

   @section('content')
       <!-- Toolbar -->
       <x-toolbar :registration="$registration" />

       <!-- Part Number Info -->
       <x-part-number-bar :aircraft="$aircraft" :qtyAdult="$qtyAdult ?? 0"
           :qtyCrew="$qtyCrew ?? 0" :qtyInfant="$qtyInfant ?? 0"
           :expAdult="$expAdult ?? 0" :expCrew="$expCrew ?? 0" :expInfant="$expInfant ?? 0" />

       <!-- Status Legend -->
       <x-status-legend />
       @include('aircraft.partials.a330-300d')  {{-- ← GANTI NAMA INI --}}

       <!-- Date Modal -->
       @include('components.date-modal')
   @endsection

   @push('scripts')
       <script>
           window.AIRCRAFT_CONFIG = {
               registration: '{{ $registration }}',
               updateUrl: '{{ route('aircraft.updateSeats', $registration) }}',
               deleteUrl: '{{ route('aircraft.deleteSeat', $registration) }}',
               csrfToken: '{{ csrf_token() }}'
           };
       </script>
   @endpush
   ```

### Step 3: Konfigurasi Class Rows

Edit `config/aircraft_class_rows.php`:

```php
'a330-300d' => [
    'business' => range(6, 11),
    'economy'  => array_diff(range(21, 55), [24]), // skip row 24
],
```

**Penting**: Konfigurasi ini menentukan class_type saat menyimpan seat ke database.

### Step 4: Auto-Detect

Selesai! Sistem akan **otomatis mendeteksi** layout baru.
Saat menambahkan pesawat di Fleet Manager, pilihan `a330-300d` akan langsung muncul di dropdown layout.

> ⚠️ **Catatan Penting**: Nama file partial dan template **HARUS SAMA** (contoh: kedua file bernama `a330-300d.blade.php`).

---

## 7. ➕ Menambah Tipe Pesawat Baru

Jika menambahkan **tipe pesawat yang belum ada** (misalnya Boeing 787), lakukan:

### 1. Buat Layout

Ikuti [Step 1–4 di atas](#6--membuat-layout-baru-step-by-step).

### 2. Update Buffer Spare di Blank Form

Edit `app/Http/Controllers/ReportController.php`, method `exportBlankForm()`:

```php
// Tambahkan elseif baru:
} elseif (str_contains($type, '787')) {
    $maxPax = 20; $maxInf = 30;  // Sesuaikan angka buffer
}
```

### 3. Tambah Pesawat via Fleet Manager

Buka `/fleet` → Add New Aircraft → Pilih layout yang baru dibuat.

---

## 8. 🏢 Menambah Airline Baru

### Via Fleet Manager (UI)

1. Buka `/fleet` → Tab Airlines → **+ Add New Airline**
2. Isi nama dan kode IATA → Save

### Via Migration/Seeder (Code)

```php
// database/seeders/AirlineSeeder.php
DB::table('airlines')->insert([
    'name' => 'Batik Air',
    'code' => 'ID',
    'icon' => '🏢',
    'created_at' => now(),
    'updated_at' => now(),
]);
```

### Via Migration

```php
// Di migration create_airlines_table.php sudah ada insert default:
DB::table('airlines')->insert([
    ['name' => 'Garuda Indonesia', 'code' => 'GA', 'icon' => '🦅', ...],
]);
```

---

## 9. 🎨 CSS & Theming

### File CSS

| File | Fungsi |
|------|--------|
| `resources/css/style.css` | CSS utama (seat map, toolbar, fleet, dll) |
| `resources/css/dashboard.css` | CSS khusus dashboard |

### Dark / Light Mode

Tema menggunakan CSS Variables di `:root` dan `[data-theme="dark"]`:

```css
:root {
    --bg-primary: #15161a;
    --bg-secondary: #1e1f25;
    --bg-card: #25262d;
    --text-primary: #ffffff;
    --text-secondary: #9ca3af;
    --primary: #3b82f6;
    --danger: #ef4444;
    --border: #2d2e36;
    /* ... dll */
}
```

Toggle tema disimpan di `localStorage` oleh JavaScript di `layouts/app.blade.php`.

### Kelas Status

Warna status life vest:

```css
.status-safe    { border-color: #22c55e; background: rgba(34, 197, 94, 0.15); }
.status-warning  { border-color: #f59e0b; background: rgba(245, 158, 11, 0.15); }
.status-critical { border-color: #ef4444; background: rgba(239, 68, 68, 0.15); }
.status-expired  { border-color: #8b5cf6; background: rgba(139, 92, 246, 0.15); }
.status-no-data  { border-color: #64748b; background: rgba(100, 116, 139, 0.1); }
```

---

## 10. 📄 Sistem PDF

### Library

Menggunakan **Barryvdh/DomPDF** (`barryvdh/laravel-dompdf`).

### Template PDF

| File | Fungsi |
|------|--------|
| `reports/seat-map.blade.php` | PDF lengkap (dengan warna & tanggal) |
| `reports/blank-form.blade.php` | PDF form kosong (untuk inspeksi manual) |

### Cara Kerja

1. Controller memanggil `Pdf::loadView('reports.xxx', $data)`
2. View blade di-render menjadi HTML
3. DomPDF mengkonversi HTML → PDF
4. PDF di-stream ke browser (`$pdf->stream(...)`)

### Penting untuk DomPDF

- CSS **inline** atau `<style>` tag di dalam view (tidak bisa external CSS).
- **Tidak support** semua CSS modern (contoh: `:last-of-type`, `flexbox` terbatas, `grid` tidak didukung).
- Gunakan `display: inline-block` dan `float` untuk layout.
- `page-break-inside: avoid` untuk menghindari potongan bagian.
- Ukuran kertas diset via `$pdf->setPaper('a4', 'portrait')`.

### Spare Buffer Blank Form

Di `ReportController::exportBlankForm()`, jumlah kotak spare ditentukan per tipe:

```php
if (str_contains($type, 'A320')) {
    $maxPax = 15; $maxInf = 20;
} elseif (str_contains($type, 'A330')) {
    $maxPax = 15; $maxInf = 40;
} elseif (str_contains($type, 'ATR')) {
    $maxPax = 10; $maxInf = 10;
} elseif (str_contains($type, '737')) {
    $maxPax = 10; $maxInf = 25;
} elseif (str_contains($type, '777')) {
    $maxPax = 35; $maxInf = 40;
}
```

Nilai ini ditentukan dengan menambahkan buffer di atas jumlah maksimum spare aktual.

---

## 11. 🔄 Alur Request

### Alur Update Tanggal Expiry (Seat Map)

```
User klik kursi → Select kursi → Klik "Set Date" → Pilih tanggal → Klik "Apply"
    ↓
JavaScript (app.js) → POST /aircraft/{reg}/update-seats
    ↓
AircraftController@updateSeats
    ↓
Seat::updateOrCreate() → Update database
    ↓
Response JSON → JavaScript update warna kursi di UI
```

### Alur Batch Input

```
User buka Batch Input → Paste data dari Excel → Klik "Save All"
    ↓
POST /aircraft/{reg}/batch-input
    ↓
AircraftController@storeBatchInput
    ↓
Loop per section → per kolom → per baris:
    parseFlexibleDate() → Seat::updateOrCreate()
    ↓
Redirect ke seat map dengan pesan sukses
```

### Alur Export PDF

```
User klik "Export PDF" → GET /aircraft/{reg}/report
    ↓
ReportController@exportPdf
    ↓
Seat::where(registration) -> keyBy(seat_id)
    ↓
Pdf::loadView('reports.seat-map', $data)
    ↓
DomPDF render HTML -> PDF
    ↓
$pdf->stream() -> Browser menampilkan PDF
```

---

## 12. Smart PDF Scanner dan Multi-Stage OCR Refinement

Fitur Smart PDF Scanner mengotomatiskan input data dari lembar LOPA fisik menggunakan pipeline pemrosesan citra dan kecerdasan buatan multi-tahap.

### Arsitektur dan Komponen

Fitur ini didukung oleh tiga layanan utama di folder app/Services:

1. **OcrPreprocessService**: Menghubungkan Laravel dengan script Python.
2. **PdfParserService**: Mengelola komunikasi dengan API AI (Flaz.id) untuk ekstraksi dan perbaikan data.
3. **VerificationService**: Memvalidasi data hasil ekstraksi terhadap tata letak (layout) kursi resmi di database.

### Preprocessing Citra (Python & OpenCV)

Script Python di `scripts/ocr_preprocess.py` bertanggung jawab atas:
- **Konversi PDF ke PNG**: Menggunakan Ghostscript dengan resolusi 300 DPI.
- **Koreksi Kemiringan (Deskew & Rotate)**: Mendeteksi sudut kemiringan teks dan merotasi gambar secara otomatis agar tegak lurus.
- **Tiling Gambar (Pemotongan Horizontal)**: Memecah gambar LOPA halaman penuh menjadi 3 potongan gambar horizontal (strips) dengan overlap sebesar 80px. Tiling ini sangat krusial untuk mencegah "row drift" (masalah di mana model AI salah memetakan baris karena halaman terlalu panjang).
- **Peningkatan Kontras**: Menggunakan algoritma CLAHE OpenCV untuk memperjelas stempel tinta dan tulisan tangan pulpen tanpa menghilangkan garis tabel pembatas.

### Alur Ekstraksi Multi-Tahap (Multi-Stage OCR)

Proses ekstraksi berjalan dalam dua tahap utama melalui API Flaz.id:

#### Tahap 1: Ekstraksi Struktur LOPA (Stage 1 OCR)
- Gambar potongan (tiles) dikirim ke model utama (default: Claude via Flaz API).
- AI membaca setiap baris dan kolom sesuai panduan prompt ketat dan menghasilkan struktur JSON awal yang berisi baris kursi, tanggal kedaluwarsa yang terbaca, dan status keyakinan (confidence).

#### Tahap 2: Refinement Data (Stage 2 Refinement)
- Jika opsi `FLAZ_REFINEMENT_ENABLED` bernilai true, hasil ekstraksi Tahap 1 akan dikirim kembali bersama file gambar asli ke model penalaran tingkat tinggi (default: GPT-5 via Flaz API).
- Model GPT-5 membandingkan data teks hasil Tahap 1 dengan gambar asli untuk mencari ketidakcocokan, memperbaiki penulisan bulan (misalnya konversi dari bahasa Indonesia ke Inggris), dan memperbaiki karakter yang kurang terbaca (seperti huruf O yang dibaca sebagai angka 0).
- Tahap ini menghasilkan metadata tambahan berupa jumlah field yang dikoreksi (`refinement_corrections`).

### Konfigurasi Environment (.env)

Pengembang harus mengatur variabel berikut agar sistem pemindaian berfungsi:
- `PYTHON_PATH`: Jalur eksekusi Python lokal (misal: "python").
- `TESSERACT_PATH`: Jalur file tesseract.exe.
- `GHOSTSCRIPT_PATH`: Jalur file gswin64c.exe.
- `FLAZ_API_KEY`: Kunci API untuk akses layanan Flaz.id.
- `FLAZ_MODEL`: Model untuk Stage 1 (contoh: `claude-sonnet-4-6`).
- `FLAZ_REFINEMENT_ENABLED`: `true` atau `false` untuk mengontrol Stage 2.
- `FLAZ_REFINEMENT_MODEL`: Model untuk Stage 2 (contoh: `gpt-5`).

```
