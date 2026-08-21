<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('carts:prune')->daily();
//
// Schedule::command(PruneAbandonedCarts::class, ['--days' => 7])
//    ->hourly()
//    ->withoutOverlapping()
//    ->onOneServer();
