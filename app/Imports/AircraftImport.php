<?php

namespace App\Imports;

use App\Models\Aircraft;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class AircraftImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi format file: Pastikan kolom utama ada di header
        if (!array_key_exists('registration', $row)) {
            throw new \Exception("Format file salah! Pastikan Anda mengunggah template AIRCRAFT (kolom 'Registration' tidak ditemukan).");
        }

        // Skip empty rows and the warning/example row
        if (empty($row['registration']) || str_contains((string)$row['registration'], 'CONTOH PENGISIAN')) {
            return null;
        }

        return Aircraft::updateOrCreate(
            [
                'registration' => strtoupper($row['registration']),
            ],
            [
                'airline_id' => $row['airline_id'] ?? 1,
                'type'       => strtoupper($row['type'] ?? 'B737'),
                'layout'     => strtolower($row['layout'] ?? 'b737-e46'),
                'status'     => strtolower($row['status'] ?? 'active'),
                'pn_adult'   => $row['pn_adult'] ?? null,
                'pn_crew'    => $row['pn_crew'] ?? null,
                'pn_infant'  => $row['pn_infant'] ?? null,
            ]
        );
    }
}
