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
        $gsCmd = sprintf('"%s" -dNOPAUSE -dBATCH -sDEVICE=png16m -r200 -sOutputFile="%s" "%s" 2>&1', $this->ghostscriptPath, $outputPattern, $pdfPath);
        
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

        // RE-SORT: Move all Spare/Infant seats to the very bottom
        $normalSeats = [];
        $spareSeats = [];
        foreach ($allResults['seats'] as $seat) {
            $id = strtolower($seat['seat_id'] ?? '');
            if (str_contains($id, 'pax-') || str_contains($id, 'inf-') || str_contains($id, 'spare')) {
                $spareSeats[] = $seat;
            } else {
                $normalSeats[] = $seat;
            }
        }
        $allResults['seats'] = array_merge($normalSeats, $spareSeats);

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

        $geminiKey = env('GEMINI_API_KEY');
        $openRouterKey = env('OPENROUTER_API_KEY');
        
        if (empty($geminiKey) && empty($openRouterKey)) {
            throw new \Exception('Belum ada API Key. Set GEMINI_API_KEY atau OPENROUTER_API_KEY di .env');
        }

        $useGemini = !empty($geminiKey);
        
        $prompt = "You are a specialized Aircraft LOPA extractor.
Extract all seat data from the image into a MINIFIED JSON (no spaces, no indentation).
DO NOT provide explanation or markdown. JUST THE RAW JSON.

STEP 1: Read the REGISTRATION (e.g. PK-GIA, PK-GIG) and AIRCRAFT TYPE from the document header. The registration is NEVER 'PENDING'. Look for it at the top of the page.
STEP 2: Apply ONLY the matching layout below. DO NOT mix rules from other types.

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
SPARE: Read the actual count from PDF. Use pax-1,pax-2,...pax-N then inf-1,inf-2,...inf-N.
B737 has NO att/d13, d14, d23, d24.
CRITICAL CHECKPOINT B737: You MUST read until the bottom. Output MUST include att/d12-LL, att/d22-RR, and ALL Spares. If not, you FAILED.

=== IF A330 ===
ATTENDANT TOP: att/d11-LL1, att/d11-LL2, att/d11-LR (3 left), att/d21-R (1 right).
SEATS: Business Rows 6-11 columns AC-DG-HK. After Row 11 is a LARGE GAP - DO NOT STOP. Economy Rows 21-44 AC-DEFG-HK. Rows 45-48 AC-DFG-HK (no E). Row 49 ONLY D,F,G.
ATTENDANT MID: att/d12-L1, att/d12-L2, att/d22-R1, att/d22-R2.
ATTENDANT BOTTOM: att/d13-L, att/d23-R (only if visible).
ATTENDANT VERY BOTTOM: att/d14-L, att/d24-R - DO NOT SKIP.
SPARE: Read actual count from PDF. Use pax-1,...pax-N then inf-1,...inf-N.
CRITICAL CHECKPOINT A330: You MUST read until the bottom. Output MUST include att/d14-L, att/d24-R, and ALL Spares. If not, you FAILED.

=== IF B777 ===
ATTENDANT DOORS (CRITICAL - DO NOT SKIP!):
Look for labels in the PDF like 'Att / door-1L', 'Att / center door-1', 'Att / door-2L', etc.
- DOOR 1: Map 'Att / door-1L' -> att/d1-L. Map 'Att / center door-1' (left date) -> att/d1-CL, and (right date) -> att/d1-CR. Map 'Att / door-1R' -> att/d1-R. (These 4 MUST appear).
- DOOR 2 (Usually between Business and Economy): If you see 'Att / door-2L' with 1 date, output that date for BOTH att/d2-L1 AND att/d2-L2. If you see 'Att / door-2R' with 1 date, output it for BOTH att/d2-R1 AND att/d2-R2.
- DOOR 3 (Usually near Row 36): If you see 'Att / door-3L', output att/d3-L. If you see 'Att / door-3R', output att/d3-R.
- DOOR 4 & 5: If they do NOT appear in the document, DO NOT invent them! DO NOT output att/d4 or att/d5!

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
  ECONOMY CLASS (Rows 21-63, skip row 24):
    Rows 21-35: ALL columns A,B,C,D,F,G,H,J,K
    Row 36: ONLY seats D, F, G (3 seats only! No A,B,C,H,J,K!)
    Rows 37-48: ALL columns A,B,C,D,F,G,H,J,K
    Row 49: ONLY seats A, B, C, H, J, K (6 seats only! No D,F,G!)
    Rows 50-62: ALL columns A,B,C,D,F,G,H,J,K
    Row 63: ONLY seats A, C, D, F, G, H, K (7 seats only! No B, no J!)

