<?php

/**
 * OCR Corrections Data Dictionary
 * =================================
 * Comprehensive mapping for correcting OCR misreads on handwritten LOPA documents.
 * Used by OcrPreprocessService to post-process OCR text and AI results.
 *
 * Categories:
 * 1. digit_substitutions     — Letter↔Digit confusion (O→0, I→1, etc.)
 * 2. month_corrections       — Month typos and OCR errors (IAN→JAN, OKT→OCT, etc.)
 * 3. month_fuzzy_map         — Fuzzy matching for severely garbled month names
 * 4. common_misreads         — Symbol and character substitutions
 * 5. seat_id_corrections     — Common seat ID misreads (att→att, etc.)
 * 6. registration_corrections — Aircraft registration format fixes
 * 7. valid_months            — Canonical month list for validation
 * 8. valid_year_range        — Acceptable year range for dates
 * 9. date_patterns           — Regex patterns to detect dates in various formats
 * 10. digit_pair_confusion   — Pairs of digits commonly confused in handwriting
 * 11. context_rules          — Context-aware correction rules
 */

return [

    // ═══════════════════════════════════════════════════════════════════
    // 1. DIGIT ↔ LETTER SUBSTITUTIONS
    // ═══════════════════════════════════════════════════════════════════
    // When we KNOW a character should be a digit (e.g., in a year or day),
    // these mappings correct common OCR letter→digit mistakes.
    'digit_substitutions' => [
        // Round shapes confused as digits
        'O' => '0',
        'o' => '0',
        'Q' => '0',
        'D' => '0',

        // Vertical strokes confused as 1
        'I' => '1',
        'i' => '1',
        'l' => '1',
        '|' => '1',
        '!' => '1',

        // Angular shapes confused as 2
        'Z' => '2',
        'z' => '2',

        // Curved shapes confused as 3
        // (rare, only in heavily distorted scans)

        // Shapes confused as 4
        'A' => '4',  // Only when in digit context!
        'q' => '4',  // lowercase q tail looks like 4

        // S-like shapes confused as 5
        'S' => '5',
        's' => '5',

        // Shapes confused as 6
        'G' => '6',
        'b' => '6',

        // Shapes confused as 7
        'T' => '7',

        // B-like shapes confused as 8
        'B' => '8',

        // Shapes confused as 9
        'g' => '9',
        'p' => '9',  // upside-down context
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 2. LETTER ↔ DIGIT SUBSTITUTIONS (reverse)
    // ═══════════════════════════════════════════════════════════════════
    // When we KNOW a character should be a letter (e.g., in a month name),
    // these mappings correct common OCR digit→letter mistakes.
    'letter_substitutions' => [
        '0' => 'O',
        '1' => 'I',
        '2' => 'Z',
        '3' => 'E',  // 3 looks like backwards E
        '4' => 'A',
        '5' => 'S',
        '6' => 'G',
        '7' => 'T',
        '8' => 'B',
        '9' => 'G',
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 3. MONTH CORRECTIONS — Direct OCR Error Mapping
    // ═══════════════════════════════════════════════════════════════════
    // Maps commonly misread month strings to the correct month.
    // Organized by target month, covers both OCR and handwriting errors.
    'month_corrections' => [
        // --- JAN (January) ---
        'IAN' => 'JAN',  // I↔J confusion
        'JRN' => 'JAN',  // R↔A confusion
        'JNR' => 'JAN',  // transposition
        'JNA' => 'JAN',  // transposition
        'JÅN' => 'JAN',  // diacritical artifact
        'JAN' => 'JAN',  // identity (for validation)
        'JAM' => 'JAN',  // M↔N confusion
        'JAH' => 'JAN',  // H↔N confusion
        'JPN' => 'JAN',  // P↔A confusion (rare)
        'J4N' => 'JAN',  // 4↔A confusion
        'J AN' => 'JAN',  // space artifact

        // --- FEB (February) ---
        'FEE' => 'FEB',  // E↔B confusion
        'FER' => 'FEB',  // R↔B confusion
        'FFB' => 'FEB',  // F↔E confusion
        'F3B' => 'FEB',  // 3↔E confusion
        'FE8' => 'FEB',  // 8↔B confusion
        'FEB' => 'FEB',  // identity
        'FEP' => 'FEB',  // P↔B confusion
        'FÉB' => 'FEB',  // diacritical artifact
        'FRB' => 'FEB',  // R↔E confusion
        'F EB' => 'FEB',  // space artifact

        // --- MAR (March) ---
        'NAR' => 'MAR',  // N↔M confusion
        'MAB' => 'MAR',  // B↔R confusion
        'MRR' => 'MAR',  // R↔A confusion
        'MAE' => 'MAR',  // E↔R confusion (careful: not MAY)
        'M4R' => 'MAR',  // 4↔A confusion
        'MAR' => 'MAR',  // identity
        'MÁR' => 'MAR',  // diacritical artifact
        'MAK' => 'MAR',  // K↔R confusion
        'M AR' => 'MAR',  // space artifact
        'HAR' => 'MAR',  // H↔M confusion

        // --- APR (April) ---
        'APB' => 'APR',  // B↔R confusion
        'APE' => 'APR',  // E↔R confusion
        'ARP' => 'APR',  // transposition
        'APP' => 'APR',  // P↔R confusion
        'A9R' => 'APR',  // 9↔P confusion
        '4PR' => 'APR',  // 4↔A confusion
        'APR' => 'APR',  // identity
        'ÅPR' => 'APR',  // diacritical artifact
        'APK' => 'APR',  // K↔R confusion
        'A PR' => 'APR',  // space artifact
        'APH' => 'APR',  // H↔R confusion

        // --- MAY ---
        'MAV' => 'MAY',  // V↔Y confusion
        'NAY' => 'MAY',  // N↔M confusion
        'MAI' => 'MAY',  // I↔Y (common in some languages)
        'MAT' => 'MAY',  // T↔Y confusion
        'MAY' => 'MAY',  // identity
        'MÅY' => 'MAY',  // diacritical artifact
        'M4Y' => 'MAY',  // 4↔A confusion
        'M AY' => 'MAY',  // space artifact
        'HAY' => 'MAY',  // H↔M confusion
        'MAJ' => 'MAY',  // J↔Y confusion (Swedish/Danish)
        'MYA' => 'MAY',  // transposition

        // --- JUN (June) ---
        'IUN' => 'JUN',  // I↔J confusion
        'JUH' => 'JUN',  // H↔N confusion
        'JUM' => 'JUN',  // M↔N confusion
        'JUW' => 'JUN',  // W↔N confusion
        'JUN' => 'JUN',  // identity
        'JÜN' => 'JUN',  // diacritical artifact
        'JUR' => 'JUN',  // R↔N confusion
        'J UN' => 'JUN',  // space artifact
        'JUB' => 'JUN',  // B↔N confusion
        'JU N' => 'JUN',  // space artifact

        // --- JUL (July) ---
        'IUL' => 'JUL',  // I↔J confusion
        'JUI' => 'JUL',  // I↔L confusion
        'JLY' => 'JUL',  // transposition / abbreviation
        'JUL' => 'JUL',  // identity
        'JÚL' => 'JUL',  // diacritical artifact
        'JU1' => 'JUL',  // 1↔L confusion
        'J UL' => 'JUL',  // space artifact
        'JUK' => 'JUL',  // K↔L confusion

        // --- AUG (August) ---
        'AUC' => 'AUG',  // C↔G confusion
        'AUB' => 'AUG',  // B↔G confusion
        'RUG' => 'AUG',  // R↔A confusion
        'AUG' => 'AUG',  // identity
        'ÅUG' => 'AUG',  // diacritical artifact
        '4UG' => 'AUG',  // 4↔A confusion
        'A UG' => 'AUG',  // space artifact
        'AUQ' => 'AUG',  // Q↔G confusion
        'AU6' => 'AUG',  // 6↔G confusion
        'AUS' => 'AUG',  // S↔G confusion

        // --- SEP (September) ---
        'SFP' => 'SEP',  // F↔E confusion
        'SEF' => 'SEP',  // F↔P confusion
        'SPE' => 'SEP',  // transposition
        'S3P' => 'SEP',  // 3↔E confusion
        'SEP' => 'SEP',  // identity
        '5EP' => 'SEP',  // 5↔S confusion
        'S EP' => 'SEP',  // space artifact
        'SEB' => 'SEP',  // B↔P confusion
        'SE9' => 'SEP',  // 9↔P confusion
        'SEQ' => 'SEP',  // Q↔P confusion

        // --- OCT (October) ---
        'OKT' => 'OCT',  // K↔C (German/Dutch spelling)
        'OCR' => 'OCT',  // R↔T confusion
        'OCI' => 'OCT',  // I↔T confusion
        'OC7' => 'OCT',  // 7↔T confusion
        'OCT' => 'OCT',  // identity
        '0CT' => 'OCT',  // 0↔O confusion
        'O CT' => 'OCT',  // space artifact
        'OCL' => 'OCT',  // L↔T confusion
        'OGT' => 'OCT',  // G↔C confusion
        'OET' => 'OCT',  // E↔C confusion

        // --- NOV (November) ---
        'NOY' => 'NOV',  // Y↔V confusion
        'N0V' => 'NOV',  // 0↔O confusion
        'NOU' => 'NOV',  // U↔V confusion
        'NOV' => 'NOV',  // identity
        'NOB' => 'NOV',  // B↔V confusion
        'NDV' => 'NOV',  // D↔O confusion
        'N OV' => 'NOV',  // space artifact
        'HOV' => 'NOV',  // H↔N confusion
        'MOV' => 'NOV',  // M↔N confusion
        'NOW' => 'NOV',  // W↔V confusion

        // --- DEC (December) ---
        'DFC' => 'DEC',  // F↔E confusion
        'DFG' => 'DEC',  // F↔E, G↔C confusion
        'DBC' => 'DEC',  // B↔E confusion
        'DEC' => 'DEC',  // identity
        'D3C' => 'DEC',  // 3↔E confusion
        'D EC' => 'DEC',  // space artifact
        'DEG' => 'DEC',  // G↔C confusion
        'OEC' => 'DEC',  // O↔D confusion
        'DCC' => 'DEC',  // C↔E confusion
        'DER' => 'DEC',  // R↔C confusion (rare)
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 4. MONTH FUZZY MAP — Levenshtein-based matching
    // ═══════════════════════════════════════════════════════════════════
    // For severely garbled month names that aren't in the direct mapping,
    // we try fuzzy matching against this canonical list.
    'month_fuzzy_candidates' => [
        'JAN' => ['JANUARY', 'JANUARI'],
        'FEB' => ['FEBRUARY', 'FEBRUARI'],
        'MAR' => ['MARCH', 'MARET'],
        'APR' => ['APRIL'],
        'MAY' => ['MAI', 'MEI'],
        'JUN' => ['JUNE', 'JUNI'],
        'JUL' => ['JULY', 'JULI'],
        'AUG' => ['AUGUST', 'AGUSTUS'],
        'SEP' => ['SEPTEMBER'],
        'OCT' => ['OCTOBER', 'OKTOBER'],
        'NOV' => ['NOVEMBER'],
        'DEC' => ['DECEMBER', 'DESEMBER'],
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 5. COMMON SYMBOL / CHARACTER MISREADS
    // ═══════════════════════════════════════════════════════════════════
    'common_misreads' => [
        // Pipe and vertical bars → 1
        '|' => '1',
        '¦' => '1',
        '│' => '1',
        '‖' => '11',

        // Exclamation → 1
        '!' => '1',

        // Bracket confusion
        '{' => '(',
        '}' => ')',
        '[' => '(',
        ']' => ')',

        '`' => "'",
        '´' => "'",
        "'" => "'",
        '’' => "'",
        '‘' => "'",
        '"' => '"',
        '«' => '"',
        '»' => '"',

        // Dash/hyphen normalization
        '–' => '-',  // en-dash
        '—' => '-',  // em-dash
        '‒' => '-',  // figure dash
        '−' => '-',  // minus sign
        '⁃' => '-',  // hyphen bullet

        // Slash normalization
        '∕' => '/',
        '⁄' => '/',
        '＼' => '\\',

        // Period/dot confusion
        '·' => '.',
        '•' => '.',
        '●' => '.',

        // Space artifacts
        '\xC2\xA0' => ' ',  // non-breaking space
        '\xE2\x80\x83' => ' ',  // em space
        '\xE2\x80\x82' => ' ',  // en space
        '\xE2\x80\x89' => ' ',  // thin space
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 6. SEAT ID CORRECTIONS
    // ═══════════════════════════════════════════════════════════════════
    // Common OCR mistakes in seat IDs (e.g., "att/d11-LL" misread)
    'seat_id_corrections' => [
        // Attendant door prefix variations
        'Att/' => 'att/',
        'ATT/' => 'att/',
        'att /   ' => 'att/',
        'aLt/' => 'att/',
        'aft/' => 'att/',  // Note: 'aft-LC' is valid, don't overcorrect

        // Pilot/Copilot variations
        'Pilot' => 'pilot',
        'PILOT' => 'pilot',
        'Copilot' => 'copilot',
        'COPILOT' => 'copilot',
        'CoPilot' => 'copilot',
        'Co-Pilot' => 'copilot',
        'Co-pilot' => 'copilot',
        'Copil' => 'copilot',

        // Observer variations
        'Observer1' => 'observer1',
        'Observer2' => 'observer2',
        'OBSERVER1' => 'observer1',
        'OBSERVER2' => 'observer2',
        'Oberver1' => 'observer1',  // common typo
        'Oberver2' => 'observer2',

        // Pax/Inf spare ID normalization
        'PAX-' => 'pax-',
        'Pax-' => 'pax-',
        'INF-' => 'inf-',
        'Inf-' => 'inf-',
        'SPARE-' => 'pax-',
        'Spare-' => 'pax-',
        'Adult-' => 'pax-',
        'ADULT-' => 'pax-',
        'Infant-' => 'inf-',
        'INFANT-' => 'inf-',
        'Baby-' => 'inf-',
        'Child-' => 'inf-',
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 7. REGISTRATION FORMAT CORRECTIONS
    // ═══════════════════════════════════════════════════════════════════
    // Aircraft registrations follow the PK-XXX format (Indonesian)
    'registration_corrections' => [
        // Common OCR errors in "PK-" prefix
        'PK -' => 'PK-',   // space before dash
        'PK- ' => 'PK-',   // space after dash
        'PK –' => 'PK-',   // en-dash
        'PK—' => 'PK-',   // em-dash
        'P K-' => 'PK-',   // space in prefix
        'PX-' => 'PK-',   // X↔K confusion
        'BK-' => 'PK-',   // B↔P confusion
        'FK-' => 'PK-',   // F↔P confusion (rare)
        'RK-' => 'PK-',   // R↔P confusion

        // Common letter substitutions in suffix
        // (applied character-by-character after prefix)
        '0' => 'O',  // In registration context, 0 is always O
        '1' => 'I',  // In registration context, 1 is always I
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 8. DIGIT PAIR CONFUSION — Handwriting-Specific
    // ═══════════════════════════════════════════════════════════════════
    // Pairs of digits that are frequently confused in handwritten dates.
    // Used for flagging uncertainty (not auto-correcting, since both are valid digits).
    'digit_pair_confusion' => [
        ['0', '6'],   // Round top, distinguished by bottom closure
        ['0', '8'],   // Both round, 8 has middle pinch
        ['1', '7'],   // Both have vertical stroke, 7 has crossbar
        ['2', '7'],   // Curved vs angular top
        ['2', 'Z'],   // Curved 2 vs angular Z
        ['3', '8'],   // Both have curves, 8 is closed
        ['3', '5'],   // Open top vs closed top
        ['4', '9'],   // Top closure differs
        ['5', '6'],   // Bottom curve direction
        ['5', 'S'],   // S-shape similarity
        ['6', '0'],   // Bottom loop presence
        ['6', 'b'],   // Handwriting overlap
        ['7', '1'],   // Without serif or crossbar
        ['9', '4'],   // Tail direction
        ['9', '7'],   // Tail vs crossbar
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 9. VALID MONTHS (canonical list for validation)
    // ═══════════════════════════════════════════════════════════════════
    'valid_months' => [
        'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
        'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC',
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 10. VALID YEAR RANGE
    // ═══════════════════════════════════════════════════════════════════
    'valid_year_range' => [2020, 2040],

    // ═══════════════════════════════════════════════════════════════════
    // 11. DATE FORMAT PATTERNS (Regex)
    // ═══════════════════════════════════════════════════════════════════
    // Used to detect and extract dates from raw OCR text.
    'date_patterns' => [
        // "31 MAY 2029" — full format with day
        '/(\d{1,2})\s+(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{2,4})/i',
        // "MAY 2029" — month + year only
        '/(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{2,4})/i',
        // "05/2029" or "05-2029" — numeric month/year
        '/(\d{1,2})[\/\-](\d{2,4})/',
        // "2029-05" or "2029/05" — ISO-ish format
        '/(\d{4})[\/\-](\d{1,2})/',
        // "MAY 29" — month + 2-digit year
        '/(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{2})(?!\d)/i',
        // "31 MAY 29" — day + month + 2-digit year
        '/(\d{1,2})\s+(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{2})(?!\d)/i',
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 12. CONTEXT-AWARE CORRECTION RULES
    // ═══════════════════════════════════════════════════════════════════
    // Rules that consider surrounding context to make smarter corrections.
    'context_rules' => [
        // Day of month: must be 1-31
        'day_range' => [1, 31],

        // Year normalization: 2-digit years
        'year_2digit_base' => 2000,  // "28" → 2028, "25" → 2025

        // If year < 2020, probably misread (e.g., 2005 should be 2025)
        'year_minimum' => 2020,
        'year_maximum' => 2040,

        // Maximum day per month (for validation)
        'max_days_per_month' => [
            'JAN' => 31, 'FEB' => 29, 'MAR' => 31, 'APR' => 30,
            'MAY' => 31, 'JUN' => 30, 'JUL' => 31, 'AUG' => 31,
            'SEP' => 30, 'OCT' => 31, 'NOV' => 30, 'DEC' => 31,
        ],

        // Common year misreads (specific digit swaps)
        'year_corrections' => [
            '2O25' => '2025',  // O→0
            '2O26' => '2026',
            '2O27' => '2027',
            '2O28' => '2028',
            '2O29' => '2029',
            '2O30' => '2030',
            '2O31' => '2031',
            '2O32' => '2032',
            '2O33' => '2033',
            '2O34' => '2034',
            '2O35' => '2035',
            '20Z5' => '2025',  // Z→2 in year context
            '20Z6' => '2026',
            '20Z7' => '2027',
            '20Z8' => '2028',
            '2OZ5' => '2025',  // double confusion
            '2OZ6' => '2026',
            'ZO25' => '2025',  // Z→2 in first position
            'ZO26' => '2026',
            'ZO27' => '2027',
            'ZO28' => '2028',
            '2025' => '2025',  // identity (for validation)
            '2026' => '2026',
            '2027' => '2027',
            '2028' => '2028',
            '2029' => '2029',
            '2030' => '2030',
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 13. WHITESPACE / FORMATTING CLEANUP
    // ═══════════════════════════════════════════════════════════════════
    'whitespace_rules' => [
        // Multiple spaces → single space
        'collapse_spaces' => true,
        // Remove leading/trailing whitespace from each cell
        'trim_cells' => true,
        // Remove zero-width characters
        'strip_invisible' => [
            "\xEF\xBB\xBF",     // BOM
            "\xE2\x80\x8B",     // zero-width space
            "\xE2\x80\x8C",     // zero-width non-joiner
            "\xE2\x80\x8D",     // zero-width joiner
            "\xE2\x80\xAA",     // left-to-right embedding
            "\xE2\x80\xAB",     // right-to-left embedding
            "\xE2\x80\xAC",     // pop directional formatting
            "\xE2\x80\xAD",     // left-to-right override
            "\xE2\x80\xAE",     // right-to-left override
            "\xC2\xAD",         // soft hyphen
        ],
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 14. AIRCRAFT TYPE NORMALIZATION
    // ═══════════════════════════════════════════════════════════════════
    // Normalize aircraft type strings from OCR
    'aircraft_type_corrections' => [
        // B737 variants
        'B-737' => 'B737',
        'B 737' => 'B737',
        'b737' => 'B737',
        '737' => 'B737',
        'B737-800' => 'B737',
        'B737-8' => 'B737',
        'B737NG' => 'B737',

        // B777 variants
        'B-777' => 'B777',
        'B 777' => 'B777',
        'b777' => 'B777',
        '777' => 'B777',
        'B777-300' => 'B777',
        'B777-300ER' => 'B777',
        'B777-3' => 'B777',
        'B773' => 'B777',

        // A330 variants
        'A-330' => 'A330',
        'A 330' => 'A330',
        'a330' => 'A330',
        '330' => 'A330',
        'A330-900' => 'A330-900',
        'A330-900NEO' => 'A330-900',
        'A330-300' => 'A330-300',
        'A333' => 'A330',
        'A339' => 'A330-900',

        // A320 variants
        'A-320' => 'A320',
        'A 320' => 'A320',
        'a320' => 'A320',
        '320' => 'A320',
        'A320-200' => 'A320',

        // ATR72
        'ATR-72' => 'ATR72',
        'ATR 72' => 'ATR72',
        'atr72' => 'ATR72',
    ],

    // ═══════════════════════════════════════════════════════════════════
    // 15. CONFIDENCE THRESHOLDS
    // ═══════════════════════════════════════════════════════════════════
    // Minimum confidence levels for various OCR operations
    'confidence_thresholds' => [
        'orientation_detection' => 1.0,    // pytesseract OSD confidence for auto-rotate
        'ocr_character' => 60,             // minimum char confidence to trust OCR (0-100)
        'date_correction_auto' => 85,      // auto-apply correction if confidence >= this
        'date_correction_flag' => 50,      // flag for review if confidence between this and auto
    ],
];
