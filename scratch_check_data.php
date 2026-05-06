<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Seat;
use Carbon\Carbon;

$count2027 = Seat::where('expiry_date', '>=', '2027-01-01')->count();
echo "Seats expiring in 2027 or later: " . $count2027 . "\n";

$maxDate = Seat::max('expiry_date');
echo "Max expiry date: " . $maxDate . "\n";
