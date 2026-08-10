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
        return $this->attributesForCode(
            fake()->randomElement(Topping::getAvailableToppingCodes())
        );
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
     * @return array{code: string, name: string, md_cal: int}
     *
     * @throws \Exception
     */
    private function attributesForCode(string $code): array
    {
        return [
            'code' => $code,
            'name' => Topping::getNameByCode($code),
            'md_cal' => config("calories.toppings.$code"),
        ];
    }
}
