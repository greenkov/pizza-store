<?php

namespace App\Services\Ai;

use Arr;

final readonly class ResponseWrapper
{
    /**
     * @param array $response
     */
    public function __construct(private array $response) {}

    /**
     * @return bool
     */
    public function hasFunctionCalls(): bool
    {
        return count($this->getFunctionCalls()) > 0;
    }

    /**
     * @return string
     */
    public function getOutputText(): string
    {
        $texts = $this->getMessagesText();

        return implode("\n", $texts);
    }

    /**
     * @return array
     */
    public function getOutput(): array
    {
        return Arr::get($this->response, 'output', []) ?? [];
    }

    /**
     * @return array
     */
    public function getFunctionCalls(): array
    {
        return array_filter($this->getOutput(), static function ($item) {
            return ($item['type'] ?? '') === 'function_call';
        });
    }

    /**
     * @return array
     */
    public function getMessagesText(): array
    {
        $messageOutputItems = array_filter($this->getOutput(), static function ($item) {
            return ($item['type'] ?? '') === 'message';
        });

        $result = array_map(static function ($messageItem) {
            $contentArray = $messageItem['content'] ?? [];

            return array_map(static function ($item) {
                $type = $item['type'] ?? '';
                if ($type !== 'output_text') {
                    return null;
                }

                return $item['text'];
            }, $contentArray);
        }, $messageOutputItems);

        return array_values(array_filter(Arr::flatten($result)));
    }
}
