<?php

namespace App\Services\Ai\Agents\ResponseWrappers;

use App\Models\AgentUsage;
use App\Services\Ai\Agents\TrackingMeta;
use Arr;

class TokensLoggerWrapper implements WithRawResponse
{
    public function __construct(private readonly array $response, private TrackingMeta $metadata) {}

    public function getRawResponse(): array
    {
        $inputTokens = Arr::get($this->response, 'usage.input_tokens', 0);
        $outputTokens = Arr::get($this->response, 'usage.output_tokens', 0);
        $totalTokens = Arr::get($this->response, 'usage.total_tokens', 0);

        AgentUsage::create([
            'agent_name' => $this->metadata->agentName,
            'conversation_uuid' => $this->metadata->conversationUuid,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
        ]);

        return $this->response;
    }
}
