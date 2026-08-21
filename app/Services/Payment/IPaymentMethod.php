<?php

namespace App\Services\Payment;

interface IPaymentMethod
{
    public function pay(int $orderId, float $total): void;
}
