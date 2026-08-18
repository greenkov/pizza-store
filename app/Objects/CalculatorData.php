<?php

namespace App\Objects;

class CalculatorData
{
    public static function getData(): array
    {
        return [
            'md_base_cal' => config('calories.md_base_cal'),
            'md_base_price' => config('calories.md_base_price'),
            'sm_coefficient' => config('calories.sm_coefficient'),
            'lg_coefficient' => config('calories.lg_coefficient'),
        ];
    }
}
