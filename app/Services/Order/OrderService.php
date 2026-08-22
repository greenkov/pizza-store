<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Services\Cart\Objects\CartDTO;
use App\Services\Delivery\DeliveryMethods\AbstractDeliveryMethodDriver;
use App\Services\Delivery\DeliveryMethods\DeliveryDriverFactory;
use App\Services\Delivery\DeliveryService;
use App\Services\Order\Exceptions\InvalidOrderException;
use App\Services\Order\Middleware\HandleOrderControllerExceptionsMiddleware;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * @param  CartDTO  $cart
     * @return Order
     *
     * @throws InvalidOrderException
     */
    public function createPendingOrder(CartDTO $cart): Order
    {
        if (count($cart->getItems()) === 0) {
            throw new InvalidOrderException('Order is empty.');
        }

        $totalPrice = $cart->calcPrice();

        $order = Order::create([
            'user_id' => auth()->user()->id,
            'status' => Order::STATUS_PENDING,
            'total_price' => $totalPrice,
        ]);

        foreach ($cart->getItems() as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $order->orderedPizzas()->create([
                    'name' => $item->name,
                    'size' => $item->size,
                    'type' => $item->presetId ? OrderedPizza::TYPE_PRESET : OrderedPizza::TYPE_CUSTOM,
                    'preset_id' => $item->presetId,
                    'topping_codes' => $item->toppingsList,
                    'price' => $item->calcPrice(),
                ]);
            }
        }

        return $order;
    }

    /**
     * @return Collection|Order[]
     */
    public function loadPaidOrders(): Collection
    {
        return Order::where('user_id', auth()->id())
            ->where('status', Order::STATUS_PAID)
            ->get();
    }

    /**
     * @param  Order  $order
     */
    public function refreshStatus(Order $order): void
    {
        $deliveryService = app(DeliveryService::class, ['deliveryDriver' => DeliveryDriverFactory::DRIVER_DHL]);
        $newStatus = $deliveryService->requestParcelStatus($order);

        switch ($newStatus) {
            case AbstractDeliveryMethodDriver::STATUS_PENDING:
            case AbstractDeliveryMethodDriver::STATUS_IN_PROCESS:
                $order->update(['status' => Order::STATUS_DELIVERING]);
                break;
            case AbstractDeliveryMethodDriver::STATUS_DELIVERED:
                $order->update(['status' => Order::STATUS_COMPLETED]);
                break;
            case AbstractDeliveryMethodDriver::STATUS_FAILED:
                $order->update(['status' => Order::STATUS_CANCELED]);
                break;
            default:
        }
    }
}
