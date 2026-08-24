<?php

namespace App\Services\Ai\Tools;

interface Tool
{
    public function definition(): array;

    public function use(array $params): string;
}
