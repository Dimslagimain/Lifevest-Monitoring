<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Seat;

$count = 0;
Seat::all()->each(function($s) use (&$count) {
    $oldReg = $s->registration;
    $newReg = trim($oldReg);
    if ($oldReg !== $newReg) {
        $s->update(['registration' => $newReg]);
        $count++;
    }
});

echo "Selesai! $count data berhasil dibersihkan dari spasi.";
