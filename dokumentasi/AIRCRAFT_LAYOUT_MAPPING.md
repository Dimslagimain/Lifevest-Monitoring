# Aircraft Layout Mapping Documentation

## Garuda Indonesia A330-900 Fleet

### Group 1: Business + Economy (Layout: a330-900a)
**Starting Row: 6 (Business Class)**

| Registration | Aircraft Type | Layout | Business Rows | Economy Rows | Status |
|---|---|---|---|---|---|
| **PK-GHE** | **A330-900** | **a330-900a** | 6-11 | 21-58 (skip 24) | Active |
| **PK-GHF** | **A330-900** | **a330-900a** | 6-11 | 21-58 (skip 24) | Prolong |
| **PK-GHG** | **A330-900** | **a330-900a** | 6-11 | 21-58 (skip 24) | Prolong |

**Columns (all rows):** A, C, D, E, F, G, H, K
**Total Rows:** Business (6) + Economy (37) = 43 rows per aircraft
**Total Seats:** 6 rows × 6 cols (Business) + 37 rows × 8 cols (Economy) = 296 + 296 = **296 passenger seats**

---

### Group 2: Economy Only (Layout: a330-900b)
**Starting Row: 21 (Economy Only)**

| Registration | Aircraft Type | Layout | Economy Rows | Status |
|---|---|---|---|---|
| **PK-GHH** | **A330-900** | **a330-900b** | 21-58 (skip 24) | Active |
| **PK-GHI** | **A330-900** | **a330-900b** | 21-58 (skip 24) | Prolong |

**Columns (all rows):** A, C, D, E, F, G, H, K
**Total Rows:** 37 rows (all economy, same as PK-GHE economy section)
**Total Seats:** 37 rows × 8 cols = **296 passenger seats** (same capacity)

---

## Key Differences Summary

| Aspect | PK-GHE/F/G (a330-900a) | PK-GHH/I (a330-900b) |
|---|---|---|
| **Configuration Name** | Mixed Cabin | All Economy |
| **First Section** | Business rows 6-11 | - |
| **Economy Section Start** | Row 21 | Row 21 |
| **Economy Section Range** | Rows 21-58 (37 rows) | Rows 21-58 (37 rows) - **SAME** |
| **Row 24 (Galley)** | Skipped | Skipped |
| **Total Passenger Rows** | 43 rows | 37 rows |
| **Columns Available** | A, C, D, E, F, G, H, K (8 columns) | A, C, D, E, F, G, H, K (8 columns) |
| **Use Case** | Mixed cabin offering | All-economy configuration |

---

## Configuration Implementation

### aircraft_class_rows.php
```php
'a330-900a' => [
    'business' => range(6, 11),
    'economy' => array_diff(range(21, 58), [24]),  // Rows 21-58, skip 24
],

'a330-900b' => [
    'economy' => array_diff(range(21, 58), [24]),  // Rows 21-58, skip 24 (same range as a330-900a economy)
],
```

### aircraft_economy_sections.php
```php
'a330-900a' => [
    ['name' => 'Economy Class - Rows 21-40', 'rows' => array_values(array_diff(range(21, 40), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ['name' => 'Economy Class - Rows 41-58', 'rows' => range(41, 58), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'], 'exceptions' => ['41A', '41C', '41H', '41K']],
],

'a330-900b' => [
    ['name' => 'Economy Class - Rows 21-40', 'rows' => array_values(array_diff(range(21, 40), [24])), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
    ['name' => 'Economy Class - Rows 41-58', 'rows' => range(41, 58), 'columns' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K']],
],
```

### aircraft_columns.php
```php
'a330-900a' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'],
'a330-900b' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'],
```

