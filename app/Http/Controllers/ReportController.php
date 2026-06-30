<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Seat;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Export Aircraft Seat Map Report to PDF
     */
    public function exportPdf($registration)
    {
        // 1. Ambil data pesawat & seats (mirip method show di AircraftController)
        $aircraft = Aircraft::where('registration', $registration)->firstOrFail();

        // Load seats relationship if not already loaded, or fetch manually if needed
        // Assuming we need existing logic to fetch seats.
        // Ideally we reuse the logic from AircraftController or a Service.
        // For now, I'll replicate the basic fetch or assume $aircraft->seats is enough if relation exists
        // but looking at AircraftController, it might be using a specific query.

        // Let's check AircraftController logic later. For now, assuming $aircraft->seats is accessible.
        // If the seat data is complex (parsed from JSON columns etc), we need that logic.

        // Let's fetch the seats as an array keyed by logic ID if necessary.
        // Based on previous views: $seats[$seatId]

        $seats = Seat::where('registration', $registration)
            ->get()
            ->keyBy('seat_id');

        // 2. Render PDF
        $pdf = Pdf::loadView('reports.seat-map', [
            'aircraft' => $aircraft,
            'registration' => $registration,
            'seats' => $seats,
            'isPdfExport' => true,
        ]);

        // 3. Setup paper
        $pdf->setPaper('a4', 'portrait');

        // 4. Return stream (preview di browser)
        return $pdf->stream('Report_'.$registration.'_'.date('Y-m-d').'.pdf');
    }

    /**
     * Export Blank Form for Technicians (larger boxes, no dates)
     */
    public function exportBlankForm($registration)
    {
        $aircraft = Aircraft::where('registration', $registration)->firstOrFail();

        // Pass empty seats collection - the view will show blank boxes
        $seats = collect();

        // Determine buffer counts based on aircraft type
        $type = strtoupper($aircraft->type);
        $maxPax = 12;
        $maxInf = 20;

        if (str_contains($type, 'A320')) {
            $maxPax = 15;
            $maxInf = 20;
        } elseif (str_contains($type, 'A330')) {
            $maxPax = 15;
            $maxInf = 40;
        } elseif (str_contains($type, 'ATR')) {
            $maxPax = 10;
            $maxInf = 10;
        } elseif (str_contains($type, '737')) {
            $maxPax = 10;
            $maxInf = 25;
        } elseif (str_contains($type, '777')) {
            $maxPax = 35;
            $maxInf = 40;
        }

        // Render PDF with blank form template
        $pdf = Pdf::loadView('reports.blank-form', [
            'aircraft' => $aircraft,
            'registration' => $registration,
            'seats' => $seats,
            'isBlankForm' => true,
            'maxPax' => $maxPax,
            'maxInf' => $maxInf,
        ]);

        // Setup paper
        $pdf->setPaper('a4', 'portrait');

        // Return stream
        return $pdf->stream('BlankForm_'.$registration.'_'.date('Y-m-d').'.pdf');
    }
}
