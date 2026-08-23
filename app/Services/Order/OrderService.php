<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Services\Cart\Objects\CartDTO;
use App\Services\Delivery\DeliveryMethods\AbstractDeliveryMethodDriver;
use App\Services\Delivery\DeliveryMethods\DeliveryDriverFactory;
use App\Services\Delivery\DeliveryService;
use App\Services\Order\Exceptions\InvalidOrderException;
use Illuminate\Support\Collection;

class OrderService
{
    /**
     * @param CartDTO $cart
     *
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

        $deliveryServiceKey = DeliveryDriverFactory::AVAILABLE_DRIVERS[array_rand(DeliveryDriverFactory::AVAILABLE_DRIVERS)];
        $order = Order::create([
            'user_id' => auth()->user()->id,
            'status' => Order::STATUS_PENDING,
            'total_price' => $totalPrice,
            'delivery_key' => $deliveryServiceKey,
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
     * @param array|null $orderIds
     *
     * @return Collection
     */
    public function loadPaidOrders(?array $orderIds = []): Collection
    {
        return Order::where('status', Order::STATUS_PAID)
            ->when($orderIds && count($orderIds) > 0, function ($query) use ($orderIds) {
                $query->whereIn('id', $orderIds);
            })
            ->get();
    }

    /**
     * @param array|null $orderIds
     *
     * @return Collection
     */
    public function loadDeliveringOrders(?array $orderIds = []): Collection
    {
        return Order::where('status', Order::STATUS_DELIVERING)
            ->when($orderIds && count($orderIds) > 0, function ($query) use ($orderIds) {
                $query->whereIn('id', $orderIds);
            })
            ->get();
    }

    /**
     * @param Order $order
     */
    public function sendDeliveryRequest(Order $order): void
    {
        $deliveryServiceKey = $order->delivery_key ?? DeliveryDriverFactory::DRIVER_DHL;
        $deliveryService = app(DeliveryService::class, ['deliveryDriver' => $deliveryServiceKey]);
        $deliveryService->requestDelivery($order);
        $order->update(['status' => Order::STATUS_DELIVERING]);
    }

    /**
     * @param Order $order
     */
    public function refreshStatus(Order $order): void
    {
        $deliveryServiceKey = $order->delivery_key ?? DeliveryDriverFactory::DRIVER_DHL;
        $deliveryService = app(DeliveryService::class, ['deliveryDriver' => $deliveryServiceKey]);
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
                $order->update([
                    'status' => Order::STATUS_CANCELED,
                    'cancellation_reason' => 'Delivery failed',
                ]);
                break;
            default:
        }
    }
}
