<?php

namespace App\Services\Order\Jobs;

use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDeliveringOrdersJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    private const string DEFAULT_QUEUE = '{ProcessDeliveringOrderQueue}';

    /**
     * @var Order
     */
    private Order $order;

    /**
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order->withoutRelations();
        $this->onQueue(self::DEFAULT_QUEUE);
    }

    public function handle(): void
    {
        app(OrderService::class)->refreshStatus($this->order);
    }

    /**
     * @return string
     */
    public function uniqueId(): string
    {
        return 'ODRER:' . md5($this->order->id) . 'QUEUE:' . $this->queue;
    }
}
