<?php

namespace App\Console\Commands;

use App\Services\Ai\OrdersAnalyzer\AgentModel as OrdersAnalyzerAgentModel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

#[Signature('chat')]
#[Description('Command description')]
class ChatCommand extends Command
{
    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function handle()
    {
        $agent = new OrdersAnalyzerAgentModel;
        $this->info($agent->runModel(OrdersAnalyzerAgentModel::PERIOD_WEEK));
    }
}
