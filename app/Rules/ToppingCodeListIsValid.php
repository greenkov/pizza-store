<?php

namespace App\Rules;

use App\Models\PizzaPreset;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ToppingCodeListIsValid implements ValidationRule
{
    /**
     * @param Closure(string, ?string=): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $toppingCodes = $value;
        sort($toppingCodes);
        $toppingCodesJsonString = json_encode($toppingCodes);

        /** @var \App\Models\PizzaPreset|null $existingPizza */
        $existingPizza = PizzaPreset::where('topping_codes', $toppingCodesJsonString)
            ->get(['id', 'name'])
            ->first();
        if ($existingPizza !== null) {
            $fail("Pizza with such topping set already exists: '{$existingPizza->name}'.");
        }
    }
}
