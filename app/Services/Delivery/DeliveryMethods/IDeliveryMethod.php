<?php

namespace App\Services\Delivery\DeliveryMethods;

interface IDeliveryMethod
{
    public function sendDataToDeliver(array $parcelData);

    public function requestParcelStatus(array $parcelData): string;
}
