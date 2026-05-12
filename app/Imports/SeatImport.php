<?php

namespace App\Imports;

use App\Models\Seat;
use App\Models\Aircraft;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SeatImport implements ToModel, WithHeadingRow
{
    // Array of [registration => [['seat_id' => '...', 'class_type' => '...', 'expiry_date' => '...'], ...]]
    public array $affectedData = [];

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi format file
        if (!array_key_exists('seat_id', $row) || !array_key_exists('expiry_date', $row)) {
            throw new \Exception("Format file salah! Pastikan kolom 'Seat_ID' dan 'Expiry_Date' ada.");
        }

        if (empty($row['registration']) || empty($row['seat_id']) || empty($row['expiry_date'])) {
            return null;
        }

        // 1. Normalisasi Registrasi
        $rawReg = strtoupper(trim((string)$row['registration']));
        $cleanReg = str_replace('-', '', $rawReg);
        
        $aircraft = Aircraft::where('registration', $rawReg)
                    ->orWhere('registration', $cleanReg)
                    ->first();
        
        if (!$aircraft) {
            Log::warning("[PDF Import] Pesawat TIDAK ditemukan!", ['registration_di_excel' => $rawReg]);
            return null;
        }
        $registration = $aircraft->registration;

        // 2. Normalisasi Tanggal (JAN 28 -> Jan 2028)
        $dateValue = '';
        try {
            $dateValue = strtoupper(trim((string)$row['expiry_date']));
            if (is_numeric($dateValue)) {
                $expiryDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue));
            } else {
                $dateValue = str_replace(['/', '.', ' '], '-', $dateValue);
                if (preg_match('/^([A-Z]{3})-(\d{2,4})$/', $dateValue, $matches)) {
                    $year = $matches[2];
                    if (strlen($year) == 2) $year = '20' . $year;
                    $expiryDate = Carbon::parse("01-" . $matches[1] . "-$year");
                } else {
                    $expiryDate = Carbon::parse($dateValue);
                }
            }
        } catch (\Exception $e) {
            Log::error("[PDF Import] Gagal baca tanggal!", ['value' => $dateValue, 'error' => $e->getMessage()]);
            return null;
        }

        if (!$expiryDate) return null;

        // 3. Normalisasi Seat ID & Mapping Cerdas
        $rawSeatId = strtoupper(trim((string)$row['seat_id']));
        $seatIdLower = strtolower($rawSeatId);
        $finalSeatId = $rawSeatId;
        $classType = 'economy';
        $rowNum = null;
        $col = $rawSeatId;

        // MAPPING ATTENDANT (Cerdas: D2-R1 -> RL, D2-R2 -> RR)
        if (str_contains($seatIdLower, 'att/') || str_starts_with($seatIdLower, 'd')) {
            $classType = 'attendant';
            if (preg_match('/D(\d+)-?([LR])(\d+)?/i', $rawSeatId, $m)) {
                $doorNum = $m[1];
                $side = strtoupper($m[2]);
                $suffix = isset($m[3]) ? $m[3] : null;
                
                $pos = ($side == 'L') ? 'L' : 'R';
                $subPos = ($suffix == '2') ? 'R' : 'L'; 
                if (!$suffix) $subPos = $pos; // D2-L -> LL

                if (strlen($doorNum) == 1) {
                    $finalSeatId = "att/d{$doorNum}{$doorNum}-{$pos}{$subPos}";
                } else {
                    $finalSeatId = "att/d{$doorNum}-{$pos}{$subPos}";
                }
            } else {
                $finalSeatId = 'att/' . strtolower(str_replace('ATT/', '', $rawSeatId));
            }
            $col = $finalSeatId;
        } 
        // COCKPIT
        elseif (in_array($seatIdLower, ['captain', 'pilot', 'copilot', 'observer1', 'observer2'])) {
            $classType = 'cockpit';
            $finalSeatId = (in_array($seatIdLower, ['captain', 'pilot'])) ? 'pilot' : $seatIdLower;
            $finalSeatId = strtolower($finalSeatId);
            $col = $finalSeatId;
        }
        // SPARE
        elseif (preg_match('/^(pax|inf|spare)-?(\d+)$/i', $rawSeatId, $m)) {
            $type = strtolower($m[1]) === 'inf' ? 'spare-inf' : 'spare-pax';
            $prefix = strtolower($m[1]) === 'inf' ? 'inf-' : 'pax-';
            $classType = $type;
            $finalSeatId = $prefix . $m[2];
            $col = $finalSeatId;
        }
        // REGULAR (6-A -> 6A)
        else {
            $finalSeatId = preg_replace('/[^A-Z0-9]/', '', $rawSeatId);
            if (preg_match('/^(\d+)([A-Z]+)$/', $finalSeatId, $matches)) {
                $rowNum = (int)$matches[1];
                $col = $matches[2];
            }
        }

        Log::info("[PDF Import] Memproses Seat:", [
            'registration' => $registration,
            'excel_seat_id' => $rawSeatId,
            'db_seat_id' => $finalSeatId,
            'expiry_date' => $expiryDate->toDateString()
        ]);

        $seat = Seat::updateOrCreate(
            ['registration' => $registration, 'seat_id' => $finalSeatId],
            [
                'row' => $rowNum,
                'col' => $col,
                'class_type' => $classType,
                'expiry_date' => $expiryDate->toDateString()
            ]
        );

        // Catat data yang terpengaruh agar bisa dilog oleh Controller
        $this->affectedData[$registration][] = [
            'seat_id'    => $finalSeatId,
            'class_type' => $classType,
            'expiry_date' => $expiryDate->toDateString()
        ];

        return $seat;
    }
}
