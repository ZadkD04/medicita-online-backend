<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tokens = \DB::table('personal_access_tokens')->get();
echo "TOKENS:\n";
foreach ($tokens as $t) {
    echo "Tokenable ID: {$t->tokenable_id} | Name: {$t->name} | Last used: {$t->last_used_at} | Created: {$t->created_at}\n";
}

$citas = App\Models\Cita::all();
echo "CITAS:\n";
foreach ($citas as $c) {
    echo "ID: {$c->id} | Doctor: {$c->doctor_id} | Patient: {$c->patient_id} | Date: {$c->appointment_date} {$c->appointment_time}\n";
}

