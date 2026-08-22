<?php

namespace App\Services\Payment;

use App\Services\Payment\Exceptions\PaymentCredentialsInvalidException;
use App\Services\Payment\Exceptions\PaymentLimitExceededException;
use Arr;
use Log;
use Psr\Log\LoggerInterface;
use Str;
use Throwable;

abstract class BasicPaymentMethod implements IPaymentMethod
{
    public const string MASTERCARD = 'mastercard';

    public const string VISA = 'visa';

    public const string PAY_PAL = 'pay_pal';

    public const string GOODLE_PAY = 'google_pay';

    /**
     * @var string[]
     */
    public static $availableMethods = [
        self::MASTERCARD,
        self::VISA,
        self::PAY_PAL,
        self::GOODLE_PAY,
    ];

    /**
     * @var float
     */
    protected float $fee = 0.0;

    /**
     * @var float
     */
    protected float $paymentLimit = 0.0;

    /**
     * @var int|null
     */
    private ?int $orderId = null;

    public function __construct()
    {
        $data = config("payment.methods.{$this->getMethodKey()}");
        $limit = Arr::get($data, 'limit', 0);
        $feePercent = Arr::get($data, 'fee_percent', 0);

        $this->fee = $feePercent / 100.0;
        $this->paymentLimit = $limit;
    }

    /**
     * @return string
     */
    abstract protected function getMethodKey(): string;

    /**
     * @return bool
     */
    abstract protected function validateCredentials(): bool;

    /**
     * @param int $orderId
     */
    public function setOrderId(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @param  float  $total
     * @return float
     *
     * @throws PaymentCredentialsInvalidException
     * @throws PaymentLimitExceededException
     */
    public function prepare(float $total): float
    {
        $this->logInfo('Handling prerequisites...');

        $updatedTotal = $this->updateTotal($total);
        $this->validate($updatedTotal);

        $this->logInfo('Retrieving token...');

        $this->logInfo('Validation complete.');

        return $updatedTotal;
    }

    /**
     * @param  float  $total
     *
     * @throws PaymentCredentialsInvalidException
     * @throws PaymentLimitExceededException
     */
    public function validate(float $total): void
    {
        if (! $this->validateCredentials()) {
            $this->logError('Invalid credentials.');
            throw new PaymentCredentialsInvalidException('Invalid credentials.');
        }
        $this->validateTotal($total);
    }

    /**
     * @param  float  $total
     *
     * @throws PaymentLimitExceededException
     */
    protected function validateTotal(float $total): void
    {
        if ($this->paymentLimit > 0 && $total > $this->paymentLimit) {
            $this->logError('Payment limit exceeded', [
                'limit' => $this->paymentLimit,
                'total' => $total,
            ]);
            throw new PaymentLimitExceededException('Payment limit exceeded');
        }
    }

    /**
     * @param  string  $message
     * @param  array  $context
     */
    public function logInfo(string $message, array $context = []): void
    {
        $this->getLogger()->info("{$this->getLogPrefix()}: (OID:{$this->orderId}) {$message}", $context);
    }

    /**
     * @param  string  $message
     * @param  array  $context
     * @param  Throwable|null  $throwable
     */
    public function logError(string $message, array $context = [], ?Throwable $throwable = null): void
    {
        $additionalData = [...$context];
        if ($throwable !== null) {
            $additionalData['exception'] = $throwable->getMessage();
        }
        $this->getLogger()->error("{$this->getLogPrefix()}: (OID:{$this->orderId}) {$message}", $additionalData);
    }

    /**
     * @return string
     */
    private function getLogPrefix(): string
    {
        return Str::upper($this->getMethodKey());
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        return Log::channel('payment_logs');
    }

    /**
     * @param  float  $total
     * @return float
     */
    private function updateTotal(float $total): float
    {
        return $total * (1 + $this->fee);
    }
}
