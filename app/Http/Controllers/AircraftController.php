<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\ActivityLog;
use App\Models\Aircraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AircraftController extends Controller
{
    /**
     * Show aircraft seat map
     */
    public function show(string $registration)
    {
        $aircraft = Aircraft::where('registration', $registration)->first();

        if (!$aircraft) {
            abort(404, 'Aircraft not found');
        }

        // Convert object to array for compatibility if needed, but object is better
        // For now, let's keep array structure or use object direct
        $layout = [
            'type' => $aircraft->type,
            'icon' => $aircraft->icon,
            'layout' => $aircraft->layout,
        ];

        // Get all seats for this registration
        $seats = Seat::where('registration', $registration)
            ->get()
            ->keyBy('seat_id');

        // Determine template from DB layout
        $template = 'aircraft.' . ($aircraft->layout ?? 'show');

        // Reuse the collection for stats to avoid redundant DB queries
        $today = now()->startOfDay();

        $adultSeats = $seats->filter(fn($s) => in_array($s->class_type, ['business', 'economy', 'spare-pax']));
        $crewSeats = $seats->filter(fn($s) => in_array($s->class_type, ['cockpit', 'attendant']));
        $infantSeats = $seats->filter(fn($s) => $s->class_type === 'spare-inf');

        $qtyAdult = $adultSeats->count();
        $qtyCrew = $crewSeats->count();
        $qtyInfant = $infantSeats->count();

        $expAdult = $adultSeats->filter(fn($s) => $s->expiry_date && $s->expiry_date < $today)->count();
        $expCrew = $crewSeats->filter(fn($s) => $s->expiry_date && $s->expiry_date < $today)->count();
        $expInfant = $infantSeats->filter(fn($s) => $s->expiry_date && $s->expiry_date < $today)->count();

        // Get last update time from the collection
        $lastUpdate = $seats->max('updated_at');

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return view($template, [
            'registration' => $registration,
            'layout' => $layout,
            'seats' => $seats,
            'lastUpdate' => $lastUpdate ? \Carbon\Carbon::parse($lastUpdate) : null,
            'aircraft' => $aircraft,
            'qtyAdult' => $qtyAdult,
            'qtyCrew' => $qtyCrew,
            'qtyInfant' => $qtyInfant,
            'expAdult' => $expAdult,
            'expCrew' => $expCrew,
            'expInfant' => $expInfant,
            'logs' => ($user && $user->isAdmin()) 
                ? ActivityLog::with(['user', 'aircraft'])->where('registration', $registration)->latest()->take(10)->get() 
                : collect(),
        ]);
    }

    /**
     * Update seat expiry dates
     */
    public function updateSeats(Request $request, string $registration)
    {
        try {
            $request->validate([
                'seat_ids' => 'required|array',
                'seat_ids.*' => 'required|string',
                'expiry_date' => 'required|date',
            ]);

            $seatIds = $request->input('seat_ids');
            $expiryDate = $request->input('expiry_date');

            // Get layout for this aircraft once (fix N+1 query issue)
            $aircraft = Aircraft::where('registration', $registration)->first();
            $layout = $aircraft->layout ?? null;
            $classRows = $layout ? config("aircraft_class_rows.{$layout}", []) : [];

            foreach ($seatIds as $seatId) {
                // Determine class type based on seat ID format
                $classType = 'economy'; // default
                $row = null;
                $col = null;

                // Cockpit seats
                if (in_array($seatId, ['captain', 'copilot', 'observer1', 'observer2'])) {
                    $classType = 'cockpit';
                    $col = $seatId;
                }
                // PAX spare seats (pax-1, pax-2, etc.)
                elseif (str_starts_with($seatId, 'pax-')) {
                    $classType = 'spare-pax';
                    $col = $seatId;
                }
                // INF spare seats (inf-1, inf-2, etc.)
                elseif (str_starts_with($seatId, 'inf-')) {
                    $classType = 'spare-inf';
                    $col = $seatId;
                }
                // Attendant seats (att/d11-A, att/d12-C, att/d22-H, etc.)
                elseif (str_starts_with($seatId, 'att/')) {
                    $classType = 'attendant';
                    $col = $seatId;
                }
                // Regular seats (6A, 21B, etc.)
                else {
                    preg_match('/^(\d+)?(.+)$/', $seatId, $matches);
                    $row = $matches[1] ?: null;
                    $col = $matches[2] ?: $seatId;

                    if ($row && !empty($classRows)) {
                        $rowNum = (int) $row;
                        foreach ($classRows as $class => $rows) {
                            if (in_array($rowNum, $rows)) {
                                $classType = $class;
                                break;
                            }
                        }
                    }
                }

                Seat::updateOrCreate(
                    [
                        'registration' => $registration,
                        'seat_id' => $seatId,
                    ],
                    [
                        'row' => $row,
                        'col' => $col,
                        'class_type' => $classType,
                        'expiry_date' => $expiryDate,
                    ]
                );
            }

            // Determine all unique P/Ns affected by this update
            $affectedPns = [];
            $aircraft = Aircraft::where('registration', $registration)->first();
            
            foreach ($seatIds as $seatId) {
                if (str_starts_with($seatId, 'inf-')) {
                    $affectedPns[] = $aircraft->pn_infant;
                } elseif (in_array($seatId, ['captain', 'fo', 'obs-1', 'obs-2']) || str_starts_with($seatId, 'att/')) {
                    $affectedPns[] = $aircraft->pn_crew;
                } else {
                    $affectedPns[] = $aircraft->pn_adult;
                }
            }
            $uniquePns = array_values(array_unique($affectedPns));

            // Log the activity
            ActivityLog::create([
                'user_id' => Auth::id(),
                'registration' => $registration,
                'action' => 'update',
                'details' => [
                    'seat_count' => count($seatIds),
                    'expiry_date' => $expiryDate,
                    'pns' => $uniquePns, // Changed from 'pn' to 'pns' (array)
                    'seats' => array_slice($seatIds, 0, 50)
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => count($seatIds) . ' seat(s) updated',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a spare seat (PAX/INF)
     */
    public function deleteSeat(Request $request, string $registration)
    {
        $request->validate([
            'seat_id' => 'required|string',
        ]);

        $seatId = $request->input('seat_id');

        // Only allow deletion of spare seats (pax-*, inf-*)
        if (!str_starts_with($seatId, 'pax-') && !str_starts_with($seatId, 'inf-')) {
            return response()->json([
                'success' => false,
                'message' => 'Only spare seats (PAX/INF) can be deleted',
            ], 403);
        }

        $deleted = Seat::where('registration', $registration)
            ->where('seat_id', $seatId)
            ->delete();

        if ($deleted) {
            // Log the deletion
            ActivityLog::create([
                'user_id' => Auth::id(),
                'registration' => $registration,
                'action' => 'delete',
                'details' => [
                    'seat_id' => $seatId,
                    'type' => str_starts_with($seatId, 'pax-') ? 'spare-pax' : 'spare-inf'
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Seat deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Seat not found',
        ], 404);
    }

    /**
     * Show Batch Input Form (Economy Only) - Sectioned
     */
    public function batchInput(string $registration)
    {
        $aircraft = Aircraft::where('registration', $registration)->firstOrFail();
        $layout = $aircraft->layout ?? 'b737-e46'; // fallback

        // Get Economy Sections from config
        $sections = config("aircraft_economy_sections.{$layout}", []);

        // Fallback if config missing
        if (empty($sections)) {
            $sections = [
                ['name' => 'Economy Class', 'rows' => range(21, 46), 'columns' => ['A', 'B', 'C', 'H', 'J', 'K']]
            ];
        }

        return view('aircraft.batch-input', [
            'registration' => $registration,
            'aircraft' => $aircraft,
            'sections' => $sections,
        ]);
    }

    /**
     * Store Batch Input Data - Sectioned
     */
    public function storeBatchInput(Request $request, string $registration)
    {
        $aircraft = Aircraft::where('registration', $registration)->firstOrFail();
        $layout = $aircraft->layout ?? 'b737-e46';

        // Get sections config
        $sections = config("aircraft_economy_sections.{$layout}", []);
        if (empty($sections)) {
            $sections = [
                ['name' => 'Economy Class', 'rows' => range(21, 46), 'columns' => ['A', 'B', 'C', 'H', 'J', 'K']]
            ];
        }

        $savedCount = 0;

        // Process each section
        foreach ($sections as $sectionIndex => $section) {
            $rows = $section['rows'];
            $columns = $section['columns'];

            foreach ($columns as $col) {
                $input = $request->input("section_{$sectionIndex}_col_{$col}", '');
                if (empty(trim($input)))
                    continue;

                $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $input)));
                $rowIndex = 0;
                $lineIndex = 0;

                // Get exceptions for this section
                $paramExceptions = $section['exceptions'] ?? [];

                // Process each input line
                while ($lineIndex < count($lines)) {
                    // Stop if we ran out of rows
                    if ($rowIndex >= count($rows))
                        break;

                    $row = $rows[$rowIndex];
                    $seatId = $row . $col;

                    // Check if this specific seat is an exception (doesn't exist)
                    if (in_array($seatId, $paramExceptions)) {
                        // Skip this seat (it doesn't exist), try next row
                        $rowIndex++;
                        continue;
                    }

                    // This is a valid seat, use the current input line
                    $dateStr = $lines[$lineIndex];
                    $parsedDate = $this->parseFlexibleDate($dateStr);

                    if ($parsedDate) {
                        Seat::updateOrCreate(
                            ['registration' => $registration, 'seat_id' => $seatId],
                            [
                                'row' => $row,
                                'col' => $col,
                                'class_type' => 'economy',
                                'expiry_date' => $parsedDate,
                            ]
                        );
                        $savedCount++;
                    }

                    // Move to next input and next row
                    $lineIndex++;
                    $rowIndex++;
                }
            }
        }

        // Process PAX Spare (auto-count from lines)
        $paxDates = $request->input('pax_dates', '');
        if (!empty(trim($paxDates))) {
            $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $paxDates)));
            for ($i = 0; $i < count($lines); $i++) {
                $parsedDate = $this->parseFlexibleDate($lines[$i]);
                if ($parsedDate) {
                    $seatId = 'pax-' . ($i + 1);
                    Seat::updateOrCreate(
                        ['registration' => $registration, 'seat_id' => $seatId],
                        [
                            'row' => null,
                            'col' => $seatId,
                            'class_type' => 'spare-pax',
                            'expiry_date' => $parsedDate,
                        ]
                    );
                    $savedCount++;
                }
            }
        }

        // Process INF Spare (auto-count from lines)
        $infDates = $request->input('inf_dates', '');
        if (!empty(trim($infDates))) {
            $lines = array_filter(array_map('trim', preg_split('/[\r\n]+/', $infDates)));
            for ($i = 0; $i < count($lines); $i++) {
                $parsedDate = $this->parseFlexibleDate($lines[$i]);
                if ($parsedDate) {
                    $seatId = 'inf-' . ($i + 1);
                    Seat::updateOrCreate(
                        ['registration' => $registration, 'seat_id' => $seatId],
                        [
                            'row' => null,
                            'col' => $seatId,
                            'class_type' => 'spare-inf',
                            'expiry_date' => $parsedDate,
                        ]
                    );
                    $savedCount++;
                }
            }
        }

        // Log the activity
        if ($savedCount > 0) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'registration' => $registration,
                'action' => 'batch',
                'details' => [
                    'seat_count' => $savedCount,
                    'message' => 'Performed batch update for ' . $savedCount . ' seats'
                ]
            ]);
        }

        return redirect()->route('aircraft.show', $registration)
            ->with('success', "Batch input saved: {$savedCount} seats updated.");
    }

    /**
     * Parse flexible date formats: dd/mm/yyyy, dd-mmm-yy, mmm-yy
     */
    private function parseFlexibleDate(string $dateStr): ?\Carbon\Carbon
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr))
            return null;

        $formats = [
            'Y-m-d',     // 2030-03-01
            'Y/m/d',     // 2030/03/01
            'd/m/Y',     // 01/03/2030
            'd-m-Y',     // 01-03-2030
            'd/m/y',     // 01/03/30
            'd-m-y',     // 01-03-30
            'd-M-y',     // 24-Jan-25
            'd-M-Y',     // 24-Jan-2025
            'M-y',       // Oct-25
            'M-Y',       // Oct-2025
            'M/y',       // Oct/25
            'M/Y',       // Oct/2025
        ];

        foreach ($formats as $format) {
            try {
                $date = \Carbon\Carbon::createFromFormat($format, $dateStr);
                if ($date) {
                    // For month-year only formats, set to first day
                    if (in_array($format, ['M-y', 'M-Y', 'M/y', 'M/Y'])) {
                        $date->startOfMonth();
                    }
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback to generic PHP strtotime parsing if all specific formats fail
        try {
            $parsed = strtotime($dateStr);
            if ($parsed !== false) {
                return \Carbon\Carbon::createFromTimestamp($parsed);
            }
        } catch (\Exception $e) {
            // Do nothing, let it return null below
        }

        return null;
    }
}

