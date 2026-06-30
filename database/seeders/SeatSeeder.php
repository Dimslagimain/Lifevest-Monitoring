<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SeatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '2048M');

        $aircrafts = Aircraft::all();
        $today = Carbon::now()->startOfDay();
        $threeM = $today->copy()->addMonths(3);
        $sixM = $today->copy()->addMonths(6);
        $classRows = config('aircraft_class_rows');

        // 1. ABSOLUTE TARGETS FROM GAMBAR 1
        $targetTotalFleet = 30778;
        $budget = [
            'P0723-103W' => ['expired' => 1682, 'critical' => 67, 'warning' => 68],
            'P01074-201W' => ['expired' => 937,  'critical' => 330, 'warning' => 537],
            'P0640-101' => ['expired' => 252,  'critical' => 16,  'warning' => 19],
            'P01074-221W' => ['expired' => 173,  'critical' => 1,   'warning' => 64],
            'P01074-201WC' => ['expired' => 109,  'critical' => 5,   'warning' => 11],
            'P0723-103WC' => ['expired' => 99,   'critical' => 16,  'warning' => 8],
            '63600-505:70167' => ['expired' => 15,   'critical' => 0,   'warning' => 0],
            'P01074-205WC' => ['expired' => 3,    'critical' => 0,   'warning' => 1],
        ];

        // Safe budget = Total - ActionRequired
        $actionRequired = 3270 + 435 + 708;
        $safeBudget = $targetTotalFleet - $actionRequired;

        Seat::truncate();

        $generatedCount = 0;

        foreach ($aircrafts as $index => $aircraft) {
            $reg = $aircraft->registration;
            $layout = $aircraft->layout;
            $layoutConfig = $classRows[$layout] ?? [];

            $pnMap = [
                'business' => $aircraft->pn_adult,
                'economy' => $aircraft->pn_adult,
                'economy_premium' => $aircraft->pn_adult,
                'first' => $aircraft->pn_adult,
                'spare-pax' => $aircraft->pn_adult,
                'cockpit' => $aircraft->pn_crew,
                'attendant' => $aircraft->pn_crew,
                'spare-inf' => $aircraft->pn_infant,
            ];

            $seats = [];

            // A. PASSENGER SEATS (Based on Layout Config)
            foreach ($layoutConfig as $class => $rows) {
                // Determine Columns for this layout
                $cols = $this->getColumnsForLayout($layout, $class);

                foreach ($rows as $row) {
                    foreach ($cols as $col) {
                        $seats[] = [
                            'registration' => $reg,
                            'seat_id' => $row.$col,
                            'row' => $row,
                            'col' => $col,
                            'class_type' => $class,
                            'expiry_date' => $this->getNuclearExpiry($pnMap[$class], $budget, $safeBudget, $today),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            // B. CREW SEATS (Fixed based on aircraft size)
            $isWideBody = str_contains($aircraft->type, '777') || str_contains($aircraft->type, '330');
            $crewMap = $this->getCrewMap($isWideBody);
            foreach ($crewMap as $seatId => $class) {
                $seats[] = [
                    'registration' => $reg,
                    'seat_id' => $seatId,
                    'row' => null, 'col' => null,
                    'class_type' => $class,
                    'expiry_date' => $this->getNuclearExpiry($pnMap[$class], $budget, $safeBudget, $today),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // C. BRIDGE THE GAP (Spares)
            // To hit 30,778 exactly, we distribute the remaining slots as spares
            $remainingAc = 133 - $index;
            $targetForThisAc = $targetTotalFleet - $generatedCount;

            // Standard spares for this aircraft
            $sparePaxCount = $isWideBody ? rand(10, 20) : rand(1, 5);
            $spareInfCount = $isWideBody ? rand(20, 30) : rand(5, 10);

            // If it's the last aircraft, force it to hit the target total
            if ($remainingAc == 1) {
                $currentAcSeats = count($seats);
                $diff = $targetForThisAc - $currentAcSeats;
                $sparePaxCount = (int) ($diff / 2);
                $spareInfCount = $diff - $sparePaxCount;
            }

            // Add Spares
            for ($s = 1; $s <= $sparePaxCount; $s++) {
                $seats[] = [
                    'registration' => $reg, 'seat_id' => 'pax-'.$s,
                    'row' => null, 'col' => null, 'class_type' => 'spare-pax',
                    'expiry_date' => $this->getNuclearExpiry($pnMap['spare-pax'], $budget, $safeBudget, $today),
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }
            for ($s = 1; $s <= $spareInfCount; $s++) {
                $seats[] = [
                    'registration' => $reg, 'seat_id' => 'inf-'.$s,
                    'row' => null, 'col' => null, 'class_type' => 'spare-inf',
                    'expiry_date' => $this->getNuclearExpiry($pnMap['spare-inf'], $budget, $safeBudget, $today),
                    'created_at' => now(), 'updated_at' => now(),
                ];
            }

            $generatedCount += count($seats);
            foreach (array_chunk($seats, 1000) as $chunk) {
                Seat::insert($chunk);
            }
        }
    }

    private function getColumnsForLayout(string $layout, string $class): array
    {
        if (str_starts_with($layout, 'b737')) {
            return ($class === 'business') ? ['A', 'C', 'H', 'K'] : ['A', 'B', 'C', 'H', 'J', 'K'];
        }
        if (str_starts_with($layout, 'b777')) {
            if ($class === 'first') {
                return ['A', 'D', 'G', 'K'];
            }
            if ($class === 'business') {
                return ['A', 'C', 'D', 'G', 'H', 'K'];
            }

            return ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K'];
        }
        if (str_starts_with($layout, 'a330')) {
            if ($class === 'business') {
                return ['A', 'C', 'D', 'G', 'H', 'K'];
            }

            return ['A', 'C', 'D', 'E', 'F', 'G', 'H', 'K'];
        }
        if ($layout === 'atr72') {
            return ['A', 'C', 'D', 'F'];
        }

        // Default A320 or others
        return ['A', 'B', 'C', 'D', 'E', 'F'];
    }

    private function getCrewMap(bool $isWideBody): array
    {
        $map = [
            'captain' => 'cockpit', 'fo' => 'cockpit', 'obs-1' => 'cockpit',
            'att/d11-LL' => 'attendant', 'att/d11-LR' => 'attendant',
            'att/d12-LL' => 'attendant', 'att/d12-LR' => 'attendant',
        ];
        if ($isWideBody) {
            $map['obs-2'] = 'cockpit';
            $map['att/d21-RL'] = 'attendant';
            $map['att/d21-RR'] = 'attendant';
        }

        return $map;
    }

    private function getNuclearExpiry(string $pn, array &$budget, int &$safeBudget, Carbon $today): string
    {
        if (isset($budget[$pn])) {
            if ($budget[$pn]['expired'] > 0) {
                $budget[$pn]['expired']--;

                // Past due
                return $today->copy()->subDays(rand(1, 1000))->format('Y-m-d');
            }
            if ($budget[$pn]['critical'] > 0) {
                $budget[$pn]['critical']--;

                // 5 to 80 days (Safely inside < 90 days / 3 months)
                return $today->copy()->addDays(rand(5, 80))->format('Y-m-d');
            }
            if ($budget[$pn]['warning'] > 0) {
                $budget[$pn]['warning']--;

                // 100 to 170 days (Safely inside 90-180 days / 3-6 months)
                return $today->copy()->addDays(rand(100, 170))->format('Y-m-d');
            }
        }

        if ($safeBudget > 0) {
            $safeBudget--;
        }

        // Very safe (2-5 years)
        return $today->copy()->addYears(rand(2, 5))->format('Y-m-d');
    }
}
