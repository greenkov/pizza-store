<?php

namespace App\Http\Requests\Cart;

use App\Models\PizzaPreset;
use App\Rules\ToppingCodesInPizzaOrder;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $availableSizesString = implode(',', PizzaPreset::$availableSizes);

        return [
            'name' => ['required_without:preset_id', 'string', 'max:255'],
            'size' => ['required', 'string', "in:{$availableSizesString}"],
            'preset_id' => ['sometimes', 'nullable', 'exists:pizza_presets,id'],
            'topping_codes' => [
                'required_without:preset_id',
                'array',
                new ToppingCodesInPizzaOrder,
            ],
        ];
    }
}
