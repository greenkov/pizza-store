<?php

namespace App\Console\Commands;

use App\Services\Ai\Agents\AgentTokensLimitProxy;
use App\Services\Ai\Agents\OrdersAnalyzer\AgentModel as OrdersAnalyzerAgentModel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

#[Signature('ai:orders-analysis {period=week : Time period to analyze (week, month)}')]
#[Description('Command to run AI orders analysis agent for specified period. Produces toppings combination recommendations, sets "hot" flag on popular presets')]
class ChatCommand extends Command
{
    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle()
    {
        $period = $this->argument('period');
        $availablePeriodValues = [OrdersAnalyzerAgentModel::PERIOD_WEEK, OrdersAnalyzerAgentModel::PERIOD_MONTH];
        if (!in_array($period, $availablePeriodValues)) {
            $this->error('Invalid period value. Available values: ' . implode(', ', $availablePeriodValues));

            return;
        }

        $agent = new OrdersAnalyzerAgentModel(period: $period);
        $proxy = new AgentTokensLimitProxy($agent);
        $this->info($proxy->runModel()->getOutputText());
    }
}
