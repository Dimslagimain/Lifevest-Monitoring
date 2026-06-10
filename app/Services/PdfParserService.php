<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PdfParserService
{
    protected string $ghostscriptPath;
    protected ?string $apiKey;
    protected OcrPreprocessService $ocrPreprocess;

    public function __construct()
    {
        $this->ghostscriptPath = env('GHOSTSCRIPT_PATH', 'C:/Program Files/gs/gs10.07.0/bin/gswin64c.exe');
        $this->apiKey = env('OPENROUTER_API_KEY');
        $this->ocrPreprocess = new OcrPreprocessService();
    }

    public function processFile(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') {
            return $this->processPdf($filePath);
        }
        
        $allResults = $this->analyzeWithAI([$filePath]);
        
        // Save scan image for review page display
        $scanDir = 'scan_preview';
        $storageScanDir = storage_path("app/public/{$scanDir}");
        if (!is_dir($storageScanDir)) {
            mkdir($storageScanDir, 0755, true);
        }
        // Clean old previews
        foreach (glob($storageScanDir . '/*') as $old) @unlink($old);
        
        $filename = "page_1.jpg";
        $imgRes = @imagecreatefromstring(file_get_contents($filePath));
        if ($imgRes !== false) {
            imagejpeg($imgRes, $storageScanDir . '/' . $filename, 85);
            imagedestroy($imgRes);
        } else {
            copy($filePath, $storageScanDir . '/' . $filename);
        }
        
        $allResults['scan_images'] = ["/storage/{$scanDir}/{$filename}"];
        
        return $allResults;
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

        // Save scan images to public storage for review page display
        $scanImagePaths = [];
        $scanDir = 'scan_preview';
        $storageScanDir = storage_path("app/public/{$scanDir}");
        if (!is_dir($storageScanDir)) {
            mkdir($storageScanDir, 0755, true);
        }
        // Clean old previews
        foreach (glob($storageScanDir . '/*') as $old) @unlink($old);
        
        foreach ($pageImages as $idx => $img) {
            $filename = "page_" . ($idx + 1) . ".jpg";
            // Compress to JPEG for web display
            $imgRes = imagecreatefrompng($img);
            if ($imgRes === false) $imgRes = imagecreatefromstring(file_get_contents($img));
            if ($imgRes !== false) {
                imagejpeg($imgRes, $storageScanDir . '/' . $filename, 85);
                imagedestroy($imgRes);
            } else {
                copy($img, $storageScanDir . '/' . $filename);
            }
            $scanImagePaths[] = "/storage/{$scanDir}/{$filename}";
        }
        $allResults['scan_images'] = $scanImagePaths;

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
- ATTENDANT DOORS are CRITICAL and EASY TO MISS. They appear at specific locations in vertical order:
  * TOP SECTION (before economy seating) — Usually rows just above row 21
  * MIDDLE SECTION (left and right sides during economy)
  * BOTTOM SECTION (after main economy seating) — Often grouped together
  * VERY BOTTOM (final rear section) — Last group before spares
  Read EACH location carefully. Do NOT assume all doors are in one place.

OUTPUT RULES:
- Berikan hasil akhir dalam format JSON yang valid di dalam blok markdown ```json (JANGAN gunakan format minified string tanpa baris baru, karena akan membuat AI mengalami kegagalan memori/truncation di tengah dokumen).
- Jangan berikan kalimat penjelasan, pembuka, atau penutup apa pun di luar blok JSON tersebut. Hanya kirimkan RAW JSON di dalam blok markdown.

STEP 1: Read the REGISTRATION (e.g. PK-GIA, PK-GIG, PK-GHH) and AIRCRAFT TYPE from the document header. The registration is NEVER 'PENDING'. Look for it at the top of the page.

=== UNIVERSAL TABLE ALIGNMENT & ANTI-DRIFT RULES (CRITICAL) ===
1. DETEKSI DAN ISOLASI KOLOM NOMOR BARIS 'NO':
   - Perhatikan bahwa di dalam tabel terdapat kolom vertikal khusus bernama 'NO' yang berisi angka urut baris (seperti 6, 7, 8, 9... atau 21, 22, 23, 24...).
   - Kolom 'NO' ini terletak di antara kolom kursi (misalnya di antara kolom F/G dan kolom H).
   - JANGAN PERNAH mengambil angka dari kolom 'NO' ini untuk digabungkan atau disisipkan ke dalam teks tanggal kursi! (Contoh kesalahan: Angka '6' dari kolom NO dibaca masuk ke kursi H menjadi '6 JAN 2034' atau '6 SEP 2034'. Ini SALAH).
   - Angka di kolom 'NO' HANYA digunakan untuk mengidentifikasi nomor baris (row_no), bukan nilai tanggal kedaluwarsa.

2. VERIFIKASI KONSISTENSI STEMPEL:
   - Tanggal pada sel kursi yang berdekatan dalam satu baris sering kali menggunakan stempel yang sama secara massal. Jika kamu mendeteksi bulan/tahun yang aneh atau tidak sinkron dengan sel tetangganya, periksa kembali apakah kamu tidak sengaja membaca garis tabel atau angka dari kolom 'NO'.
   - Format tanggal wajib: 3 huruf bulan (Bahasa Inggris) + 4 digit tahun (Contoh: 'JAN 2035', 'MAR 2034').

AIRCRAFT IDENTIFICATION GUIDE:
- Look for text like: Aircraft, Type, Model, A/C Type, Registration labels in the header.
- PK-GHH, PK-GHI = A330-900 (Garuda Indonesia) — DO NOT confuse with other A330s
- PK-GHE, PK-GHF, PK-GHG = A330-900a (WITH Business Class, 1-1-1-1 stagger)
- If you see: A330-900, A330-900NEO, or A333 = It IS an Airbus A330
- If you see: B777, 777 = It IS a Boeing B777
- If you see: B737, 737 = It IS a Boeing B737
- If you see: A320, 320 = It IS an Airbus A320

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

=== IF A330 or A330-900 (Check for Business Class first!) ===

STEP 2A: DETERMINE VARIANT
- Scan the document top-to-bottom for Section starting at Row 6 or Row 21
- If you see BUSINESS CLASS section (rows starting with 6): Use A330-900a rules below
- If you see ONLY ECONOMY section (rows starting directly at 21, NO Business Class rows): Use A330-900b rules (see next section)

=== IF A330-900a (WITH Business Class) — PK-GHE, PK-GHF, PK-GHG ===
ATTENDANT TOP (4 seats): att/d11-LL1, att/d11-LL2, att/d11-LR (3 left), att/d21-R (1 right).

BUSINESS CLASS (Rows 6-11, 1-1-1-1 STAGGER layout — NOT 2-4-2!):
  Each row has ONLY 4 seats in columns: A, D, G, K.
  Read LEFT→RIGHT for each row: A first, then D, then G, then K.
  Total: 6 rows × 4 seats = 24 business class seats.
  Seat IDs: 6A, 6D, 6G, 6K, 7A, 7D, 7G, 7K, 8A, 8D, 8G, 8K, 9A, 9D, 9G, 9K, 10A, 10D, 10G, 10K, 11A, 11D, 11G, 11K.
  WARNING: Do NOT output columns C, E, F, H for business class — they DO NOT EXIST in this layout!

ATTENDANT MID (4 seats, between Business and Economy): att/d12-LL1, att/d12-LL2 (2 left), att/d22-RR1, att/d22-RR2 (2 right).
  WARNING: The IDs use double-L and double-R (LL1, LL2, RR1, RR2). Do NOT output att/d12-L1 or att/d22-R1 (single letter = WRONG).

After Row 11 is a LARGE GAP — DO NOT STOP. Economy section starts at Row 21.

ECONOMY SECTION 1 (Rows 21-40, skip row 24, 2-4-2 layout):
  Standard columns: A, C, D, E, F, G, H, K (8 seats per row).
  Row 40 EXCEPTION: ONLY columns A, C, H, K (no D, E, F, G).

ATTENDANT D13/D23 (2 seats, between Row 40 and Row 41): att/d13-L, att/d23-R.
  These are ALWAYS present — do NOT skip.

ECONOMY SECTION 2 (Rows 41-58, 2-4-2 layout):
  Standard columns: A, C, D, E, F, G, H, K.
  Row 41 EXCEPTION: ONLY columns D, E, F, G (no A, C, H, K).
  Rows 42-53: ALL columns A, C, D, E, F, G, H, K.
  Rows 54-56 EXCEPTION: ONLY columns A, C, D, F, G, H, K (no E).
  Row 57 EXCEPTION: ONLY columns A, C, D, F, G.
  Row 58 EXCEPTION: ONLY columns D, F, G.

ATTENDANT BOTTOM (2 seats ONLY): att/d14-L, att/d24-R.
  WARNING: There is NO aft-LC or aft-RC in this layout! Only 2 seats here.

SPARE: Read actual count from PDF. Use pax-1,...pax-N then inf-1,...inf-N.

CRITICAL CHECKPOINT A330-900a: You MUST output ALL of the following:
  - 4 top attendants (att/d11-LL1, att/d11-LL2, att/d11-LR, att/d21-R)
  - 24 business seats (rows 6-11, cols A/D/G/K)
  - 4 mid attendants (att/d12-LL1, att/d12-LL2, att/d22-RR1, att/d22-RR2)
  - Economy rows 21-58 (skip 24, with per-row column exceptions)
  - 2 bottom-mid attendants (att/d13-L, att/d23-R)
  - 2 bottom attendants (att/d14-L, att/d24-R)
  - ALL spares (pax + inf)
  If any of these is missing, you FAILED.

=== IF A330-900b ECONOMY-ONLY (Garuda PK-GHH, PK-GHI, NO Business Class) ===
IMPORTANT: This variant has ONLY Economy rows (21-58), NO Business Class.
Registration format: PK-GHH, PK-GHI (Garuda Indonesia).

ATTENDANT DOOR LOCATIONS (VERY CRITICAL - MANY DOORS, EASY TO MISS):
The document will have MULTIPLE attendant door sections. YOU MUST FIND AND RECORD ALL OF THEM:

[DOOR 1] Top front section (before Economy seating starts):
  - Left side has 3 seats:
    Label looks like 'Att / door-1L' or 'Att D1L' or similar → output: att/d11-LL1
    Label looks like 'Att / door-1LC' or 'Att D1LC'         → output: att/d11-LL2
    Label looks like 'Att / door-1R' or 'Att D1R'           → output: att/d11-LR
  - Right side has 1 seat:
    Label looks like 'Att / door-21R' or 'Att D21R' or 'Att / door-1R' on right → output: att/d21-R
  These FOUR items (att/d11-LL1, att/d11-LL2, att/d11-LR, att/d21-R) are mandatory at the TOP.

[DOOR 2] Left & Right side section after row 30 (around middle of document):
  - Left side has ONLY 1 seat:
    Label looks like 'Att / door-2L' or similar              → output: att/d12-L1
  - Right side has ONLY 1 seat:
    Label looks like 'Att / door-3' or 'Att / galley' area on RIGHT → output: att/d22-R1
  These TWO items (att/d12-L1, att/d22-R1) are mandatory in the middle. Do NOT output any duplicate L2/R2 seats here.

[DOOR 3] Bottom section (between row 51-52):
  Label looks like 'Att / door-4L' or bottom-left area    → output: att/d13-L
  Label looks like 'Att / door-4R' or bottom-right area   → output: att/d23-R
  These TWO items are mandatory after row 51.

[DOOR 4] VERY BOTTOM after row 69 / final check:
  Label 'Att / door-5L' or bottom-left rear area          → output: att/d14-L
  Label 'Att / door-5R' or bottom-right rear area         → output: att/d24-R
  These TWO items are mandatory at the very bottom.

SCAN STRATEGY (Critical!):
1. Read ENTIRE document from TOP to BOTTOM
2. MARK each door location as you find it (mentally note page/position)
3. CROSS-CHECK: Count total att/d items found. Expected: Exactly 10 items (att/d11-LL1, att/d11-LL2, att/d11-LR, att/d21-R, att/d12-L1, att/d22-R1, att/d13-L, att/d23-R, att/d14-L, att/d24-R)
4. If fewer than 10, GO BACK and scan again. Doors are often faint or use abbreviations.

SEATS: Economy ONLY, Rows 21-58 (skip galley row 24). Columns: A, C, D, E, F, G, H, K.
SPARE: Read actual count from PDF. Use pax-1,...pax-N then inf-1,...inf-N.

CRITICAL CHECKPOINT A330-900b: 
  - You MUST output att/d11-LL1, att/d11-LL2, att/d11-LR, att/d21-R at minimum (TOP doors)
  - You MUST output att/d12-L1, att/d22-R1 (MIDDLE doors)
  - You MUST output att/d13-L, att/d23-R, att/d14-L, att/d24-R (BOTTOM doors)
  - You MUST read all economy rows 21-58 (skip 24)
  - If any of these 10 door items is missing OR economy rows are incomplete, you FAILED.

=== IF B777 ===
HOW TO IDENTIFY B777: Look for 'B777', '777', or 'B777-300' in the document header.

SPLIT DOCUMENT SYSTEM FOR B777:
The B777 LOPA is usually split into TWO separate documents. Identify which document you are processing based on the rows and contents present:

1. DOCUMENT 1 (Cockpit, Business, and Economy Rows 21-49):
   - COCKPIT: pilot, copilot, observer1, observer2.
   - BUSINESS CLASS (Rows 6-12, STAGGERED - follow EXACTLY):
     === Boeing B777 BUSINESS CLASS STAGGERED MAPPING RULES (CRITICAL) ===
     Perhatikan dengan sangat teliti! Layout Business Class pada B777 bersifat selang-seling (Staggered). Kamu WAJIB memetakan koordinat visual kolom secara kaku. Jangan pernah menggeser nilai ke kolom tetangga!

     Aturan Baris Spesifik (Wajib Diikuti):
     * Row 6: Hanya memiliki kolom C, E, F, H (CEFH).
       - Nilai kolom C adalah tanggal pertama dari kiri.
       - Nilai kolom E adalah tanggal kedua dari kiri.
       - Nilai kolom F adalah tanggal ketiga dari kiri.
       - Nilai kolom H adalah tanggal keempat dari kiri (Paling kanan, TEPAT di sebelah kiri kolom nomor 'NO').
     * Row 7: Hanya memiliki kolom A, D, G, K (ADGK). Jangan masukkan data Row 7 ke kolom milik Row 6!
     * Row 8: Hanya memiliki kolom C, E, F, H (CEFH).
     * Row 9: Hanya memiliki kolom A, D, G, K (ADGK).
     * Row 10: Hanya memiliki kolom C, E, F, H (CEFH).
     * Row 11: Hanya memiliki kolom A, D, G, K (ADGK).
     * Row 12: Hanya memiliki kolom E, F.

     DETEKSI DAN ISOLASI KOLOM NOMOR BARIS 'NO' (ANTI-HALUSINASI):
     1. Kolom bertuliskan angka '6', '7', '8', '9', '10', '11', '12' di bagian tengah-kanan tabel adalah kolom 'NO' (Nomor Baris Dokumen).
     2. JANGAN PERNAH mengambil angka dari kolom 'NO' ini untuk digabungkan ke dalam string tanggal! (Contoh Kesalahan: Angka '6' pada kolom NO dibaca menjadi '6 JAN 2024' atau '6 SEP 2034' untuk kursi H. Ini SALAH BESAR!).
     3. Angka '6' pada kolom NO hanyalah penanda baris, abaikan angka tersebut saat mengekstrak tanggal kedaluwarsa di kursi H. Tanggal di kursi 6H berdiri sendiri secara terpisah.

     VERIFIKASI KEMBAR (DOUBLE-CHECK):
     Sebelum menuliskan hasil akhir untuk kursi 6F dan 6H, pastikan kamu melihat stempel aslinya. Jika stempel pada 6F dan 6H adalah stempel yang sama dan identik di dokumen, maka nilainya harus sama (yaitu '08 JAN 2035'). Jangan menciptakan bulan atau tahun baru (seperti SEP atau 2034) jika tidak ada tinta stempel yang menunjukkan kata tersebut secara jelas!
   - ECONOMY CLASS (Rows 21-49, skip row 24):
     * Rows 21-35: ALL columns A,B,C,D,F,G,H,J,K
     * Row 36: ONLY seats D, F, G (No A,B,C,H,J,K)
     * Rows 37-48: ALL columns A,B,C,D,F,G,H,J,K
     * Row 49: ONLY seats A, B, C, H, J, K (No D,F,G)
   - ATTENDANT DOORS (Expected in Document 1):
     * Door 1 (4 items): att/d1-L, att/d1-CL, att/d1-CR, att/d1-R
     * Door 2 (2 items): att/d2-L, att/d2-R
     * Door 3 (2 items): att/d3-L, att/d3-R (might be at the very bottom or not fully visible; extract if present)
   - CRITICAL CHECKPOINT B777 DOCUMENT 1: You MUST output all elements from Cockpit through Row 49.

2. DOCUMENT 2 (Economy Rows 50-63 only):
   - COCKPIT & BUSINESS: Do NOT extract (not present).
   - ECONOMY CLASS (Rows 50-63):
     * Rows 50-62: ALL columns A,B,C,D,F,G,H,J,K
     * Row 63: ONLY seats A, C, D, F, G, H, K (No B, no J)
   - ATTENDANT DOORS (Expected in Document 2):
     * Door 3 (2 items): att/d3-L, att/d3-R (usually near Row 50-51)
     * Posterior doors (Door 4, Door 5) if visible.
     * Do NOT extract Door 1 or Door 2 (not present).
   - CRITICAL CHECKPOINT B777 DOCUMENT 2: You MUST output all elements from Row 50 through Row 63.

SPARE TABLE (Expected at the end of Document 2 or Document 1):
The spare table has these columns:
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

CRITICAL CHECKPOINT B777: Extract either Document 1 or Document 2 structure completely depending on which one is shown. Do not invent missing sections that belong to the other document.

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

=== ATTENDANT DOOR VISUAL RECOGNITION GUIDE ===
Attendant doors often use abbreviations or labels that can be hard to spot. Use these visual cues:

LABEL PATTERNS TO LOOK FOR:
- Att/ prefix followed by door designation (most common)
- A/T D or A/T Door or Att D (abbreviated)
- aft or galley (alternate names for certain doors)
- Large rectangular boxes with dates inside (visual indicator of door section)
- Rows of 2-4 cells grouped together vertically with small headers above

VISUAL LAYOUT OF DOORS:
- Doors are usually grouped in BLOCKS (each block = one door section)
- Each block is separated from seat rows by white space or section dividers
- Left-side doors: Always on LEFT column area of the document
- Right-side doors: Always on RIGHT column area of the document
- Center doors: May appear in center for aircraft with center aisles (B777, some A330)

READING ORDER FOR DOORS:
1. Start at TOP of document (after cockpit if present)
2. Look LEFT then RIGHT in each horizontal section
3. Move DOWN through the document
4. DO NOT jump sections — read in strict vertical order
5. Mark mentally: Section 1, Section 2, etc.

If a label is unclear or you are unsure, DO NOT SKIP. Output the date with a ? suffix (e.g., 15 JAN 2025?) to flag uncertainty. DO NOT output an empty cell for a door — that is worse than a marked uncertain date.

=== STAMP & HANDWRITING DIGIT/MONTH AUTO-CORRECTION RULES ===
Lakukan koreksi mandiri yang ketat terhadap hasil pembacaan teks stempel/tulisan tangan yang sering rusak akibat bersinggungan dengan garis hitam kisi-kisi tabel:

KOREKSI TAHUN (Wajib Berupa 4 Digit Angka):
- Jika digit TERAKHIR dari tahun terdeteksi sebagai huruf 'O', 'o', atau 'Q', ubah paksa menjadi angka '0' (Contoh: '203O' -> '2030', '203o' -> '2030').
- Jika digit dari tahun terdeteksi berupa huruf 'l' (L kecil), 'i', 'I' (i besar), atau karakter vertikal '|', ubah paksa menjadi angka '1' (Contoh: '203l' -> '2031', '203I' -> '2031').
- Jika digit dari tahun terdeteksi berupa huruf 'S' atau 's', ubah paksa menjadi angka '5' (Contoh: '203S' -> '2035').
- Jika digit dari tahun terdeteksi berupa huruf 'b', ubah paksa menjadi angka '6' (Contoh: '203b' -> '2036').

KOREKSI NAMA BULAN (Wajib Berupa 3 Huruf Valid: JAN, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC):
- Jika terdeteksi angka '4' di dalam nama bulan, ubah menjadi huruf 'A' (Contoh: 'M4R' -> 'MAR', '4PR' -> 'APR').
- Jika terdeteksi angka '1', '7', atau huruf 'I'/'l' di tengah/akhir bulan, kembalikan ke karakter bulan alfabetis yang valid (Contoh: 'J1N' -> 'JUN' atau 'JAN', 'A1G' -> 'AUG').
- Jika terdeteksi angka '0' di awal nama bulan, ubah menjadi huruf 'O' (Contoh: '0CT' -> 'OCT').

JIKA SEL KOSONG ATAU UNREADABLE:
- Jika sel benar-benar kosong atau coretan tinta tidak membentuk pola tanggal, isi dengan string kosong \"\".

COLUMN ALIGNMENT (CRITICAL - THIS IS THE MOST COMMON ERROR!):
- The table has column headers at the TOP (e.g. A, B, C or A, C, D, F, G, H, J, K).
- For EACH row, read LEFT to RIGHT strictly. Match each cell to its column header by vertical alignment.
- Do NOT drift. If row 41A = OCT 2026, then 41B is the NEXT cell to the RIGHT — a completely different cell.
- Common mistake: reading 42A as the value from 42B or 42C because 42A handwriting is faint or unclear.
- If a cell looks empty or unclear, output '' — do NOT substitute with the neighboring cell's value.

1. READ EACH CELL TWICE before recording. First pass = initial read, second pass = verify.
2. CROSS-CHECK vertically: Compare each date with 2-3 seats ABOVE and BELOW in the SAME COLUMN.
   Same-column seats usually share similar expiry year ranges (e.g. 2025-2028).
   If your reading differs drastically from same-column neighbors → you likely read the WRONG column. Re-examine!
3. SANITY CHECK: Typical expiry years are 2024-2036. Re-examine if outside this range.
4. FOCUS ON INK, not grid lines. Handwriting may touch or cross grid lines — track the ink strokes only.
5. DO NOT GUESS. Cannot read a cell? → output empty string '' instead of a wrong date.
6. A blank/empty cell = no expiry date → output empty string ''.
7. UNCERTAINTY FLAG: If the handwriting is very faint, scribbled over, ambiguous, or you are NOT 100% sure about your reading, you MUST append a '?' to the end of the date (e.g. 'MAY 2028?').

DATA FORMAT (MINIFIED JSON):
{\"registration\":\"PK-GIA\",\"aircraft_type\":\"B777-300\",\"seats\":[[\"pilot\",\"31 MAY 2029\"],[\"copilot\",\"17 JAN 2035\"],[\"att/d1-L\",\"14 MAY 2029\"],[\"att/d1-CL\",\"17 MAY 2029\"],[\"6C\",\"12 MAR 2030\"],[\"pax-1\",\"SEP 28\"],[\"inf-1\",\"SEP 23\"]]}";

        // === STEP A: PYTHON OCR PREPROCESSING (opencv-python + pytesseract) ===
        // Enhance images and extract OCR text BEFORE sending to AI
        $ocrResult = null;
        $aiImagePaths = $imagePaths; // Default: use original images
        try {
            Log::info('[PDF Scanner] Running Python OCR preprocessing on ' . count($imagePaths) . ' image(s)');
            $ocrResult = $this->ocrPreprocess->preprocess($imagePaths);

            if ($ocrResult['success'] ?? false) {
                // Use AI-enhanced images (moderate enhancement, preserves color/context)
                $aiEnhanced = $ocrResult['ai_enhanced_images'] ?? [];
                if (!empty($aiEnhanced) && count($aiEnhanced) === count($imagePaths)) {
                    $aiImagePaths = $aiEnhanced;
                    Log::info('[PDF Scanner] Using AI-enhanced images from Python OpenCV', [
                        'count' => count($aiImagePaths),
                        'preprocessing' => $ocrResult['preprocessing_applied'] ?? [],
                    ]);
                }

                // Add corrected OCR text to prompt
                $ocrText = $ocrResult['corrected_ocr_text'] ?? ($ocrResult['ocr_text'] ?? '');
                if (!empty($ocrText)) {
                    $prompt .= "\n\n=== PYTESSERACT OCR TRANSCRIPT (OpenCV Enhanced) ===\n";
                    $prompt .= "Below is text extracted by Tesseract OCR from OpenCV-enhanced images. Use this as REFERENCE for reading handwriting (especially dates). Match these text strings to the table structure you see in the image:\n";
                    $prompt .= "```\n" . substr($ocrText, 0, 8000) . "\n```\n";
                }

                // Log orientation detection results
                $orientations = $ocrResult['orientations'] ?? [];
                foreach ($orientations as $idx => $orient) {
                    if ($orient['needs_rotation'] ?? false) {
                        Log::info("[PDF Scanner] Page " . ($idx + 1) . " was auto-rotated", [
                            'angle' => $orient['angle'],
                            'confidence' => $orient['confidence'],
                        ]);
                    }
                }
            } else {
                Log::warning('[PDF Scanner] Python OCR preprocessing failed, using original images', [
                    'errors' => $ocrResult['errors'] ?? [],
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('[PDF Scanner] Python OCR preprocessing exception, using original images', [
                'error' => $e->getMessage(),
            ]);
        }

        // === STEP B: GOOGLE CLOUD VISION API (Additional OCR Fallback) ===
        $visionText = '';
        if (!empty($geminiKey)) {
            $visionText = $this->getVisionOcrText($imagePaths, $geminiKey);
            if (!empty($visionText)) {
                $prompt .= "\n\n=== GOOGLE CLOUD VISION OCR TRANSCRIPT ===\n";
                $prompt .= "Below is the exact text extracted by a highly accurate OCR engine. Use this as your PRIMARY source of truth for reading handwriting (especially dates). Match these text strings to the table structure you see in the image:\n";
                $prompt .= "```\n" . $visionText . "\n```\n";
            }
        }

        $geminiParts = [['text' => $prompt]];
        $openAiContent = [['type' => 'text', 'text' => $prompt]];
        $openRouterContent = [['type' => 'text', 'text' => $prompt]];
        $anthropicImages = [];

        // Use AI-enhanced images if available, otherwise original
        foreach ($aiImagePaths as $imagePath) {
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
                $aircraftType = $parsedData['aircraft_type'] ?? 'Unknown';
                $seats = $parsedData['seats'] ?? [];

                // === STEP C: APPLY DATA DICTIONARY POST-CORRECTIONS ===
                // Correct registration using OCR corrections dictionary
                $registration = $this->ocrPreprocess->correctRegistration($registration);
                // Correct aircraft type
                $aircraftType = $this->ocrPreprocess->correctAircraftType($aircraftType);
                // Correct all seat IDs and expiry dates
                $seats = $this->ocrPreprocess->correctSeatsData($seats);

                Log::info("[PDF Scanner] Successfully parsed + corrected (attempt {$attempt})", [
                    'registration' => $registration,
                    'aircraft_type' => $aircraftType,
                    'seats_count' => count($seats),
                ]);

                return [
                    'registration' => $registration,
                    'aircraft_type' => $aircraftType,
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

    private function getVisionOcrText(array $imagePaths, string $apiKey): string
    {
        $visionRequests = [];
        foreach ($imagePaths as $imagePath) {
            $imageData = base64_encode(file_get_contents($imagePath));
            $visionRequests[] = [
                'image' => ['content' => $imageData],
                'features' => [['type' => 'DOCUMENT_TEXT_DETECTION']]
            ];
        }

        try {
            Log::info("[PDF Scanner] Calling Google Cloud Vision API for " . count($imagePaths) . " image(s)");
            $response = Http::timeout(60)->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
                'requests' => $visionRequests
            ]);

            if ($response->failed()) {
                Log::warning("[PDF Scanner] Vision API failed", ['error' => $response->body()]);
                return '';
            }

            $visionText = '';
            foreach ($response->json('responses', []) as $resp) {
                if (isset($resp['fullTextAnnotation']['text'])) {
                    $visionText .= $resp['fullTextAnnotation']['text'] . "\n\n";
                }
            }
            
            if (!empty($visionText)) {
                Log::info("[PDF Scanner] Vision API successfully extracted text", ['length' => strlen($visionText)]);
            }
            
            return trim($visionText);
        } catch (\Exception $e) {
            Log::warning("[PDF Scanner] Vision API exception", ['error' => $e->getMessage()]);
            return '';
        }
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

    /**
     * Verify extracted data and apply corrections
     * 
     * @param array $extractedData {registration, aircraft_type, seats: [[seat_id, expiry_date], ...]}
     * @param array|string $imagePaths Original images for AI validation
     * @return array Enhanced data with confidence scores
     */
    private function verifyExtractionResults(array $extractedData, array|string $imagePaths): array
    {
        try {
            Log::info('[PDF Scanner] Starting verification pass');
            
            $verificationService = new VerificationService();
            $verificationResult = $verificationService->verify($extractedData, $imagePaths);
            
            Log::info('[PDF Scanner] Verification complete', [
                'auto_accepted' => $verificationResult['summary']['auto_accepted'] ?? 0,
                'flagged' => $verificationResult['summary']['flagged'] ?? 0,
                'needs_review' => $verificationResult['summary']['needs_review'] ?? 0,
            ]);
            
            // Return in format compatible with existing pipeline
            return [
                'registration' => $verificationResult['registration'],
                'aircraft_type' => $verificationResult['aircraft_type'],
                'seats' => $verificationResult['seats'],
                'verification' => [
                    'enabled' => true,
                    'summary' => $verificationResult['summary'],
                    'confidence_threshold' => $verificationResult['confidence_threshold'],
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('[PDF Scanner] Verification failed, returning unverified data', [
                'error' => $e->getMessage()
            ]);
            
            // Graceful fallback: return original data without verification
            return array_merge($extractedData, [
                'verification' => [
                    'enabled' => false,
                    'error' => $e->getMessage(),
                ]
            ]);
        }
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
