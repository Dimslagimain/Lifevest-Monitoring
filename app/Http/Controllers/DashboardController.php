<?php

namespace App\Http\Controllers;

use App\Models\Seat;
use App\Models\ActivityLog;
use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $lastSeatUpdate = Seat::max('updated_at');
        $lastAircraftUpdate = Aircraft::max('updated_at');
        $lastAirlineUpdate = Airline::max('updated_at');
        
        $cacheKey = 'dashboard_data_v5_' . md5($lastSeatUpdate . $lastAircraftUpdate . $lastAirlineUpdate);

        $data = Cache::rememberForever($cacheKey, function () use ($lastSeatUpdate) {
            // Load from Database (All status: active & prolong) with airline relationship
        $aircrafts = Aircraft::with('airline')->get();
        // Fetch all seats once and group by registration to avoid N+1 issues
        $allSeats = Seat::all();
        $seatsByRegistration = $allSeats->groupBy('registration');

        $fleet = [];
        $totalStats = [
            'safe' => 0,
            'warning' => 0,
            'critical' => 0,
            'expired' => 0,
            'no_data' => 0,
        ];

        foreach ($aircrafts as $aircraft) {
            $registration = $aircraft->registration;
            $seats = $seatsByRegistration->get($registration, collect());

            $stats = [
                'safe' => 0,
                'warning' => 0,
                'critical' => 0,
                'expired' => 0,
                'no_data' => 0,
            ];

            foreach ($seats as $seat) {
                $status = $seat->status;
                $key = $status === 'no-data' ? 'no_data' : $status;
                $stats[$key]++;
                $totalStats[$key]++;
            }

            $total = array_sum($stats) ?: 1;
            $healthPercent = round(($stats['safe'] / $total) * 100);

            $fleet[$registration] = [
                'type' => $aircraft->type,
                'registration' => $registration,
                'icon' => $aircraft->icon ?? '',
                'status' => $aircraft->status,
                'stats' => $stats,
                'health' => $healthPercent,
                'airline_id' => $aircraft->airline_id,
                'airline_name' => $aircraft->airline?->name ?? 'Unknown',
                'airline_icon' => $aircraft->airline?->icon ?? '🏢',
            ];
        }

        // Build per-fleet-type stats
        $perFleetStats = [];
        foreach ($fleet as $registration => $acData) {
            preg_match('/^([A-Z]+\d+)/', $acData['type'], $matches);
            $baseType = $matches[1] ?? $acData['type'];

            if (!isset($perFleetStats[$baseType])) {
                $perFleetStats[$baseType] = [
                    'safe' => 0,
                    'warning' => 0,
                    'critical' => 0,
                    'expired' => 0,
                    'no_data' => 0,
                    'count' => 0,
                ];
            }
            $perFleetStats[$baseType]['safe'] += $acData['stats']['safe'];
            $perFleetStats[$baseType]['warning'] += $acData['stats']['warning'];
            $perFleetStats[$baseType]['critical'] += $acData['stats']['critical'];
            $perFleetStats[$baseType]['expired'] += $acData['stats']['expired'];
            $perFleetStats[$baseType]['no_data'] += $acData['stats']['no_data'];
            $perFleetStats[$baseType]['count']++;
        }
        ksort($perFleetStats);

        // Get global last update time
        $lastUpdate = Seat::max('updated_at');

        // Get all airlines for grouping
        $airlines = Airline::all();

        // Group fleet by Airline -> then by Aircraft Type
        $fleetByAirline = [];
        foreach ($airlines as $airline) {
            $airlineFleet = collect($fleet)->filter(fn($a) => $a['airline_id'] === $airline->id);

            if ($airlineFleet->isEmpty()) {
                continue; // Skip airlines with no aircraft
            }

            // Group by aircraft type within this airline
            $byType = [];
            foreach ($airlineFleet as $registration => $aircraft) {
                // Extract base type (B737, B777, A330, ATR72)
                preg_match('/^([A-Z]+\d+)/', $aircraft['type'], $matches);
                $baseType = $matches[1] ?? $aircraft['type'];

                if (!isset($byType[$baseType])) {
                    $byType[$baseType] = [
                        'name' => $baseType . ' Fleet',
                        'icon' => $aircraft['icon'],
                        'aircraft' => [],
                    ];
                }
                $byType[$baseType]['aircraft'][$registration] = $aircraft;
            }

            $fleetByAirline[$airline->id] = [
                'name' => $airline->name,
                'icon' => $airline->icon,
                'code' => $airline->code,
                'types' => $byType,
                'aircraft_count' => $airlineFleet->count(),
            ];
        }

        // Build Part Number replacement summary
        $today = now()->startOfDay();
        $sixMonths = $today->copy()->addMonths(6);
        $threeMonths = $today->copy()->addMonths(3);
        $pnSummary = [];

        foreach ($aircrafts as $aircraft) {
            $reg = $aircraft->registration;
            $pnMap = [
                'adult' => ['pn' => $aircraft->pn_adult, 'types' => ['business', 'economy', 'first', 'spare-pax']],
                'crew' => ['pn' => $aircraft->pn_crew, 'types' => ['cockpit', 'attendant']],
                'infant' => ['pn' => $aircraft->pn_infant, 'types' => ['spare-inf']],
            ];

            $acSeats = $seatsByRegistration->get($reg, collect());

            foreach ($pnMap as $category => $info) {
                if (empty($info['pn']))
                    continue;

                $pn = $info['pn'];
                $catSeats = $acSeats->filter(fn($s) => in_array($s->class_type, $info['types']));
                $total = $catSeats->count();

                $expired = 0;
                $critical = 0;
                $warning = 0;

                foreach ($catSeats as $seat) {
                    if (!$seat->expiry_date) continue;
                    $expiry = \Carbon\Carbon::parse($seat->expiry_date);
                    if ($expiry->lt($today)) {
                        $expired++;
                    } elseif ($expiry->lt($threeMonths)) {
                        $critical++;
                    } elseif ($expiry->lt($sixMonths)) {
                        $warning++;
                    }
                }

                $key = $pn . '|' . $category;
                if (!isset($pnSummary[$key])) {
                    $pnSummary[$key] = [
                        'pn' => $pn,
                        'category' => $category,
                        'total' => 0,
                        'expired' => 0,
                        'critical' => 0,
                        'warning' => 0,
                        'aircraft' => [],
                    ];
                }
                $pnSummary[$key]['total'] += $total;
                $pnSummary[$key]['expired'] += $expired;
                $pnSummary[$key]['critical'] += $critical;
                $pnSummary[$key]['warning'] += $warning;
                if ($expired > 0 || $critical > 0 || $warning > 0) {
                    $pnSummary[$key]['aircraft'][] = [
                        'reg' => $reg,
                        'expired' => $expired,
                        'critical' => $critical,
                        'warning' => $warning,
                    ];
                }
            }
        }

        // Sort: most attention-needed first, then alphabetically
        usort($pnSummary, function ($a, $b) {
            $aAttention = $a['expired'] + $a['critical'] + $a['warning'];
            $bAttention = $b['expired'] + $b['critical'] + $b['warning'];
            if ($bAttention !== $aAttention)
                return $bAttention - $aAttention;
            return strcmp($a['pn'], $b['pn']);
        });

        // ============================================================
        // Build Replacement Plans (Weekly, Monthly, Yearly)
        // Cutoff: Include all data up to end of March 2027
        // ============================================================
        $replacementPlans = [
            'weekly' => [],
            'monthly' => [],
            'yearly' => [],
        ];
        $cutoff = \Carbon\Carbon::createFromDate(2027, 4, 30)->endOfDay(); // Include all data up to end of April 2027

        // Pre-populate Weekly buckets for 52 weeks (~12 months)
        $weekStart = \Carbon\Carbon::createFromDate(2026, 5, 4)->startOfWeek(); // Starting week of the period
        for ($i = 0; $i < 52; $i++) {
            $curr = $weekStart->copy()->addWeeks($i);
            $key = $curr->format('o-\WW');
            $replacementPlans['weekly'][$key] = [
                'key' => $key,
                'label' => $curr->format('d M') . ' - ' . $curr->copy()->endOfWeek()->format('d M Y'),
                'sort' => $curr->format('o-W'),
                'start_date' => $curr->copy(),
                'total' => 0,
                'pn_breakdown' => [],
                'aircraft_breakdown' => [],
            ];
        }

        // Pre-populate Monthly buckets for 12 months (May 2026 - April 2027)
        $monthStart = \Carbon\Carbon::createFromDate(2026, 5, 1)->startOfMonth();
        for ($i = 0; $i < 12; $i++) {
            $curr = $monthStart->copy()->addMonths($i);
            $key = $curr->format('Y-m');
            $replacementPlans['monthly'][$key] = [
                'key' => $key,
                'label' => $curr->format('F Y'),
                'sort' => $key,
                'start_date' => $curr->copy(),
                'total' => 0,
                'pn_breakdown' => [],
                'aircraft_breakdown' => [],
            ];
        }

        // Pre-populate Yearly buckets
        foreach (['2026', '2027'] as $year) {
            $replacementPlans['yearly'][$year] = [
                'key' => $year,
                'label' => $year,
                'sort' => $year,
                'start_date' => \Carbon\Carbon::createFromDate((int)$year, 1, 1)->startOfYear(),
                'total' => 0,
                'pn_breakdown' => [],
                'aircraft_breakdown' => [],
            ];
        }

        foreach ($aircrafts as $aircraft) {
            $reg = $aircraft->registration;
            $acType = $aircraft->type;
            $pnMap = [
                'adult' => ['pn' => $aircraft->pn_adult, 'types' => ['business', 'economy', 'first', 'spare-pax']],
                'crew' => ['pn' => $aircraft->pn_crew, 'types' => ['cockpit', 'attendant']],
                'infant' => ['pn' => $aircraft->pn_infant, 'types' => ['spare-inf']],
            ];

            $acSeats = $seatsByRegistration->get($reg, collect())->filter(fn($s) => !is_null($s->expiry_date));

            foreach ($acSeats as $seat) {
                $expiryDate = \Carbon\Carbon::parse($seat->expiry_date);

                // Only include seats expiring within warning window (expired + < 180 days)
                if ($expiryDate->gt($cutoff)) {
                    continue;
                }

                // Determine which P/N category this seat belongs to
                $seatPn = null;
                $seatCategory = null;
                foreach ($pnMap as $category => $info) {
                    if (in_array($seat->class_type, $info['types'])) {
                        $seatPn = $info['pn'];
                        $seatCategory = $category;
                        break;
                    }
                }
                // Skip if no PN (means no life vest for this category) or unknown class_type
                if (!$seatPn || !$seatCategory) {
                    continue;
                }

                // Decide buckets for all 3 intervals
                $intervals = [];
                if ($expiryDate->lt($today)) {
                    $intervals = [
                        'weekly'  => ['key' => 'overdue', 'label' => 'Overdue', 'sort' => '0000-00'],
                        'monthly' => ['key' => 'overdue', 'label' => 'Overdue', 'sort' => '0000-00'],
                        'yearly'  => ['key' => 'overdue', 'label' => 'Overdue', 'sort' => '0000-00'],
                    ];
                } else {
                    $weekStart = $expiryDate->copy()->startOfWeek();
                    $weekEnd = $expiryDate->copy()->endOfWeek();
                    $intervals['weekly'] = [
                        'key' => $expiryDate->format('o-\WW'),
                        'label' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y'),
                        'sort' => $expiryDate->format('o-W'),
                        'start_date' => $weekStart,
                    ];
                    $intervals['monthly'] = [
                        'key' => $expiryDate->format('Y-m'),
                        'label' => $expiryDate->format('F Y'),
                        'sort' => $expiryDate->format('Y-m'),
                        'start_date' => $expiryDate->copy()->startOfMonth(),
                    ];
                    $intervals['yearly'] = [
                        'key' => $expiryDate->format('Y'),
                        'label' => $expiryDate->format('Y'),
                        'sort' => $expiryDate->format('Y'),
                        'start_date' => $expiryDate->copy()->startOfYear(),
                    ];
                }

                foreach ($intervals as $intervalName => $bucketInfo) {
                    $bucketKey = $bucketInfo['key'];

                    if (!isset($replacementPlans[$intervalName][$bucketKey])) {
                        $replacementPlans[$intervalName][$bucketKey] = [
                            'key' => $bucketKey,
                            'label' => $bucketInfo['label'],
                            'sort' => $bucketInfo['sort'],
                            'start_date' => $bucketInfo['start_date'] ?? null,
                            'total' => 0,
                            'pn_breakdown' => [],
                            'aircraft_breakdown' => [],
                        ];
                    }

                    $replacementPlans[$intervalName][$bucketKey]['total']++;

                    // P/N breakdown
                    $pnKey = $seatPn . '|' . $seatCategory;
                    if (!isset($replacementPlans[$intervalName][$bucketKey]['pn_breakdown'][$pnKey])) {
                        $replacementPlans[$intervalName][$bucketKey]['pn_breakdown'][$pnKey] = [
                            'pn' => $seatPn,
                            'category' => $seatCategory,
                            'count' => 0,
                            'aircraft' => [],
                        ];
                    }
                    $replacementPlans[$intervalName][$bucketKey]['pn_breakdown'][$pnKey]['count']++;

                    if (!isset($replacementPlans[$intervalName][$bucketKey]['pn_breakdown'][$pnKey]['aircraft'][$reg])) {
                        $replacementPlans[$intervalName][$bucketKey]['pn_breakdown'][$pnKey]['aircraft'][$reg] = 0;
                    }
                    $replacementPlans[$intervalName][$bucketKey]['pn_breakdown'][$pnKey]['aircraft'][$reg]++;

                    // Aircraft total breakdown
                    if (!isset($replacementPlans[$intervalName][$bucketKey]['aircraft_breakdown'][$reg])) {
                        $replacementPlans[$intervalName][$bucketKey]['aircraft_breakdown'][$reg] = [
                            'type' => $acType,
                            'count' => 0,
                        ];
                    }
                    $replacementPlans[$intervalName][$bucketKey]['aircraft_breakdown'][$reg]['count']++;
                }
            }
        }

        $criticalBoundary = $today->copy()->addDays(89);  // < 90 days
        $warningBoundary  = $today->copy()->addDays(179); // < 180 days

        foreach (['weekly', 'monthly', 'yearly'] as $intervalName) {
            uasort($replacementPlans[$intervalName], function ($a, $b) {
                return strcmp($a['sort'], $b['sort']);
            });

            foreach ($replacementPlans[$intervalName] as $key => &$bucket) {
                if ($key === 'overdue') {
                    $bucket['urgency'] = 'overdue';
                    $bucket['isCurrentMonth'] = false;
                } else {
                    $start = $bucket['start_date'];
                    
                    if ($start->lte($criticalBoundary)) {
                        $bucket['urgency'] = 'critical';
                    } else {
                        $bucket['urgency'] = 'warning';
                    }

                    if ($intervalName === 'weekly') {
                        $bucket['isCurrentMonth'] = ($key === $today->format('o-\WW'));
                    } elseif ($intervalName === 'monthly') {
                        $bucket['isCurrentMonth'] = ($key === $today->format('Y-m'));
                    } else {
                        $bucket['isCurrentMonth'] = ($key === $today->format('Y'));
                    }
                }
                unset($bucket['start_date']); // Don't need this in frontend
            }
            unset($bucket); // Break reference

            // Remove past empty periods (total=0) that are before the current period.
            // Keep: 'overdue', current period, future periods, and any past period with actual data.
            $currentKeyMap = [
                'weekly'  => $today->format('o-\WW'),
                'monthly' => $today->format('Y-m'),
                'yearly'  => $today->format('Y'),
            ];
            $currentKey = $currentKeyMap[$intervalName];

            $replacementPlans[$intervalName] = array_filter(
                $replacementPlans[$intervalName],
                function ($bucket, $key) use ($currentKey) {
                    // Always keep overdue bucket
                    if ($key === 'overdue') return true;
                    // Always keep buckets with data
                    if ($bucket['total'] > 0) return true;
                    // Keep current and future periods even if empty
                    if (strcmp($bucket['sort'], $currentKey) >= 0) return true;
                    // Remove past empty periods
                    return false;
                },
                ARRAY_FILTER_USE_BOTH
            );
        }

        return [
            'fleet' => $fleet,
            'fleetByAirline' => $fleetByAirline,
            'totalStats' => $totalStats,
            'perFleetStats' => $perFleetStats,
            'lastUpdate' => $lastSeatUpdate ? \Carbon\Carbon::parse($lastSeatUpdate) : null,
            'pnSummary' => $pnSummary,
            'replacementPlans' => $replacementPlans,
        ];
    }); // End Cache

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Fetch non-cached data (real-time logs for admins)
        $data['recentLogs'] = $user && $user->isAdmin() 
            ? ActivityLog::with(['user', 'aircraft'])
                ->whereIn('action', ['update', 'batch', 'pn_update', 'delete', 'import'])
                ->latest()
                ->take(10)
                ->get() 
            : collect();

        return view('dashboard', $data);
    }
}
