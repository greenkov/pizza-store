<?php

namespace App\Rules;

use App\Models\Topping;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ToppingCodesInPizzaOrder implements ValidationRule
{
    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null) {
            return;
        }

        if (count($value) === 0) {
            $fail('Topping codes should not be empty.');
        }

        $toppingCodes = array_unique($value);
        $validToppingsCount = Topping::whereIn('code', $toppingCodes)->count();

        if ($validToppingsCount !== count($toppingCodes)) {
            $fail('Some of topping codes are unavailable.');
        }
    }
}
