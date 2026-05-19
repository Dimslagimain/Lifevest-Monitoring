<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PdfParserService;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PdfScanExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfScanController extends Controller
{
    protected PdfParserService $pdfParser;

    public function __construct(PdfParserService $pdfParser)
    {
        $this->pdfParser = $pdfParser;
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
        $path = $file->store('temp_scans');
        $fullPath = storage_path('app/private/' . $path);

        Log::info('[PDF Scan] Starting scan', ['file' => $file->getClientOriginalName(), 'size' => $file->getSize()]);

        try {
            // processFile now returns the parsed data array directly (from AI)
            $parsed = $this->pdfParser->processFile($fullPath);

            Storage::delete($path);

            // Ensure each seat item has a 'registration' key
            $registration = $parsed['registration'] ?? 'PENDING';
            $seats = array_map(function($seat) use ($registration) {
                $seat['registration'] = $seat['registration'] ?? $registration;
                return $seat;
            }, $parsed['seats'] ?? []);

            Log::info('[PDF Scan] Scan complete', [
                'registration' => $registration,
                'seats_count' => count($seats),
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
            $rawText .= "Aircraft Type: " . ($parsed['aircraft_type'] ?? 'Unknown') . "\n";
            $rawText .= "Total seats terdeteksi: " . count($seats) . "\n";

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
                'aircraftType' => $parsed['aircraft_type'] ?? 'Unknown',
                'extractedData' => $seats
            ];

            // Simpan ke session agar tidak hilang saat navigasi
            session(['pdf_scan_result' => $result]);

            return view('superadmin.pdf-scan-review', $result);
        } catch (\Exception $e) {
            Storage::delete($path);
            Log::error('[PDF Scan] Scan failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $data = $request->input('data', []);
        $exportData = [];
        foreach ($data as $item) {
            $exportData[] = [
                $item['registration'] ?? 'PENDING',
                $item['seat_id'] ?? 'UNKNOWN',
                $item['expiry_date'] ?? '-'
            ];
        }
        return Excel::download(new PdfScanExport($exportData), 'pdf_scan_result.xlsx');
    }
}
