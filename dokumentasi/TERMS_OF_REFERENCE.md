# TERMS OF REFERENCE (TOR) / KERANGKA ACUAN KERJA (KAK)
## PROPOSAL PENGEMBANGAN SISTEM LIFE VEST TRACKER - GMF AEROASIA

---

### **1. Latar Belakang**

#### **1.1. Mengapa Website Perlu Dibuat**
Dalam operasional industri penerbangan, aspek keselamatan merupakan prioritas tertinggi yang tidak dapat ditawar. PT GMF AeroAsia Tbk., sebagai penyedia layanan pemeliharaan pesawat terkemuka, memiliki tanggung jawab besar untuk memastikan seluruh alat keselamatan di setiap armada pesawat dalam kondisi layak dan memenuhi standar kepatuhan regulasi keselamatan. Salah satu alat keselamatan krusial yang harus dipantau secara berkala adalah pelampung keselamatan (*life vest*).

Pencatatan dan pemantauan masa berlaku pelampung keselamatan yang dilakukan secara manual atau menggunakan lembaran fisik seperti formulir LOPA (*Layout of Passenger Accommodations*) "Inventory Check" (formulir GMF/Q-447) memiliki beberapa kelemahan, yaitu:
1. Rentan terhadap kesalahan manusia (*human error*) dalam proses pencatatan tanggal kedaluwarsa (*expiry date*).
2. Memerlukan waktu yang lama untuk melakukan rekapitulasi data kelayakan dari seluruh pesawat.
3. Sulitnya melacak pelampung yang mendekati tanggal kedaluwarsa secara cepat.

Oleh karena itu, diperlukan sebuah sistem pemantauan real-time cerdas berbasis web yang dapat mengotomatisasi proses input, visualisasi, pemantauan, dan pelaporan kelayakan pelampung keselamatan pesawat secara akurat dan efisien.

#### **1.2. Masalah yang Ingin Diselesaikan**
Sistem ini dirancang untuk mengatasi permasalahan operasional berikut:
1. **Kesulitan Visualisasi Layout Kabin**: Sulitnya mengidentifikasi letak fisik kursi yang memiliki pelampung kedaluwarsa tanpa adanya peta kursi (*seat map*) digital yang interaktif.
2. **Inefisiensi Proses Input Data**: Proses input data lapangan dari formulir fisik ke dalam sistem yang memakan waktu lama jika dilakukan satu per satu.
3. **Risiko Ketidakpatuhan Regulasi**: Ketiadaan peringatan dini (*early warning*) yang terpusat mengenai pelampung yang akan atau telah kedaluwarsa, yang berpotensi melanggar kepatuhan keselamatan penerbangan.
4. **Kurangnya Jejak Audit (*Audit Trail*)**: Tidak adanya catatan riwayat perubahan data tanggal kedaluwarsa yang akurat untuk melacak siapa, kapan, dan perubahan apa yang telah dilakukan pada pelampung keselamatan.
5. **Kesulitan Pengolahan Dokumen LOPA**: Kebutuhan untuk mentranskrip data tulisan tangan atau stempel dari file scan LOPA PDF ke format digital secara otomatis.

> [!TIP]
> **Rekomendasi Lampiran Gambar Pendukung Bab 1:**
> * **Kode Gambar**: Gambar 1.1
> * **Nama Gambar**: Contoh Lembar Fisik LOPA (Form GMF/Q-447) Pengecekan Lapangan.
> * **Deskripsi & Kegunaan**: Foto atau salinan digital scan dokumen LOPA fisik dengan coretan tulisan tangan/stempel inspeksi lapangan. Gambar ini berguna untuk memberikan pemahaman visual mengenai kompleksitas data yang menjadi latar belakang masalah.

---

### **2. Tujuan Proyek**
Proyek pengembangan sistem Life Vest Tracker ini bertujuan untuk:
1. Menyediakan dasbor kendali (*Command Center*) terpusat untuk memantau status kesehatan keselamatan (*health status*) pelampung di seluruh armada pesawat secara real-time.
2. Mengotomatisasi ekstraksi data dari lembar inspeksi fisik LOPA menggunakan teknologi pemrosesan gambar dan kecerdasan buatan (*Hybrid AI OCR*).
3. Menyediakan representasi visual 2D tata letak kursi pesawat (*Interactive Seat Map*) guna mempercepat pembaruan data tanggal kedaluwarsa secara massal.
4. Meningkatkan akurasi data serta memudahkan kepatuhan terhadap audit keselamatan penerbangan melalui pencatatan riwayat aktivitas perubahan (*Audit Trail*).
5. Menyediakan ekspor dokumen pelaporan yang terstandardisasi baik dalam bentuk PDF berwarna maupun formulir kosong untuk inspeksi lapangan.

