<?php
// Fix PK-GTK spare expiry dates based on real data

use App\Models\Seat;

// PAX expiry dates (1-18)
$paxDates = [
    1  => '2029-06-01',
    2  => '2033-10-01',
    3  => '2033-10-01',
    4  => '2029-10-01',
    5  => '2034-11-01',
    6  => '2031-03-01',
    7  => '2035-03-01',
    8  => '2033-04-01',
    9  => '2029-06-01',
    10 => '2035-03-01',
    11 => '2033-10-01',
    12 => '2031-01-01',
    13 => '2029-06-01',
    14 => '2029-06-01',
    15 => '2027-02-01',
    16 => '2028-07-01',
    17 => '2032-07-01',
    18 => '2029-03-01',
];

// INF expiry dates (1-10)
$infDates = [
    1  => '2029-10-01',
    2  => '2027-09-01',
    3  => '2028-10-01',
    4  => '2029-11-01',
    5  => '2027-10-01',
    6  => '2029-02-01',
    7  => '2027-02-01',
    8  => '2029-06-01',
    9  => '2028-05-01',
    10 => '2033-05-01',
];

// Update PAX
foreach ($paxDates as $num => $date) {
    $updated = Seat::where('registration', 'PK-GTK')
        ->where('seat_id', "pax-$num")
        ->update(['expiry_date' => $date]);
    echo "pax-$num => $date " . ($updated ? '✓' : '✗') . "\n";
}

// Update INF
foreach ($infDates as $num => $date) {
    $updated = Seat::where('registration', 'PK-GTK')
        ->where('seat_id', "inf-$num")
        ->update(['expiry_date' => $date]);
    echo "inf-$num => $date " . ($updated ? '✓' : '✗') . "\n";
}

echo "\n=== Done! ===\n";
echo "PAX: " . Seat::where('registration', 'PK-GTK')->where('seat_id', 'like', 'pax-%')->whereNotNull('expiry_date')->count() . " with dates\n";
echo "INF: " . Seat::where('registration', 'PK-GTK')->where('seat_id', 'like', 'inf-%')->whereNotNull('expiry_date')->count() . " with dates\n";
