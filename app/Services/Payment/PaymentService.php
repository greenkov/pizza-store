<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Services\Payment\Exceptions\UnsupportedPaymentMethodException;
use App\Services\Payment\Objects\PaymentMethodParams;

class PaymentService
{
    /**
     * @param  Order  $order
     * @param  PaymentMethodParams  $paymentParams
     *
     * @throws UnsupportedPaymentMethodException
     */
    public function payOrder(Order $order, PaymentMethodParams $paymentParams): void
    {
        $paymentMethod = PaymentMethodFactory::make($paymentParams->paymentMethod, $paymentParams->paymentDetails);
        $paymentMethod->pay($order->id, $order->total_price);
    }
}
