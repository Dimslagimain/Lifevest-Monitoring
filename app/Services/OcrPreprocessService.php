<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * OCR Preprocessing Service
 * ==========================
 * Bridge between PHP and the Python OCR preprocessing script.
 * 
 * Responsibilities:
 * 1. Call Python script (opencv-python + pytesseract) for image enhancement
 * 2. Apply OCR correction data dictionary to fix common misreads
 * 3. Return enhanced images + corrected OCR text for AI consumption
 */
class OcrPreprocessService
{
    protected string $pythonPath;
    protected string $tesseractPath;
    protected string $scriptPath;
    protected array $corrections;

    public function __construct()
    {
        $this->pythonPath = env('PYTHON_PATH', 'python');
        $this->tesseractPath = env('TESSERACT_PATH', 'C:/Program Files/Tesseract-OCR/tesseract.exe');
        $this->scriptPath = base_path('scripts/ocr_preprocess.py');
        $this->corrections = config('ocr_corrections', []);
    }

    /**
     * Run the full preprocessing pipeline on a set of images.
     * 
     * @param array $imagePaths Paths to page images
     * @param string|null $outputDir Directory for enhanced images (default: same dir as input)
     * @return array {
     *   'success' => bool,
     *   'enhanced_images' => string[],        // OCR-enhanced image paths
     *   'ai_enhanced_images' => string[],      // AI-enhanced image paths (moderate enhancement)
     *   'ocr_text' => string,                  // Raw OCR text from pytesseract
     *   'corrected_ocr_text' => string,        // OCR text after data dictionary correction
     *   'orientations' => array[],             // Orientation info per page
     *   'preprocessing_applied' => string[],   // List of preprocessing steps applied
     *   'errors' => string[]
     * }
     */
    public function preprocess(array $imagePaths, ?string $outputDir = null): array
    {
        $fallback = [
            'success' => false,
            'enhanced_images' => [],
            'ai_enhanced_images' => [],
            'ocr_text' => '',
            'corrected_ocr_text' => '',
            'orientations' => [],
            'preprocessing_applied' => [],
            'errors' => [],
        ];

        // Validate script exists
        if (!file_exists($this->scriptPath)) {
            Log::warning('[OCR Preprocess] Python script not found', ['path' => $this->scriptPath]);
            $fallback['errors'][] = 'Python script not found: ' . $this->scriptPath;
            return $fallback;
        }

        // Validate image paths
        $validPaths = [];
        foreach ($imagePaths as $path) {
            if (file_exists($path)) {
                $validPaths[] = $path;
            } else {
                Log::warning('[OCR Preprocess] Image not found', ['path' => $path]);
            }
        }

        if (empty($validPaths)) {
            $fallback['errors'][] = 'No valid image files found';
            return $fallback;
        }

        // Determine output directory
        if (!$outputDir) {
            $outputDir = dirname($validPaths[0]);
        }

        // Build command
        $escapedPaths = array_map(function ($p) {
            return '"' . str_replace('"', '\\"', $p) . '"';
        }, $validPaths);

        $cmd = sprintf(
            '"%s" "%s" %s --output-dir "%s" --tesseract-path "%s" 2>&1',
            $this->pythonPath,
            $this->scriptPath,
            implode(' ', $escapedPaths),
            $outputDir,
            $this->tesseractPath
        );

        Log::info('[OCR Preprocess] Running Python OCR script', [
            'cmd' => $cmd,
            'images_count' => count($validPaths),
        ]);

        $startTime = microtime(true);
        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);
        $elapsed = round(microtime(true) - $startTime, 2);

        $rawOutput = implode("\n", $output);

        Log::info('[OCR Preprocess] Python script completed', [
            'return_code' => $returnCode,
            'elapsed_seconds' => $elapsed,
            'output_length' => strlen($rawOutput),
        ]);

        // Parse JSON output from Python
        $result = json_decode($rawOutput, true);

        if ($result === null || !is_array($result)) {
            Log::error('[OCR Preprocess] Failed to parse Python output', [
                'raw_output' => substr($rawOutput, 0, 1000),
                'json_error' => json_last_error_msg(),
            ]);
            $fallback['errors'][] = 'Failed to parse Python script output';
            $fallback['errors'][] = substr($rawOutput, 0, 500);
            return $fallback;
        }

        // Apply data dictionary corrections to OCR text
        $rawOcrText = $result['ocr_text'] ?? '';
        $correctedText = $this->correctOcrText($rawOcrText);

        $result['corrected_ocr_text'] = $correctedText;

