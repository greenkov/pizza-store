<?php

namespace App\Http\Requests;

use App\Models\PizzaPreset;
use App\Rules\ToppingCodesInPizzaOrder;
use Illuminate\Foundation\Http\FormRequest;

class OrderPizzaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $availableSizesString = implode(',', PizzaPreset::$availableSizes);

        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*.size' => ['required', 'string', "in:{$availableSizesString}"],
            'order.*.preset_id' => ['sometimes', 'nullable', 'exists:presets,id'],
            'order.*.topping_codes' => [
                'required_if:order.*.preset_id,null',
                new ToppingCodesInPizzaOrder,
            ],
        ];
    }
}
