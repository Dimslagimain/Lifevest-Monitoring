<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelReportController extends Controller
{
    private string $colorHeader    = 'FF1A237E';
    private string $colorSubHeader = 'FF283593';
    private string $colorExpired   = 'FFE1BEE7';
    private string $colorCritical  = 'FFFFCDD2';
    private string $colorWarning   = 'FFFFF9C4';
    private string $colorSafe      = 'FFC8E6C9';
    private string $colorWhite     = 'FFFFFFFF';
    private string $colorLightGray = 'FFF5F5F5';

    public function exportReplacementPlan()
    {
        $today = now()->startOfDay();
        $cutoff = Carbon::createFromDate(2027, 3, 31)->endOfDay();
        $threeMonthsBoundary = $today->copy()->addDays(89);

        $aircrafts = Aircraft::with('airline')->get();

        $intervalData = [
            'weekly' => [],
            'monthly' => [],
            'yearly' => [],
        ];
        $allRows = [];

        foreach ($aircrafts as $aircraft) {
            $reg = $aircraft->registration;
            $acType = $aircraft->type;
            $airlineName = $aircraft->airline?->name ?? 'Unknown';

            $pnMap = [
                'adult'  => ['pn' => $aircraft->pn_adult,  'types' => ['business', 'economy', 'first', 'spare-pax']],
                'crew'   => ['pn' => $aircraft->pn_crew,   'types' => ['cockpit', 'attendant']],
                'infant' => ['pn' => $aircraft->pn_infant, 'types' => ['spare-inf']],
            ];

            $seats = Seat::where('registration', $reg)
                ->whereNotNull('expiry_date')
                ->orderBy('expiry_date', 'asc')
                ->get();

            foreach ($seats as $seat) {
                $expiryDate = Carbon::parse($seat->expiry_date);

                if ($expiryDate->gt($cutoff)) {
                    continue;
                }

                $seatPn = null;
                $seatCategory = null;
                foreach ($pnMap as $category => $info) {
                    if (in_array($seat->class_type, $info['types'])) {
                        $seatPn = $info['pn'] ?: null;
                        $seatCategory = $category;
                        break;
                    }
                }
                if (!$seatPn || !$seatCategory) {
                    continue;
                }

                $daysRemaining = $today->diffInDays($expiryDate, false);
                if ($daysRemaining < 0) {
                    $status = 'EXPIRED';
                } elseif ($daysRemaining < 90) {
                    $status = 'CRITICAL';
                } elseif ($daysRemaining < 180) {
                    $status = 'WARNING';
                } else {
                    $status = 'SAFE';
                }

                $rowData = [
                    'airline'    => $airlineName,
                    'reg'        => $reg,
                    'type'       => $acType,
                    'seat_id'    => $seat->seat_id,
                    'class_type' => strtoupper($seat->class_type),
                    'pn'         => $seatPn,
                    'category'   => strtoupper($seatCategory),
                    'expiry'     => $expiryDate->format('d-M-Y'),
                    'expiry_raw' => $expiryDate,
                    'days'       => (int) $daysRemaining,
                    'status'     => $status,
                ];
                $allRows[] = $rowData;

                $intervals = [];
                if ($expiryDate->lt($today)) {
                    $intervals = [
                        'weekly'  => ['key' => '0000-00-Overdue', 'label' => 'Overdue', 'short' => 'Overdue'],
                        'monthly' => ['key' => '0000-00-Overdue', 'label' => 'Overdue', 'short' => 'Overdue'],
                        'yearly'  => ['key' => '0000-00-Overdue', 'label' => 'Overdue', 'short' => 'Overdue'],
                    ];
                } else {
                    $weekStart = $expiryDate->copy()->startOfWeek();
                    $weekEnd = $expiryDate->copy()->endOfWeek();
                    $intervals['weekly'] = [
                        'key' => $expiryDate->format('o-\WW'),
                        'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y'),
                        'short' => $expiryDate->format('o-\WW'), // short form for matrix column
                    ];
                    $intervals['monthly'] = [
                        'key' => $expiryDate->format('Y-m'),
                        'label' => $expiryDate->format('F Y'),
                        'short' => $expiryDate->format('M Y'), // short form
                    ];
                    $intervals['yearly'] = [
                        'key' => $expiryDate->format('Y'),
                        'label' => $expiryDate->format('Y'),
                        'short' => $expiryDate->format('Y'),
                    ];
                }

                foreach ($intervals as $intervalName => $bucketInfo) {
                    $bucketKey = $bucketInfo['key'];
                    if (!isset($intervalData[$intervalName][$bucketKey])) {
                        $intervalData[$intervalName][$bucketKey] = [
                            'label' => $bucketInfo['label'],
                            'short' => $bucketInfo['short'],
                            'rows'  => [],
                        ];
                    }
                    $rowCopy = $rowData;
                    $rowCopy['period_label'] = $bucketInfo['label'];
                    $intervalData[$intervalName][$bucketKey]['rows'][] = $rowCopy;
                }
            }
        }

        foreach (['weekly', 'monthly', 'yearly'] as $intervalName) {
            ksort($intervalData[$intervalName]);
            foreach ($intervalData[$intervalName] as &$bucket) {
                usort($bucket['rows'], fn($a, $b) => $a['expiry_raw'] <=> $b['expiry_raw']);
            }
            unset($bucket);
        }

        $allPns = collect($allRows)->pluck('pn')->filter()->unique()->sort()->values()->toArray();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach (['weekly', 'monthly', 'yearly'] as $intervalName) {
            $this->buildIntervalSheet($spreadsheet, ucfirst($intervalName), $intervalData[$intervalName], $allPns, $today, $threeMonthsBoundary);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Replacement_Plan_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function exportSummaryDashboard()
    {
        $today = now()->startOfDay();
        $threeMonthsBoundary = $today->copy()->addDays(89);

        $aircrafts = Aircraft::with('airline')->get();
        $allRows = [];

        foreach ($aircrafts as $aircraft) {
            $reg = $aircraft->registration;
            $acType = $aircraft->type;
            $airlineName = $aircraft->airline?->name ?? 'Unknown';

            $pnMap = [
                'adult'  => ['pn' => $aircraft->pn_adult,  'types' => ['business', 'economy', 'first', 'spare-pax']],
                'crew'   => ['pn' => $aircraft->pn_crew,   'types' => ['cockpit', 'attendant']],
                'infant' => ['pn' => $aircraft->pn_infant, 'types' => ['spare-inf']],
            ];

            $seats = Seat::where('registration', $reg)
                ->whereNotNull('expiry_date')
                ->get();

            foreach ($seats as $seat) {
                $expiryDate = Carbon::parse($seat->expiry_date);
                
                $seatPn = null;
                $seatCategory = null;
                foreach ($pnMap as $category => $info) {
                    if (in_array($seat->class_type, $info['types'])) {
                        $seatPn = $info['pn'] ?: null;
                        $seatCategory = $category;
                        break;
                    }
                }
                if (!$seatPn || !$seatCategory) continue;

                $daysRemaining = $today->diffInDays($expiryDate, false);
                if ($daysRemaining < 0) {
                    $status = 'EXPIRED';
                } elseif ($daysRemaining < 90) {
                    $status = 'CRITICAL';
                } elseif ($daysRemaining < 180) {
                    $status = 'WARNING';
                } else {
                    $status = 'SAFE';
                }

                $allRows[] = [
                    'status' => $status,
                    'pn'     => $seatPn,
                ];
            }
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->buildSummarySheet($spreadsheet, $allRows, $today, $threeMonthsBoundary);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'LifeVest_Summary_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function buildSummarySheet(Spreadsheet $spreadsheet, array $allRows, Carbon $today, Carbon $threeMonthsBoundary): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Summary');

        $sheet->mergeCells("A1:E1");
        $sheet->setCellValue('A1', 'LIFE VEST REPLACEMENT DASHBOARD — GMF AeroAsia');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => $this->colorWhite]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        $sheet->mergeCells("A2:E2");
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('d M Y, H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => $this->colorWhite]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorSubHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(14);

        $row = 4;
        $sheet->setCellValue("A{$row}", 'OVERALL FLEET STATUS');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;

        $expiredCount  = count(array_filter($allRows, fn($r) => $r['status'] === 'EXPIRED'));
        $criticalCount = count(array_filter($allRows, fn($r) => $r['status'] === 'CRITICAL'));
        $warningCount  = count(array_filter($allRows, fn($r) => $r['status'] === 'WARNING'));
        $totalCount    = count($allRows);

        $summaryItems = [
            ['Total Life Vests Tracked', $totalCount, $this->colorLightGray],
            ['Expired (Past due)', $expiredCount, $this->colorExpired],
            ['Critical (< 3 months)', $criticalCount, $this->colorCritical],
            ['Warning (3-6 months)', $warningCount, $this->colorWarning],
        ];

        foreach ($summaryItems as $item) {
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->setCellValue("A{$row}", $item[0]);
            $sheet->setCellValue("E{$row}", $item[1]);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $item[2]]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }
        
        // --- Legend ---
        $row += 2;
        $sheet->setCellValue("A{$row}", 'Legend:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        $legends = [
            ['OVERDUE / EXPIRED', 'Life vest sudah melewati tanggal kadaluarsa', $this->colorExpired],
            ['CRITICAL', 'Life vest tersisa < 3 bulan', $this->colorCritical],
            ['WARNING', 'Life vest tersisa 3-6 bulan', $this->colorWarning],
        ];
        foreach ($legends as $legend) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->setCellValue("A{$row}", $legend[0]);
            $sheet->mergeCells("C{$row}:E{$row}");
            $sheet->setCellValue("C{$row}", $legend[1]);
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $legend[2]]],
                'font' => ['bold' => true],
            ]);
            $row++;
        }
        
        // --- TOP PART NUMBERS REQUIRING ATTENTION ---
        $row += 2;
        $sheet->setCellValue("A{$row}", 'TOP PART NUMBERS REQUIRING REPLACEMENT');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;

        $pnCounts = [];
        foreach ($allRows as $r) {
            if (in_array($r['status'], ['EXPIRED', 'CRITICAL', 'WARNING'])) {
                $pn = $r['pn'] ?? 'UNKNOWN';
                if (!isset($pnCounts[$pn])) {
                    $pnCounts[$pn] = ['total' => 0, 'expired' => 0, 'critical' => 0, 'warning' => 0];
                }
                $pnCounts[$pn]['total']++;
                $pnCounts[$pn][strtolower($r['status'])]++;
            }
        }
        
        uasort($pnCounts, fn($a, $b) => $b['total'] <=> $a['total']);
        $topPns = array_slice($pnCounts, 0, 10, true); // Get top 10
        
        if (empty($topPns)) {
            $sheet->setCellValue("A{$row}", "Semua Part Number dalam kondisi aman.");
            $sheet->mergeCells("A{$row}:E{$row}");
        } else {
            $sheet->setCellValue("A{$row}", "Part Number");
            $sheet->setCellValue("B{$row}", "Total Qty");
            $sheet->setCellValue("C{$row}", "Expired");
            $sheet->setCellValue("D{$row}", "Critical");
            $sheet->setCellValue("E{$row}", "Warning");
            
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
            ]);
            $row++;
            
            foreach ($topPns as $pn => $counts) {
                $sheet->setCellValue("A{$row}", $pn);
                $sheet->setCellValue("B{$row}", $counts['total']);
                $sheet->setCellValue("C{$row}", $counts['expired']);
                $sheet->setCellValue("D{$row}", $counts['critical']);
                $sheet->setCellValue("E{$row}", $counts['warning']);
                
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                ]);
                $sheet->getStyle("B{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
        }
    }

    private function buildIntervalSheet(Spreadsheet $spreadsheet, string $tabName, array $dataBuckets, array $allPns, Carbon $today, Carbon $threeMonthsBoundary): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($tabName);

        // --- PART 1: P/N SUMMARY MATRIX ---
        $row = 1;
        $sheet->setCellValue("A{$row}", strtoupper($tabName) . ' P/N SUMMARY MATRIX');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row += 2;

        $matrixHeaderRow = $row;
        $sheet->setCellValue("A{$matrixHeaderRow}", 'Part Number');
        $sheet->getStyle("A{$matrixHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
        ]);
        $sheet->getColumnDimension('A')->setWidth(22);

        $colIdx = 2;
        $bucketKeys = array_keys($dataBuckets);
        foreach ($bucketKeys as $bKey) {
            $colStr = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue("{$colStr}{$matrixHeaderRow}", $dataBuckets[$bKey]['short']);
            $sheet->getColumnDimension($colStr)->setWidth(16);
            $sheet->getStyle("{$colStr}{$matrixHeaderRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
            ]);
            $colIdx++;
        }

        // Grand Total col
        $gtColStr = Coordinate::stringFromColumnIndex($colIdx);
        $sheet->setCellValue("{$gtColStr}{$matrixHeaderRow}", 'Grand Total');
        $sheet->getColumnDimension($gtColStr)->setWidth(16);
        $sheet->getStyle("{$gtColStr}{$matrixHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
        ]);

        $matrixLastCol = $gtColStr;
        $row++;
        
        $grandTotalBucket = array_fill(0, count($bucketKeys) + 1, 0);

        foreach ($allPns as $pn) {
            $sheet->setCellValue("A{$row}", $pn);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorLightGray]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);

            $pnGrandTotal = 0;
            $bIdx = 0;
            $colIdx = 2;
            foreach ($bucketKeys as $bKey) {
                $cColStr = Coordinate::stringFromColumnIndex($colIdx);
                $c = count(array_filter($dataBuckets[$bKey]['rows'], fn($r) => $r['pn'] === $pn));
                
                $sheet->setCellValue("{$cColStr}{$row}", $c);
                $sheet->getStyle("{$cColStr}{$row}")->applyFromArray([
                    'font' => ['size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                ]);
                
                // Urgency Coloring
                if ($bKey === '0000-00-Overdue') {
                    $sheet->getStyle("{$cColStr}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->colorExpired);
                }
                
                $pnGrandTotal += $c;
                $grandTotalBucket[$bIdx] += $c;
                $bIdx++;
                $colIdx++;
            }
            
            // Grand Total cell
            $sheet->setCellValue("{$gtColStr}{$row}", $pnGrandTotal);
            $sheet->getStyle("{$gtColStr}{$row}")->applyFromArray([
                'font' => ['size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorLightGray]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);
            $grandTotalBucket[$bIdx] += $pnGrandTotal;
            $row++;
        }

        // Grand Total row
        $sheet->setCellValue("A{$row}", 'GRAND TOTAL');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
        ]);
        $bIdx = 0;
        $colIdx = 2;
        foreach ($bucketKeys as $bKey) {
            $cColStr = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue("{$cColStr}{$row}", $grandTotalBucket[$bIdx]);
            $bIdx++;
            $colIdx++;
        }
        $sheet->setCellValue("{$gtColStr}{$row}", $grandTotalBucket[$bIdx]);

        $sheet->getStyle("A{$row}:{$matrixLastCol}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE3F2FD']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // --- PART 2: RAW DATA TABLE ---
        $row += 4;
        
        $tableHeaderRow = $row;
        $headers = ['Period', 'No', 'Airline', 'Registration', 'Aircraft Type', 'Seat ID', 'Class', 'Part Number', 'Category', 'Expiry Date', 'Days Remaining', 'Status'];
        $colWidths = [24, 6, 20, 14, 14, 10, 14, 20, 12, 16, 16, 14];

        foreach ($headers as $i => $h) {
            $col = chr(65 + $i); // Valid for A-L
            $sheet->setCellValue("{$col}{$tableHeaderRow}", $h);
            $sheet->getColumnDimension($col)->setWidth($colWidths[$i]);
        }

        $sheet->getStyle("A{$tableHeaderRow}:L{$tableHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
        ]);
        $sheet->getRowDimension($tableHeaderRow)->setRowHeight(25);

        $row++;
        
        $tableData = [];
        $statusMap = [];
        $idx = 1;
        
        foreach ($dataBuckets as $bKey => $bData) {
            foreach ($bData['rows'] as $data) {
                $tableData[] = [
                    $data['period_label'],
                    $idx++,
                    $data['airline'],
                    $data['reg'],
                    $data['type'],
                    $data['seat_id'],
                    $data['class_type'],
                    $data['pn'],
                    $data['category'],
                    $data['expiry'],
                    $data['days'],
                    $data['status'],
                ];
                $statusMap[] = $data['status'];
            }
        }

        if (!empty($tableData)) {
            $sheet->fromArray($tableData, null, "A{$row}");
            
            $lastDataRow = $row + count($tableData) - 1;
            
            // Bulk Apply Table Borders & Vertical Align
            $sheet->getStyle("A{$row}:L{$lastDataRow}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Bulk Apply Horizontal Center to specific columns
            foreach (['A', 'B', 'E', 'G', 'J', 'K', 'L'] as $cl) {
                $sheet->getStyle("{$cl}{$row}:{$cl}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            // Apply Background & Font colors by chunking identical statuses
            // This drops the styling operations from 10,000 down to ~4 (because data is sorted by date => status)
            $statusBlocks = [];
            $currentStatus = $statusMap[0] ?? '';
            $blockStart = $row;
            $currentRow = $row;

            foreach ($statusMap as $status) {
                if ($status !== $currentStatus) {
                    $statusBlocks[] = ['status' => $currentStatus, 'start' => $blockStart, 'end' => $currentRow - 1];
                    $currentStatus = $status;
                    $blockStart = $currentRow;
                }
                $currentRow++;
            }
            if (!empty($statusMap)) {
                $statusBlocks[] = ['status' => $currentStatus, 'start' => $blockStart, 'end' => $currentRow - 1];
            }

            foreach ($statusBlocks as $block) {
                $status = $block['status'];
                $bgColor = match ($status) {
                    'EXPIRED'  => $this->colorExpired,
                    'CRITICAL' => $this->colorCritical,
                    'WARNING'  => $this->colorWarning,
                    'SAFE'     => $this->colorSafe,
                    default    => $this->colorLightGray,
                };
                
                $statusFontColor = match ($status) {
                    'EXPIRED'  => 'FF7B1FA2',
                    'CRITICAL' => 'FFC62828',
                    'WARNING'  => 'FFF57F17',
                    'SAFE'     => 'FF2E7D32',
                    default    => 'FF000000',
                };

                $sheet->getStyle("A{$block['start']}:L{$block['end']}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($bgColor);

                $sheet->getStyle("L{$block['start']}:L{$block['end']}")
                    ->getFont()->setBold(true)->getColor()->setARGB($statusFontColor);
            }
        } else {
            $lastDataRow = $row - 1;
        }
        // Apply auto-filter on the data table
        if ($lastDataRow >= $tableHeaderRow + 1) {
            $sheet->setAutoFilter("A{$tableHeaderRow}:L{$lastDataRow}");
        }
        
        // Freeze both the Summary Matrix columns and the Raw Data Top header 
        // This is tricky in Excel when stacking. We will just freeze A2 (no horizontal freeze)
        // Actually, freeze pane is global. We will freeze underneath the raw table header.
        $freezeRow = $tableHeaderRow + 1;
        $sheet->freezePane("C{$freezeRow}");
    }

    public function exportActivityLog(Request $request)
    {
        $logs = \App\Models\ActivityLog::with(['user', 'aircraft'])
            ->whereIn('action', ['update', 'batch', 'pn_update', 'delete', 'import'])
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Activity Log');

        // Header Style
        $sheet->mergeCells("A1:H1");
        $sheet->setCellValue('A1', 'GMF AEROASIA - CABIN MAINTENANCE');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => $this->colorWhite]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        $sheet->mergeCells("A2:H2");
        $sheet->setCellValue('A2', 'LIFE VEST TRACKER - FULL ACTIVITY AUDIT LOG');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => $this->colorWhite]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorSubHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->mergeCells("A3:H3");
        $sheet->setCellValue('A3', 'Generated on: ' . now()->format('d M Y, H:i'));
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        // Table Header
        $headers = ['DATE & TIME', 'ADMIN', 'AIRCRAFT', 'ACTIVITY', 'PART NUMBER', 'QTY', 'SEATS LIST', 'EXPIRY DATE'];
        $colIdx = 1;
        foreach ($headers as $header) {
            $colStr = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->setCellValue($colStr . '5', $header);
            $sheet->getStyle($colStr . '5')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $colIdx++;
        }

        // Column Widths
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(50);
        $sheet->getColumnDimension('H')->setWidth(15);

        $rowIdx = 6;
        foreach ($logs as $log) {
            $pn = '-';
            if ($log->action === 'update' || $log->action === 'batch' || $log->action === 'import') {
                $pns = $log->details['pns'] ?? (isset($log->details['pn']) ? [$log->details['pn']] : []);
                if (empty($pns) && isset($log->details['seats'][0]) && $log->aircraft) {
                    $firstSeat = $log->details['seats'][0];
                    if (str_starts_with($firstSeat, 'inf-')) { $pns[] = $log->aircraft->pn_infant; }
                    elseif (in_array($firstSeat, ['captain', 'fo', 'obs-1', 'obs-2']) || str_starts_with($firstSeat, 'att/')) { $pns[] = $log->aircraft->pn_crew; }
                    else { $pns[] = $log->aircraft->pn_adult; }
                }
                $pn = !empty($pns) ? implode("\n", $pns) : '-';
            } elseif ($log->action === 'pn_update') {
                $changes = [];
                foreach(['adult', 'crew', 'infant'] as $t) {
                    if(($log->details['old'][$t] ?? '') !== ($log->details['new'][$t] ?? '')) {
                        $changes[] = strtoupper($t) . ": " . ($log->details['old'][$t] ?: '---') . " -> " . ($log->details['new'][$t] ?: '---');
                    }
                }
                $pn = implode(' | ', $changes);
            }

            $sheet->setCellValue('A' . $rowIdx, $log->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue('B' . $rowIdx, strtoupper($log->user->name ?? 'SYSTEM'));
            $sheet->setCellValue('C' . $rowIdx, $log->registration ?? '-');
            $sheet->setCellValue('D' . $rowIdx, strtoupper($log->action));
            
            $details = '-';
            if ($log->action === 'suspend_user') {
                $details = 'TARGET: ' . ($log->details['target_user_name'] ?? '-') . ' | REASON: ' . ($log->details['reason'] ?? '-');
            } elseif ($log->action === 'unsuspend_user') {
                $details = 'TARGET: ' . ($log->details['target_user_name'] ?? '-');
            } elseif (isset($log->details['seats'])) {
                $details = implode(', ', (array)$log->details['seats']);
            } elseif ($log->action === 'import') {
                $details = $log->details['message'] ?? 'BULK IMPORT';
            } elseif (isset($log->details['seat_id'])) {
                $details = $log->details['seat_id'];
            }

            $sheet->setCellValue('E' . $rowIdx, $pn);
            $sheet->setCellValue('F' . $rowIdx, $log->details['seat_count'] ?? (isset($log->details['seats']) ? count($log->details['seats']) : '-'));
            $sheet->setCellValue('G' . $rowIdx, $details);
            
            $expiryDateStr = '-';
            if (isset($log->details['expiry_date']) && $log->details['expiry_date'] !== null) {
                try {
                    $expiryDateStr = Carbon::parse($log->details['expiry_date'])->format('d/m/Y');
                } catch (\Exception $e) {
                    $expiryDateStr = $log->details['expiry_date'];
                }
            }
            $sheet->setCellValue('H' . $rowIdx, $expiryDateStr);

            // Apply Base Alignment (Center) to all cells in the row
            $sheet->getStyle("A{$rowIdx}:H{$rowIdx}")->getAlignment()->setHorizontal('center')->setVertical('center');

            // Override P/N (E) and Seats List (G) to Left Alignment for better readability
            $sheet->getStyle('E' . $rowIdx)->getAlignment()->setHorizontal('left')->setWrapText(true);
            $sheet->getStyle('G' . $rowIdx)->getAlignment()->setHorizontal('left')->setWrapText(true);

            // Zebra Stripping & Borders
            $range = "A{$rowIdx}:H{$rowIdx}";
            $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE0E0E0');
            if ($rowIdx % 2 === 0) {
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->colorLightGray);
            }

            // Status Coloring
            $statusColor = match($log->action) {
                'delete' => 'FFFFCDD2', // Reddish
                'add', 'batch' => 'FFC8E6C9', // Greenish
                default => 'FFE3F2FD', // Blueish
            };
            $sheet->getStyle('D' . $rowIdx)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColor);

            $rowIdx++;
        }

        $filename = 'LifeVest_ActivityLog_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Export a single activity log entry with its detailed seat data
     */
    public function exportSingleActivity(int $id)
    {
        $log = \App\Models\ActivityLog::with(['user', 'aircraft'])->findOrFail($id);
        $today = now()->startOfDay();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Activity Detail');

        // ---- HEADER ----
        $sheet->mergeCells("A1:H1");
        $sheet->setCellValue('A1', 'GMF AEROASIA - CABIN MAINTENANCE');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => $this->colorWhite]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        $sheet->mergeCells("A2:H2");
        $sheet->setCellValue('A2', 'LIFE VEST TRACKER - ACTIVITY DETAIL REPORT');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => $this->colorWhite]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorSubHeader]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->mergeCells("A3:H3");
        $sheet->setCellValue('A3', 'Generated on: ' . now()->format('d M Y, H:i'));
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['italic' => true, 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);

        // ---- ACTIVITY SUMMARY ----
        $row = 5;
        $sheet->setCellValue("A{$row}", 'ACTIVITY SUMMARY');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;

        $actionLabel = strtoupper(match($log->action) {
            'update' => 'Seats Updated',
            'batch' => 'Batch Input',
            'pn_update' => 'P/N Update',
            'delete' => 'Seat Deleted',
            'import' => 'Bulk Import',
            default => $log->action,
        });

        $summaryFields = [
            ['Date & Time', $log->created_at->format('d M Y, H:i:s')],
            ['Admin', strtoupper($log->user->name ?? 'SYSTEM')],
            ['Aircraft', $log->registration ?? '-'],
            ['Activity', $actionLabel],
            ['Qty Seats', $log->details['seat_count'] ?? (isset($log->details['seats']) ? count($log->details['seats']) : '-')],
            ['Expiry Date Set', isset($log->details['expiry_date']) ? Carbon::parse($log->details['expiry_date'])->format('d M Y') : '-'],
        ];

        // P/N info
        $pns = $log->details['pns'] ?? (isset($log->details['pn']) ? [$log->details['pn']] : []);
        if (empty($pns) && isset($log->details['seats'][0]) && $log->aircraft) {
            $firstSeat = $log->details['seats'][0];
            if (str_starts_with($firstSeat, 'inf-')) { $pns[] = $log->aircraft->pn_infant; }
            elseif (in_array($firstSeat, ['captain', 'fo', 'obs-1', 'obs-2']) || str_starts_with($firstSeat, 'att/')) { $pns[] = $log->aircraft->pn_crew; }
            else { $pns[] = $log->aircraft->pn_adult; }
        }
        if (!empty($pns)) {
            $summaryFields[] = ['Part Number(s)', implode(', ', $pns)];
        }

        foreach ($summaryFields as $field) {
            $sheet->setCellValue("A{$row}", $field[0]);
            $sheet->mergeCells("B{$row}:D{$row}");
            $sheet->setCellValue("B{$row}", $field[1]);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorLightGray]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);
            $sheet->getStyle("B{$row}:D{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);
            $row++;
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(14);

        // ---- SEAT DETAIL TABLE ----
        if (in_array($log->action, ['update', 'batch', 'import']) && !empty($log->details['seats'])) {
            $row += 2;
            $sheet->setCellValue("A{$row}", 'SEAT DETAIL');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;

            $seatHeaders = ['No', 'Seat ID', 'Class Type', 'Part Number', 'Current Expiry', 'Days Remaining', 'Status'];
            foreach ($seatHeaders as $i => $h) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$row}", $h);
            }
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
            ]);
            $sheet->getRowDimension($row)->setRowHeight(25);
            $row++;

            // Fetch current seat data from DB
            $seatIds = (array) $log->details['seats'];
            $registration = $log->registration;
            $aircraft = $log->aircraft;

            $pnMap = [];
            if ($aircraft) {
                $pnMap = [
                    'adult'  => ['pn' => $aircraft->pn_adult, 'types' => ['business', 'economy', 'first', 'spare-pax']],
                    'crew'   => ['pn' => $aircraft->pn_crew, 'types' => ['cockpit', 'attendant']],
                    'infant' => ['pn' => $aircraft->pn_infant, 'types' => ['spare-inf']],
                ];
            }

            $seats = Seat::where('registration', $registration)
                ->whereIn('seat_id', $seatIds)
                ->get()
                ->keyBy('seat_id');

            $idx = 1;
            foreach ($seatIds as $seatId) {
                $seat = $seats->get($seatId);
                $classType = $seat ? strtoupper($seat->class_type) : '-';
                
                // Determine P/N
                $seatPn = '-';
                if ($seat && $aircraft) {
                    foreach ($pnMap as $cat => $info) {
                        if (in_array($seat->class_type, $info['types'])) {
                            $seatPn = $info['pn'] ?? '-';
                            break;
                        }
                    }
                }

                $expiryStr = '-';
                $daysStr = '-';
                $statusStr = '-';
                $statusColor = $this->colorLightGray;

                if ($seat && $seat->expiry_date) {
                    $expiry = Carbon::parse($seat->expiry_date);
                    $expiryStr = $expiry->format('d-M-Y');
                    $days = (int) $today->diffInDays($expiry, false);
                    $daysStr = $days;

                    if ($days < 0) {
                        $statusStr = 'EXPIRED';
                        $statusColor = $this->colorExpired;
                    } elseif ($days < 90) {
                        $statusStr = 'CRITICAL';
                        $statusColor = $this->colorCritical;
                    } elseif ($days < 180) {
                        $statusStr = 'WARNING';
                        $statusColor = $this->colorWarning;
                    } else {
                        $statusStr = 'SAFE';
                        $statusColor = $this->colorSafe;
                    }
                }

                $sheet->setCellValue("A{$row}", $idx);
                $sheet->setCellValue("B{$row}", $seatId);
                $sheet->setCellValue("C{$row}", $classType);
                $sheet->setCellValue("D{$row}", $seatPn);
                $sheet->setCellValue("E{$row}", $expiryStr);
                $sheet->setCellValue("F{$row}", $daysStr);
                $sheet->setCellValue("G{$row}", $statusStr);

                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Status row coloring
                $sheet->getStyle("G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($statusColor);
                $sheet->getStyle("G{$row}")->getFont()->setBold(true);

                // Zebra striping
                if ($idx % 2 === 0) {
                    $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->colorLightGray);
                }

                $idx++;
                $row++;
            }

            // Auto-filter
            $headerRow = $row - count($seatIds) - 1;
            $lastRow = $row - 1;
            $sheet->setAutoFilter("A{$headerRow}:G{$lastRow}");

        } elseif ($log->action === 'pn_update') {
            // P/N Update detail
            $row += 2;
            $sheet->setCellValue("A{$row}", 'PART NUMBER CHANGES');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;

            $pnHeaders = ['Category', 'Previous P/N', 'New P/N'];
            foreach ($pnHeaders as $i => $h) {
                $col = chr(65 + $i);
                $sheet->setCellValue("{$col}{$row}", $h);
            }
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => $this->colorWhite], 'size' => 10],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $this->colorHeader]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF424242']]],
            ]);
            $row++;

            foreach (['adult', 'crew', 'infant'] as $type) {
                $oldVal = $log->details['old'][$type] ?? '';
                $newVal = $log->details['new'][$type] ?? '';
                if ($oldVal !== $newVal) {
                    $sheet->setCellValue("A{$row}", strtoupper($type));
                    $sheet->setCellValue("B{$row}", $oldVal ?: '---');
                    $sheet->setCellValue("C{$row}", $newVal ?: '---');
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                    // Highlight changes
                    $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF1565C0');
                    $sheet->getStyle("B{$row}")->getFont()->getColor()->setARGB('FF9E9E9E');
                    $row++;
                }
            }
        } elseif ($log->action === 'delete') {
            $row += 2;
            $sheet->setCellValue("A{$row}", 'DELETED SEAT');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
            $row++;
            $sheet->setCellValue("A{$row}", 'Seat ID');
            $sheet->setCellValue("B{$row}", $log->details['seat_id'] ?? '-');
            $sheet->setCellValue("C{$row}", 'Type');
            $sheet->setCellValue("D{$row}", strtoupper($log->details['type'] ?? '-'));
            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
            ]);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("C{$row}")->getFont()->setBold(true);
        }

        // Build filename
        $regPart = $log->registration ? "_{$log->registration}" : '';
        $actionPart = str_replace(' ', '', $actionLabel);
        $datePart = $log->created_at->format('Ymd_Hi');
        $filename = "Activity_{$actionPart}{$regPart}_{$datePart}.xlsx";

        $tempPath = storage_path('app/' . $filename);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
