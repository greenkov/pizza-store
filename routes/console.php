<?php

use App\Console\Commands\ProcessDeliveringOrders;
use App\Console\Commands\ProcessPaidOrders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

Schedule::command(ProcessPaidOrders::class)
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command(ProcessDeliveringOrders::class)
//    ->everyMinute()
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();
