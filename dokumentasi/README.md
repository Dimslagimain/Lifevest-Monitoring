# 📚 Dokumentasi — Life Vest Tracker

Folder ini berisi panduan lengkap untuk pengguna, developer, dan proposal kerangka acuan kerja proyek.

---

## 📝 Terms of Reference (TOR) / Proposal Proyek

Kerangka Acuan Kerja (KAK) dan proposal pengembangan sistem Life Vest Tracker.

| Format | File |
|--------|------|
| 📄 Markdown | [TERMS_OF_REFERENCE.md](TERMS_OF_REFERENCE.md) ← baca langsung di GitHub |

**Isi:**
- Latar Belakang & Masalah
- Tujuan Proyek
- Ruang Lingkup Pekerjaan (Sistem Login, Dashboard Admin, dll)
- Fitur yang Dibutuhkan (Smart PDF Scanner, dll)
- Target Pengguna (Karyawan internal GMF AeroAsia)
- Deliverables (Source Code, Database, Dokumentasi, User Manual)
- Kriteria Keberhasilan

---

## 📘 User Manual (Panduan Pengguna)

Panduan penggunaan aplikasi Life Vest Tracker untuk end-user / teknisi.

| Format | File |
|--------|------|
| 📄 Markdown | [USER_MANUAL.md](USER_MANUAL.md) ← baca langsung di GitHub |
| 📕 PDF | [User Manual.pdf](User%20Manual.pdf) ← download / print |

**Isi:**
- Dashboard, Fleet Overview, Life Vest Replacement Summary, Quick Stats
- Seat Map (select kursi, set tanggal expiry, Part Number Bar)
- Export PDF & Blank Form
- Batch Input
- Fleet Manager (kelola pesawat, airline, Part Number)
- Dark / Light Mode & Keyboard Shortcuts
- Tips & FAQ (teknis penggunaan app)

---

## 📕 Developer Manual (Panduan Developer)

Panduan teknis untuk developer yang ingin mengembangkan atau maintain aplikasi.

| Format | File |
|--------|------|
| 📄 Markdown | [DEVELOPER_MANUAL.md](DEVELOPER_MANUAL.md) ← baca langsung di GitHub |
| 📕 PDF | [Developer Manual.pdf](Developer%20Manual.pdf) ← download / print |

**Isi:**
- Tech Stack & Setup Development
- Struktur Folder & Database Schema
- Routes & Controllers
- **Step-by-step membuat layout pesawat baru**
- Menambah tipe pesawat & airline baru
- CSS Theming (Dark/Light Mode)
- Sistem PDF (DomPDF)
- Alur Request (Seat Map, Batch Input, Export PDF)

---

## 🛠️ Spesifikasi & Pemetaan Teknis (Technical Specs)

Dokumen pendukung arsitektur, pemetaan tata letak armada, dan log riwayat perubahan sistem.

| Dokumen | Deskripsi |
|---------|-----------|
| 🌐 [AIRCRAFT_LAYOUT_MAPPING.md](AIRCRAFT_LAYOUT_MAPPING.md) | Pemetaan rinci baris, kolom, dan kapasitas kursi untuk tipe armada A330-900 (Business/Economy) dan spesifikasi LOPA lainnya. |
| 🧠 [SMART_PDF_SCANNER_DESIGN.md](SMART_PDF_SCANNER_DESIGN.md) | Penjelasan arsitektur *Hybrid AI OCR*, pipeline pemrosesan citra OpenCV, integrasi API Flaz (Claude/GPT-5), serta penanganan *row drift*. |
| 📝 [CHANGES_SUMMARY.md](CHANGES_SUMMARY.md) | Rangkuman seluruh riwayat pembaruan, refaktorisasi kode, penambahan fitur utama, dan pengujian sistem. |

