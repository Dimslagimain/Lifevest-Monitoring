# Perubahan Smart PDF Scanner - Ringkasan

**Tanggal:** 3 Juni 2026  
**Tujuan:** Membuat PDF scanner yang smart dan membungkus setiap registration dengan layout-nya sendiri

---

## 📋 Ringkasan Perubahan

### 1. Konfigurasi Aircraft Layout

#### `config/aircraft_class_rows.php`
- ✅ Diperbarui `a330-900b` dari ekonomi_premium + ekonomi menjadi **ekonomi saja (rows 21-58)**
- ✅ Sama dengan range ekonomi PK-GHE, tapi tanpa business class rows 6-11
- **Perbedaan utama:**
  - PK-GHE (a330-900a): Business 6-11, Economy 21-58
  - PK-GHH (a330-900b): Economy saja 21-58

#### `config/aircraft_economy_sections.php`
- ✅ Diperbarui `a330-900b` sections untuk batch input
- Rows 21-40 dan 41-58 (sama dengan PK-GHE, cocok untuk batch input)

### 2. Database & Seeder

#### `database/seeders/AircraftSeeder.php`
- ✅ PK-GHE, PK-GHF, PK-GHG → `layout: 'a330-900a'` (sudah benar)
- ✅ PK-GHH, PK-GHI → `layout: 'a330-900b'` (sudah benar)

---

## 🧠 Smart Validation Logic

### `app/Services/VerificationService.php`

#### Method Baru: `validateAndCorrectRowsByRegistration()`
```php
protected function validateAndCorrectRowsByRegistration(
    string $registration,
    array $seats
): array
```

**Apa yang dilakukan:**
1. Lookup registration di database → dapatkan aircraft.layout
2. Load config('aircraft_class_rows')[$layout]
3. Extract expected rows dari config
4. Validasi setiap seat terhadap expected rows
5. **Flag atau reject** rows yang tidak sesuai

**Contoh:**
```
PK-GHH dideteksi → layout = 'a330-900b'
Expected rows: 21-58 (NO row 6!)

Seat 6A detected → Row 6 < min(21) → FLAG!
  Issue: "Row 6 below expected minimum 21 for a330-900b"
```

#### Updated Method: `verify()`
```php
public function verify(array $extractedData, ...): array
```

**Pipeline baru:**
1. **validateAndCorrectRowsByRegistration()** ← NEW! Per-aircraft layout check
2. applyRuleBasedCorrections() ← Existing (date format, typos)
3. applyAiValidation() ← Existing (image verification)
4. calculateSummary()

### `app/Http/Controllers/PdfScanController.php`

#### Updated: Dependency Injection
```php
public function __construct(
    PdfParserService $pdfParser,
    VerificationService $verificationService  // ← NEW!
) { ... }
```

#### Updated: `scan()` Method
1. PDF uploaded → PdfParserService extracts (AI)
2. **NEW: VerificationService validates** per-aircraft
3. Layout info ditampilkan di review page
4. Flagged items ditampilkan dengan penjelasan layout

**Output baru:**
```
📋 Expected Layout Structure:
  • business: rows 6-11 (6 rows)
  • economy: rows 21-58 (37 rows)

⚠️ 2 seats flagged for review (possible row/layout mismatches)
```

---

## 📄 Dokumentasi

### `AIRCRAFT_LAYOUT_MAPPING.md` (Updated)
- Group 1: PK-GHE/F/G (a330-900a) - Business + Economy
- Group 2: PK-GHH/I (a330-900b) - Economy Only
- Tabel perbandingan
- **Baru:** Smart PDF Scanner Integration section

### `SMART_PDF_SCANNER_DESIGN.md` (Baru)
- Arsitektur lengkap sistem
- Database layer, Config layer, Service layer
- Scenario A, B, C dengan contoh step-by-step
- Troubleshooting guide
- Future enhancements

---

## 🎯 Workflow Contoh: PK-GHH PDF Scan

### Scenario: Benar (semua seats dari row 21)

```
User upload PDF
    ↓
AI extracts: [21A 13MAR2026, 21C 17APR2028, ..., 58K 22JAN2030]
    ↓
VerificationService.validate():
  1. Lookup: PK-GHH → layout = 'a330-900b'
  2. Expected rows: 21-58 (no business!)
  3. Check 21A: row 21 ✓ in range
  4. Check 58K: row 58 ✓ in range
    ↓
Result: ✅ No flags, all seats pass
    ↓
Review page displays:
  • Registration: PK-GHH
  • Aircraft Type: A330-900
  • Layout: a330-900b
  • 📋 Economy: rows 21-58 (37 rows)
  • ✅ 0 seats flagged
```

### Scenario: Salah (ada seats dari row 6-11)

```
User upload PDF (mixed atau error)
    ↓
AI extracts: [6C 15OCT2027, 11K 08DEC2026, 21A 13MAR2026, ..., 58K 22JAN2030]
    ↓
VerificationService.validate():
  1. Lookup: PK-GHH → layout = 'a330-900b'
  2. Expected rows: 21-58 (NO row 6!)
  3. Check 6C: row 6 < min(21) → FLAG!
  4. Check 11K: row 11 < min(21) → FLAG!
  5. Check 21A: row 21 ✓ pass
  6. Check 58K: row 58 ✓ pass
    ↓
Result: ⚠️ 2 seats flagged
    ↓
Review page displays:
  • Registration: PK-GHH
  • Layout: a330-900b
  • 📋 Economy: rows 21-58 (37 rows)
  • ⚠️ 2 seats flagged:
    - [6C] Row 6 below expected minimum 21 for a330-900b
    - [11K] Row 11 below expected minimum 21 for a330-900b
    ↓
User must manually remove rows 6 & 11 or correct them
```

---

## ✅ Checklist Perubahan

- [x] Update `config/aircraft_class_rows.php` - a330-900b
- [x] Update `config/aircraft_economy_sections.php` - a330-900b  
- [x] Verify `database/seeders/AircraftSeeder.php` - PK-GHH/I correct
- [x] Add `validateAndCorrectRowsByRegistration()` method
- [x] Update `verify()` method flow
- [x] Integrate VerificationService ke PdfScanController
- [x] Update PdfScanController to show layout info
- [x] Create `AIRCRAFT_LAYOUT_MAPPING.md` documentation
- [x] Create `SMART_PDF_SCANNER_DESIGN.md` documentation

---

## 🚀 Testing

### Test Case 1: PK-GHH dengan seats yang benar (21-58)
```bash
1. Upload PDF dengan seats: 21A, 21C, ..., 58K
2. Expected: No flags, all pass
3. Verify review page shows "Layout: a330-900b"
```

### Test Case 2: PK-GHH dengan seats dari row 6
```bash
1. Upload PDF dengan seats: 6A, 6C, 21A, ..., 58K
2. Expected: 2 seats flagged (6A, 6C)
3. Verify message: "Row 6 below expected minimum 21"
```

### Test Case 3: PK-GHE scan (comparison)
```bash
1. Upload PDF dengan seats: 6A, 6C, 21A, ..., 58K
2. Expected: All pass (no flags)
3. Verify review page shows "Layout: a330-900a"
   - business: rows 6-11
   - economy: rows 21-58
```

---

## 📌 Notes

- Setiap registration sekarang dibungkus dengan layout-nya sendiri
- Tidak akan ada confusion antara PK-GHE dan PK-GHH
- PDF scanner automatically intelligent berdasarkan registration
- User tidak perlu specify layout - system detect otomatis

---

**Status:** ✅ COMPLETE  
**Ready for:** Testing & Review