IF FIRST CLASS EXISTS (Document starts at Row 1):
  First Class Rows 1-2: A,D,G,K.
  Business Rows 6-16 staggered: Row6 A,E,F,K; Row7 C,D,G,H; Row8 A,K only; Row9 A,E,F,K; Row10 C,D,G,H; Row11 A,E,F,K; Row12 C,D,G,H; Row14 A,E,F,K; Row15 C,D,G,H; Row16 A,E,F,K.
  Economy Rows 21-52 (skip 24). Row 25 only DFG. Row 38 no DFG. Row 52 no B,J.

SPARE: Count CAREFULLY from the PDF spare table. Count each column SEPARATELY. Infant count and Adult(PAX) count are usually DIFFERENT numbers. Use pax-1,...pax-N then inf-1,...inf-N.
CRITICAL CHECKPOINT B777: You MUST read the ENTIRE document top to bottom. att/d1-L, att/d1-CL, att/d1-CR, att/d1-R MUST appear. Extract only visible attendant doors. If you skip visible items or fabricate invisible ones, you FAILED.

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

DATA FORMAT (MINIFIED JSON):
{\"registration\":\"PK-GIA\",\"aircraft_type\":\"B777-300\",\"seats\":[[\"pilot\",\"31 MAY 2029\"],[\"copilot\",\"17 JAN 2035\"],[\"att/d1-L\",\"14 MAY 2029\"],[\"att/d1-CL\",\"17 MAY 2029\"],[\"6C\",\"12 MAR 2030\"],[\"pax-1\",\"SEP 28\"],[\"inf-1\",\"SEP 23\"]]}";

        $geminiParts = [['text' => $prompt]];
        $openRouterContent = [['type' => 'text', 'text' => $prompt]];

        foreach ($imagePaths as $imagePath) {
            $mimeType = mime_content_type($imagePath);
            $imageData = base64_encode(file_get_contents($imagePath));

            $geminiParts[] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
                    'data' => $imageData
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

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                if ($useGemini) {
                    // === GOOGLE GEMINI API (Gratis, token besar) ===
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
                                'maxOutputTokens' => 16384,
                                'responseMimeType' => 'application/json'
                            ]
                        ]);
                } else {
                    // === OPENROUTER API (Fallback) ===
                    Log::info("[PDF Scanner] OpenRouter API call attempt {$attempt}/{$maxRetries} with " . count($imagePaths) . " image(s)");

                    $response = Http::timeout(240)->withHeaders([
                        'Authorization' => 'Bearer ' . $openRouterKey,
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => 'http://localhost:8000',
                        'X-Title' => 'Life Vest Tracker',
                    ])->post('https://openrouter.ai/api/v1/chat/completions', [
                        'model' => 'google/gemini-2.0-flash-001',
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $openRouterContent
                            ]
                        ],
                        'temperature' => 0.1,
                        'max_tokens' => 10000,
                        'response_format' => ['type' => 'json_object']
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
                if ($useGemini) {
                    $rawContent = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                } else {
                    $rawContent = $responseData['choices'][0]['message']['content'] ?? '';
                }
                
                // Strip the loop detection bypass tag
                $rawContent = str_replace('[ignoring loop detection]', '', $rawContent);

                Log::info("[PDF Scanner] Raw AI response (attempt {$attempt})", [
                    'content_length' => strlen($rawContent),
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
