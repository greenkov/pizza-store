<?php

namespace App\Http\Requests\PizzaPresets;

use App\Rules\ToppingCodesCreatePizzaPresetRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePizzaPreset extends FormRequest
{
    /**
     * @var string
     */
    protected $redirectRoute = 'pizza-presets.create';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'preset_id' => ['sometimes', 'integer', 'exists:pizza_presets,id'],
            'name' => ['nullable', 'string'],
            'is_hot' => ['nullable', 'boolean'],
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
            '*.integer' => 'The :attribute has invalid type.',
            '*.string' => 'The :attribute has invalid type.',
            '*.boolean' => 'The :attribute has invalid type.',
            '*.exists' => 'Preset does not exist.',
        ];
    }
}
