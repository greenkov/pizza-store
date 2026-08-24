<?php

namespace App\Services\Ai\Tools;

use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use Throwable;

abstract class AbstractTool implements Tool
{
    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param string $message
     * @param array $context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->getLogger()->info("{$this->getName()}: {$message}", $context);
    }

    /**
     * @param string $message
     * @param array $context
     * @param Throwable|null $throwable
     */
    protected function logError(string $message, array $context = [], ?Throwable $throwable = null): void
    {
        $additionalContext = [...$context];
        if ($throwable) {
            $additionalContext['exception'] = $throwable->getMessage();
        }

        $this->getLogger()->error("{$this->getName()}: {$message}", $additionalContext);
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        return Log::channel('ai_logs');
    }
}
