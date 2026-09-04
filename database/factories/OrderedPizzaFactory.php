<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Models\PizzaPreset;
use App\Models\Topping;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderedPizza>
 */
class OrderedPizzaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'size' => fake()->randomElement(PizzaPreset::$availableSizes),
            'type' => OrderedPizza::TYPE_CUSTOM,
            'preset_id' => null,
            'name' => Str::headline(fake()->words(2, true)),
            'topping_codes' => fake()->randomElements(
                Topping::getAvailableToppingCodes(),
                fake()->numberBetween(2, 4)
            ),
            'price' => fake()->randomFloat(2, 5, 30),
        ];
    }

    /**
     * Build a line ordered from the given preset, keeping type, preset_id,
     * name and topping codes consistent with each other.
     *
     * @param PizzaPreset $preset
     *
     * @return static
     */
    public function forPreset(PizzaPreset $preset): static
    {
        return $this->state([
            'type' => OrderedPizza::TYPE_PRESET,
            'preset_id' => $preset->id,
            'name' => $preset->name,
            'topping_codes' => $preset->topping_codes,
        ]);
    }
}
