<?php

namespace Database\Factories;

use App\Models\PizzaPreset;
use App\Models\Topping;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PizzaPreset>
 */
class PizzaPresetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::headline(fake()->words(2, true)),
            'topping_codes' => fake()->randomElements(
                Topping::getAvailableToppingCodes(),
                fake()->numberBetween(2, 6)
            ),
        ];
    }
}
