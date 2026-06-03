# Smart PDF Scanner - Per-Aircraft Layout Validation

## Overview

The PDF scanner system now includes intelligent per-aircraft validation that ensures seat data matches the expected layout for each aircraft registration. This prevents common errors where seats from different aircraft types get mixed up or mis-scanned.

---

## Architecture

### 1. Database Layer (`Aircraft` Model)

Each aircraft has a `layout` column that references a configuration key:

```php
// Example: PK-GHE A330-900 with Business + Economy
Aircraft::create([
    'registration' => 'PK-GHE',
    'type' => 'A330-900',
    'layout' => 'a330-900a',  // Key that maps to config files
    'status' => 'active',
    'airline_id' => 1,
]);

// Example: PK-GHH A330-900 with Economy Only
Aircraft::create([
    'registration' => 'PK-GHH',
    'type' => 'A330-900',
    'layout' => 'a330-900b',  // Different key! Different layout!
    'status' => 'active',
    'airline_id' => 1,
]);
```

### 2. Configuration Layer (config/*.php)

Three config files define the layout:

#### `aircraft_class_rows.php`
Defines which rows belong to each cabin class:

```php
'a330-900a' => [
    'business' => range(6, 11),
    'economy' => array_diff(range(21, 58), [24]),  // 21-58 skip galley row 24
],

'a330-900b' => [
    'economy' => array_diff(range(21, 58), [24]),  // SAME economy rows, NO business
],
```

#### `aircraft_columns.php`
Defines available columns:

```php
'a330-900a' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'],
'a330-900b' => ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'],  // Same columns
```

#### `aircraft_economy_sections.php`
Defines batch input sections:

```php
'a330-900a' => [
    ['name' => 'Economy Class - Rows 21-40', ...],
    ['name' => 'Economy Class - Rows 41-58', ...],
],

'a330-900b' => [
    ['name' => 'Economy Class - Rows 21-40', ...],
    ['name' => 'Economy Class - Rows 41-58', ...],  // Same for batch input
],
```

### 3. Service Layer

#### `VerificationService::validateAndCorrectRowsByRegistration()`

Called early in verification pipeline:

```php
/**
 * For each registration, this method:
 * 1. Looks up aircraft in database
 * 2. Gets the aircraft.layout value (e.g., 'a330-900b')
 * 3. Loads config('aircraft_class_rows')[layout]
 * 4. Extracts min/max expected rows from config
 * 5. Validates each seat against those rows
 * 6. Flags any mismatches
 */
protected function validateAndCorrectRowsByRegistration(
    string $registration,
    array $seats
): array
```

Example flow:
```
Registration: PK-GHH
    ↓
Aircraft lookup → layout = 'a330-900b'
    ↓
Load config → expectedRows = [21,22,23,25,...58] (skip 24)
    ↓
minRow = 21, maxRow = 58
    ↓
Check seat 6A → row=6 < minRow=21 → FLAG!
Check seat 21A → row=21 >= minRow ✓, <= maxRow ✓ → OK
```

#### `PdfScanController::scan()`

Integration point:

```php
$parsed = $this->pdfParser->processFile($fullPath);  // AI extracts

// NEW: Smart validation
$verificationResult = $this->verificationService->verify([
    'registration' => $parsed['registration'],
    'aircraft_type' => $parsed['aircraft_type'],
    'seats' => $parsed['seats'],
]);

// Display includes:
// - Flagged items with specific layout info
// - Aircraft layout structure for reference
```

---

## Example Scenarios

### Scenario A: Correct Scan of PK-GHH

**PDF shows:**
```
21 A C D E F G H K
21A 13MAR2026
21C 17APR2028
...
58K 22JAN2030
```

**Extraction:** [21A, 21C, ..., 58K, pax-1, inf-1]

**Validation:**
```
1. Lookup: PK-GHH → layout = 'a330-900b'
2. Expected: rows 21-58 (no business!)
3. Check: 21A → 21 ✓ in range
4. Check: 58K → 58 ✓ in range
5. Result: All pass → No flags
```

