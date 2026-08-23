<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\GetArrayOptionTrait;
use App\Services\Order\Jobs\ProcessDeliveringOrdersJob;
use App\Services\Order\Jobs\ProcessPaidOrdersJob;
use App\Services\Order\OrderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('order:process-delivering
    {--orderIds= : Order ids to process}
    {--Q|queue= : Queue name}')]
#[Description('Processed delivering orders.')]
class ProcessDeliveringOrders extends Command
{
    use GetArrayOptionTrait;

    public function handle(): void
    {
        $orderIds = $this->getIntArrayOption('orderIds');
        $queue = $this->option('queue');

        $orders = app(OrderService::class)->loadDeliveringOrders($orderIds);
        foreach ($orders as $order) {
            $job = new ProcessDeliveringOrdersJob($order);
            if ($queue !== null) {
                $job->onQueue($queue);
            }
            dispatch($job);

            $this->info("Scheduled processing order: {$order->id} ({$order->status})");
        }
    }
}
