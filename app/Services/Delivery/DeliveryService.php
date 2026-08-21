<?php

namespace App\Services\Delivery;

use App\Models\Order;
use App\Services\Delivery\DeliveryMethods\DeliveryDriverFactory;
use App\Services\Delivery\DeliveryMethods\IDeliveryMethod;
use App\Services\Delivery\Exceptions\UnsupportedDeliveryMethodException;

class DeliveryService
{
    private IDeliveryMethod $deliveryDriver;

    /**
     * @param  string  $deliveryDriver
     *
     * @throws UnsupportedDeliveryMethodException
     */
    public function __construct(string $deliveryDriver = DeliveryDriverFactory::DRIVER_FED_EX)
    {
        $this->deliveryDriver = DeliveryDriverFactory::make($deliveryDriver);
    }

    /**
     * @param  Order  $order
     * @return string
     */
    public function requestParcelStatus(Order $order): string
    {
        return $this->deliveryDriver->requestParcelStatus($order->toArray());
    }

    /**
     * @param  Order  $order
     * @return void
     */
    private function requestDelivery(Order $order): void
    {
        $this->deliveryDriver->sendDataToDeliver($order->toArray());
    }
}
