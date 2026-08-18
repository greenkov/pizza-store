<?php

namespace App\Http\Requests\PizzaPresets;

use App\Rules\ToppingCodesCreatePizzaPresetRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePizzaPresets extends FormRequest
{
    /**
     * Redirect to the bare create page rather than "back", which on this GET
     * route is the failing URL itself and would loop.
     *
     * @var string
     */
    protected $redirectRoute = 'pizza-presets.create';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'topping_codes' => [
                'sometimes',
                'string',
                new ToppingCodesCreatePizzaPresetRule,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'The :attribute field is required.',
            '*.string' => 'The :attribute has invalid type.',
        ];
    }
}
