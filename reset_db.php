<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;

$kernel = $app->make(Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\StringInput('migrate:fresh --seed --force'),
    new Symfony\Component\Console\Output\ConsoleOutput()
);

echo "Reset completed with status: " . $status;
