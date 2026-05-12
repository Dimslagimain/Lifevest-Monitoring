<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Seat;

$out = "";
$lastSeats = Seat::where('registration', 'PK-GFU')
    ->whereIn('seat_id', ['pilot', 'copilot', 'observer1', 'observer2'])
    ->get();

$out .= "Data Cockpit untuk PK-GFU:\n";
foreach ($lastSeats as $seat) {
    $out .= "- ID: {$seat->seat_id} | Expiry: {$seat->expiry_date->format('Y-m-d')} | Status: {$seat->status}\n";
}

$allRegs = Seat::distinct()->pluck('registration');
$out .= "\nRegistrasi yang ada di tabel seats: " . implode(', ', $allRegs->toArray()) . "\n";

file_put_contents('db_result.txt', $out);
echo "Done, results saved to db_result.txt";