---

### **3. Ruang Lingkup Pekerjaan (Scope of Work)**

#### **3.1. Pembuatan Landing Page**


#### **3.2. Sistem Login Pengguna**
Pengembangan mekanisme keamanan otentikasi pengguna untuk membatasi akses sistem berdasarkan peran (*Role-Based Access Control - RBAC*). Fitur ini mencakup halaman login, otentikasi sesi aman, serta manajemen profil pengguna untuk pembaruan kata sandi.

#### **3.3. Dashboard Admin**
Pembuatan pusat kendali visual (*Command Center*) yang menyajikan informasi secara real-time:
* **Fleet Overview**: Statistik ringkasan kondisi pelampung (Safe, Warning, Critical, Expired, No Data) di seluruh maskapai dan tipe pesawat.
* **Filter Fleet Cerdas**: Penyaringan data berdasarkan maskapai, tipe pesawat (A320, A330, ATR72, B737, B777), status kesehatan, dan registrasi pesawat.
* **Life Vest Replacement Summary**: Ringkasan kebutuhan penggantian pelampung per *Part Number* (Adult, Crew, Infant) yang terintegrasi dengan daftar registrasi pesawat terkait.
* **Quick Stats & Activity Logs**: Informasi cepat mengenai *Health Score* armada, jumlah kursi terlacak, serta akses cepat ke log aktivitas audit terbaru.

> [!TIP]
> **Rekomendasi Lampiran Gambar Pendukung Bab 3:**
> * **Kode Gambar**: Gambar 3.1
> * **Nama Gambar**: Antarmuka Dashboard Utama (Command Center) - Dark Mode.
> * **Deskripsi & Kegunaan**: Screenshot halaman utama dashboard sistem yang menunjukkan ringkasan fleet, diagram lingkaran/batang status pelampung, serta panel filter pesawat. Berguna untuk melampirkan desain UI dasbor pada laporan.
>
> * **Kode Gambar**: Gambar 3.2
> * **Nama Gambar**: Peta Kursi 2D Interaktif (Interactive Seat Map).
> * **Deskripsi & Kegunaan**: Screenshot tampilan layout kursi pesawat dengan visualisasi warna status pelampung (Hijau, Kuning, Merah, Ungu) dan modal *Bulk Update Expiry Date*. Berguna untuk mendokumentasikan ruang lingkup antarmuka interaksi seat map.

#### **3.4. Integrasi Payment Gateway**


#### **3.5. Responsive Mobile**


---

### **4. Fitur yang Dibutuhkan**

#### **4.1. Registrasi Pengguna**


#### **4.2. Manajemen Konten**


#### **4.3. Upload Dokumen**
Implementasi fitur **Smart PDF Scanner (Hybrid AI OCR)** untuk mengunggah dokumen scan PDF formulir LOPA "Inventory Check" (formulir GMF/Q-447). Sistem mengadopsi pipeline multi-tahap:
1. Konversi halaman PDF menjadi gambar resolusi tinggi menggunakan Ghostscript.
2. Pra-pemrosesan gambar menggunakan OpenCV dan PyTesseract (koreksi rotasi dan kontras).
3. Ekstraksi teks sekunder menggunakan Google Cloud Vision API.
4. Pemetaan baris dan kolom kabin otomatis menggunakan Model Bahasa Besar (LLM seperti Claude/Gemini/GPT) ke database.
5. Halaman peninjauan (*Review Page*) hasil ekstraksi sebelum diekspor ke Excel atau template impor sistem.

> [!TIP]
> **Rekomendasi Lampiran Gambar Pendukung Bab 4:**
> * **Kode Gambar**: Gambar 4.1
> * **Nama Gambar**: Diagram Alur Kerja (Pipeline) Smart PDF Scanner.
> * **Deskripsi & Kegunaan**: Skema diagram/flowchart yang memperlihatkan alur pengunggahan PDF, pemrosesan OpenCV, ekstraksi Google Vision OCR, pemetaan AI (LLM), hingga penyimpanan ke database.
>

>
> * **Kode Gambar**: Gambar 4.2
> * **Nama Gambar**: Antarmuka Halaman Peninjauan (Review & Verification Page) PDF Scan.
> * **Deskripsi & Kegungaan**: Screenshot halaman peninjauan yang menampilkan file PDF asli berdampingan dengan tabel draf hasil ekstraksi OCR di web, termasuk baris yang terkena bendera peringatan (*uncertainty flag*).

#### **4.4. Notifikasi Email**


#### **4.5. Chatbot, dll.**


---