### AircraftSeeder.php
```php
// A330-900 Layout: a330-900a
['registration' => 'PK-GHE', 'type' => 'A330-900', 'layout' => 'a330-900a', 'status' => 'active', 'airline_id' => 1],
['registration' => 'PK-GHF', 'type' => 'A330-900', 'layout' => 'a330-900a', 'status' => 'prolong', 'airline_id' => 1],
['registration' => 'PK-GHG', 'type' => 'A330-900', 'layout' => 'a330-900a', 'status' => 'prolong', 'airline_id' => 1],

// A330-900 Layout: a330-900b (NEW: Economy only, starting row 21)
['registration' => 'PK-GHH', 'type' => 'A330-900', 'layout' => 'a330-900b', 'status' => 'active', 'airline_id' => 1],
['registration' => 'PK-GHI', 'type' => 'A330-900', 'layout' => 'a330-900b', 'status' => 'prolong', 'airline_id' => 1],
```

---

## Important Notes

✅ **Layout sama** (a330-900a dan a330-900b kedua-duanya punya ekonomi rows 21-58)
✅ **PK-GHH & PK-GHI start dari row 21** (ekonomi saja, tidak ada business class)
✅ **PK-GHE/F/G start dari row 6** (business rows 6-11, then ekonomi rows 21-58)
✅ **Setiap aircraft dibungkus dengan registration dan type yang jelas di seeder**

---

---

## Smart PDF Scanner Integration

### How it Works

The smart PDF scanner now validates seat data per-aircraft based on the database layout configuration:

1. **PDF uploaded** → AI extracts registration (e.g., PK-GHH)
2. **Registration detected** → System looks up aircraft layout from database
3. **Layout rules applied** → Validation Service checks if extracted rows match expected layout
4. **Flagging & feedback** → Any mismatches are flagged for review with specific layout information

### Example: PK-GHH PDF Scan

**Scenario 1: PDF correctly shows seats starting from row 21** ✅
- Scanner extracts: [21A, 21C, 21D, ... 58K, pax-1, inf-1...]
- Validation: All rows 21-58 match layout `a330-900b`
- Result: Accepted with high confidence

**Scenario 2: PDF mistakenly shows seats from row 6** ⚠️
- Scanner extracts: [6C, 6E, ... 11K, 21A, ...]
- Validation: Detects row 6 is BELOW expected minimum (21) for `a330-900b`
- Flagged rows: [6C, 6E, ... 11K]
- Message: "Row 6 below expected minimum 21 for a330-900b"
- Result: Flagged for manual review and correction

### Per-Aircraft Wrapping

Each aircraft registration is wrapped with its specific layout config:

```php
// PK-GHE Configuration
Aircraft::create([
    'registration' => 'PK-GHE',
    'type' => 'A330-900',
    'layout' => 'a330-900a',  // ← Layout key
]);

// PK-GHH Configuration (Different!)
Aircraft::create([
    'registration' => 'PK-GHH',
    'type' => 'A330-900',
    'layout' => 'a330-900b',  // ← Different layout key
]);
```

When PDF scanner runs:
- **For PK-GHE**: Validates min_row=6, max_row=58
- **For PK-GHH**: Validates min_row=21, max_row=58 (row 6 would be flagged!)

### Validation Service Flow

```
User uploads PDF
    ↓
PdfParserService → AI extracts seats
    ↓
PdfScanController.scan()
    ↓
VerificationService.verify()
    ├→ validateAndCorrectRowsByRegistration()
    │   └→ Looks up Aircraft model by registration
    │   └→ Gets layout from aircraft.layout
    │   └→ Gets expected rows from config('aircraft_class_rows')[layout]
    │   └→ Checks each extracted seat against expected row range
    │   └→ Flags mismatches with layout info
    ├→ applyRuleBasedCorrections()
    │   └→ Fixes typos, date formats, etc.
    └→ applyAiValidation()
        └→ Cross-checks with original images if available

    ↓
PdfScanReview page shows:
  • Extracted seats
  • Flagged items with specific reason
  • Layout structure (for reference)
  • Aircraft-specific info
```

---

Generated: 2026-06-03 (with Smart PDF Scanner Integration)

