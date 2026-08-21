<?php

namespace App\Services\Payment\Methods;

use App\Services\Payment\BasicPaymentMethod;
use App\Services\Payment\Exceptions\PaymentCredentialsInvalidException;
use App\Services\Payment\Exceptions\PaymentLimitExceededException;

class MastercardPaymentMethod extends BasicPaymentMethod
{
    /**
     * @var string|mixed|null
     */
    private ?string $cardNumber;

    /**
     * @var string|mixed|null
     */
    private ?string $cvv;

    /**
     * @var string|mixed|null
     */
    private ?string $expiration;

    /**
     * @param  array  $params
     */
    public function __construct(array $params)
    {
        parent::__construct();

        $this->cardNumber = $params['card_number'] ?? null;
        $this->cvv = $params['cvv'] ?? null;
        $this->expiration = $params['expires_at'] ?? null;
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

        if ($this->cardNumber === null || $this->cvv === null || $this->expiration === null) {
            $this->logError('Specified params are not valid', [
                'cardNumber' => $this->cardNumber,
                'cvv' => $this->cvv ? 'set' : 'null',
                'expiration' => $this->expiration,
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
        return BasicPaymentMethod::MASTERCARD;
    }
}
