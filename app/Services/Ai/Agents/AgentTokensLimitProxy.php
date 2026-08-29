<?php

namespace App\Services\Ai\Agents;

use App\Models\AgentUsage;
use App\Services\Ai\Agents\ResponseWrappers\ResponseWrapper;
use App\Services\Ai\Exceptions\AgentDailyQuotaExceededException;

class AgentTokensLimitProxy implements Agent
{
    /**
     * @var int
     */
    private int $dailyQuota;

    /**
     * @param Agent $agent
     */
    public function __construct(private readonly Agent $agent)
    {
        $this->dailyQuota = config('ai.limits.daily_agent_quota');
    }

    /**
     * @return ResponseWrapper
     *
     * @throws AgentDailyQuotaExceededException
     */
    public function runModel(): ResponseWrapper
    {
        $totalTokensUsedToday = $this->loadTotalDailyUsedTokensByAgent();
        $averageAgentUsePrice = $this->loadAverageAgentUsePrice();

        $tokensLeftToday = $this->dailyQuota - $totalTokensUsedToday;

        if ($tokensLeftToday < $averageAgentUsePrice) {
            throw new AgentDailyQuotaExceededException('Daily quota exceeded for agent: ' . $this->getAgentName());
        }

        return $this->agent->runModel();
    }

    /**
     * @return string
     */
    public function getAgentName(): string
    {
        return $this->agent->getAgentName();
    }

    /**
     * @return int
     */
    private function loadAverageAgentUsePrice(): int
    {
        $conversationUuids = AgentUsage::query()
            ->select(['conversation_uuid'])
            ->where('agent_name', $this->getAgentName())
            ->distinct()
            ->orderByDesc('id')
            ->limit(3)
            ->pluck('conversation_uuid')
            ->toArray();

        $totals = AgentUsage::query()
            ->selectRaw('conversation_uuid, SUM(total_tokens) AS total_tokens')
            ->whereIn('conversation_uuid', $conversationUuids)
            ->groupBy('conversation_uuid')
            ->pluck('total_tokens')
            ->toArray();

        return (int) (array_sum($totals) / count($conversationUuids));
    }

    /**
     * @return int
     */
    private function loadTotalDailyUsedTokensByAgent(): int
    {
        return AgentUsage::select(['total_tokens'])
            ->where('agent_name', $this->getAgentName())
            ->whereDate('created_at', today())
            ->sum('total_tokens');
    }
}
