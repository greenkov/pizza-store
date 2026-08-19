<?php

namespace App\Http\Requests\Cart;

use App\Models\PizzaPreset;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $availableSizesString = implode(',', PizzaPreset::$availableSizes);

        return [
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'size' => ['sometimes', 'string', "in:{$availableSizesString}"],
        ];
    }
}