**Display in review page:**
```
✅ 0 seats flagged
📋 Layout: a330-900b
  • economy: rows 21-58 (37 rows)
```

---

### Scenario B: Mixed/Wrong Scan of PK-GHH

**PDF shows (mixed with PK-GHE data or error):**
```
6  A C D E F G H K
6C 15OCT2027    ← Business row from PK-GHE!
11K 08DEC2026
21A 13MAR2026   ← Correct economy start
58K 22JAN2030
```

**Extraction:** [6C, 11K, 21A, ..., 58K]

**Validation:**
```
1. Lookup: PK-GHH → layout = 'a330-900b'
2. Expected: rows 21-58 (NO row 6!)
3. Check: 6C → 6 < minRow(21) → FLAG!
   Issue: "Row 6 below expected minimum 21 for a330-900b"
4. Check: 11K → 11 < minRow(21) → FLAG!
5. Check: 21A → 21 ✓ pass
6. Check: 58K → 58 ✓ pass
```

**Display in review page:**
```
⚠️  2 seats flagged
📋 Layout: a330-900b
  • economy: rows 21-58 (37 rows)

Flagged Items:
  [6C]  Row 6 below expected minimum 21 for a330-900b
  [11K] Row 11 below expected minimum 21 for a330-900b
  
→ User must manually remove these rows or correct them
```

---

### Scenario C: PK-GHE Scan (for comparison)

**PDF shows correctly:**
```
6  A C D E F G H K
6C 15OCT2027    ← Business row (allowed!)
11K 08DEC2026
21A 13MAR2026
58K 22JAN2030
```

**Extraction:** [6C, 11K, 21A, ..., 58K]

**Validation:**
```
1. Lookup: PK-GHE → layout = 'a330-900a'
2. Expected: business rows 6-11 + economy rows 21-58
3. minRow = 6, maxRow = 58
4. Check: 6C → 6 ✓ in range
5. Check: 11K → 11 ✓ in range
6. Check: 21A → 21 ✓ in range
7. Check: 58K → 58 ✓ in range
```

**Display in review page:**
```
✅ 0 seats flagged
📋 Layout: a330-900a
  • business: rows 6-11 (6 rows)
  • economy: rows 21-58 (37 rows)
```

---

## Database Migration Path

If you later need to add more A330-900 variants:

1. **Add new layout to config files:**
   ```php
   // config/aircraft_class_rows.php
   'a330-900c' => [
       'economy_premium' => array_diff(range(21, 27), [24]),
       'economy' => range(28, 70),
   ],
   ```

2. **Add aircraft to seeder:**
   ```php
   ['registration' => 'PK-GHJ', 'type' => 'A330-900', 'layout' => 'a330-900c', ...],
   ```

3. **System automatically handles validation** based on new layout config!

---

## Troubleshooting

### "Row X below expected minimum Y"
- Aircraft scanned has seats that don't match its configured layout
- Check that PDF is actually for this registration (PK-GHH, etc.)
- May indicate mixed PDFs or scanning error

### "Layout config not found"
- Aircraft exists in DB but its layout key doesn't exist in config files
- Add the layout to `aircraft_class_rows.php`, `aircraft_columns.php`, etc.

### All seats flagged as incorrect
- Registration may not exist in database
- Or wrong layout is assigned to registration
- Check: `Aircraft::where('registration', 'PK-XXX')->first()->layout`

---

## Future Enhancements

1. **Auto-reassign rows** - Instead of just flagging, offer to shift rows automatically
2. **Layout import wizard** - UI to set aircraft layout when adding new aircraft
3. **Comparison mode** - Show side-by-side what was scanned vs what's expected
4. **Per-section override** - Allow user to specify "this PDF is from rows X-Y" upfront

---

**Last updated:** 2026-06-03  
**Maintainer:** Development Team
