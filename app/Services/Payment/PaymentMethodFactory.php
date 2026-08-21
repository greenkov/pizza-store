<?php

namespace App\Services\Payment;

use App\Services\Payment\Exceptions\UnsupportedPaymentMethodException;
use App\Services\Payment\Methods\GooglePayPaymentMethod;
use App\Services\Payment\Methods\MastercardPaymentMethod;
use App\Services\Payment\Methods\PaypalPaymentMethod;
use App\Services\Payment\Methods\VisaPaymentMethod;

class PaymentMethodFactory
{
    /**
     * @param  string  $paymentMethod
     * @param  array  $credentials
     * @return IPaymentMethod
     *
     * @throws UnsupportedPaymentMethodException
     */
    public static function make(string $paymentMethod, array $credentials = []): IPaymentMethod
    {
        return match ($paymentMethod) {
            BasicPaymentMethod::MASTERCARD => new MastercardPaymentMethod($credentials),
            BasicPaymentMethod::VISA => new VisaPaymentMethod($credentials),
            BasicPaymentMethod::PAY_PAL => new PaypalPaymentMethod($credentials),
            BasicPaymentMethod::GOODLE_PAY => new GooglePayPaymentMethod,
            default => throw new UnsupportedPaymentMethodException,
        };
    }
}
