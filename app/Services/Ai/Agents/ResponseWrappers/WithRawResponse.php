<?php

namespace App\Services\Ai\Agents\ResponseWrappers;

interface WithRawResponse
{
    public function getRawResponse(): array;
}
