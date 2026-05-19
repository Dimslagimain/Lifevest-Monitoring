<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PdfParserService
{
    protected string $ghostscriptPath;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->ghostscriptPath = env('GHOSTSCRIPT_PATH', 'C:/Program Files/gs/gs10.07.0/bin/gswin64c.exe');
        $this->apiKey = env('OPENROUTER_API_KEY');
    }

    public function processFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $this->processPdf($filePath);
        }
        return $this->analyzeWithAI([$filePath]);
    }

    public function processPdf(string $pdfPath): array
    {
        $tempDir = storage_path('app/private/temp_pdf_pages');
        if (is_dir($tempDir)) {
            foreach (glob($tempDir . '/*') as $file) @unlink($file);
        } else {
            mkdir($tempDir, 0755, true);
        }

        $outputPattern = $tempDir . '/page_%03d.png';
        // 300 DPI: maximum readability for handwriting (truncation fixed via maxOutputTokens=65536)
        $gsCmd = sprintf('"%s" -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -sOutputFile="%s" "%s" 2>&1', $this->ghostscriptPath, $outputPattern, $pdfPath);
        
        Log::info('[PDF Scanner] Running Ghostscript command', ['cmd' => $gsCmd]);
        $gsOutput = [];
        $gsReturnCode = 0;
        exec($gsCmd, $gsOutput, $gsReturnCode);
        Log::info('[PDF Scanner] Ghostscript result', ['return_code' => $gsReturnCode, 'output_lines' => count($gsOutput)]);

        $pageImages = glob($tempDir . '/page_*.png');
        if (empty($pageImages)) {
            Log::error('[PDF Scanner] Ghostscript produced no images', ['gs_output' => implode("\n", $gsOutput)]);
            throw new \Exception('Gagal memproses PDF ke gambar. Pastikan Ghostscript terinstall dengan benar.');
        }
        sort($pageImages);
        Log::info('[PDF Scanner] Processing ' . count($pageImages) . ' page(s) AT ONCE');

        // Send ALL pages to AI in a single request!
        $allResults = $this->analyzeWithAI($pageImages);

        // Log raw IDs for debugging
        $spareIds = array_filter(
            array_column($allResults['seats'], 'seat_id'),
            fn($id) => preg_match('/pax|inf|spare|adult|infant|child|baby/i', $id)
        );
        Log::info('[PDF Scanner] Spare IDs from AI', ['spare_ids' => array_values($spareIds)]);

        // RE-SORT: Move all Spare/Infant seats to the very bottom, grouped (pax first, then inf)
        $normalSeats = [];
        $paxSeats = [];
        $infSeats = [];
        foreach ($allResults['seats'] as $seat) {
            $id = strtolower($seat['seat_id'] ?? '');
            // PAX / Adult patterns
            if (preg_match('/^(pax|adult|spare.?pax|spare.?adult|spares?)-?\d/i', $id) || $id === 'pax') {
                $paxSeats[] = $seat;
            // INF / Infant patterns
            } elseif (preg_match('/^(inf|infant|baby|child|spare.?inf|spare.?infant)-?\d/i', $id)) {
                $infSeats[] = $seat;
            // Generic spare → assume pax
            } elseif (str_contains($id, 'spare') || str_contains($id, 'adult') || str_contains($id, 'pax')) {
                $paxSeats[] = $seat;
            } elseif (str_contains($id, 'inf') || str_contains($id, 'infant') || str_contains($id, 'baby')) {
                $infSeats[] = $seat;
            } else {
                $normalSeats[] = $seat;
            }
        }
        // Sort pax and inf by their number suffix
        $sortByNum = function($a, $b) {
            preg_match('/(\d+)$/', $a['seat_id'] ?? '', $ma);
            preg_match('/(\d+)$/', $b['seat_id'] ?? '', $mb);
            return ((int)($ma[1] ?? 0)) - ((int)($mb[1] ?? 0));
        };
        usort($paxSeats, $sortByNum);
        usort($infSeats, $sortByNum);
        $allResults['seats'] = array_merge($normalSeats, $paxSeats, $infSeats);
        Log::info('[PDF Scanner] After sort', [
            'normal' => count($normalSeats),
            'pax' => count($paxSeats),
            'inf' => count($infSeats),
        ]);

        Log::info('[PDF Scanner] Final result', [
            'registration' => $allResults['registration'],
            'total_seats' => count($allResults['seats']),
        ]);

        $this->cleanTempDir($tempDir);
        return $allResults;
    }

    public function analyzeWithAI(array|string $imagePaths): array
    {
        if (is_string($imagePaths)) {
            $imagePaths = [$imagePaths];
        }

        $anthropicKey = env('ANTHROPIC_API_KEY');
        $openaiKey = env('OPENAI_API_KEY');
        $geminiKey = env('GEMINI_API_KEY');
        $openRouterKey = env('OPENROUTER_API_KEY');
        $snifoxKey = env('SNIFOX_API_KEY');
        
        if (empty($anthropicKey) && empty($openaiKey) && empty($geminiKey) && empty($openRouterKey) && empty($snifoxKey)) {
            throw new \Exception('Belum ada API Key. Set SNIFOX_API_KEY, ANTHROPIC_API_KEY, OPENAI_API_KEY, GEMINI_API_KEY, atau OPENROUTER_API_KEY di .env');
        }

        // Priority: Snifox > Gemini > Anthropic > OpenAI > OpenRouter
        $provider = !empty($snifoxKey) ? 'openrouter' : (!empty($geminiKey) ? 'gemini' : (!empty($anthropicKey) ? 'anthropic' : (!empty($openaiKey) ? 'openai' : 'openrouter')));
        
        $prompt = "You are a specialized Aircraft LOPA (Layout of Passenger Accommodations) extractor.
Your job is to EXTRACT, not summarize. You must output EVERY item visible in the document.

DOCUMENT READING RULES (MANDATORY):
- Scan ALL pages from TOP to BOTTOM without skipping ANY section.
- Do NOT stop early. Do NOT skip any row, column, section header, or label.
- If the document has multiple pages, treat them as ONE continuous document.
- Every attendant door label, every seat cell, every spare count MUST be extracted.
- If you skip any item, the output is WRONG and INCOMPLETE.

OUTPUT RULES:
- Output ONLY a MINIFIED JSON (no spaces, no indentation, no markdown, no explanation).
- JUST THE RAW JSON. Nothing before it, nothing after it.

STEP 1: Read the REGISTRATION (e.g. PK-GIA, PK-GIG) and AIRCRAFT TYPE from the document header. The registration is NEVER 'PENDING'. Look for it at the top of the page.
STEP 2: Identify the aircraft type, then apply ONLY the matching layout below. DO NOT mix rules from other types.

=== COCKPIT (ALL TYPES) ===
PDF may label them as 'Pilot', 'Copil'/'Co-Pilot', 'Observer'/'Oberver'. Map to exact IDs:
- Pilot → pilot
- Copilot (or 'Copil') → copilot
- Observer LEFT (appears left side) → observer1
- Observer RIGHT (appears right side) → observer2

=== IF B737 ===
ATTENDANT FWD: att/d11-LL, att/d11-LR (2 seats).
SEATS: Business Rows 6-8 columns ACHK. Economy Rows 21-49 columns ABC-HJK (skip row 24). Last rows may have fewer seats.
ATTENDANT AFT: att/d12-LL, att/d12-LR, att/d22-RL, att/d22-RR (4 seats).
SPARE (VERY IMPORTANT - READ CAREFULLY):
The spare table has TWO separate groups. Read each label and its count independently:
- If label says 'Adult' or 'PAX' or 'Pax': count the NUMBER of items under it. That count = N. Output pax-1, pax-2, ... pax-N.
- If label says 'Infant' or 'INF': count the NUMBER of items under it. That count = M. Output inf-1, inf-2, ... inf-M.
WARNING: Adult count and Infant count are DIFFERENT numbers! Do NOT swap them! Do NOT assume they are equal!
OUTPUT ORDER: ALL pax FIRST (pax-1 to pax-N), then ALL inf AFTER (inf-1 to inf-M). Do NOT interleave.
B737 has NO att/d13, d14, d23, d24.
CRITICAL CHECKPOINT B737: You MUST read until the bottom. Output MUST include att/d12-LL, att/d22-RR, and ALL Spares. If not, you FAILED.

=== IF A330 ===
ATTENDANT TOP: att/d11-LL1, att/d11-LL2, att/d11-LR (3 left), att/d21-R (1 right).
SEATS: Business Rows 6-11 columns AC-DG-HK. After Row 11 is a LARGE GAP - DO NOT STOP. Economy Rows 21-44 AC-DEFG-HK. Rows 45-48 AC-DFG-HK (no E). Row 49 ONLY D,F,G.
ATTENDANT MID: att/d12-L1, att/d12-L2, att/d22-R1, att/d22-R2.
ATTENDANT BOTTOM: att/d13-L, att/d23-R (only if visible).
ATTENDANT VERY BOTTOM: There are 4 items here! From left to right: att/d14-L, aft-LC, aft-RC, att/d24-R. DO NOT SKIP the two 'aft' items located in the middle.
SPARE: Read actual count from PDF. Use pax-1,...pax-N then inf-1,...inf-N.
CRITICAL CHECKPOINT A330: You MUST read until the bottom. Output MUST include att/d14-L, aft-LC, aft-RC, att/d24-R, and ALL Spares. If not, you FAILED.

=== IF B777 ===
HOW TO IDENTIFY B777: Look for 'B777', '777', or 'B777-300' in the document header. B777 is the ONLY type that has 'Att / center door-1' (with 2 seats inside it). If you see this center door label, it is ALWAYS a B777. Do NOT call it B737.

ATTENDANT DOORS — YOU MUST OUTPUT ALL OF THESE. NO EXCEPTIONS.
Scan the entire document for these labels and output each one:

[MANDATORY - DOOR 1, 4 items]
  Label 'Att / door-1L'        → output: att/d1-L
  Label 'Att / center door-1'  → TWO dates inside: left date = att/d1-CL, right date = att/d1-CR
  Label 'Att / door-1R'        → output: att/d1-R

[MANDATORY - DOOR 2, 2 items, located between Business and Economy section]
  Label 'Att / door-2L'        → output: att/d2-L
  Label 'Att / door-2R'        → output: att/d2-R

[MANDATORY - DOOR 3, 2 items, located near GALLEY row]
  Label 'Att / door-3L' or similar on LEFT side  → output: att/d3-L
  Label 'Att / door-3R' or similar on RIGHT side → output: att/d3-R
  NOTE: Door 3R appears on the FAR RIGHT. Do NOT miss it!

DOOR 4 & 5: Include ONLY if visible. Do NOT fabricate.

PRE-OUTPUT SELF-CHECK (do this before writing JSON):
  Count your att/ items. Expected: att/d1-L, att/d1-CL, att/d1-CR, att/d1-R, att/d2-L, att/d2-R, att/d3-L, att/d3-R = 8 items.
  If you have fewer than 8, go back and find the missing ones NOW.
  Only proceed to write JSON when all 8 are found.

SEATING LAYOUT (Check if First Class Row 1 exists):
IF NO FIRST CLASS (Document starts at Row 6):
  BUSINESS CLASS (Rows 6-12, STAGGERED - follow EXACTLY):
    Row 6: ONLY seats C, E, F, H
    Row 7: ONLY seats A, D, G, K
    Row 8: ONLY seats C, E, F, H
    Row 9: ONLY seats A, D, G, K
    Row 10: ONLY seats C, E, F, H
    Row 11: ONLY seats A, D, G, K
    Row 12: ONLY seats E, F
  ECONOMY CLASS (Rows 21-63, skip row 24) — READ EVERY ROW, DO NOT STOP EARLY:
    Rows 21-35: ALL columns A,B,C,D,F,G,H,J,K
    Row 36: ONLY seats D, F, G (3 seats only! No A,B,C,H,J,K!)
    Rows 37-48: ALL columns A,B,C,D,F,G,H,J,K
    Row 49: ONLY seats A, B, C, H, J, K (6 seats only! No D,F,G!)
    Rows 50-62: ALL columns A,B,C,D,F,G,H,J,K  ← MANDATORY, DO NOT SKIP!
    Row 63: ONLY seats A, C, D, F, G, H, K (7 seats only! No B, no J!)  ← THIS IS THE LAST ROW

  SEAT COUNT CHECKPOINT (No FC):
    Business: 26 seats (4+4+4+4+4+4+2). Economy: ~367 seats (14×9 + 3 + 12×9 + 6 + 13×9 + 7).
    TOTAL REGULAR SEATS (excl. cockpit/attendant/spare): ~393.
    If your count is below 350, you stopped too early. Go back and extract rows 50-63 NOW.

IF FIRST CLASS EXISTS (Document starts at Row 1):
  First Class Rows 1-2: A,D,G,K.
  Business Rows 6-16 staggered: Row6 A,E,F,K; Row7 C,D,G,H; Row8 A,K only; Row9 A,E,F,K; Row10 C,D,G,H; Row11 A,E,F,K; Row12 C,D,G,H; Row14 A,E,F,K; Row15 C,D,G,H; Row16 A,E,F,K.
  Economy Rows 21-52 (skip 24). Row 25 only DFG. Row 38 no DFG. Row 52 no B,J.  ← Row 52 IS THE LAST ROW for this variant.

  SEAT COUNT CHECKPOINT (With FC):
    If your economy count is below 200, you stopped too early. Go back and extract until row 52.

SPARE TABLE READING RULES (B777):
The spare table is on the RIGHT SIDE of the document. It has these columns:
  - No. column = row number (1, 2, 3...) — DO NOT use this as the count!
  - INFANT column = expiry date for infant spare life vests
  - SPARE column = may show count or be a label — IGNORE this column
  - ADULT or A/Craft column = expiry date for adult spare life vests

HOW TO COUNT SPARES (READ THIS CAREFULLY):
- Physically count how many rows have a date written in the ADULT column. That number = N. Output pax-1, pax-2, ...pax-N.
- Physically count how many rows have a date written in the INFANT column. That number = M. Output inf-1, inf-2, ...inf-M.
- N and M are ALMOST ALWAYS DIFFERENT. (e.g. 6 adult, 2 infant is normal — do NOT assume they are equal!)
- SKIP any row where the cell is completely BLANK or EMPTY.
- DO NOT use the row number in the 'No.' column as your count. Count only filled date cells.
- SPARE COUNT CHECKPOINT: If you output pax-1 and inf-1 only (just 1 each), that is almost certainly WRONG. Re-examine the spare table carefully.

CRITICAL CHECKPOINT B777: You MUST output ALL 8 door items (att/d1-L, att/d1-CL, att/d1-CR, att/d1-R, att/d2-L, att/d2-R, att/d3-L, att/d3-R). You MUST read the ENTIRE document including ALL economy rows. If any door is missing OR economy rows stop before 52/63, you FAILED.

=== IF A320 ===
ATTENDANT FWD: att/d11-LL, att/d11-LR (2 seats).
SEATS: Economy Rows 1-31 columns ABC-DEF (skip row 13).
ATTENDANT AFT: att/d12-L, att/d22-RL, att/d22-RR (3 seats).
SPARE: Read actual count from PDF. Use pax-1,...pax-N then inf-1,...inf-N.
A320 has NO att/d13, d14, d23, d24.
CRITICAL CHECKPOINT A320: You MUST read until the bottom. Output MUST include att/d12-L, att/d22-RR, and ALL Spares. If not, you FAILED.

=== OUTPUT ORDER ===
Output items IN THE EXACT ORDER they appear vertically in the document (top to bottom).
Example sequence: Cockpit -> Front Attendants -> Front Seats -> Mid Attendants -> Mid Seats -> Rear Attendants.
EXCEPTION: ALL SPARE (pax and inf) MUST be grouped together at the VERY END.

=== FINAL CRITICAL RULE ===
Output ONLY seat IDs listed for the detected type. Do NOT invent IDs from other types. Registration is NEVER 'PENDING'.

=== HANDWRITING READING RULES ===
Many dates are HANDWRITTEN. Read them with EXTREME care. Verify each character individually.

DIGIT CONFUSION GUIDE (check every digit!):
  0 ↔ 6 ↔ 8 (round shapes, look at top opening)
  1 ↔ 7 (straight strokes, check for crossbar on 7 or serif on 1)
  2 ↔ 7 ↔ Z (curved vs angular, check bottom stroke)
  3 ↔ 8 ↔ 5 (look at closed vs open loops)
  4 ↔ 9 (look at top closure and bottom stroke)
  5 ↔ 6 ↔ S (check if bottom is round or flat)
  9 ↔ 4 ↔ 7 (look at tail direction)

MONTH CONFUSION GUIDE (check every letter!):
  JAN ↔ JUN (A vs U — very common confusion!)
  MAR ↔ MAY (R vs Y — look at bottom stroke carefully)
  APR ↔ AUG (P vs U, R vs G — check second/third letter)
  JUL ↔ JUN (L vs N — check last letter shape)
  MAR ↔ MAY ↔ MAI (some write MAI for MAY)
  SEP ↔ FEB (S vs F, P vs B — check each letter)
  OCT ↔ OKT (some use K instead of C)
  NOV ↔ DEC (completely different — look at first letter N vs D)
  Valid months are ONLY: JAN, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC.

YEAR RULES:
  - Years should be 4 digits (2024-2040 range is typical).
  - If only 2 digits (e.g. '28'), interpret as '2028'. Never '1928'.
  - Check: 2025 vs 2026, 2029 vs 2024, 2030 vs 2036.

ACCURACY RULES (VERY IMPORTANT!):

COLUMN ALIGNMENT (CRITICAL - THIS IS THE MOST COMMON ERROR!):
- The table has column headers at the TOP (e.g. A, B, C or A, C, D, F, G, H, J, K).
- For EACH row, read LEFT to RIGHT strictly. Match each cell to its column header by vertical alignment.
- Do NOT drift. If row 41A = OCT 2026, then 41B is the NEXT cell to the RIGHT — a completely different cell.
- Common mistake: reading 42A as the value from 42B or 42C because 42A handwriting is faint or unclear.
  If a cell looks empty or unclear, output '' — do NOT substitute with the neighboring cell's value.

1. READ EACH CELL TWICE before recording. First pass = initial read, second pass = verify.
2. CROSS-CHECK vertically: Compare each date with 2-3 seats ABOVE and BELOW in the SAME COLUMN.
   Same-column seats usually share similar expiry year ranges (e.g. 2025-2028).
   If your reading differs drastically from same-column neighbors → you likely read the WRONG column. Re-examine!
3. SANITY CHECK: Typical expiry years are 2024-2036. Re-examine if outside this range.
4. FOCUS ON INK, not grid lines. Handwriting may touch or cross grid lines — track the ink strokes only.
5. DO NOT GUESS. Cannot read a cell? → output empty string '' instead of a wrong date.
6. A blank/empty cell = no expiry date → output empty string ''.

DATA FORMAT (MINIFIED JSON):
{\"registration\":\"PK-GIA\",\"aircraft_type\":\"B777-300\",\"seats\":[[\"pilot\",\"31 MAY 2029\"],[\"copilot\",\"17 JAN 2035\"],[\"att/d1-L\",\"14 MAY 2029\"],[\"att/d1-CL\",\"17 MAY 2029\"],[\"6C\",\"12 MAR 2030\"],[\"pax-1\",\"SEP 28\"],[\"inf-1\",\"SEP 23\"]]}";

        $geminiParts = [['text' => $prompt]];
        $openAiContent = [['type' => 'text', 'text' => $prompt]];
        $openRouterContent = [['type' => 'text', 'text' => $prompt]];
        $anthropicImages = [];

        foreach ($imagePaths as $imagePath) {
            // Compress to JPEG to reduce payload size (PNG at 300 DPI can be 10MB+)
            $originalSize = filesize($imagePath);
            $img = imagecreatefrompng($imagePath);
            if ($img === false) {
                // Fallback: try as any format
                $img = imagecreatefromstring(file_get_contents($imagePath));
            }
            
            if ($img !== false) {
                // === IMAGE ENHANCEMENT FOR BETTER OCR ===
                // 1. Boost contrast so handwriting stands out from faint grid lines
                imagefilter($img, IMG_FILTER_CONTRAST, -20); // negative = more contrast
                // 2. Sharpen using unsharp mask convolution
                $sharpenMatrix = [
                    [ 0, -1,  0],
                    [-1,  9, -1],
                    [ 0, -1,  0],
                ];
                $divisor = array_sum(array_map('array_sum', $sharpenMatrix)); // = 5
                imageconvolution($img, $sharpenMatrix, $divisor, 0);
                // 3. Compress to JPEG at 92% (high quality, readable for AI)
                ob_start();
                imagejpeg($img, null, 92); // 92% - high quality for handwriting clarity
                $compressedData = ob_get_clean();
                imagedestroy($img);
                $mimeType = 'image/jpeg';
                $imageData = base64_encode($compressedData);
                Log::info('[PDF Scanner] Image enhanced + compressed', [
                    'original_kb' => round($originalSize / 1024),
                    'compressed_kb' => round(strlen($compressedData) / 1024),
                ]);
            } else {
                // Fallback: send raw
                $mimeType = mime_content_type($imagePath);
                $imageData = base64_encode(file_get_contents($imagePath));
                Log::warning('[PDF Scanner] Could not compress image, sending raw', ['path' => $imagePath]);
            }

            $anthropicImages[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $mimeType,
                    'data' => $imageData,
                ]
            ];

            $geminiParts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $imageData
                ]
            ];

            $openAiContent[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$imageData}",
                    'detail' => 'high' // Use high detail for handwriting accuracy
                ]
            ];

            $openRouterContent[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => "data:{$mimeType};base64,{$imageData}"
                ]
            ];
        }

        $maxRetries = 2;
        $lastError = null;

        // Build Anthropic content: images first, then text prompt
        $anthropicContent = array_merge($anthropicImages, [['type' => 'text', 'text' => $prompt]]);

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                if ($provider === 'anthropic') {
                    // === ANTHROPIC CLAUDE 3.5 SONNET (Best for table reading) ===
                    Log::info("[PDF Scanner] Anthropic Claude call attempt {$attempt}/{$maxRetries} with " . count($imagePaths) . " image(s)");

                    $response = Http::timeout(240)->withHeaders([
                        'x-api-key' => $anthropicKey,
                        'anthropic-version' => '2023-06-01',
                        'content-type' => 'application/json',
                    ])->post('https://api.anthropic.com/v1/messages', [
                        'model' => 'claude-3-5-sonnet-20241022',
                        'max_tokens' => 16000,
                        'temperature' => 0.1,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $anthropicContent
                            ]
                        ]
                    ]);

                } elseif ($provider === 'openai') {
                    // === OPENAI GPT-4o Vision (Best for handwriting) ===
                    Log::info("[PDF Scanner] OpenAI GPT-4o call attempt {$attempt}/{$maxRetries} with " . count($imagePaths) . " image(s)");

                    $response = Http::timeout(240)->withHeaders([
                        'Authorization' => 'Bearer ' . $openaiKey,
                        'Content-Type' => 'application/json',
                    ])->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $openAiContent
                            ]
                        ],
                        'temperature' => 0.1,
                        'max_tokens' => 16000,
                        'response_format' => ['type' => 'json_object']
                    ]);

                } elseif ($provider === 'gemini') {
                    // === GOOGLE GEMINI API ===
                    Log::info("[PDF Scanner] Google Gemini API call attempt {$attempt}/{$maxRetries} with " . count($imagePaths) . " image(s)");

                    $response = Http::timeout(240)
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$geminiKey}", [
                            'contents' => [
                                [
                                    'parts' => $geminiParts
                                ]
                            ],
                            'generationConfig' => [
                                'temperature' => 0.1,
                                'maxOutputTokens' => 65536,
                                'responseMimeType' => 'application/json',
                            ]
                        ]);
                } else {
                    // === SNIFOX AI / OPENROUTER (OpenAI-compatible) ===
                    $isSnifox = !empty($snifoxKey);
                    $routerKey = $isSnifox ? $snifoxKey : $openRouterKey;
                    $routerBase = $isSnifox ? 'https://core.snifoxai.com/v1' : 'https://openrouter.ai/api/v1';
                    $routerModel = $isSnifox
                        ? env('SNIFOX_MODEL', 'google/gemini-3.1-pro-preview')
                        : 'google/gemini-3.1-pro-preview';

                    Log::info("[PDF Scanner] " . ($isSnifox ? 'Snifox' : 'OpenRouter') . " API call attempt {$attempt}/{$maxRetries} with " . count($imagePaths) . " image(s)", [
                        'key_prefix' => substr($routerKey, 0, 15),
                        'model' => $routerModel,
                        'base' => $routerBase,
                    ]);

                    $headers = [
                        'Authorization' => 'Bearer ' . $routerKey,
                        'Content-Type' => 'application/json',
                    ];
                    if (!$isSnifox) {
                        $headers['HTTP-Referer'] = 'http://localhost:8000';
                        $headers['X-Title'] = 'Life Vest Tracker';
                    }

                    $response = Http::timeout(300)->withHeaders($headers)->post("{$routerBase}/chat/completions", [
                        'model' => $routerModel,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a JSON-only output machine. Never output anything except valid minified JSON. No markdown, no explanation, no code blocks.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $openRouterContent
                            ]
                        ],
                        'temperature' => 0.05,
                        'max_tokens' => 32000,
                        // Note: response_format NOT sent - Gemini models via proxy don't support it
                    ]);
                }

                if ($response->failed()) {
                    $errorBody = $response->body();
                    Log::error("[PDF Scanner] API returned error (attempt {$attempt})", [
                        'status' => $response->status(),
                        'body' => substr($errorBody, 0, 500),
                    ]);
                    $lastError = new \Exception('API Error (HTTP ' . $response->status() . '): ' . substr($errorBody, 0, 200));
                    if ($attempt < $maxRetries) { sleep(2); continue; }
                    throw $lastError;
                }

                $responseData = $response->json();
                
                // Parse response berdasarkan provider
                if ($provider === 'anthropic') {
                    $rawContent = $responseData['content'][0]['text'] ?? '';
                } elseif ($provider === 'gemini') {
                    $rawContent = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                } else {
                    // OpenAI and OpenRouter use same response format
                    $rawContent = $responseData['choices'][0]['message']['content'] ?? '';
                }
                
                // Strip the loop detection bypass tag
                $rawContent = str_replace('[ignoring loop detection]', '', $rawContent);

                Log::info("[PDF Scanner] Raw AI response (provider: {$provider}, attempt {$attempt})", [
                    'content_length' => strlen($rawContent),
                    'raw_preview'    => substr($rawContent, 0, 3000),
                ]);

                if (empty(trim($rawContent))) {
                    Log::warning("[PDF Scanner] API returned empty content (attempt {$attempt})");
                    $lastError = new \Exception('AI returned empty content');
                    if ($attempt < $maxRetries) { sleep(2); continue; }
                    throw $lastError;
                }

                // Use the dedicated extractJson method
                $parsedData = $this->extractJson($rawContent);
                
                if ($parsedData === null) {
                    Log::error("[PDF Scanner] JSON extraction failed", ['raw_preview' => substr($rawContent, 0, 500)]);
                    $lastError = new \Exception('Gagal parsing JSON dari response AI');
                    if ($attempt < $maxRetries) { sleep(2); continue; }
                    throw $lastError;
                }

                $registration = $parsedData['registration'] ?? 'PENDING';
                $seats = $parsedData['seats'] ?? [];

                Log::info("[PDF Scanner] Successfully parsed (attempt {$attempt})", [
                    'registration' => $registration,
                    'seats_count' => count($seats),
                ]);

                return [
                    'registration' => $registration,
                    'aircraft_type' => $parsedData['aircraft_type'] ?? 'Unknown',
                    'seats' => $seats
                ];

            } catch (\Exception $e) {
                Log::error("[PDF Scanner] Exception (attempt {$attempt})", ['error' => $e->getMessage()]);
                $lastError = $e;
                if ($attempt < $maxRetries) { sleep(2); continue; }
            }
        }

        throw $lastError ?? new \Exception('Gagal menganalisis gambar setelah beberapa percobaan.');
    }

    private function extractJson(string $content): ?array
    {
        $content = trim($content);
        $candidates = [];

        // 1. Try markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $candidates[] = trim($matches[1]);
        }
        
        // 2. Try to find the first '{' and the last '}'
        $firstBrace = strpos($content, '{');
        $lastBrace = strrpos($content, '}');
        if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
            $candidates[] = substr($content, $firstBrace, $lastBrace - $firstBrace + 1);
        }
        
        // 3. Try the whole content if it starts with '{' (might be truncated at the end)
        if ($firstBrace !== false) {
            $candidates[] = substr($content, $firstBrace);
        }

        $candidates[] = $content;

        foreach ($candidates as $json) {
            $json = trim($json);
            if (empty($json)) continue;

            $decoded = json_decode($json, true);
            if ($decoded !== null && is_array($decoded)) {
                return $this->normalizeResult($decoded);
            }

            $cleaned = $this->cleanJson($json);
            $decoded = json_decode($cleaned, true);
            if ($decoded !== null && is_array($decoded)) {
                return $this->normalizeResult($decoded);
            }
        }

        // Try fixing truncated ones
        foreach ($candidates as $json) {
            $json = trim($json);
            if (empty($json)) continue;
            
            $fixed = $this->fixTruncatedJson($json);
            $decoded = json_decode($fixed, true);
            if ($decoded !== null && is_array($decoded)) {
                return $this->normalizeResult($decoded);
            }
        }

        return null;
    }

    private function cleanJson(string $json): string
    {
        $json = preg_replace('/[\x{FEFF}\x{200B}\x{200C}\x{200D}]/u', '', $json);
        $json = preg_replace('/\/\/[^\n]*/', '', $json);
        $json = preg_replace('/\/\*[\s\S]*?\*\//', '', $json);
        $json = preg_replace('/,\s*([\}\]])/', '$1', $json);
        $json = preg_replace('/([{\[,])\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $json);
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json);
        return trim($json);
    }

    private function fixTruncatedJson(string $json): string
    {
        $json = trim($json);
        if (empty($json)) return '';

        // Clean basic issues first
        $json = $this->cleanJson($json);

        // Close unclosed quotes
        $quotes = substr_count($json, '"') - substr_count($json, '\\"');
        if ($quotes % 2 !== 0) {
            $json .= '"';
        }

        // Simple brace counting
        $openBraces = substr_count($json, '{');
        $closeBraces = substr_count($json, '}');
        $openBrackets = substr_count($json, '[');
        $closeBrackets = substr_count($json, ']');

        if ($openBraces > $closeBraces || $openBrackets > $closeBrackets) {
            // Find the last complete object/array ending
            $lastCompletePos = max(strrpos($json, '}'), strrpos($json, ']'));
            
            if ($lastCompletePos !== false) {
                // If there's a comma after the last complete element, strip it
                $afterLast = substr($json, $lastCompletePos + 1);
                if (str_contains($afterLast, ',')) {
                    $json = substr($json, 0, strrpos($json, ','));
                } else {
                    // Truncate to the last complete structural element
                    $json = substr($json, 0, $lastCompletePos + 1);
                }
            }

            // Recount and close
            $openBraces = substr_count($json, '{');
            $closeBraces = substr_count($json, '}');
            $openBrackets = substr_count($json, '[');
            $closeBrackets = substr_count($json, ']');

            $json .= str_repeat(']', max(0, $openBrackets - $closeBrackets));
            $json .= str_repeat('}', max(0, $openBraces - $closeBraces));
            
            // Final cleanup of trailing commas
            $json = preg_replace('/,\s*([\}\]])/', '$1', $json);
        }

        return $json;
    }

    private function normalizeResult(array $data): ?array
    {
        if (empty($data)) return null;

        $seats = [];
        $rawSeats = $data['seats'] ?? (isset($data[0]) ? $data : []);

        foreach ($rawSeats as $item) {
            if (isset($item[0]) && str_contains(strtolower((string)$item[0]), 'seat')) continue;

            if (isset($item['seat_id'])) {
                $seats[] = [
                    'seat_id' => $item['seat_id'],
                    'expiry_date' => $item['expiry_date'] ?? ''
                ];
            } elseif (is_array($item) && count($item) >= 2) {
                $seats[] = [
                    'seat_id' => $item[0],
                    'expiry_date' => $item[1] ?? ''
                ];
            }
        }

        return [
            'registration' => $data['registration'] ?? 'PENDING',
            'aircraft_type' => $data['aircraft_type'] ?? 'Unknown',
            'seats' => $seats
        ];
    }

    private function cleanTempDir(string $dir): void
    {
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $file) @unlink($file);
            @rmdir($dir);
        }
    }

    public function parseText(string $text): string { return $text; }
}
