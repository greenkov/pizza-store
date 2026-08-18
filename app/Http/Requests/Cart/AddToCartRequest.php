<?php

namespace App\Http\Requests\Cart;

use App\Models\OrderedPizza;
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
        $availableSizesString = implode(',', OrderedPizza::$availableSizes);

        return [
            'size' => ['required', 'string', "in:{$availableSizesString}"],
            'preset_id' => ['sometimes', 'nullable', 'exists:pizza_presets,id'],
            'topping_codes' => [
                'required_if:preset_id,null',
                'array',
                new ToppingCodesInPizzaOrder,
            ],
        ];
    }
}
