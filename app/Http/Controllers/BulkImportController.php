<?php

namespace App\Http\Controllers;

use App\Exports\BulkImportTemplateExport;
use App\Imports\AircraftImport;
use App\Imports\SeatImport;
use App\Imports\UserImport;
use App\Models\ActivityLog;
use App\Models\Aircraft;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
                            'message' => 'Bulk imported/updated aircraft data',
                        ],
                    ]);
                }
            } elseif ($type === 'seat') {
                $import = new SeatImport;
                Excel::import($import, $file);

                foreach ($import->affectedData as $reg => $items) {
                    $seatIds = array_column($items, 'seat_id');
                    $classTypes = array_column($items, 'class_type');
                    $dates = array_filter(array_unique(array_column($items, 'expiry_date')));
                    $count = count($items);

                    // Fetch P/Ns for this aircraft based on affected types
                    $aircraft = Aircraft::where('registration', $reg)->first();
                    $pns = [];
                    $uniqueTypes = array_unique($classTypes);
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

                    $expiry_display = null;
                    if (count($dates) === 1) {
                        $expiry_display = Carbon::parse(reset($dates))->format('d-m-Y');
                    } elseif (count($dates) > 1) {
                        $minDate = Carbon::parse(min($dates))->format('d-m-Y');
                        $maxDate = Carbon::parse(max($dates))->format('d-m-Y');
                        $expiry_display = $minDate."\nto\n".$maxDate;
                    }

                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'registration' => $reg,
                        'action' => 'import',
                        'details' => [
                            'type' => 'seat',
                            'seat_count' => $count,
                            'pns' => array_values(array_unique($pns)),
                            'seats' => array_slice($seatIds, 0, 1000),
                            'expiry_date' => $expiry_display,
                            'message' => "Bulk imported/updated $count seats",
                        ],
                    ]);
                }
            } elseif ($type === 'user') {
                Excel::import(new UserImport, $file);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'import',
                    'details' => [
                        'type' => 'user',
                        'message' => 'Bulk imported user accounts',
                    ],
                ]);
            }

            return redirect()->back()->with('success', 'Data '.ucfirst($type).' berhasil di-import secara massal!');
        } catch (\Exception $e) {
            Log::error('Bulk Import Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Terjadi kesalahan saat meng-import: '.$e->getMessage());
        }
    }

    /**
     * Download template Excel beserta data contoh
     */
    public function downloadTemplate(string $type)
    {
        if (! in_array($type, ['aircraft', 'seat', 'user'])) {
            abort(404);
        }

        return Excel::download(new BulkImportTemplateExport($type), "template_{$type}_import.xlsx");
    }
}
