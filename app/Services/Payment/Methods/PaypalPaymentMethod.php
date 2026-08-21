<?php

namespace App\Services\Payment\Methods;

use App\Services\Payment\BasicPaymentMethod;
use App\Services\Payment\Exceptions\PaymentCredentialsInvalidException;
use App\Services\Payment\Exceptions\PaymentLimitExceededException;

class PaypalPaymentMethod extends BasicPaymentMethod
{
    private ?string $email;

    public function __construct(array $params)
    {
        parent::__construct();

        $this->email = $params['email'] ?? null;
    }

    /**
     * @param  float  $total
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

        if ($this->email === null) {
            $this->logError('Specified params are not valid', [
                'email' => $this->email,
            ]);

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function getMethodKey(): string
    {
        return BasicPaymentMethod::PAY_PAL;
    }
}