        Log::info('[OCR Preprocess] OCR text correction applied', [
            'original_length' => strlen($rawOcrText),
            'corrected_length' => strlen($correctedText),
            'pages_processed' => $result['pages_processed'] ?? 0,
            'errors' => $result['errors'] ?? [],
        ]);

        return $result;
    }

    /**
     * Apply all OCR correction rules from the data dictionary to raw text.
     */
    public function correctOcrText(string $rawText): string
    {
        if (empty(trim($rawText))) {
            return $rawText;
        }

        $text = $rawText;

        // Step 1: Clean invisible characters and whitespace
        $text = $this->cleanWhitespace($text);

        // Step 2: Apply common symbol/character misreads
        $text = $this->applyCommonMisreads($text);

        // Step 3: Find and correct dates in the text
        $text = $this->correctDatesInText($text);

        return $text;
    }

    /**
     * Correct a single date string using the data dictionary.
     * Input formats: "31 MAY 2029", "MAY 2029", "MAY 29", etc.
     */
    public function correctDate(string $date): string
    {
        $date = trim($date);
        if (empty($date)) return '';

        // Remove uncertainty markers for processing (we'll re-add if needed)
        $hasUncertainty = str_ends_with($date, '?');
        $cleanDate = rtrim($date, '? ');

        // Step 1: Clean whitespace
        $cleanDate = $this->cleanWhitespace($cleanDate);

        // Step 2: Apply common misreads
        $cleanDate = $this->applyCommonMisreads($cleanDate);

        // Step 3: Try to parse and correct the date components
        $corrected = $this->correctDateComponents($cleanDate);

        // Re-add uncertainty marker if it was present
        if ($hasUncertainty && !str_ends_with($corrected, '?')) {
            $corrected .= '?';
        }

        return $corrected;
    }

    /**
     * Apply corrections to an entire array of seat results.
     * Called after AI returns its parsed data, as a post-processing step.
     * 
     * @param array $seats Array of ['seat_id' => ..., 'expiry_date' => ...]
     * @return array Corrected seats array
     */
    public function correctSeatsData(array $seats): array
    {
        $corrected = [];
        $correctionLog = [];

        foreach ($seats as $seat) {
            $originalSeatId = $seat['seat_id'] ?? '';
            $originalDate = $seat['expiry_date'] ?? '';

            // Correct seat ID
            $correctedSeatId = $this->correctSeatId($originalSeatId);

            // Correct expiry date
            $correctedDate = $this->correctDate($originalDate);

            // Log corrections
            if ($correctedSeatId !== $originalSeatId) {
                $correctionLog[] = "Seat ID: '{$originalSeatId}' → '{$correctedSeatId}'";
            }
            if ($correctedDate !== $originalDate) {
                $correctionLog[] = "Date [{$correctedSeatId}]: '{$originalDate}' → '{$correctedDate}'";
            }

            $corrected[] = [
                'seat_id' => $correctedSeatId,
                'expiry_date' => $correctedDate,
            ];
        }

        if (!empty($correctionLog)) {
            Log::info('[OCR Corrections] Applied data dictionary corrections', [
                'total_corrections' => count($correctionLog),
                'details' => array_slice($correctionLog, 0, 20), // Log first 20
            ]);
        }

        return $corrected;
    }

    /**
     * Correct an aircraft registration string.
     */
    public function correctRegistration(string $registration): string
    {
        $reg = strtoupper(trim($registration));
        
        $corrections = $this->corrections['registration_corrections'] ?? [];
        
        // Apply prefix corrections
        foreach ($corrections as $wrong => $right) {
            if (strlen($wrong) > 2) { // Only apply prefix-level corrections
                $reg = str_replace($wrong, $right, $reg);
            }
        }

        // Ensure PK- format
        if (preg_match('/^PK[\s\-–—]/', $reg)) {
            $reg = 'PK-' . ltrim(substr($reg, 2), " -–—");
        }

        // In registration context, digits should be letters (PK-GIA, not PK-G1A)
        $suffix = substr($reg, 3); // After "PK-"
        if (strlen($suffix) >= 2) {
            $regLetterSubs = ['0' => 'O', '1' => 'I', '5' => 'S', '8' => 'B'];
            $correctedSuffix = '';
            for ($i = 0; $i < strlen($suffix); $i++) {
                $char = $suffix[$i];
                $correctedSuffix .= $regLetterSubs[$char] ?? $char;
            }
            $reg = 'PK-' . $correctedSuffix;
        }

        return $reg;
    }

    /**
     * Correct an aircraft type string.
     */
    public function correctAircraftType(string $type): string
    {
        $type = trim($type);
        $corrections = $this->corrections['aircraft_type_corrections'] ?? [];

        foreach ($corrections as $wrong => $right) {
            if (strcasecmp($type, $wrong) === 0) {
                return $right;
            }
        }

        return $type;
    }

    /**
     * Run a self-test to verify Python + Tesseract are working.
     */
    public function selfTest(): array
    {
        $cmd = sprintf(
            '"%s" "%s" --test --tesseract-path "%s" 2>&1',
            $this->pythonPath,
            $this->scriptPath,
            $this->tesseractPath
        );

        $output = [];
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        $rawOutput = implode("\n", $output);
        $result = json_decode($rawOutput, true);

        return [
            'return_code' => $returnCode,
            'parsed' => $result !== null,
            'result' => $result ?? ['raw_output' => $rawOutput],
        ];
    }

    // ================================================================
    // PRIVATE HELPER METHODS
    // ================================================================

    /**
     * Clean invisible characters and normalize whitespace.
     */
    private function cleanWhitespace(string $text): string
    {
        $rules = $this->corrections['whitespace_rules'] ?? [];

        // Strip invisible characters
        $invisible = $rules['strip_invisible'] ?? [];
        foreach ($invisible as $char) {
            $text = str_replace($char, '', $text);
        }

        // Collapse multiple spaces
        if ($rules['collapse_spaces'] ?? true) {
            $text = preg_replace('/\s{2,}/', ' ', $text);
        }

        // Trim
        if ($rules['trim_cells'] ?? true) {
            $text = trim($text);
        }

        return $text;
    }

    /**
     * Apply common symbol/character substitutions.
     */
    private function applyCommonMisreads(string $text): string
    {
        $misreads = $this->corrections['common_misreads'] ?? [];

        foreach ($misreads as $wrong => $right) {
            $text = str_replace($wrong, $right, $text);
        }

        return $text;
    }

    /**
     * Find dates in text and correct them using the data dictionary.
     */
    private function correctDatesInText(string $text): string
    {
        $patterns = $this->corrections['date_patterns'] ?? [];

        foreach ($patterns as $pattern) {
            $text = preg_replace_callback($pattern, function ($matches) {
                $fullMatch = $matches[0];
                return $this->correctDateComponents($fullMatch);
            }, $text);
        }

        return $text;
    }

    /**
     * Parse a date string into components (day, month, year) and correct each.
     */
    private function correctDateComponents(string $date): string
    {
        $date = trim($date);
        if (empty($date)) return '';

        $monthCorrections = $this->corrections['month_corrections'] ?? [];
        $validMonths = $this->corrections['valid_months'] ?? [];
        $contextRules = $this->corrections['context_rules'] ?? [];
        $yearRange = $this->corrections['valid_year_range'] ?? [2020, 2040];

        // Try to match: "DD MONTH YYYY" or "DD MONTH YY"
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3,})\s+(\S+)$/i', $date, $m)) {
            $day = $this->correctDay($m[1], $contextRules);
            $month = $this->correctMonth($m[2], $monthCorrections, $validMonths);
            $year = $this->correctYear($m[3], $contextRules, $yearRange);

            // Validate day against month
            $day = $this->validateDayForMonth($day, $month, $contextRules);

            return trim("{$day} {$month} {$year}");
        }

        // Try to match: "MONTH YYYY" or "MONTH YY"
        if (preg_match('/^([A-Za-z]{3,})\s+(\S+)$/i', $date, $m)) {
            $month = $this->correctMonth($m[1], $monthCorrections, $validMonths);
            $year = $this->correctYear($m[2], $contextRules, $yearRange);

            return trim("{$month} {$year}");
        }

        // Try just a month name
        if (preg_match('/^[A-Za-z]{3,}$/i', $date)) {
            return $this->correctMonth($date, $monthCorrections, $validMonths);
        }

        // Return as-is if no pattern matches
        return $date;
    }

    /**
     * Correct a day number (1-31 range).
     */
    private function correctDay(string $day, array $contextRules): string
    {
        // Apply digit substitutions for non-numeric characters
        $digitSubs = $this->corrections['digit_substitutions'] ?? [];
        $corrected = '';
        for ($i = 0; $i < strlen($day); $i++) {
            $char = $day[$i];
            if (ctype_digit($char)) {
                $corrected .= $char;
            } else {
                $corrected .= $digitSubs[$char] ?? $char;
            }
        }

        $dayNum = (int) $corrected;
        $maxDay = $contextRules['day_range'][1] ?? 31;

        if ($dayNum < 1) $dayNum = 1;
        if ($dayNum > $maxDay) $dayNum = $maxDay;

        return (string) $dayNum;
    }

    /**
     * Correct a month name using the data dictionary.
     */
    private function correctMonth(string $month, array $corrections, array $validMonths): string
    {
        $upper = strtoupper(trim($month));

        // Already valid?
        if (in_array($upper, $validMonths)) {
            return $upper;
        }

        // Direct correction mapping
        if (isset($corrections[$upper])) {
            return $corrections[$upper];
        }

        // Try with common letter↔digit substitutions applied
        $letterSubs = $this->corrections['letter_substitutions'] ?? [];
        $subbed = '';
        for ($i = 0; $i < strlen($upper); $i++) {
            $char = $upper[$i];
            $subbed .= $letterSubs[$char] ?? $char;
        }
        if (in_array($subbed, $validMonths)) {
            return $subbed;
        }
        if (isset($corrections[$subbed])) {
            return $corrections[$subbed];
        }

        // Fuzzy match using Levenshtein distance
        $bestMatch = $this->fuzzyMatchMonth($upper);
        if ($bestMatch !== null) {
            return $bestMatch;
        }

        // Check full month names
        $fuzzyMap = $this->corrections['month_fuzzy_candidates'] ?? [];
        foreach ($fuzzyMap as $abbr => $fullNames) {
            foreach ($fullNames as $fullName) {
                if (strtoupper(substr($month, 0, strlen($fullName))) === $fullName) {
                    return $abbr;
                }
                if (levenshtein($upper, $fullName) <= 2) {
                    return $abbr;
                }
            }
        }

        return $upper; // Return as-is if no match found
    }

    /**
     * Fuzzy match a garbled month string against valid months.
     */
    private function fuzzyMatchMonth(string $input): ?string
    {
        $validMonths = $this->corrections['valid_months'] ?? [];
        $bestDistance = PHP_INT_MAX;
        $bestMatch = null;

        foreach ($validMonths as $month) {
            $distance = levenshtein($input, $month);
            // Accept if distance <= 1 (one character off)
            if ($distance < $bestDistance && $distance <= 1) {
                $bestDistance = $distance;
                $bestMatch = $month;
            }
        }

        return $bestMatch;
    }

    /**
     * Correct a year string.
     */
    private function correctYear(string $year, array $contextRules, array $yearRange): string
    {
        // Check year_corrections mapping first
        $yearCorrections = $contextRules['year_corrections'] ?? [];
        if (isset($yearCorrections[$year])) {
            return $yearCorrections[$year];
        }

        // Apply digit substitutions for non-numeric characters
        $digitSubs = $this->corrections['digit_substitutions'] ?? [];
        $corrected = '';
        for ($i = 0; $i < strlen($year); $i++) {
            $char = $year[$i];
            if (ctype_digit($char)) {
                $corrected .= $char;
            } else {
                $corrected .= $digitSubs[$char] ?? $char;
            }
        }

        // 2-digit year → 4-digit
        if (strlen($corrected) === 2) {
            $base = $contextRules['year_2digit_base'] ?? 2000;
            $corrected = (string)($base + (int)$corrected);
        }

        // Validate range
        $yearNum = (int) $corrected;
        $min = $yearRange[0] ?? 2020;
        $max = $yearRange[1] ?? 2040;

        if ($yearNum < $min || $yearNum > $max) {
            // Try common digit swaps: e.g. 2005 might be 2025
            if ($yearNum < 2020 && $yearNum >= 2000) {
                $candidate = $yearNum + 20; // 2005 → 2025
                if ($candidate >= $min && $candidate <= $max) {
                    return (string)$candidate;
                }
            }
        }

        return $corrected;
    }

    /**
     * Validate that a day number is valid for the given month.
     */
    private function validateDayForMonth(string $day, string $month, array $contextRules): string
    {
        $maxDays = $contextRules['max_days_per_month'] ?? [];
        $dayNum = (int)$day;

        if (isset($maxDays[$month]) && $dayNum > $maxDays[$month]) {
            // Clamp to max valid day for this month
            return (string)$maxDays[$month];
        }

        return $day;
    }

    /**
     * Correct a seat ID using the seat ID corrections dictionary.
     */
    private function correctSeatId(string $seatId): string
    {
        $seatId = trim($seatId);
        if (empty($seatId)) return $seatId;

        $corrections = $this->corrections['seat_id_corrections'] ?? [];

        // Apply prefix corrections
        foreach ($corrections as $wrong => $right) {
            if (str_starts_with($seatId, $wrong)) {
                $seatId = $right . substr($seatId, strlen($wrong));
                break; // Only apply one prefix correction
            }
        }

        return $seatId;
    }
}
