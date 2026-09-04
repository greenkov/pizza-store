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
    private const MIN_TOPPINGS = 2;

    private const MAX_TOPPINGS = 6;

    /**
     * Every topping combination the factory may draw, encoded as a bitmask over the
     * catalog. `pizza_presets.topping_codes` carries a unique index, and one mask maps
     * to exactly one sorted set, so drawing masks through fake()->unique() is what stops
     * bulk creation from colliding.
     *
     * @var int[]|null
     */
    private static ?array $combinationMasks = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => Str::headline(fake()->words(2, true)),
            'topping_codes' => fn () => $this->drawDistinctToppingCodes(),
        ];
    }

    /**
     * @return string[]
     */
    private function drawDistinctToppingCodes(): array
    {
        $codes = Topping::getAvailableToppingCodes();
        $mask = fake()->unique()->randomElement(self::combinationMasks(count($codes)));

        $result = [];
        foreach ($codes as $index => $code) {
            if (($mask & (1 << $index)) !== 0) {
                $result[] = $code;
            }
        }

        return $result;
    }

    /**
     * @param int $catalogSize
     *
     * @return int[]
     */
    private static function combinationMasks(int $catalogSize): array
    {
        if (self::$combinationMasks !== null) {
            return self::$combinationMasks;
        }

        $masks = [];
        for ($mask = 1; $mask < (1 << $catalogSize); $mask++) {
            $toppingCount = substr_count(decbin($mask), '1');
            if ($toppingCount >= self::MIN_TOPPINGS && $toppingCount <= self::MAX_TOPPINGS) {
                $masks[] = $mask;
            }
        }

        return self::$combinationMasks = $masks;
    }
}
