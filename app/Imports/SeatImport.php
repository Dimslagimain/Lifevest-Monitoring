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
        // Validasi format file
        if (!array_key_exists('seat_id', $row) || !array_key_exists('expiry_date_yyyy_mm_dd', $row)) {
            throw new \Exception("Format file salah! Pastikan Anda mengunggah template SEAT / LIFE VEST (kolom 'Seat_ID' atau 'Expiry_Date' tidak ditemukan).");
        }

        // Skip empty rows and the warning/example row
        if (empty($row['registration']) || empty($row['seat_id']) || empty($row['expiry_date_yyyy_mm_dd']) || str_contains((string)$row['registration'], 'CONTOH PENGISIAN')) {
            return null;
        }

        $registration = strtoupper($row['registration']);
        $seatId = $row['seat_id'];
        
        // Parse expiry date
        try {
            $dateValue = $row['expiry_date_yyyy_mm_dd'];
            
            if (is_numeric($dateValue)) {
                // Jika formatnya terbaca sebagai Excel Serial Date Number (contoh: 46387)
                $expiryDate = \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue));
            } else {
                // Jika formatnya teks (contoh: 31/12/2026 atau 2026-12-31)
                // Ubah '/' menjadi '-' agar Carbon memahaminya sebagai format DD-MM-YYYY (European) bukan MM/DD/YYYY (US)
                $dateValue = str_replace('/', '-', $dateValue);
                $expiryDate = \Carbon\Carbon::parse($dateValue);
            }
        } catch (\Exception $e){
            $expiryDate = null;
        }

        if (!$expiryDate || $expiryDate->year < 2000) {
            return null; // Don't process invalid dates or default 1970 dates
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
