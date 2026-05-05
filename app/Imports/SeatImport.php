<?php

namespace App\Imports;

use App\Models\Seat;
use App\Models\Aircraft;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class SeatImport implements ToModel, WithHeadingRow
{
    // Array of [registration => [['seat_id' => '...', 'class_type' => '...', 'expiry_date' => '...'], ...]]
    public array $affectedData = [];

    /**



     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi format file
        if (!array_key_exists('seat_id', $row) || !array_key_exists('expiry_date', $row)) {
            throw new \Exception("Format file salah! Pastikan Anda mengunggah template SEAT / LIFE VEST (kolom 'Seat_ID' atau 'Expiry_Date' tidak ditemukan).");
        }

        // Skip empty rows and the warning/example row
        if (empty($row['registration']) || empty($row['seat_id']) || empty($row['expiry_date']) || str_contains(strtoupper((string)$row['registration']), 'CONTOH')) {
            return null;
        }

        $registration = strtoupper($row['registration']);
        $seatId = $row['seat_id'];
        
        // Parse expiry date
        try {
            $dateValue = $row['expiry_date'];
            
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

        // Format seat_id agar seragam (case-insensitive)
        $rawSeatId = trim($row['seat_id']);
        $seatIdLower = strtolower($rawSeatId);
        
        $classType = 'economy'; // default
        $rowNum = null;
        $col = null;
        $finalSeatId = $rawSeatId;

        // Cockpit seats
        if (in_array($seatIdLower, ['captain', 'copilot', 'observer1', 'observer2'])) {
            $classType = 'cockpit';
            $finalSeatId = $seatIdLower; // standar huruf kecil
            $col = $finalSeatId;
        }
        // PAX spare seats (pax-1, pax-2, etc.)
        elseif (str_starts_with($seatIdLower, 'pax-')) {
            $classType = 'spare-pax';
            $finalSeatId = $seatIdLower;
            $col = $finalSeatId;
        }
        // INF spare seats (inf-1, inf-2, etc.)
        elseif (str_starts_with($seatIdLower, 'inf-')) {
            $classType = 'spare-inf';
            $finalSeatId = $seatIdLower;
            $col = $finalSeatId;
        }
        // Attendant seats (att/d11-l, ATT/D11-L, d11-l, D11-L)
        elseif (str_starts_with($seatIdLower, 'att/') || preg_match('/^d\d+-[a-z]+$/', $seatIdLower)) {
            $classType = 'attendant';
            // Ambil bagian pintunya saja (misal: d11-l) lalu jadikan huruf besar (D11-L), tambahkan att/ di depan
            $doorPartRaw = preg_replace('/^att\//i', '', $rawSeatId);
            $finalSeatId = 'att/' . strtoupper($doorPartRaw);
            $col = $finalSeatId;
        }
        // Regular seats (6A, 21B, 6a, 21b)
        else {
            $finalSeatId = strtoupper($rawSeatId); // 6a -> 6A
            preg_match('/^(\d+)?(.+)$/', $finalSeatId, $matches);
            $rowNum = $matches[1] ?: null;
            $col = $matches[2] ?: $finalSeatId;
        }

        if (!isset($this->affectedData[$registration])) {
            $this->affectedData[$registration] = [];
        }
        $this->affectedData[$registration][] = [
            'seat_id' => $finalSeatId,
            'class_type' => $classType,
            'expiry_date' => $expiryDate ? $expiryDate->toDateString() : null,
        ];

        return Seat::updateOrCreate(

            [
                'registration' => $registration,
                'seat_id'      => $finalSeatId,
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
