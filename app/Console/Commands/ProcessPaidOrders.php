<?php

namespace App\Console\Commands;

use App\Services\Order\Jobs\ProcessPaidOrdersJob;
use App\Services\Order\OrderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('order:process-paid')]
#[Description('Processed paid orders.')]
class ProcessPaidOrders extends Command
{
    public function handle()
    {
        $orders = app(OrderService::class)->loadPaidOrders();
        foreach ($orders as $order) {
            $this->info("Scheduled processing order: {$order->id} ({$order->status})");
            dispatch(new ProcessPaidOrdersJob($order));
        }
    }
}
