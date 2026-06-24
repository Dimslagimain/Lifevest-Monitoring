<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerificationService
{
    protected ?string $flazKey;
    protected ?string $flazModel;
    protected array $imageData; // base64-encoded images for AI validation
    protected float $confidenceThreshold; // min confidence for auto-accept (0.85 = 85%)

    public function __construct(float $confidenceThreshold = 0.85)
    {
        $this->flazKey  = env('FLAZ_API_KEY');
        $this->flazModel = env('FLAZ_MODEL', 'claude-sonnet-4-6');
        $this->confidenceThreshold = $confidenceThreshold;
    }

    /**
     * Verify and correct extracted data from PDF
     * 
     * @param array $extractedData {registration, aircraft_type, seats: [[seat_id, expiry_date], ...]}
     * @param array|string|null $imagePaths Original image paths for AI validation (optional)
     * @return array Enhanced data with confidence scores + correction flags
     *              {
     *                 registration, 
     *                 aircraft_type,
     *                 seats: [
     *                    {
     *                       seat_id, 
     *                       expiry_date,
     *                       original_value,
     *                       confidence (0-1),
     *                       was_corrected (bool),
     *                       correction_type (typo|date_format|digit_confusion|ai_validation|none),
     *                       issue_detected (null|string),
     *                       suggestion (null|string)
     *                    },
     *                    ...
     *                 ],
     *                 summary: {auto_accepted, flagged, needs_review}
     *              }
     */
    public function verify(array $extractedData, array|string|null $imagePaths = null): array
    {
        Log::info('[Verification] Starting verification process', [
            'registration' => $extractedData['registration'] ?? 'PENDING',
            'total_seats' => count($extractedData['seats'] ?? []),
            'has_images' => !empty($imagePaths),
        ]);

        // Step 1: Apply registration-specific row validation (per-aircraft layout)
        $registration = $extractedData['registration'] ?? 'PENDING';
        $extractedData['seats'] = $this->validateAndCorrectRowsByRegistration(
            $registration,
            $extractedData['seats'] ?? []
        );

        // Step 2: Apply rule-based corrections
        $verifiedSeats = $this->applyRuleBasedCorrections($extractedData['seats'] ?? []);

        // Step 3: Apply AI validation if images provided
        if (!empty($imagePaths) && !empty($this->flazKey)) {
            try {
                $this->imageData = $this->prepareImageData($imagePaths);
                $aiValidation = $this->applyAiValidation($extractedData, $verifiedSeats);
                // Merge AI validation results back with rules:
                // - If AI reports match==false and suggests a correction with confidence >= threshold,
                //   accept the suggested_correction as new expiry_date and mark was_corrected.
                // - Otherwise attach AI fields (confidence, issue, image_value) for review.
                foreach ($verifiedSeats as $idx => $seat) {
                    if (!isset($aiValidation[$idx])) continue;
                    $aiData = $aiValidation[$idx];

                    // Attach AI metadata
                    $verifiedSeats[$idx] = array_merge($seat, [
                        'confidence' => $aiData['confidence'] ?? ($seat['confidence'] ?? 0.5),
                        'issue_detected' => $aiData['issue'] ?? ($seat['issue_detected'] ?? null),
                        'image_value' => $aiData['image_value'] ?? null,
                        'original_value' => $aiData['original_value'] ?? ($seat['original_value'] ?? null),
                    ]);

                    // If AI found a mismatch and suggested a correction
                    $suggestion = $aiData['suggested_correction'] ?? null;
                    $match = isset($aiData['match']) ? (bool)$aiData['match'] : null;
                    $conf = $aiData['confidence'] ?? ($verifiedSeats[$idx]['confidence'] ?? 0);

                    if ($match === false && !empty($suggestion) && $conf >= $this->confidenceThreshold) {
                        // AI confident about a correction → auto-fix
                        $verifiedSeats[$idx]['expiry_date'] = $suggestion;
                        $verifiedSeats[$idx]['was_corrected'] = true;
                        $verifiedSeats[$idx]['correction_type'] = 'ai_validation';
                        $verifiedSeats[$idx]['suggestion'] = $suggestion;
                        $verifiedSeats[$idx]['issue_detected'] = $aiData['issue'] ?? $verifiedSeats[$idx]['issue_detected'] ?? 'mismatch_detected';
                        // confidence stays as returned by AI (already high)
                    } elseif ($match === false) {
                        // AI detected mismatch but could not / would not auto-correct.
                        // Force confidence LOW so it surfaces as flagged/needs_review.
                        $verifiedSeats[$idx]['was_corrected'] = false;
                        $verifiedSeats[$idx]['correction_type'] = 'ai_flag';
                        $verifiedSeats[$idx]['issue_detected'] = $aiData['issue'] ?? 'mismatch_detected';
                        $verifiedSeats[$idx]['suggestion'] = $suggestion;          // may be null
                        // Clamp confidence to below the threshold so it never auto-passes
                        $verifiedSeats[$idx]['confidence'] = min($conf, 0.69);
                    } else {
                        // match === true or null — no mismatch found by AI
                        $verifiedSeats[$idx]['was_corrected'] = $verifiedSeats[$idx]['was_corrected'] ?? false;
                        $verifiedSeats[$idx]['correction_type'] = $verifiedSeats[$idx]['correction_type'] ?? 'none';
                        $verifiedSeats[$idx]['suggestion'] = $suggestion ?? ($verifiedSeats[$idx]['suggestion'] ?? null);
                        // If AI confirms match, raise confidence moderately — NOT to 1.0
                        // AI re-reading same image is not a guarantee of correctness
                        if ($match === true) {
                            $verifiedSeats[$idx]['confidence'] = min(max($conf, 0.90), 0.95);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('[Verification] AI validation failed, continuing with rule-based only', [
                    'error' => $e->getMessage()
                ]);
                // Continue without AI validation - rule-based is still applied
            }
        }

        // Step 3: Calculate summary
        $summary = $this->calculateSummary($verifiedSeats);

        return [
            'registration' => $extractedData['registration'] ?? 'PENDING',
            'aircraft_type' => $extractedData['aircraft_type'] ?? 'Unknown',
            'seats' => $verifiedSeats,
            'summary' => $summary,
            'confidence_threshold' => $this->confidenceThreshold,
        ];
    }

    /**
     * Validate and correct row numbers based on aircraft layout from database
     * 
     * For each registration (aircraft), lookup the configured layout and validate
     * that seat rows match the expected range for that layout.
     * 
     * Example:
     * - PK-GHE (layout: a330-900a): Business 6-11, Economy 21-58
     * - PK-GHH (layout: a330-900b): Economy only 21-58 (no business, so min row is 21)
     * 
     * If scan shows "6C" but layout says min is 21, flag or correct it.
     * 
     * @param string $registration Aircraft registration (e.g., PK-GHH)
     * @param array $seats Extracted seats from PDF
     * @return array Seats with row validation applied
     */
    protected function validateAndCorrectRowsByRegistration(string $registration, array $seats): array
    {
        if ($registration === 'PENDING' || empty($seats)) {
            return $seats;
        }

        try {
            // Lookup aircraft from database
            $aircraft = \App\Models\Aircraft::where('registration', $registration)->first();
            if (!$aircraft) {
                Log::warning('[Verification] Aircraft not found in database', ['registration' => $registration]);
                return $seats; // No validation possible
            }

            $layout = $aircraft->layout;
            $classRowsConfig = config('aircraft_class_rows');
            if (!isset($classRowsConfig[$layout])) {
                Log::warning('[Verification] Layout config not found', ['layout' => $layout, 'registration' => $registration]);
                return $seats; // No validation possible
            }

            // Get the expected row ranges for this aircraft
            $layoutConfig = $classRowsConfig[$layout];
            $expectedRows = [];
            $minRow = null;
            $maxRow = null;

            foreach ($layoutConfig as $class => $rows) {
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $expectedRows[] = $row;
                        if ($minRow === null || $row < $minRow) $minRow = $row;
                        if ($maxRow === null || $row > $maxRow) $maxRow = $row;
                    }
                }
            }

            Log::info('[Verification] Aircraft layout validated', [
                'registration' => $registration,
                'layout' => $layout,
                'min_expected_row' => $minRow,
                'max_expected_row' => $maxRow,
            ]);

            // Validate each seat
            foreach ($seats as &$seat) {
                $seatId = $seat['seat_id'] ?? ($seat[0] ?? '');
                
                // Extract row number from seat_id (e.g., "6A" → 6, "21C" → 21)
                if (preg_match('/^(\d+)/', $seatId, $m)) {
                    $row = (int)$m[1];
                    
                    // Check if row is valid for this aircraft
                    if ($minRow !== null && $row < $minRow) {
                        Log::warning('[Verification] Seat row below minimum for layout', [
                            'registration' => $registration,
                            'seat_id' => $seatId,
                            'row' => $row,
                            'min_expected' => $minRow,
                            'layout' => $layout,
                        ]);
                        
                        // Flag for review - don't auto-correct row numbers as that's risky
                        $seat['issue_detected'] = "Row {$row} below expected minimum {$minRow} for {$layout}";
                        $seat['was_flagged'] = true;
                    } elseif ($maxRow !== null && $row > $maxRow) {
                        Log::warning('[Verification] Seat row above maximum for layout', [
                            'registration' => $registration,
                            'seat_id' => $seatId,
                            'row' => $row,
                            'max_expected' => $maxRow,
                            'layout' => $layout,
                        ]);
                        
                        // Flag for review
                        $seat['issue_detected'] = "Row {$row} above expected maximum {$maxRow} for {$layout}";
                        $seat['was_flagged'] = true;
                    }
                }
            }

            return $seats;
        } catch (\Exception $e) {
            Log::error('[Verification] Row validation error', [
                'registration' => $registration,
                'error' => $e->getMessage(),
            ]);
            return $seats; // Return original on error
        }
    }

    /**
     * Apply rule-based corrections (deterministic, no AI)
     * Handles: typos, date format normalization, obvious digit/letter confusion
     */
    protected function applyRuleBasedCorrections(array $seats): array
    {
        $corrected = [];

        foreach ($seats as $seat) {
            $seatId = $seat['seat_id'] ?? $seat[0] ?? '';
            $expiryDate = $seat['expiry_date'] ?? $seat[1] ?? '';

            $seatResult = [
                'seat_id' => $seatId,
                'expiry_date' => $expiryDate,
                'original_value' => $expiryDate, // Store for comparison
                // Rule-based can only verify FORMAT, not actual content correctness.
                // Keep at 0.80 (below 0.85 threshold) — only AI validation can upgrade to "Yakin Benar"
                // by re-reading the original image and confirming the value matches.
                'confidence' => 0.80,
                'was_corrected' => false,
                'correction_type' => 'none',
                'issue_detected' => null,
                'suggestion' => null,
            ];

            // ----- SEAT ID CORRECTIONS -----
            $correctedSeatId = $this->correctSeatId($seatId);
            if ($correctedSeatId !== $seatId) {
                $seatResult['seat_id'] = $correctedSeatId;
                $seatResult['was_corrected'] = true;
                $seatResult['correction_type'] = 'typo';
                $seatResult['issue_detected'] = "Seat ID typo";
                $seatResult['suggestion'] = "{$seatId} → {$correctedSeatId}";
                $seatId = $correctedSeatId; // Use corrected for further processing
            }

            // ----- EXPIRY DATE CORRECTIONS -----
            $dateResult = $this->correctExpiryDate($expiryDate);
            if ($dateResult['corrected'] !== $expiryDate) {
                $seatResult['expiry_date'] = $dateResult['corrected'];
                $seatResult['was_corrected'] = true;
                $seatResult['correction_type'] = 'date_format';
                $seatResult['issue_detected'] = $dateResult['issue'];
                $seatResult['suggestion'] = $dateResult['suggestion'];
                $seatResult['confidence'] = $dateResult['confidence']; // May be <1.0 if ambiguous
            }

            $corrected[] = $seatResult;
        }

        return $corrected;
    }

    /**
     * Correct common seat ID typos
     */
    protected function correctSeatId(string $seatId): string
    {
        $original = $seatId;

        // Trim and uppercase
        $seatId = trim($seatId);

        // Remove extra spaces
        $seatId = preg_replace('/\s+/', '', $seatId);

        // Cockpit: normalize spellings
        if (preg_match('/^(pilot|capt?a?i?n|capt|flt)$/i', $seatId)) {
            return 'pilot';
        }
        if (preg_match('/^(copilot|co-pilot|co_pilot|copil|fo|first.?officer)$/i', $seatId)) {
            return 'copilot';
        }
        if (preg_match('/^(observer|obs).*?1/i', $seatId)) {
            return 'observer1';
        }
        if (preg_match('/^(observer|obs).*?2/i', $seatId)) {
            return 'observer2';
        }

        // Attendant door: normalize format
        // Examples: "d1-R" → "att/d1-R", "D1-R" → "att/d1-R", "att/d1-R" → "att/d1-R"
        if (preg_match('/^(?:att\/)?d(\d+)-([a-zA-Z]{1,2})$/i', $seatId, $m)) {
            $door = strtolower($m[1]);
            $position = strtoupper($m[2]);
            return "att/d{$door}-{$position}";
        }

        // Spare/PAX seats: normalize format
        // Examples: "PAX-1", "pax 1", "ADULT-1" → "pax-1"
        if (preg_match('/^(pax|adult|spare.*?pax)\s*[-_]?\s*(\d+)$/i', $seatId, $m)) {
            return "pax-" . $m[2];
        }
        // Infant/Baby: "INF-1", "inf 1", "baby-1" → "inf-1"
        if (preg_match('/^(inf|infant|baby|child)\s*[-_]?\s*(\d+)$/i', $seatId, $m)) {
            return "inf-" . $m[2];
        }

        // Regular seat: normalize format
        // Examples: "6AA" → "6A", "06A" → "6A", "6 A" → "6A", "ROW 6 COL A" → "6A"
        $seatId = preg_replace('/^(?:row|col)\s*/i', '', $seatId);
        $seatId = preg_replace('/\s+/', '', $seatId);
        
        // Handle duplicate letters: "6AA" → "6A", "12CC" → "12C"
        if (preg_match('/^(\d+)([a-zA-Z])\1$/i', $seatId, $m)) {
            return strtoupper($m[1] . $m[2]);
        }

        // Standard seat: number + letter(s)
        if (preg_match('/^(\d+)([a-zA-Z]{1,2})$/i', $seatId, $m)) {
            $row = (int)$m[1]; // Remove leading zeros
            $col = strtoupper($m[2]);
            return "{$row}{$col}";
        }

        return $original;
    }

    /**
     * Correct and normalize expiry dates
     * Handles multiple formats: "28 JAN 2028", "JAN 28 2028", "1/28/2028", Excel numeric dates, etc.
     * 
     * @return array {corrected: string (YYYY-MM-DD or original), confidence: 0-1, issue: string|null, suggestion: string|null}
     */
    protected function correctExpiryDate(string $expiryDate): array
    {
        $original = trim($expiryDate);

        if (empty($original)) {
            return [
                'corrected' => '',
                'confidence' => 1.0,
                'issue' => null,
                'suggestion' => null,
            ];
        }

        // Remove uncertainty flag (?) if present
        $hasUncertaintyFlag = str_ends_with($original, '?');
        $dateStr = $hasUncertaintyFlag ? rtrim($original, '?') : $original;

        // If already in ISO format (YYYY-MM-DD), validate and return
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($dateStr))) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', trim($dateStr), 'UTC');
                if ($date && $date->year >= 2024 && $date->year <= 2036) {
                    return [
                        'corrected' => $date->format('Y-m-d'),
                        'confidence' => 1.0,
                        'issue' => null,
                        'suggestion' => null,
                    ];
                }
            } catch (\Exception $e) {
                // Fall through to other parsing methods
            }
        }

        // Try to parse with Carbon (handles most formats)
        $carbonConfidence = 1.0;
        try {
            // Try common date formats
            $formats = [
                'DD MMM YYYY', 'D MMM YYYY',  // "28 JAN 2028"
                'MMM DD YYYY', 'MMM D YYYY',  // "JAN 28 2028"
                'DD/MM/YYYY', 'D/M/YYYY',     // "28/01/2028"
                'MM/DD/YYYY', 'M/D/YYYY',     // "01/28/2028"
                'DD-MM-YYYY', 'D-M-YYYY',     // "28-01-2028"
                'YYYY/MM/DD',                  // "2028/01/28"
                'DD MMM', 'D MMM',             // "28 JAN" (no year)
                'MMM DD', 'MMM D',             // "JAN 28" (no year)
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, trim($dateStr), 'UTC');
                    
                    // If no year provided, assume current or next year
                    if (!preg_match('/\d{4}/', trim($dateStr))) {
                        $currentYear = Carbon::now('UTC')->year;
                        if ($date->month < Carbon::now('UTC')->month) {
                            $date = $date->year($currentYear + 1);
                        } else {
                            $date = $date->year($currentYear);
                        }
                        $carbonConfidence = 0.85; // Slightly lower confidence for guessed year
                    }

                    // Sanity check: year should be reasonable (2024-2036)
                    if ($date->year >= 2024 && $date->year <= 2036) {
                        $issue = null;
                        $suggestion = null;

                        // Flag if had uncertainty marker
                        if ($hasUncertaintyFlag) {
                            $issue = "Uncertain in original (marked with ?)";
                            $carbonConfidence = 0.80;
                        }

                        return [
                            'corrected' => $date->format('Y-m-d'),
                            'confidence' => $carbonConfidence,
                            'issue' => $issue,
                            'suggestion' => $suggestion,
                        ];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            // Fall through
        }

        // If all parsing fails, return original with low confidence
        return [
            'corrected' => $original,
            'confidence' => 0.5,
            'issue' => 'Could not parse date format',
            'suggestion' => "Please verify format; expected: DD MMM YYYY (e.g., 28 JAN 2028)",
        ];
    }

    /**
     * Prepare base64-encoded images for AI validation.
     * Returns array of ['data' => base64string, 'mime_type' => 'image/jpeg']
     */
    protected function prepareImageData(array|string $imagePaths): array
    {
        if (is_string($imagePaths)) {
            $imagePaths = [$imagePaths];
        }

        $images = [];
        foreach ($imagePaths as $path) {
            // Convert web-relative path (/storage/...) to absolute filesystem path
            if (str_starts_with($path, '/storage/')) {
                $path = public_path($path);
            } elseif (str_starts_with($path, '/')) {
                $path = public_path($path);
            }

            if (!file_exists($path)) {
                Log::warning('[Verification] Image file not found', ['path' => $path]);
                continue;
            }

            try {
                $content = file_get_contents($path);
                if ($content === false) continue;

                // Compress to JPEG (same as PdfParserService)
                $img = @imagecreatefrompng($path);
                if ($img === false) {
                    $img = @imagecreatefromstring($content);
                }

                if ($img !== false) {
                    imagefilter($img, IMG_FILTER_CONTRAST, -20);
                    $sharpenMatrix = [[0, -1, 0], [-1, 9, -1], [0, -1, 0]];
                    $divisor = array_sum(array_map('array_sum', $sharpenMatrix));
                    imageconvolution($img, $sharpenMatrix, $divisor, 0);

                    ob_start();
                    imagejpeg($img, null, 92);
                    $compressedData = ob_get_clean();
                    imagedestroy($img);

                    $images[] = ['data' => base64_encode($compressedData), 'mime_type' => 'image/jpeg'];
                } else {
                    $mimeType = mime_content_type($path) ?: 'image/jpeg';
                    $images[] = ['data' => base64_encode($content), 'mime_type' => $mimeType];
                }
            } catch (\Exception $e) {
                Log::warning('[Verification] Failed to prepare image', ['path' => $path, 'error' => $e->getMessage()]);
                continue;
            }
        }

        return $images;
    }

    /**
     * Apply AI validation pass using Flaz.id (OpenAI-compatible API)
     * Re-examine original images and provide confidence scores per seat.
     */
    protected function applyAiValidation(array $extractedData, array $verifiedSeats): array
    {
        if (empty($this->imageData)) {
            Log::warning('[Verification] No image data for AI validation');
            return [];
        }

        Log::info('[Verification] Starting AI validation with Flaz.id', [
            'model'          => $this->flazModel,
            'seats_to_validate' => count($verifiedSeats),
            'images_count'   => count($this->imageData),
        ]);

        $aircraftType = $extractedData['aircraft_type'] ?? 'Unknown';

        try {
            // Batch large seat lists to avoid truncated responses
            $batchSize = 60;
            $allResults = [];

            $seatChunks = array_chunk($verifiedSeats, $batchSize, true);

            foreach ($seatChunks as $chunkIndex => $chunk) {
                Log::info('[Verification] Processing batch', [
                    'batch'          => $chunkIndex + 1,
                    'total_batches'  => count($seatChunks),
                    'seats_in_batch' => count($chunk),
                ]);

                $chunkSeatsJson = json_encode(array_values(array_map(fn($s) => [
                    'seat_id'     => $s['seat_id'],
                    'expiry_date' => $s['expiry_date'],
                ], $chunk)));

                $chunkPrompt = "You are an expert auditor of aircraft life-vest maintenance records."
                    . " Do NOT guess or hallucinate values."
                    . "\n\nTASK: Re-examine the ORIGINAL IMAGES provided and COMPARE them to the extracted values below."
                    . " For each item, re-read the image and state the EXACT textual value you see."
                    . "\n\nCONTEXT: Aircraft type: {$aircraftType}."
                    . "\n\nEXTRACTED DATA (for reference only):\n{$chunkSeatsJson}"
                    . "\n\nINSTRUCTIONS:"
                    . "\n1) For each seat entry, look at the corresponding row on the image and read the expiry date value exactly as it appears in handwriting."
                    . "\n2) Return these fields per item: seat_id, original_value, image_value, match (true/false), confidence (0.0-1.0), issue (null or short string), suggested_correction (YYYY-MM-DD or null)."
                    . "\n3) If the image area is illegible, return image_value as empty string, confidence < 0.7, issue: illegible."
                    . "\n4) Never invent a value. If unsure, set confidence low."
                    . "\n\nOUTPUT: Return ONLY a minified JSON object with key validation_items containing an array.";

                // Build message content: text prompt + images
                $userContent = [['type' => 'text', 'text' => $chunkPrompt]];
                foreach ($this->imageData as $imgItem) {
                    $mimeType = $imgItem['mime_type'] ?? 'image/jpeg';
                    $b64      = $imgItem['data'] ?? '';
                    $userContent[] = [
                        'type'      => 'image_url',
                        'image_url' => [
                            'url'    => "data:{$mimeType};base64,{$b64}",
                            'detail' => 'high',
                        ],
                    ];
                }

                $response = Http::timeout(300)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->flazKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://ai.flaz.id/v1/chat/completions', [
                    'model'       => $this->flazModel,
                    'temperature' => 0.05,
                    'max_tokens'  => 16000,
                    'messages'    => [
                        [
                            'role'    => 'system',
                            'content' => 'You are a JSON-only output machine. Never output anything except valid minified JSON. No markdown, no explanation, no code blocks.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $userContent,
                        ],
                    ],
                ]);

                if ($response->failed()) {
                    Log::warning('[Verification] Batch API failed', [
                        'batch'  => $chunkIndex + 1,
                        'status' => $response->status(),
                        'body'   => substr($response->body(), 0, 500),
                    ]);
                    continue;
                }

                $responseData = $response->json();
                $rawContent   = $responseData['choices'][0]['message']['content'] ?? '';

                if (empty($rawContent)) {
                    Log::warning('[Verification] Empty response for batch ' . ($chunkIndex + 1));
                    continue;
                }

                Log::info('[Verification] AI validation batch response', [
                    'batch'          => $chunkIndex + 1,
                    'content_length' => strlen($rawContent),
                    'preview'        => substr($rawContent, 0, 300),
                ]);

                // Strip markdown code fences if model wrapped the JSON
                $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
                $rawContent = preg_replace('/```\s*$/', '', $rawContent);

                $validationData = json_decode($rawContent, true);
                if (!is_array($validationData)) {
                    $validationData = $this->extractPartialJson($rawContent);
                }

                if (!is_array($validationData)) {
                    Log::warning('[Verification] JSON parse failed for batch ' . ($chunkIndex + 1));
                    continue;
                }

                $validationItems = $validationData['validation_items'] ?? [];

                // Index by seat_id for quick lookup
                $validationBySeatId = [];
                foreach ($validationItems as $item) {
                    $validationBySeatId[$item['seat_id'] ?? ''] = $item;
                }

                // Match back to original indices
                foreach ($chunk as $idx => $seat) {
                    $seatId = $seat['seat_id'];
                    if (isset($validationBySeatId[$seatId])) {
                        $aiData = $validationBySeatId[$seatId];
                        $allResults[$idx] = [
                            'confidence'           => $aiData['confidence'] ?? 0.5,
                            'match'                => $aiData['match'] ?? null,
                            'issue'                => $aiData['issue'] ?? null,
                            'issue_detected'       => $aiData['issue'] ?? null,
                            'image_value'          => $aiData['image_value'] ?? null,
                            'original_value'       => $aiData['original_value'] ?? null,
                            'suggested_correction' => $aiData['suggested_correction'] ?? null,
                            'suggestion'           => $aiData['suggested_correction'] ?? null,
                            'correction_type'      => 'ai_validation',
                        ];
                    }
                }

                Log::info('[Verification] Batch processed', [
                    'batch'            => $chunkIndex + 1,
                    'ai_items_parsed'  => count($validationItems),
                    'matched_to_seats' => count(array_intersect_key($allResults, $chunk)),
                ]);
            }

            Log::info('[Verification] All batches complete', [
                'total_ai_results' => count($allResults),
                'total_seats'      => count($verifiedSeats),
            ]);

            return $allResults;

        } catch (\Exception $e) {
            Log::error('[Verification] AI validation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary(array $verifiedSeats): array
    {
        $autoAccepted = 0;
        $flagged = 0;
        $needsReview = 0;

        foreach ($verifiedSeats as $seat) {
            $confidence = $seat['confidence'] ?? 0;
            
            if ($confidence >= $this->confidenceThreshold) {
                $autoAccepted++;
            } elseif ($confidence >= 0.70) {
                $flagged++;
            } else {
                $needsReview++;
            }
        }

        return [
            'total' => count($verifiedSeats),
            'auto_accepted' => $autoAccepted,
            'flagged' => $flagged,
            'needs_review' => $needsReview,
            'auto_accept_rate' => count($verifiedSeats) > 0 ? round(($autoAccepted / count($verifiedSeats)) * 100, 1) : 0,
        ];
    }

    /**
     * Get only flagged items (those below threshold or corrected)
     */
    public function getFlaggedItems(array $verificationResult): array
    {
        $flagged = [];
        $threshold = $verificationResult['confidence_threshold'] ?? 0.85;

        foreach ($verificationResult['seats'] ?? [] as $seat) {
            $confidence = $seat['confidence'] ?? 1.0;
            
            // Include if: corrected OR confidence below threshold
            if ($seat['was_corrected'] || $confidence < $threshold) {
                $flagged[] = $seat;
            }
        }

        return $flagged;
    }

    /**
     * Get high-confidence items (auto-accepted)
     */
    public function getHighConfidenceItems(array $verificationResult): array
    {
        $highConfidence = [];
        $threshold = $verificationResult['confidence_threshold'] ?? 0.85;

        foreach ($verificationResult['seats'] ?? [] as $seat) {
            $confidence = $seat['confidence'] ?? 1.0;
            
            if ($confidence >= $threshold && !$seat['was_corrected']) {
                $highConfidence[] = $seat;
            }
        }

        return $highConfidence;
    }

    /**
     * Extract partial JSON from potentially truncated AI response
     * Salvages as many validation_items as possible even if the JSON is incomplete
     */
    protected function extractPartialJson(string $content): ?array
    {
        $content = trim($content);
        if (empty($content)) return null;

        // Try to find and extract JSON
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');
        
        if ($firstBrace === false) return null;

        // If we have matching braces, try that substring
        if ($lastBrace !== false && $lastBrace > $firstBrace) {
            $json = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) return $decoded;
        }

        // JSON is truncated — try to repair it
        $json = substr($content, $firstBrace);
        
        // Remove trailing incomplete entries
        // Find the last complete object "}" in the validation_items array
        $lastCompleteObj = strrpos($json, '}');
        if ($lastCompleteObj !== false) {
            $json = substr($json, 0, $lastCompleteObj + 1);
        }

        // Strip trailing comma
        $json = preg_replace('/,\s*$/', '', $json);

        // Count and close unclosed brackets/braces
        $openBrackets = substr_count($json, '[') - substr_count($json, ']');
        $openBraces = substr_count($json, '{') - substr_count($json, '}');

        $json .= str_repeat(']', max(0, $openBrackets));
        $json .= str_repeat('}', max(0, $openBraces));

        // Clean trailing commas before closing brackets
        $json = preg_replace('/,\s*([\}\]])/', '$1', $json);

        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $itemCount = count($decoded['validation_items'] ?? []);
            Log::info('[Verification] Recovered truncated JSON', ['items_recovered' => $itemCount]);
            return $decoded;
        }

        return null;
    }
}
