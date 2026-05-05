<?php

namespace App\Imports;

use App\Models\Aircraft;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class AircraftImport implements ToModel, WithHeadingRow
{
    public array $registrations = [];

    /**

     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validasi format file: Pastikan kolom utama khusus Aircraft ada di header
        // Seat template juga punya 'registration', jadi kita harus cek kolom lain seperti 'type' atau 'airline_id'
        if (!array_key_exists('registration', $row) || !array_key_exists('type', $row) || !array_key_exists('airline_id', $row)) {
            throw new \Exception("Format file salah! Pastikan Anda mengunggah template AIRCRAFT (kolom 'Type' atau 'Airline_ID' tidak ditemukan).");
        }

        // Skip empty rows and the warning/example row
        if (empty($row['registration']) || str_contains(strtoupper((string)$row['registration']), 'CONTOH')) {
            return null;
        }

        $reg = strtoupper($row['registration']);
        $this->registrations[] = $reg;

        return Aircraft::updateOrCreate(
            [
                'registration' => $reg,
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
