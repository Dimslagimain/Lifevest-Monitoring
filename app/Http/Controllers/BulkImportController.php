<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AircraftImport;
use App\Imports\SeatImport;
use App\Imports\UserImport;
use Illuminate\Support\Facades\Log;

class BulkImportController extends Controller
{
    /**
     * Tampilkan halaman Bulk Import
     */
    public function index()
    {
        return view('superadmin.bulk-import');
    }

    /**
     * Proses unggah & import file Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_type' => 'required|in:aircraft,seat,user',
            'file' => 'required|mimes:xlsx,csv,xls|max:10240', // max 10MB
        ]);

        try {
            $type = $request->import_type;
            $file = $request->file('file');

            if ($type === 'aircraft') {
                Excel::import(new AircraftImport, $file);
            } elseif ($type === 'seat') {
                Excel::import(new SeatImport, $file);
            } elseif ($type === 'user') {
                Excel::import(new UserImport, $file);
            }

            return redirect()->back()->with('success', 'Data ' . ucfirst($type) . ' berhasil di-import secara massal!');
        } catch (\Exception $e) {
            Log::error('Bulk Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat meng-import: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel beserta data contoh
     */
    public function downloadTemplate($type)
    {
        if (!in_array($type, ['aircraft', 'seat', 'user'])) {
            abort(404);
        }

        return Excel::download(new \App\Exports\BulkImportTemplateExport($type), "template_{$type}_import.xlsx");
    }
}
