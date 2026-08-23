<?php

namespace App\Services\Delivery\DeliveryMethods;

use App\Services\Delivery\Exceptions\UnsupportedDeliveryMethodException;

class DeliveryDriverFactory
{
    public const string DRIVER_DHL = 'dhl';

    public const string DRIVER_FED_EX = 'fed_ex';

    public const array AVAILABLE_DRIVERS = [
        self::DRIVER_DHL,
        self::DRIVER_FED_EX,
    ];

    /**
     * @param  string  $deliveryDriver
     * @return IDeliveryMethod
     *
     * @throws UnsupportedDeliveryMethodException
     */
    public static function make(string $deliveryDriver): IDeliveryMethod
    {
        return match ($deliveryDriver) {
            self::DRIVER_DHL => new DHLDeliveryDriver,
            self::DRIVER_FED_EX => new FedExDeliveryDriver,
            default => throw new UnsupportedDeliveryMethodException,
        };
    }
}