### **5. Target Pengguna**

#### **5.1. Mahasiswa**


#### **5.2. Karyawan**
Sistem ini ditargetkan untuk karyawan internal GMF AeroAsia dengan pembagian peran sebagai berikut:
1. **Superadmin (IT / System Administrator)**: Personel dengan hak penuh untuk mengelola pengguna (tambah, edit, suspensi), melakukan import data massal (*Bulk Import*), serta mengoperasikan fitur Smart PDF Scanner.
2. **Admin TNP (Engineering & Maintenance Team)**: Personel teknis yang berwenang memperbarui tanggal kedaluwarsa pelampung melalui peta kursi, menggunakan Batch Input, serta mengelola data pesawat dan maskapai di Fleet Manager.
3. **User Viewer (Viewer / Auditor / Management)**: Pengguna yang hanya memiliki hak akses membaca (read-only) dasbor, melihat peta kursi, dan mengunduh laporan PDF/Excel untuk kebutuhan inspeksi atau audit.

#### **5.3. Pelanggan Umum**


---

### **6. Deliverables (Output)**

#### **6.1. Source Code**
* Repositori lengkap aplikasi berbasis kerangka kerja Laravel 12.x dan PHP 8.2+.
* Aset frontend yang dikompilasi menggunakan Vite dengan gaya desain *Dark Mode* berbasis *Glassmorphism* (Vanilla CSS & JavaScript).
* Skrip pemrosesan gambar berbasis Python (`scripts/ocr_preprocess.py`) untuk modul OCR pra-pemrosesan.

#### **6.2. Database**
* Skema database relasional MySQL / MariaDB yang mencakup tabel:
  - `airlines` (manajemen maskapai)
  - `aircraft` (registrasi dan konfigurasi tipe pesawat)
  - `seats` (posisi kursi, kelas kabin, dan tanggal kedaluwarsa pelampung)
  - `users` (manajemen pengguna dan peran RBAC)
  - `audit_logs` (riwayat aktivitas pembaruan)
* Database migration files dan seeders untuk mempermudah inisialisasi data awal pengujian.

> [!TIP]
> **Rekomendasi Lampiran Gambar Pendukung Bab 6:**
> * **Kode Gambar**: Gambar 6.1
> * **Nama Gambar**: Entity Relationship Diagram (ERD) Database Life Vest Tracker.
> * **Deskripsi & Kegunaan**: Diagram ERD yang memetakan hubungan antartabel di dalam database (misal: relasi *one-to-many* antara `airlines` ke `aircraft`, dan `aircraft` ke `seats`, serta relasi log audit).

#### **6.3. Dokumentasi**
* Panduan Teknis Pengembang (*Developer Manual*) dalam format Markdown (`dokumentasi/DEVELOPER_MANUAL.md`) dan file PDF (`dokumentasi/Developer Manual.pdf`) yang mendokumentasikan skema database, arsitektur sistem, struktur folder, perutean (*routes*), dan petunjuk pembuatan tata letak (*layout*) pesawat baru.

#### **6.4. Manual Pengguna**
* Panduan Pengguna (*User Manual*) dalam format Markdown (`dokumentasi/USER_MANUAL.md`) dan file PDF (`dokumentasi/User Manual.pdf`) yang berisi instruksi lengkap operasional dasbor, peta kursi interaktif, ekspor PDF berwarna/blank form, input massal, dan manajemen armada.

---

### **7. Jadwal Pelaksanaan**

#### **7.1. Analisis Kebutuhan**


#### **7.2. Desain UI/UX**


#### **7.3. Development**


#### **7.4. Testing**


#### **7.5. Go-live**


---

### **8. Anggaran (Opsional)**

#### **8.1. Estimasi Biaya Proyek**


---

### **9. Kriteria Keberhasilan**

#### **9.1. Website Dapat Diakses 24/7**
Sistem web dirancang agar siap di-host pada server intranet/lokal milik PT GMF AeroAsia Tbk., sehingga dapat diakses kapan saja oleh unit kerja terkait selama terhubung ke jaringan internal perusahaan.

#### **9.2. Loading < 3 Detik**
* Halaman utama dashboard dan visualisasi seat map dioptimalkan dengan kueri database yang efisien sehingga waktu pemuatan halaman di bawah 3 detik pada kondisi jaringan intranet normal.
* Proses pembaruan tanggal kedaluwarsa kursi menggunakan pertukaran data asinkron (Fetch API/AJAX) tanpa perlu memuat ulang seluruh halaman.

#### **9.3. Berjalan di Desktop dan Mobile**


---
*(c) 2026 GMF AeroAsia - Life Vest Tracker - Engineering Excellence*
