<?php

namespace App\Services\Ai\Agents;

use Arr;

class TrackingMeta
{
    /**
     * @param string $agentName
     * @param string|null $conversationUuid
     */
    public function __construct(public readonly string $agentName, public ?string $conversationUuid = null)
    {
        if ($this->conversationUuid === null) {
            $this->conversationUuid = uuid_create();
        }
    }

    /**
     * @param array $data
     *
     * @return TrackingMeta
     */
    public static function buildFromArray(array $data): TrackingMeta
    {
        $agentName = Arr::get($data, 'agent_name');
        $conversationUuid = Arr::get($data, 'conversation_uuid');

        return new self($agentName, $conversationUuid);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'agent_name' => $this->agentName,
            'conversation_uuid' => $this->conversationUuid,
        ];
    }
}
