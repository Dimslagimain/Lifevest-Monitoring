<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BulkImportTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        if ($this->type === 'aircraft') {
            return ['Registration', 'Airline_ID', 'Type', 'Layout', 'Status', 'PN_Adult', 'PN_Crew', 'PN_Infant'];
        } elseif ($this->type === 'seat') {
            return ['Registration', 'Seat_ID', 'Expiry_Date_YYYY_MM_DD'];
        } elseif ($this->type === 'user') {
            return ['Name', 'Email', 'Password', 'Role'];
        }

        return [];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Provide exactly ONE example row per type, with a clear [CONTOH] tag so it's auto-ignored
        if ($this->type === 'aircraft') {
            return [
                ['[CONTOH (DIABAIKAN)] PK-GIA', '1', 'B737-800', 'b737-e46', 'ACTIVE', 'SWL-71A', 'SWL-71A', 'SWL-71A-INF'],
            ];
        } elseif ($this->type === 'seat') {
            return [
                ['[CONTOH (DIABAIKAN)] PK-GIA', '21A', '2030-12-31'],
            ];
        } elseif ($this->type === 'user') {
            return [
                ['[CONTOH (DIABAIKAN)] Budi Santoso', 'budi@tnp.com', 'Rahasia123', 'admin'],
            ];
        }

        return [];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the first row (headings)
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF0D9488'], // A nice teal color matching the premium theme
                ],
            ],
            2 => [ // The warning row
                'font' => ['bold' => true, 'color' => ['argb' => 'FFB91C1C']], // Red text
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFEE2E2'], // Light red background
                ],
            ],
        ];
    }
}
