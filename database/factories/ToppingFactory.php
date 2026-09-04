<?php

namespace Database\Factories;

use App\Models\Topping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Topping>
 */
class ToppingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function definition(): array
    {
        return [
            'code' => fn () => fake()->unique()->randomElement(Topping::getAvailableToppingCodes()),
            'name' => fn (array $attributes) => Topping::getNameByCode($attributes['code']),
            'md_cal' => fn (array $attributes) => config("calories.toppings.{$attributes['code']}.cal"),
            'md_price' => fn (array $attributes) => config("calories.toppings.{$attributes['code']}.price"),
        ];
    }

    /**
     * Build the topping described by the given catalog code.
     *
     * @throws \Exception
     */
    public function code(string $code): static
    {
        return $this->state($this->attributesForCode($code));
    }

    /**
     * @return array{code: string, name: string, md_cal: int, md_price: float}
     *
     * @throws \Exception
     */
    private function attributesForCode(string $code): array
    {
        return [
            'code' => $code,
            'name' => Topping::getNameByCode($code),
            'md_cal' => config("calories.toppings.$code.cal"),
            'md_price' => config("calories.toppings.$code.price"),
        ];
    }
}
