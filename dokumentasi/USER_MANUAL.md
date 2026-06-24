# 📘 User Manual — Life Vest Tracker

---

## 📋 Daftar Isi

1. [Dashboard](#1--dashboard)
2. [Seat Map](#2--seat-map--halaman-detail-pesawat)
3. [Export PDF](#3--export-pdf)
4. [Blank Form](#4--blank-form)
5. [Batch Input](#5--batch-input)
6. [Fleet Manager](#6--fleet-manager)
7. [Dark / Light Mode](#7--dark--light-mode)
8. [Keyboard Shortcuts](#8--keyboard-shortcuts)
9. [Arti Warna Status](#9--arti-warna-status)
10. [Tips & FAQ](#10--tips--faq)
11. [Smart PDF Scanner](#11-smart-pdf-scanner)

> 💡 Fitur utama: Dashboard → klik kartu pesawat → Seat Map → Set Date / Batch Input / Export PDF

---

## 1. 🏠 Dashboard

Dashboard adalah halaman utama yang menampilkan ringkasan seluruh armada.

### Fleet Overview (Kartu Ringkasan)

Di bagian atas terdapat 4 kartu ringkasan:

| Kartu | Keterangan |
|-------|------------|
| 🟢 **Safe** | Life vest yang masih > 6 bulan dari kadaluarsa |
| 🟡 **Warning** | Life vest yang tersisa 3–6 bulan |
| 🔴 **Critical** | Life vest yang tersisa < 3 bulan |
| 🟣 **Expired** | Life vest yang sudah melewati tanggal kadaluarsa |

### Filter Fleet (Dropdown Multi-Select)

Klik tombol **"✈️ Filter Fleet"** di pojok kanan atas bagian Fleet Overview.

- Akan muncul dropdown dengan **checkbox** untuk setiap tipe pesawat (A320, A330, ATR72, B737, B777).
- **Centang satu atau lebih tipe** → angka di kartu ringkasan akan berubah sesuai total dari tipe yang dipilih.
- **"All Fleets"** → centang/uncentang semua sekaligus.
- **Default**: semua tipe tercentang (menampilkan total keseluruhan).

### Filter Pesawat

Klik tombol **"🔍 Filter"** untuk membuka panel filter lanjutan:

| Filter | Fungsi |
|--------|--------|
| **Airline** | Filter berdasarkan maskapai (Garuda Indonesia, Citilink, dll) |
| **Type** | Filter berdasarkan tipe pesawat (B737-800, A330-300, dll) |
| **Status** | Filter berdasarkan status pesawat (Active / Prolong) |
| **Health** | Filter berdasarkan kesehatan life vest (Safe / Warning / Critical) |
| **Search** | Cari berdasarkan nomor registrasi |

Klik **"Clear"** untuk mereset semua filter.

### Kartu Pesawat

Setiap pesawat ditampilkan sebagai kartu yang berisi:
- Nomor registrasi
- Tipe pesawat
- Mini bar status (hijau/kuning/merah/ungu)
- Health badge (Safe/Warning/Critical)

**Klik kartu** untuk masuk ke halaman seat map pesawat tersebut.

### Life Vest Replacement Summary

Di bawah Fleet Status, terdapat section **🔄 Life Vest Replacement Summary** yang menampilkan:

- Daftar semua **Part Number** life vest yang digunakan (Adult, Crew, Infant).
- **Total life vest** dan **jumlah yang expired** per Part Number.
- **Breakdown per registrasi pesawat** — menunjukkan berapa life vest expired di masing-masing pesawat.
- Kartu berwarna **merah** jika ada expired, **hijau** jika semua aman.

Gunakan section ini untuk melihat Part Number mana yang paling banyak perlu diganti.

### Quick Stats

Di bagian paling bawah Dashboard terdapat **📊 Quick Stats**:

| Stat | Keterangan |
|------|------------|
| **Airlines** | Jumlah maskapai terdaftar |
| **Aircraft** | Jumlah pesawat terdaftar |
| **Total Seats Tracked** | Jumlah kursi yang dilacak |
| **Health Score** | Persentase life vest yang masih safe |
| **Needs Attention** | Jumlah life vest yang critical + expired |

---

## 2. 🪑 Seat Map — Halaman Detail Pesawat

Halaman ini menampilkan peta kursi lengkap pesawat beserta status life vest di setiap kursi.

### Navigasi

Akses via klik kartu pesawat di Dashboard, atau langsung ke URL:
```
/aircraft/{registration}
```
Contoh: `/aircraft/PK-GFD`

### Memilih Kursi (Select)

| Aksi | Fungsi |
|------|--------|
| **Klik biasa** | Pilih 1 kursi (hapus selection sebelumnya) |
| **Ctrl + Klik** | Tambah kursi ke selection (multi-select) |
| **Shift + Klik** | Pilih range dari kursi terakhir ke kursi ini |
| **Klik nomor BARIS** (angka di kiri) | Pilih semua kursi di baris tersebut |
| **Klik huruf KOLOM** (huruf di atas) | Pilih semua kursi di kolom tersebut |

### Menghapus Selection (Unselect)

| Aksi | Fungsi |
|------|--------|
| **Klik area kosong** | Hapus semua selection |
| **Tekan ESC** | Hapus semua selection |
| **Ctrl + Klik kursi yang sudah dipilih** | Toggle kursi dari selection |
| **Klik "✖️ Clear Selection"** | Hapus semua selection |

### Set Tanggal Expiry

1. Pilih kursi yang ingin di-update (bisa satu atau banyak sekaligus).
2. Klik tombol **"📅 Set Date"** di toolbar (atau tekan **Enter**).
3. Pilih tanggal expiry life vest dari calendar.
4. Klik **"Apply"** untuk menyimpan.

> 💡 **Tips:** Bisa update banyak kursi sekaligus! Pilih semua kursi yang memiliki tanggal sama, lalu set date satu kali.

### Part Number Bar

Di bawah toolbar terdapat **Part Number Bar** yang menampilkan:

- **ADULT**: Part Number life vest, jumlah total, dan jumlah expired
- **CREW**: Part Number life vest, jumlah total, dan jumlah expired
- **INFANT**: Part Number life vest, jumlah total, dan jumlah expired

Jika ada yang expired, akan muncul badge **⚠️ expired** berwarna merah.

### Toolbar

Toolbar ada di bagian atas halaman seat map, berisi:

| Tombol | Fungsi |
|--------|--------|
| **📅 Set Date** | Set tanggal expiry untuk kursi yang dipilih |
| **✖️ Clear Selection** | Hapus semua selection |
| **Export PDF** | Export seat map sebagai PDF report (buka di tab baru) |
| **Blank Form** | Export form kosong untuk inspeksi manual (buka di tab baru) |
| **⚡ Batch Input** | Buka halaman batch input untuk input data massal |

### Bagian-bagian Seat Map

Seat map terdiri dari beberapa bagian:

1. **Cockpit** — Kursi pilot (Captain, First Officer, Observer, ACM)
2. **Business Class** — Kursi kelas bisnis (jika ada)
3. **Economy Class** — Kursi kelas ekonomi
4. **Spare** — Life vest cadangan (PAX = dewasa, INF = bayi)

---

## 3. 📄 Export PDF

Export seluruh seat map sebagai dokumen PDF berwarna, lengkap dengan status dan tanggal expiry.

### Cara Menggunakan

1. Buka halaman seat map pesawat.
2. Klik tombol **"Export PDF"** di toolbar.
3. PDF akan terbuka di tab baru browser.
4. Dari situ Anda bisa **download** atau **print** langsung.

### Isi PDF

- Header: Nama airline, registrasi, tipe pesawat, tanggal cetak
- Seat map lengkap dengan warna status
- Tanggal expiry di setiap kotak kursi
- Bagian Cockpit, Cabin, dan Spare
- Footer dan tanda tangan

---

## 4. 📝 Blank Form

Form kosong untuk diisi manual oleh teknisi saat inspeksi di lapangan.

### Cara Menggunakan

1. Buka halaman seat map pesawat.
2. Klik tombol **"Blank Form"** di toolbar.
3. PDF form kosong akan terbuka di tab baru.
4. **Print** form tersebut untuk dibawa ke lapangan.

### Isi Blank Form

- Header: Nama airline, registrasi, tipe pesawat, kolom tanggal (manual)
- Kotak kursi kosong (hanya nomor seat, tanggal diisi tangan)
- Instruksi: "Tulis tanggal expired pada setiap kotak seat (format: DD/MM/YYYY)"
- **Spare Section**: Kotak bernomor untuk cadangan PAX & INF

### Jumlah Kotak Spare (Buffer per Tipe)

Jumlah kotak spare yang dicetak sudah dihitung dengan buffer agar mencukupi:

| Tipe | PAX | INF |
|------|:---:|:---:|
| A320 | 15 | 20 |
| A330 | 15 | 40 |
| ATR72 | 10 | 10 |
| B737 | 10 | 25 |
| B777 | 35 | 40 |

- Kolom tanda tangan (Approved By / Checked By)

---

## 5. ⚡ Batch Input

Fitur untuk input data expiry secara massal — cocok untuk copy-paste dari Excel.

### Cara Menggunakan

1. Buka halaman seat map pesawat.
2. Klik tombol **"⚡ Batch Input"** di toolbar.
3. Halaman batch input akan terbuka.

### Cara Input Data

1. Setiap kolom kursi (A, B, C, D, E, F) memiliki **textarea** sendiri.
2. **Copy kolom tanggal dari Excel**, lalu **paste** ke textarea yang sesuai.
3. Setiap baris di textarea = 1 kursi (urutannya dari baris pertama ke bawah).
4. Klik **"Save All"** untuk menyimpan semua data sekaligus.

### Format Tanggal yang Didukung

| Format | Contoh |
|--------|--------|
| `MMM-YY` | `Oct-25`, `Jan-34` |
| `DD-MMM-YY` | `24-Jan-25` |
| `DD/MM/YYYY` | `01/03/2030` |

### Bagian Spare

Di bawah bagian economy, ada textarea terpisah untuk:
- **PAX Spare** — Life vest cadangan dewasa
- **INF Spare** — Life vest cadangan bayi

Paste tanggal saja, jumlah otomatis dihitung.

---

## 6. ⚙️ Fleet Manager

Fleet Manager (`/fleet`) adalah pusat kontrol untuk mengelola data pesawat dan airline.

### Cara Akses

Klik tombol **"Manage Fleet"** di navbar, atau akses langsung: `/fleet`

### Tab Aircraft

Menampilkan daftar seluruh pesawat dalam tabel.

| Fitur | Keterangan |
|-------|------------|
| **Search** | Cari berdasarkan registrasi |
| **Filter Airline** | Filter berdasarkan maskapai |
| **Filter Status** | Filter Active / Prolong |
| **Filter Type** | Filter berdasarkan tipe pesawat |
| **+ Add New Aircraft** | Tambah pesawat baru |

#### Menambah Pesawat Baru

1. Klik **"+ Add New Aircraft"**.
2. Isi form:
   - **Airline**: Pilih maskapai
   - **Registration**: Nomor registrasi (misal: PK-GPC)
   - **Type**: Tipe pesawat (misal: A330-300)
   - **Layout**: Pilih layout kursi dari dropdown
   - **Status**: Pilih Active atau Prolong
3. Klik **Save**.
4. Setelah pesawat dibuat, klik **Edit** untuk mengisi **Part Number** (lihat di bawah).

#### Edit Pesawat

- Klik **"Edit"** di baris pesawat.
- Yang bisa diubah:
  - **Status** (Active / Prolong)
  - **Part Number Adult** — P/N life vest penumpang (contoh: P0723-103W)
  - **Part Number Crew** — P/N life vest crew (contoh: P0723-103WCN)
  - **Part Number Infant** — P/N life vest bayi (contoh: P0640-101)
- Registration, Airline, Type, dan Layout **terkunci** (tidak bisa diubah).
- Klik **Save** untuk menyimpan.

#### Hapus Pesawat

- Klik **"Delete"** di baris pesawat.
- Konfirmasi penghapusan.
- ⚠️ **Data seat pesawat tersebut juga akan terhapus.**

### Tab Airlines

Menampilkan daftar maskapai yang terdaftar.

| Fitur | Keterangan |
|-------|------------|
| **Name** | Nama maskapai |
| **Code** | Kode IATA (GA, QG, dll) |
| **Aircraft Count** | Jumlah pesawat yang terdaftar |

#### Menambah Airline Baru

1. Klik **"+ Add New Airline"**.
2. Isi nama (contoh: Citilink) dan kode IATA (contoh: QG).
3. Klik **Save**.

#### Edit Airline

- Klik **"Edit"** di baris airline.
- Ubah nama atau kode IATA.
- Klik **Save**.

#### Hapus Airline

- Klik **"Delete"** di baris airline.
- ⚠️ **Hanya bisa dihapus jika tidak ada pesawat yang terdaftar di airline tersebut.**

---

## 7. 🌙 Dark / Light Mode

- Klik ikon **🌙** atau **☀️** di navbar (pojok kanan atas).
- Preferensi tema akan tersimpan di browser.
- Semua halaman mendukung kedua mode.

---

## 8. ⌨️ Keyboard Shortcuts

| Shortcut | Fungsi | Halaman |
|----------|--------|---------|
| **Ctrl + A** | Pilih SEMUA kursi | Seat Map |
| **Enter** | Buka dialog Set Date (jika ada kursi terpilih) | Seat Map |
| **Escape (ESC)** | Tutup dialog / Hapus selection | Seat Map |

---

## 9. 🎨 Arti Warna Status

| Warna | Status | Keterangan |
|-------|--------|------------|
| 🟢 **Hijau** | Safe | Expiry > 6 bulan lagi |
| 🟡 **Kuning** | Warning | Expiry 3–6 bulan lagi |
| 🔴 **Merah** | Critical | Expiry < 3 bulan lagi |
| 🟣 **Ungu** | Expired | Sudah melewati tanggal expiry |
| ⚪ **Abu-abu** | No Data | Belum ada tanggal expiry |

---

## 10. 💡 Tips & FAQ

### Tips

- **Select cepat**: Klik nomor baris atau huruf kolom untuk memilih seluruh baris/kolom sekaligus.
- **Batch update**: Pilih banyak kursi yang punya tanggal sama → Set Date sekali saja.
- **Copy-paste dari Excel**: Gunakan Batch Input untuk input data dari spreadsheet.
- **Cetak Blank Form** sebelum inspeksi di lapangan — kotak spare sudah disiapkan dengan jumlah buffer.

### FAQ

#### 📅 Tanggal & Expiry

**Q: Bagaimana cara mengubah tanggal expiry yang sudah di-set?**
A: Pilih kursi yang ingin diubah → klik **Set Date** → pilih tanggal baru → klik **Apply**. Tanggal lama akan langsung ditimpa dengan tanggal baru.

**Q: Bisa set tanggal untuk banyak kursi sekaligus?**
A: Ya. Pilih semua kursi (gunakan Ctrl+Klik, Shift+Klik, atau klik baris/kolom), lalu klik **Set Date** sekali. Semua kursi yang dipilih akan mendapat tanggal yang sama.

**Q: Bagaimana cara menghapus tanggal expiry yang salah?**
A: Saat ini tidak ada fitur hapus tanggal. Set ulang dengan tanggal yang benar menggunakan **Set Date**.

**Q: Apakah tanggal expiry langsung tersimpan ke database setelah klik Apply?**
A: Ya, langsung tersimpan. Tidak perlu save atau submit tambahan. Warna kursi juga langsung berubah sesuai status baru.

---

#### 🪑 Seat Map & Spare

**Q: Bagaimana cara menambah life vest spare?**
A: Klik tombol **"+ Add PAX"** atau **"+ Add INF"** di bagian Spare. Spare baru otomatis bernomor urut.

**Q: Bagaimana cara menghapus spare seat?**
A: Klik tombol **✕** di pojok spare card yang ingin dihapus. Data spare tersebut akan terhapus dari database.

**Q: Kalau spare dihapus, apakah nomor urutnya otomatis berubah?**
A: Tidak. Nomor urut spare tidak berubah setelah dihapus. Spare baru akan menggunakan nomor urut berikutnya.

**Q: Apakah bisa select kursi dari class yang berbeda sekaligus (misalnya Business + Economy)?**
A: Ya, bisa. Gunakan Ctrl+Klik untuk memilih kursi dari class manapun, lalu Set Date sekaligus.

---

#### 📊 Dashboard, Replacement Summary & Part Number

**Q: Replacement Summary menampilkan data dari pesawat mana saja?**
A: Dari **semua pesawat** yang terdaftar. Data di-generate otomatis dari database setiap kali dashboard dibuka.

**Q: Angka expired di Replacement Summary tidak cocok. Kenapa?**
A: Pastikan data expiry di seat map sudah benar. Angka dihitung otomatis dari seluruh kursi yang tanggal expirynya sudah lewat.

**Q: Di Replacement Summary ada breakdown per registrasi. Klik bisa tidak?**
A: Tidak bisa diklik langsung. Catat registrasinya, lalu buka pesawat tersebut di dashboard untuk melihat detail.

**Q: Part Number Bar di seat map menampilkan angka apa?**
A: Menampilkan **jumlah total life vest** dan **jumlah expired** per kategori (Adult / Crew / Infant) untuk pesawat yang sedang dibuka.

**Q: Part Number tidak muncul di Part Number Bar, kenapa?**
A: Part Number diisi di database pesawat (kolom `pn_adult`, `pn_crew`, `pn_infant`). Jika belum diisi, bar tidak muncul.

**Q: Health Score di Quick Stats dihitung dari apa?**
A: `(jumlah safe ÷ total yang punya data) × 100%`. Life vest tanpa data (abu-abu) tidak dihitung.

**Q: Part Number diisi / diedit di mana?**
A: Buka **Fleet Manager** → klik **Edit** pada pesawat → isi field **Part Number Adult**, **Part Number Crew**, dan **Part Number Infant**. Part Number hanya bisa diisi di halaman Edit, bukan saat pertama kali menambahkan pesawat.

**Q: Alur menambah pesawat baru yang lengkap bagaimana?**
A: Tambah pesawat di Fleet Manager → klik **Edit** pada pesawat yang baru ditambahkan → isi Part Number → kembali ke seat map → isi expiry via **Set Date** atau **Batch Input**.

**Q: Part Number bisa diubah setelah diisi?**
A: Ya, tinggal buka **Edit** pesawat lagi dan ganti isinya.

---

#### ✈️ Pesawat & Fleet Manager

**Q: Jika pesawat dihapus dari Fleet Manager, apakah semua data seat juga ikut terhapus?**
A: Ya, **semua data expiry kursi pesawat tersebut akan terhapus permanen** dari database. Pastikan data sudah tidak diperlukan sebelum menghapus.

**Q: Bisakah mengubah registrasi, tipe, atau airline pesawat?**
A: Tidak bisa dari halaman Edit (hanya status yang bisa diubah). Hapus pesawat dan tambahkan ulang dengan data yang benar.

**Q: Kenapa airline tidak bisa dihapus?**
A: Airline hanya bisa dihapus jika **tidak ada pesawat** yang terdaftar di airline tersebut. Hapus semua pesawatnya terlebih dahulu.

**Q: Pesawat baru ditambahkan, tapi data kursinya kosong semua?**
A: Benar. Pesawat baru tidak memiliki data expiry. Gunakan **Batch Input** untuk mengisi dari Excel, atau **Set Date** per kursi.

**Q: Bagaimana jika layout pesawat belum tersedia di dropdown?**
A: Hubungi developer untuk membuat layout baru. Panduannya ada di **Developer Manual**.

**Q: Dua pesawat bisa pakai layout yang sama?**
A: Ya. Layout hanya menentukan susunan kursi, data expiry masing-masing pesawat tetap terpisah.

---

#### 📄 PDF & Blank Form

**Q: PDF dan Blank Form dibuka di mana?**
A: Keduanya terbuka di **tab baru** browser. Dari situ bisa langsung **Ctrl+P** untuk print atau download.

**Q: Apakah PDF otomatis update jika data expiry diubah?**
A: PDF di-generate ulang setiap kali tombol diklik. Jadi selalu menampilkan data terbaru saat tombol ditekan.

**Q: Kertas apa yang direkomendasikan untuk print?**
A: **A4 Portrait**. PDF sudah diatur untuk ukuran tersebut.

**Q: Kenapa kotak spare di Blank Form jumlahnya berbeda per tipe pesawat?**
A: Jumlah kotak sudah disesuaikan dengan buffer per tipe. B777 lebih banyak dibanding ATR72 karena kapasitas lebih besar.

---

#### ⚡ Batch Input

**Q: Batch Input mengupdate kursi apa saja?**
A: Hanya kursi **Economy** dan **Spare** (PAX & INF). Cockpit dan Business harus di-update via **Set Date** di seat map.

**Q: Urutan data di textarea harus bagaimana?**
A: Sesuai **nomor baris kursi** dari atas ke bawah. Baris pertama di textarea = baris pertama di section economy.

**Q: Jika hanya update sebagian kolom, apakah kolom lain terhapus?**
A: Tidak. Kolom yang textarea-nya kosong tidak diproses. Data lama tetap aman.

**Q: Kalau ada baris kosong di tengah data paste, apa yang terjadi?**
A: Baris kosong di-skip. Tidak mengubah data kursi pada posisi tersebut.

**Q: Data yang sudah ada akan ditimpa atau ditambahkan?**
A: **Ditimpa**. Jika kursi sudah punya data expiry, Batch Input akan mengganti dengan tanggal baru.

**Q: Format tanggal apa yang didukung?**
A: Tiga format: `Oct-25`, `24-Jan-25`, dan `01/03/2030`. Format lain tidak dikenali dan akan dilewati.

**Q: Ada error setelah Save, apa yang harus dilakukan?**
A: Periksa format tanggal. Coba paste ulang satu kolom untuk identifikasi data yang bermasalah.

---

## 11. Smart PDF Scanner

Smart PDF Scanner adalah fitur khusus bagi pengguna dengan peran Superadmin untuk mengunggah lembar LOPA ("Inventory Check" formulir GMF/Q-447) fisik dalam bentuk file PDF dan mengekstrak tanggal kedaluwarsa secara otomatis menggunakan kecerdasan buatan.

### Cara Menggunakan

1. Masuk menggunakan akun Superadmin.
2. Klik menu "PDF Scanner" pada bilah navigasi (navbar) bagian atas.
3. Klik tombol "Choose File" dan pilih berkas PDF LOPA yang ingin dipindai.
4. Klik tombol "Upload & Scan LOPA".
5. Sistem akan menampilkan bilah kemajuan (progress bar) yang menunjukkan status pengerjaan:
   - Tahap awal: Mengunggah berkas dan melakukan konversi resolusi tinggi.
   - Stage 1: Kecerdasan buatan (Claude) membaca citra gambar LOPA.
   - Stage 2: Kecerdasan buatan (GPT-5) memvalidasi dan memverifikasi data (Refinement).
6. Setelah proses selesai (biasanya memakan waktu 60-180 detik tergantung jumlah halaman), Anda akan diarahkan ke halaman "Review & Verification".

### Halaman Review & Verifikasi

Halaman review menyajikan perbandingan bersisian untuk validasi data:
- **Bagian Kiri**: Menampilkan visualisasi dokumen PDF asli per halaman agar Anda dapat mencocokkan stempel/tulisan tangan secara langsung.
- **Bagian Kanan**: Menampilkan tabel data hasil ekstraksi AI.

#### Fitur Tabel Hasil Ekstraksi:
- **Pengurutan Otomatis**: Data kursi diurutkan dari kabin paling depan (Cockpit), Business, Economy, hingga cadangan (PAX/INF Spare) di bagian terbawah.
- **Penanda Ketidakpastian (Uncertainty Flag)**: Baris data yang dicurigai salah letak atau tidak sesuai dengan spesifikasi armada akan diberi bendera peringatan berwarna kuning/merah.
- **Statistik Refinement**: Menampilkan informasi model AI yang aktif dan jumlah koreksi otomatis yang dilakukan oleh Stage 2 (Refinement).

### Ekspor Hasil Scan

Setelah memeriksa kecocokan data pada tabel, Anda dapat memilih tindakan di bagian atas tabel:
- **Download Excel**: Mengunduh tabel hasil scan ke dalam berkas spreadsheet Excel (.xlsx).
- **Export to Bulk Import Template**: Mengunduh data ke dalam template resmi yang siap diunggah langsung ke sistem melalui menu Bulk Import untuk memperbarui database pesawat secara massal.



