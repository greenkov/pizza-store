<?php

namespace App\Services\Ai\Tools;

class ParamsParser
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;

    }
}
