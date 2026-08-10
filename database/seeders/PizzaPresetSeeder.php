<?php

namespace Database\Seeders;

use App\Models\PizzaPreset;
use Illuminate\Database\Seeder;
use Illuminate\Database\UniqueConstraintViolationException;

class PizzaPresetSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            retry(3, function () {
                PizzaPreset::factory()->create();
            }, 0, function (UniqueConstraintViolationException $exception) {
                return true;
            });
        }
    }
}
