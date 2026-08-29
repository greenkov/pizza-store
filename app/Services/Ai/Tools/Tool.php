<?php

namespace App\Services\Ai\Tools;

use App\Services\Ai\Agents\TrackingMeta;

interface Tool
{
    public function definition(): array;

    public function use(array $params, TrackingMeta $trackingMeta): string;
}
