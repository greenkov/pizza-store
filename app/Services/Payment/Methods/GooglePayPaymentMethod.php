<?php

namespace App\Services\Payment\Methods;

use App\Services\Payment\BasicPaymentMethod;
use App\Services\Payment\Exceptions\PaymentCredentialsInvalidException;
use App\Services\Payment\Exceptions\PaymentLimitExceededException;
use Str;

class GooglePayPaymentMethod extends BasicPaymentMethod
{
    /**
     * @param int $orderId
     * @param float $total
     *
     * @throws PaymentCredentialsInvalidException
     * @throws PaymentLimitExceededException
     */
    public function pay(int $orderId, float $total): void
    {
        $this->setOrderId($orderId);
        $updatedTotal = $this->prepare($total);

        // TODO: Process actual payment
        $this->logInfo('Processing payment...');

        $this->logInfo("Payment successful: \${$updatedTotal}");
    }

    /**
     * @return bool
     */
    public function validateCredentials(): bool
    {
        // TODO: Additional validation procedures.

        return true;
    }

    /**
     * @return string
     */
    protected function getMethodKey(): string
    {
        return BasicPaymentMethod::GOODLE_PAY;
    }
}
