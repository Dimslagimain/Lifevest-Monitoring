<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AircraftImport;
use App\Imports\SeatImport;
use App\Imports\UserImport;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;


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
                $import = new AircraftImport;
                Excel::import($import, $file);
                
                $uniqueRegs = array_unique($import->registrations);
                foreach ($uniqueRegs as $reg) {
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'registration' => $reg,
                        'action' => 'import',
                        'details' => [
                            'type' => 'aircraft',
                            'message' => 'Bulk imported/updated aircraft data'
                        ]
                    ]);
                }
            } elseif ($type === 'seat') {
                $import = new SeatImport;
                Excel::import($import, $file);
                
                foreach ($import->affectedData as $reg => $classTypes) {
                    $uniqueTypes = array_unique($classTypes);
                    $count = count($classTypes);
                    
                    // Fetch P/Ns for this aircraft based on affected types
                    $aircraft = \App\Models\Aircraft::where('registration', $reg)->first();
                    $pns = [];
                    if ($aircraft) {
                        foreach ($uniqueTypes as $classType) {
                            if (in_array($classType, ['economy', 'business', 'spare-pax']) && $aircraft->pn_adult) {
                                $pns[] = $aircraft->pn_adult;
                            } elseif (in_array($classType, ['cockpit', 'attendant']) && $aircraft->pn_crew) {
                                $pns[] = $aircraft->pn_crew;
                            } elseif ($classType === 'spare-inf' && $aircraft->pn_infant) {
                                $pns[] = $aircraft->pn_infant;
                            }
                        }
                    }

                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'registration' => $reg,
                        'action' => 'import',
                        'details' => [
                            'type' => 'seat',
                            'seat_count' => $count,
                            'pns' => array_values(array_unique($pns)),
                            'message' => "Bulk imported/updated $count seats"
                        ]
                    ]);
                }
            } elseif ($type === 'user') {
                Excel::import(new UserImport, $file);
                
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'import',
                    'details' => [
                        'type' => 'user',
                        'message' => 'Bulk imported user accounts'
                    ]
                ]);
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
