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
        // Provide example data based on the type
        if ($this->type === 'aircraft') {
            return [
                ['--- CONTOH PENGISIAN (TIDAK PERLU DIHAPUS, OTOMATIS DIABAIKAN) ---', '', '', '', '', '', '', ''],
                ['PK-GIA', '1', 'B737', 'b737-e46', 'active', 'P123-Adult', 'P456-Crew', 'P789-Infant'],
                ['PK-GIB', '1', 'A320', 'a320-standard', 'active', 'P123-Adult', 'P456-Crew', 'P789-Infant'],
            ];
        } elseif ($this->type === 'seat') {
            return [
                ['--- CONTOH PENGISIAN (TIDAK PERLU DIHAPUS, OTOMATIS DIABAIKAN) ---', '', ''],
                ['PK-GIA', '21A', '2030-12-31'],
                ['PK-GIA', '21B', '2030-12-31'],
                ['PK-GIA', 'captain', '2031-06-15'],
                ['PK-GIA', 'pax-1', '2028-10-20'],
            ];
        } elseif ($this->type === 'user') {
            return [
                ['--- CONTOH PENGISIAN (TIDAK PERLU DIHAPUS, OTOMATIS DIABAIKAN) ---', '', '', ''],
                ['John Doe', 'john.doe@example.com', 'Rahasia123', 'admin'],
                ['Jane Smith', 'jane.smith@example.com', 'Gmf12345', 'user'],
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
