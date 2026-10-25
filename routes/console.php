<?php

use App\Console\Commands\ExpirePendingOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command(ExpirePendingOrders::class)->purpose(
    'Expire pending orders/payments past their expires_at and release reserved stock.'
);
