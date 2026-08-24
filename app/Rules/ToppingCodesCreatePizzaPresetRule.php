<?php

namespace App\Rules;

use App\Models\Topping;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class ToppingCodesCreatePizzaPresetRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $toppingCodes = array_unique(explode(',', $value));
        $validToppingsCount = Topping::whereIn('code', $toppingCodes)->count();

        if ($validToppingsCount !== count($toppingCodes)) {
            $fail('Some of topping codes are unavailable.');
        }
    }
}
