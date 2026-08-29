<?php

namespace App\Services\Ai\Agents;

use App\Services\Ai\Agents\ResponseWrappers\ResponseWrapper;

interface Agent
{
    public function runModel(): ResponseWrapper;

    public function getAgentName(): string;
}
