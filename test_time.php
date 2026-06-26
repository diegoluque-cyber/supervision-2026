<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$f = \App\Models\Falta::latest('id')->first();
if ($f) {
    echo "ID: " . $f->id . "\n";
    echo "fecha_falta (Raw string from DB): " . $f->getAttributes()['fecha_falta'] . "\n";
    echo "fecha_falta (Carbon instance): " . $f->fecha_falta . "\n";
    echo "app.timezone: " . config('app.timezone') . "\n";
    echo "Carbon now: " . \Carbon\Carbon::now() . "\n";
} else {
    echo "No records found\n";
}
unlink(__FILE__); // self-delete
