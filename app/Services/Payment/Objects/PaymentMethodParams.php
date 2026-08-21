<?php

namespace App\Services\Payment\Objects;

class PaymentMethodParams
{
    /**
     * @param  string  $paymentMethod
     * @param  array  $paymentDetails
     */
    private function __construct(
        public readonly string $paymentMethod,
        public readonly array $paymentDetails,
    ) {}

    /**
     * @param  array  $params
     * @return self
     */
    public static function buildFromRequestParams(array $params): self
    {
        $paymentMethod = $params['payment_method'] ?? null;
        $paymentDetails = $params['payment_details'] ?? [];

        return new self($paymentMethod, $paymentDetails);
    }

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @return array
     */
    public function getPaymentDetails(): array
    {
        return $this->paymentDetails;
    }
}
