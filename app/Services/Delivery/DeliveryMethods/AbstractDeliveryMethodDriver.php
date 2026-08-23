<?php

namespace App\Services\Delivery\DeliveryMethods;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractDeliveryMethodDriver implements IDeliveryMethod
{
    const string STATUS_DELIVERED = 'delivered';

    const string STATUS_IN_PROCESS = 'in_process';

    const string STATUS_PENDING = 'pending';

    const string STATUS_FAILED = 'failed';

    abstract public function sendDataToDeliver(array $parcelData): void;

    abstract public function requestParcelStatus(array $parcelData): string;

    abstract protected function mapStatusToCommon(string $serviceSpecificStatus): string;

    abstract protected function getServiceKey(): string;

    /**
     * @param  string  $message
     * @param  array  $context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $prefix = Str::upper($this->getServiceKey());

        $this->getLogger()->info("{$prefix}: {$message}", $context);
    }

    /**
     * @param  string  $message
     * @param  array  $context
     * @param  Throwable|null  $throwable
     */
    protected function logError(string $message, array $context = [], ?Throwable $throwable = null): void
    {
        $additionalContext = [...$context];
        if ($throwable) {
            $additionalContext['exception'] = $throwable->getMessage();
        }

        $prefix = Str::upper($this->getServiceKey());

        $this->getLogger()->error("{$prefix}: {$message}", $additionalContext);
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        return Log::channel('delivery_logs');
    }
}
