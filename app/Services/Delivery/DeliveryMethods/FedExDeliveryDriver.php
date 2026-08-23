<?php

namespace App\Services\Delivery\DeliveryMethods;

class FedExDeliveryDriver extends AbstractDeliveryMethodDriver
{
    public const string STATUS_DEPARTED = 'departed';

    public const string STATUS_IN_DELIVERY = 'in_delivery';

    public const string STATUS_CUSTOMS_PROCEDURES = 'in_customs_service';

    public const string STATUS_HANDED_OVER = 'handed_over';

    public const string STATUS_REJECTED = 'rejected';

    /**
     * @param  array  $parcelData
     */
    public function sendDataToDeliver(array $parcelData): void
    {
        $this->logInfo('Parcel data sent to delivery service API.', $parcelData);
    }

    /**
     * @param  array  $parcelData
     * @return string
     */
    public function requestParcelStatus(array $parcelData): string
    {
        $availableStatuses = [
            self::STATUS_DEPARTED,
            self::STATUS_IN_DELIVERY,
            self::STATUS_CUSTOMS_PROCEDURES,
            self::STATUS_HANDED_OVER,
            self::STATUS_REJECTED,
        ];

        $statusIndex = array_rand($availableStatuses);
        $statusFromAPI = $availableStatuses[$statusIndex];
        $result = $this->mapStatusToCommon($statusFromAPI);
        $this->logInfo("Parcel status from API: {$statusFromAPI} (mapped: {$result})", $parcelData);

        return $result;
    }

    /**
     * @param  string  $serviceSpecificStatus
     * @return string
     */
    protected function mapStatusToCommon(string $serviceSpecificStatus): string
    {
        return match ($serviceSpecificStatus) {
            self::STATUS_DEPARTED, self::STATUS_IN_DELIVERY, self::STATUS_CUSTOMS_PROCEDURES => AbstractDeliveryMethodDriver::STATUS_IN_PROCESS,
            self::STATUS_HANDED_OVER => AbstractDeliveryMethodDriver::STATUS_DELIVERED,
            self::STATUS_REJECTED => AbstractDeliveryMethodDriver::STATUS_FAILED,
        };
    }

    /**
     * @return string
     */
    protected function getServiceKey(): string
    {
        return DeliveryDriverFactory::DRIVER_FED_EX;
    }
}
