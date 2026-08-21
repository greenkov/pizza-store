<?php

namespace App\Services\Delivery\DeliveryMethods;

class DHLDeliveryDriver extends AbstractDeliveryMethodDriver
{
    public const string STATUS_PICKED_UP = 'picked_up';

    public const string STATUS_ON_ITS_WAY = 'on_its_way';

    public const string STATUS_HANDED_OVER_TO_RECEPIENT = 'handed_over_to_recepient';

    public const string STATUS_PACKAGE_LOST = 'package_lost';

    public const string STATUS_REJECTED = 'rejected';

    /**
     * @param  array  $parcelData
     */
    public function sendDataToDeliver(array $parcelData)
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
            self::STATUS_PICKED_UP,
            self::STATUS_ON_ITS_WAY,
            self::STATUS_HANDED_OVER_TO_RECEPIENT,
            self::STATUS_PACKAGE_LOST,
            self::STATUS_REJECTED,
        ];

        $statusIndex = array_rand($availableStatuses);
        $statusFromAPI = $availableStatuses[$statusIndex];
        $result = $this->mapStatusToCommon($statusFromAPI);
        $this->logInfo("Parcel status from API: {$statusFromAPI} (mapped: {$result})");

        return $result;
    }

    /**
     * @param  string  $serviceSpecificStatus
     * @return string
     */
    protected function mapStatusToCommon(string $serviceSpecificStatus): string
    {
        return match ($serviceSpecificStatus) {
            self::STATUS_PICKED_UP, self::STATUS_ON_ITS_WAY => AbstractDeliveryMethodDriver::STATUS_IN_PROCESS,
            self::STATUS_HANDED_OVER_TO_RECEPIENT => AbstractDeliveryMethodDriver::STATUS_DELIVERED,
            self::STATUS_PACKAGE_LOST, self::STATUS_REJECTED => AbstractDeliveryMethodDriver::STATUS_FAILED,
        };
    }

    /**
     * @return string
     */
    protected function getServiceKey(): string
    {
        return 'DHL';
    }
}
