<?php

namespace App\Objects;

use App\Models\PizzaPreset;
use App\Models\Topping;
use Arr;
use Illuminate\Support\Collection;

class CartPresenter
{
    public static function present(array $cartData): array
    {
        if ($cartData === []) {
            return ['items' => [], 'total' => 0.0];
        }

        $toppings = Topping::all()->keyBy('code');
        $presetNames = PizzaPreset::whereIn('id', array_filter(array_column($cartData, 'preset_id')))
            ->pluck('name', 'id');

        $items = [];

        foreach ($cartData as $item) {
            $quantity = $item['quantity'] ?? 1;
            $items[] = [
                'id' => $item['id'],
                'name' => $item['preset_id'] !== null
                    ? ($presetNames[$item['preset_id']] ?? __('Unavailable pizza'))
                    : Arr::get($item, 'name', __('Custom pizza')),
                'size' => $item['size'],
                'toppings' => array_map(
                    static fn (string $code): string => $toppings[$code]->name ?? $code,
                    $item['topping_codes'],
                ),
                'price' => round(self::priceFor($item['topping_codes'], $item['size'], $toppings) * $quantity, 2),
                'quantity' => $quantity,
            ];
        }

        return [
            'items' => $items,
            'total' => round(array_sum(array_column($items, 'price')), 2),
        ];
    }

    private static function priceFor(array $toppingCodes, string $size, Collection $toppings): float
    {
        $coefficient = $size === PizzaPreset::SIZE_MEDIUM
            ? 1.0
            : (float) config("calories.{$size}_coefficient", 1);

        $toppingPrices = 0.0;

        foreach ($toppingCodes as $code) {
            $toppingPrices += (float) ($toppings[$code]->md_price ?? 0);
        }

        return round(((float) config('calories.md_base_price') + $toppingPrices) * $coefficient, 2);
    }
}
