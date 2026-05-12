<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PdfParserService
{
    protected $ghostscriptPath;
    protected $apiKey;

    public function __construct()
    {
        $this->ghostscriptPath = env('GHOSTSCRIPT_PATH', 'C:/Program Files/gs/gs10.07.0/bin/gswin64c.exe');
        $this->apiKey = env('OPENROUTER_API_KEY');
    }

    public function processFile($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $this->processPdf($filePath);
        }
        return $this->analyzeWithAI($filePath);
    }

    public function processPdf($pdfPath)
    {
        $tempDir = storage_path('app/private/temp_pdf_pages');
        if (is_dir($tempDir)) {
            foreach (glob($tempDir . '/*') as $file) @unlink($file);
        } else {
            mkdir($tempDir, 0755, true);
        }

        $outputPattern = $tempDir . '/page_%03d.png';
        $gsCmd = sprintf('"%s" -dNOPAUSE -dBATCH -sDEVICE=png16m -r300 -sOutputFile="%s" "%s" 2>&1', $this->ghostscriptPath, $outputPattern, $pdfPath);
        
        Log::info('[PDF Scanner] Running Ghostscript command', ['cmd' => $gsCmd]);
        $gsOutput = [];
        exec($gsCmd, $gsOutput, $gsReturnCode);
        Log::info('[PDF Scanner] Ghostscript result', ['return_code' => $gsReturnCode, 'output_lines' => count($gsOutput)]);

        $pageImages = glob($tempDir . '/page_*.png');
        if (empty($pageImages)) {
            Log::error('[PDF Scanner] Ghostscript produced no images', ['gs_output' => implode("\n", $gsOutput)]);
            throw new \Exception('Gagal memproses PDF ke gambar. Pastikan Ghostscript terinstall dengan benar.');
        }
        sort($pageImages);
        Log::info('[PDF Scanner] Processing ' . count($pageImages) . ' page(s)');

        $allResults = ['registration' => 'PENDING', 'aircraft_type' => 'Unknown', 'seats' => []];

        foreach ($pageImages as $idx => $pageImage) {
            Log::info('[PDF Scanner] Analyzing page ' . ($idx + 1) . '/' . count($pageImages));
            $pageData = $this->analyzeWithAI($pageImage);
            
            Log::info('[PDF Scanner] Page ' . ($idx + 1) . ' result', [
                'registration' => $pageData['registration'],
                'aircraft_type' => $pageData['aircraft_type'],
                'seats_count' => count($pageData['seats']),
            ]);

            if ($pageData['registration'] !== 'PENDING') $allResults['registration'] = $pageData['registration'];
            if ($pageData['aircraft_type'] !== 'Unknown') $allResults['aircraft_type'] = $pageData['aircraft_type'];
            $allResults['seats'] = array_merge($allResults['seats'], $pageData['seats']);
        }

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

    public function analyzeWithAI($imagePath)
    {
        if (empty($this->apiKey)) throw new \Exception('API Key OpenRouter belum disetting.');

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = 'image/png';

        $prompt = "This is an aircraft life vest inspection form (LOPA). 
You MUST extract data in this EXACT ORDER: 
1. Cockpit Section
2. Attendant / Door Section
3. Passenger Seats (Row by Row, from front to back)
4. Spare / Infant Section

STRICT MAPPING LOGIC (B777-200 Layout):
- BUSINESS CLASS (Rows 6-12):
  * Even Rows (6, 8, 10): Seats C, E, F, H.
  * Odd Rows (7, 9, 11): Seats A, D, G, K.
  * Row 12: Seats E, F only.
- ECONOMY CLASS (Rows 21-63):
  * Standard Layout: ABC - DFG - HJK (Row Number + Letter).
  * Row 36: D, F, G only.
  * Row 49: A, B, C and H, J, K only (No center).
  * Row 63: A, B, D, F, G, J, K.
- EXTRACT EVERYTHING: You MUST extract every single seat, attendant seat, and cockpit seat visible on the page. Do NOT stop after Business Class.

DATE EXTRACTION:
- Extract the EXACT date from the 'EXPIRY DATE' column in the image.
- DO NOT use the example date \"JAN 2030\" if the image shows something else.

DATA FORMAT (JSON):
{
  \"registration\": \"PK-GIH\",
  \"aircraft_type\": \"B777\",
  \"seats\": [
    [\"Seat_ID\", \"Expiry_Date\"],
    [\"6C\", \"DATA_FROM_IMAGE\"],
    [\"50A\", \"DATA_FROM_IMAGE\"]
  ]
}

STRICT RULES:
- Use the COMPACT ARRAY format for seats to save space.
- Return ONLY raw JSON. No markdown, no explanation.
- If the data is long, CONTINUE until all seats are listed.";

        $maxRetries = 2;
        $lastError = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                Log::info("[PDF Scanner] AI API call attempt {$attempt}/{$maxRetries}", ['image' => basename($imagePath)]);

                $response = Http::timeout(180)->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => 'http://localhost:8000',
                    'X-Title' => 'Life Vest Tracker',
                ])->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => 'google/gemini-2.0-flash-001',
                    'temperature' => 0.1,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"]]
                            ]
                        ]
                    ],
                    'max_tokens' => 20000,
                ]);

                if ($response->failed()) {
                    $errorBody = $response->body();
                    Log::error("[PDF Scanner] API returned error (attempt {$attempt})", [
                        'status' => $response->status(),
                        'body' => substr($errorBody, 0, 500),
                    ]);
                    $lastError = new \Exception('API Error (HTTP ' . $response->status() . '): ' . substr($errorBody, 0, 200));
                    
                    if ($attempt < $maxRetries) {
                        sleep(2);
                        continue;
                    }
                    throw $lastError;
                }

                $responseData = $response->json();
                $content = $responseData['choices'][0]['message']['content'] ?? null;

                if (empty($content)) {
                    Log::warning("[PDF Scanner] API returned empty content (attempt {$attempt})");
                    $lastError = new \Exception('AI returned empty content');
                    if ($attempt < $maxRetries) { sleep(2); continue; }
                    throw $lastError;
                }

                Log::info("[PDF Scanner] Raw AI response (attempt {$attempt})", [
                    'content_length' => strlen($content),
                ]);

                // Try to extract JSON from the response
                $jsonContent = $this->extractJson($content);
                
                if ($jsonContent === null) {
                    $lastError = new \Exception('Gagal parsing JSON dari response AI');
                    if ($attempt < $maxRetries) { sleep(2); continue; }
                    throw $lastError;
                }

                $seats = $jsonContent['seats'] ?? [];
                
                Log::info("[PDF Scanner] Successfully parsed (attempt {$attempt})", [
                    'registration' => $jsonContent['registration'] ?? 'PENDING',
                    'seats_count' => count($seats),
                ]);

                return [
                    'registration' => $jsonContent['registration'] ?? 'PENDING',
                    'aircraft_type' => $jsonContent['aircraft_type'] ?? 'Unknown',
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

    /**
     * Extract and parse JSON from AI response content.
     * Handles multiple formats: raw JSON, markdown code blocks, etc.
     * Also cleans up common AI JSON issues (trailing commas, truncated output).
     */
    private function extractJson($content)
    {
        $content = trim($content);
        $candidates = [];

        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $candidates[] = trim($matches[1]);
        }
        $candidates[] = $content;
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $candidates[] = trim($matches[0]);
        }

        foreach ($candidates as $json) {
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

        foreach ($candidates as $json) {
            $fixed = $this->fixTruncatedJson($json);
            $decoded = json_decode($fixed, true);
            if ($decoded !== null && is_array($decoded)) {
                return $this->normalizeResult($fixed_decoded = $decoded);
            }
        }

        return null;
    }

    /**
     * Clean common JSON syntax issues from AI output.
     */
    private function cleanJson($json)
    {
        $json = preg_replace('/[\x{FEFF}\x{200B}\x{200C}\x{200D}]/u', '', $json);
        $json = preg_replace('/\/\/[^\n]*/', '', $json);
        $json = preg_replace('/\/\*[\s\S]*?\*\//', '', $json);
        $json = preg_replace('/,\s*([\}\]])/', '$1', $json);
        $json = preg_replace('/([{\[,])\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $json);
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $json);
        return trim($json);
    }

    /**
     * Attempt to fix truncated JSON by adding missing closing brackets.
     */
    private function fixTruncatedJson($json)
    {
        $json = $this->cleanJson($json);
        $openBraces = substr_count($json, '{');
        $closeBraces = substr_count($json, '}');
        $openBrackets = substr_count($json, '[');
        $closeBrackets = substr_count($json, ']');

        if ($openBraces > $closeBraces || $openBrackets > $closeBrackets) {
            // Find the last complete object or array element
            $lastCompletePos = max(strrpos($json, '}'), strrpos($json, ']'));
            if ($lastCompletePos !== false) {
                // If it looks like it's inside an array, find the last comma before truncation
                $lastComma = strrpos($json, ',');
                if ($lastComma > $lastCompletePos) {
                    $json = substr($json, 0, $lastComma);
                } else {
                    $json = substr($json, 0, $lastCompletePos + 1);
                }
            }

            $json .= str_repeat(']', max(0, $openBrackets - substr_count($json, ']')));
            $json .= str_repeat('}', max(0, $openBraces - substr_count($json, '}')));
            $json = preg_replace('/,\s*([\}\]])/', '$1', $json);
        }

        return $json;
    }

    /**
     * Normalize the parsed result to ensure consistent structure.
     * Supports:
     * 1. Flat array of objects: [{seat_id, expiry_date}, ...]
     * 2. Full object with seats array of objects: {seats: [{seat_id, expiry_date}, ...]}
     * 3. Compact array format: {seats: [["50A", "JAN 2030"], ...]}
     */
    private function normalizeResult($data)
    {
        if (!is_array($data)) return null;

        $seats = [];
        $rawSeats = $data['seats'] ?? (isset($data[0]) ? $data : []);

        foreach ($rawSeats as $item) {
            // Skip header if AI included it
            if (isset($item[0]) && str_contains(strtolower($item[0]), 'seat')) continue;

            if (isset($item['seat_id'])) {
                // Format: {seat_id: "...", expiry_date: "..."}
                $seats[] = [
                    'seat_id' => $item['seat_id'],
                    'expiry_date' => $item['expiry_date'] ?? ''
                ];
            } elseif (is_array($item) && count($item) >= 2) {
                // Format: ["50A", "JAN 2030"]
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

    private function cleanTempDir($dir)
    {
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') as $file) @unlink($file);
            @rmdir($dir);
        }
    }

    public function parseText($text) { return $text; }
}
