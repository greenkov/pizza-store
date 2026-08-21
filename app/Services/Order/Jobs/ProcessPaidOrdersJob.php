<?php

namespace App\Services\Order\Jobs;

use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPaidOrdersJob implements ShouldQueue
{
    use Queueable;

    public $queue = '{ProcessPaidOrderQueue}';

    /**
     * @var Order
     */
    private Order $order;

    /**
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        app(OrderService::class)->refreshStatus($this->order);
    }
}
