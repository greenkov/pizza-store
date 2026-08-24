<?php

namespace App\Http\Resources;

use App\Models\Topping;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PizzaPresetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $toppingNamesMap = array_map(function ($code) {
            return [
                'code' => $code,
                'name' => Topping::getNameByCode($code),
            ];
        }, $this->topping_codes);

        $imageUrl = $this->image_path
            ? $this->resource->getImageUrl()
            : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'image_url' => $imageUrl,
            'topping_codes' => $this->topping_codes,
            'toppings' => $toppingNamesMap,
        ];
    }
}
