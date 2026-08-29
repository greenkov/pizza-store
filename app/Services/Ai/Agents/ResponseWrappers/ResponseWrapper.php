<?php

namespace App\Services\Ai\Agents\ResponseWrappers;

use App\Services\Ai\Agents\TrackingMeta;
use Arr;

final readonly class ResponseWrapper implements WithRawResponse
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
     * @return array
     */
    public function getFunctionCalls(): array
    {
        return array_filter($this->getOutputItems(), static function ($item) {
            return ($item['type'] ?? '') === 'function_call';
        });
    }

    /**
     * @return string
     */
    public function getOutputText(): string
    {
        $texts = $this->getMessageStrings();

        return implode("\n", $texts);
    }

    /**
     * @return array
     */
    public function getOutputItems(): array
    {
        return Arr::get($this->response, 'output', []) ?? [];
    }

    /**
     * @return array
     */
    private function getMessageStrings(): array
    {
        $messageOutputItems = array_filter($this->getOutputItems(), static function ($item) {
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

    /**
     * @return TrackingMeta
     */
    public function getTrackingMeta(): TrackingMeta
    {
        $metadata = Arr::get($this->response, 'metadata', []);

        return TrackingMeta::buildFromArray($metadata);
    }

    /**
     * @return array
     */
    public function getRawResponse(): array
    {
        return $this->response;
    }
}
