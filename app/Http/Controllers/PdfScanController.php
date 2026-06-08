<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PdfParserService;
use App\Services\VerificationService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PdfScanExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfScanController extends Controller
{
    protected PdfParserService $pdfParser;
    protected VerificationService $verificationService;

    public function __construct(PdfParserService $pdfParser, VerificationService $verificationService)
    {
        $this->pdfParser = $pdfParser;
        $this->verificationService = $verificationService;
    }

    public function index()
    {
        if (session()->has('pdf_scan_result')) {
            $data = session('pdf_scan_result');
            return view('superadmin.pdf-scan-review', $data);
        }
        return view('superadmin.pdf-scan');
    }

    public function clearScan()
    {
        session()->forget('pdf_scan_result');
        return redirect()->route('superadmin.pdf-scan');
    }

    public function scan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpeg,png,jpg|max:20480',
        ]);

        $file = $request->file('file');
        
        // Ensure file exists and is valid
        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'File yang diupload rusak atau gagal diterima oleh server.');
        }

        $path = $file->store('temp_scans');
        $fullPath = storage_path('app/private/' . $path);

        Log::info('[PDF Scan] Starting scan', ['file' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

        try {
            // Verify if the PDF is not corrupt before sending to process
            $extension = strtolower($file->getClientOriginalExtension());
            if ($extension === 'pdf') {
                $fileContent = file_get_contents($fullPath);
                if ($fileContent === false || empty($fileContent)) {
                    throw new \Exception('File PDF kosong atau tidak dapat dibaca.');
                }
                
                // A quick check for PDF signature %PDF-
                if (strncmp($fileContent, '%PDF-', 5) !== 0) {
                    throw new \Exception('Struktur file PDF corrupt atau bukan file PDF valid.');
                }
            }

            // processFile now returns the parsed data array directly (from AI)
            $parsed = $this->pdfParser->processFile($fullPath);

            Storage::delete($path);

            // Ensure each seat item has a 'registration' key
            $registration = $parsed['registration'] ?? 'PENDING';
            $seats = array_map(function($seat) use ($registration) {
                $seat['registration'] = $seat['registration'] ?? $registration;
                return $seat;
            }, $parsed['seats'] ?? []);

            // ===== SMART VERIFICATION: Per-Aircraft Layout Validation =====
            // Pass through VerificationService for row validation based on aircraft layout
            $verificationResult = $this->verificationService->verify([
                'registration' => $registration,
                'aircraft_type' => $parsed['aircraft_type'] ?? 'Unknown',
                'seats' => $seats,
            ]);

            $registration = $verificationResult['registration'] ?? 'PENDING';
            $seats = $verificationResult['seats'] ?? [];

            // Ensure each seat has the registration key (required by view)
            $seats = array_map(function($seat) use ($registration) {
                $seat['registration'] = $seat['registration'] ?? $registration;
                return $seat;
            }, $seats);

            Log::info('[PDF Scan] Scan complete with per-aircraft validation', [
                'registration' => $registration,
                'aircraft_type' => $verificationResult['aircraft_type'],
                'seats_count' => count($seats),
                'flagged_count' => $verificationResult['summary']['flagged'] ?? 0,
            ]);

            // Detect active provider for display
            $activeProvider = 'Unknown AI';
            if (!empty(env('SNIFOX_API_KEY'))) {
                $activeProvider = 'Snifox (' . env('SNIFOX_MODEL', 'google/gemini-3-flash-preview') . ')';
            } elseif (!empty(env('GEMINI_API_KEY'))) {
                $activeProvider = 'Google Gemini';
            } elseif (!empty(env('ANTHROPIC_API_KEY'))) {
                $activeProvider = 'Anthropic Claude';
            } elseif (!empty(env('OPENAI_API_KEY'))) {
                $activeProvider = 'OpenAI GPT-4o';
            } elseif (!empty(env('OPENROUTER_API_KEY'))) {
                $activeProvider = 'OpenRouter';
            }

            $rawText = "Data diekstrak menggunakan AI ({$activeProvider})\n";
            $rawText .= "Registration: {$registration}\n";
            $rawText .= "Aircraft Type: " . ($verificationResult['aircraft_type'] ?? 'Unknown') . "\n";
            $rawText .= "Total seats terdeteksi: " . count($seats) . "\n";

            // Get aircraft layout info
            $aircraft = \App\Models\Aircraft::where('registration', $registration)->first();
            if ($aircraft) {
                $rawText .= "Layout: " . $aircraft->layout . "\n";
                $classRowsConfig = config('aircraft_class_rows');
                if (isset($classRowsConfig[$aircraft->layout])) {
                    $layoutInfo = $classRowsConfig[$aircraft->layout];
                    $rawText .= "\n📋 Expected Layout Structure:\n";
                    foreach ($layoutInfo as $class => $rows) {
                        if (is_array($rows) && !empty($rows)) {
                            $rowStr = is_array($rows) && count($rows) > 1 
                                ? "rows " . min($rows) . "-" . max($rows)
                                : "row " . current($rows);
                            $rowCount = count($rows);
                            $rawText .= "  • {$class}: {$rowStr} ({$rowCount} rows)\n";
                        }
                    }
                }
            }

            if (!empty($verificationResult['summary']['flagged'])) {
                $rawText .= "\n⚠️  " . $verificationResult['summary']['flagged'] . " seats flagged for review (possible row/layout mismatches).\n";
            }

            if (empty($seats)) {
                $rawText .= "\n⚠ AI tidak mendeteksi data seats.\n";
                $rawText .= "Kemungkinan penyebab:\n";
                $rawText .= "- Kualitas gambar/scan terlalu rendah\n";
                $rawText .= "- Format dokumen tidak dikenali\n";
                $rawText .= "- API sedang bermasalah\n";
                $rawText .= "\nSilakan cek storage/logs/laravel.log untuk detail.";
            }

            $result = [
                'rawText' => $rawText,
                'registration' => $registration,
                'aircraftType' => $verificationResult['aircraft_type'] ?? 'Unknown',
                'extractedData' => $seats,
                'scanImages' => $parsed['scan_images'] ?? [],
                'verificationSummary' => $verificationResult['summary'] ?? [],
            ];

            // Simpan ke session agar tidak hilang saat navigasi
            session(['pdf_scan_result' => $result]);

            return view('superadmin.pdf-scan-review', $result);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Storage::delete($path);
            Log::error('[PDF Scan] Connection timeout', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', '⏱ Koneksi timeout — server AI tidak merespon dalam waktu yang ditentukan. Coba lagi dalam beberapa menit atau gunakan file dengan ukuran lebih kecil.');
        } catch (\Exception $e) {
            Storage::delete($path);
            Log::error('[PDF Scan] Scan failed', ['error' => $e->getMessage()]);

            $msg = $e->getMessage();
            // Detect specific error types for better user messaging
            if (str_contains($msg, 'API Error (HTTP 401)') || str_contains($msg, 'API Error (HTTP 403)')) {
                $userMsg = '🔑 API Key tidak valid atau tidak memiliki akses. Periksa konfigurasi API key di file .env.';
            } elseif (str_contains($msg, 'API Error (HTTP 429)')) {
                $userMsg = '⚡ Rate limit tercapai — terlalu banyak request ke AI. Tunggu 1-2 menit lalu coba lagi.';
            } elseif (str_contains($msg, 'API Error (HTTP 5')) {
                $userMsg = '🔧 Server AI sedang bermasalah (error 5xx). Coba lagi dalam beberapa menit.';
            } elseif (str_contains($msg, 'Ghostscript') || str_contains($msg, 'Gagal memproses PDF')) {
                $userMsg = '📄 Gagal mengkonversi PDF ke gambar. Pastikan Ghostscript terinstall dan path sudah benar di .env (GHOSTSCRIPT_PATH).';
            } elseif (str_contains($msg, 'empty content') || str_contains($msg, 'JSON')) {
                $userMsg = '🤖 AI mengembalikan response tidak valid. Coba ulangi scan — kualitas gambar atau format dokumen mungkin perlu diperbaiki.';
            } elseif (str_contains($msg, 'API Key') || str_contains($msg, 'Belum ada')) {
                $userMsg = $msg; // Already user-friendly from PdfParserService
            } else {
                $userMsg = 'Gagal memproses file: ' . $msg;
            }

            return redirect()->back()->with('error', $userMsg);
        }
    }

    public function exportExcel(Request $request)
    {
        $data = $request->input('data', []);
        $exportData = [];
        
        // Check if we should include verification columns
        $includeVerification = $request->input('include_verification', false);
        
        foreach ($data as $item) {
            if ($includeVerification) {
                // Include confidence and notes if verification data is available
                $confidence = isset($item['confidence']) ? round($item['confidence'] * 100) : 'N/A';
                $notes = '';
                
                if (!empty($item['was_corrected'])) {
                    $notes = ($item['correction_type'] ?? 'corrected');
                    if (!empty($item['suggestion'])) {
                        $notes .= " - " . $item['suggestion'];
                    }
                }
                if (!empty($item['issue_detected'])) {
                    $notes .= " | Issue: " . $item['issue_detected'];
                }
                
                $exportData[] = [
                    $item['registration'] ?? 'PENDING',
                    $item['seat_id'] ?? 'UNKNOWN',
                    $item['expiry_date'] ?? '-',
                    $confidence . '%',
                    $notes ?: 'OK',
                ];
            } else {
                // Standard export (without verification)
                $exportData[] = [
                    $item['registration'] ?? 'PENDING',
                    $item['seat_id'] ?? 'UNKNOWN',
                    $item['expiry_date'] ?? '-'
                ];
            }
        }
        
        // Build dynamic filename from registration + aircraft type
        $registration = $request->input('master_registration', 'scan');
        $aircraftType = $request->input('aircraft_type', '');
        
        // Sanitize for filename: PK-GIA_B777_scan.xlsx
        $regPart = preg_replace('/[^A-Za-z0-9\-]/', '', $registration) ?: 'scan';
        $typePart = preg_replace('/[^A-Za-z0-9\-]/', '', $aircraftType);
        $filenameParts = [$regPart];
        if (!empty($typePart)) {
            $filenameParts[] = $typePart;
        }
        $filenameParts[] = 'scan';
        $filename = implode('_', $filenameParts) . '.xlsx';

        return Excel::download(
            new PdfScanExport($exportData, $includeVerification), 
            $filename
        );
    }
}
