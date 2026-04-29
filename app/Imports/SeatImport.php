<?php

namespace App\Imports;

use App\Models\Seat;
use App\Models\Aircraft;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class SeatImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip empty rows
        if (!isset($row['registration']) || !isset($row['seat_id']) || !isset($row['expiry_date_yyyy_mm_dd'])) {
            return null;
        }

        $registration = strtoupper($row['registration']);
        $seatId = $row['seat_id'];
        
        // Parse expiry date
        try {
            // Excel dates might come as strings or numbers, we assume YYYY-MM-DD for simplicity
            $expiryDate = Carbon::parse($row['expiry_date_yyyy_mm_dd']);
        } catch (\Exception $e) {
            $expiryDate = null;
        }

        if (!$expiryDate) {
            return null; // Don't process invalid dates
        }

        // Determine class type based on SeatController logic
        $classType = 'economy'; // default
        $rowNum = null;
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
            $rowNum = $matches[1] ?: null;
            $col = $matches[2] ?: $seatId;

            // We could try to detect class_type properly using layout config,
            // but for bulk import 'economy' is a safe fallback or we leave it.
        }

        return Seat::updateOrCreate(
            [
                'registration' => $registration,
                'seat_id'      => $seatId,
            ],
            [
                'row'          => $rowNum,
                'col'          => $col,
                'class_type'   => $classType,
                'expiry_date'  => $expiryDate,
            ]
        );
    }
}
