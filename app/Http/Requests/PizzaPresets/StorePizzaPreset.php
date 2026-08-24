<?php

namespace App\Http\Requests\PizzaPresets;

use App\Models\Topping;
use App\Rules\ToppingCodeListIsValid;
use Illuminate\Foundation\Http\FormRequest;

class StorePizzaPreset extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        $availableToppingCodes = Topping::getAvailableToppingCodes();

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'topping_codes' => [
                'required',
                'array',
                'min:1',
                new ToppingCodeListIsValid,
            ],
            'topping_codes.*' => ['string', 'in:'.implode(',', $availableToppingCodes)],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'The :attribute field is required.',
            '*.string' => 'The :attribute has invalid type.',
            '*.array' => 'The :attribute has invalid type.',
            'topping_codes.*.string' => 'Topping code has invalid type.',
            'topping_codes.*.in' => 'Topping code is invalid.',
        ];
    }
}
