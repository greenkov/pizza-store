<?php

namespace App\Objects\DTO;

use App\Models\PizzaPreset;
use Arr;

class CartItemDTO
{
    private function __construct(
        public ?string $id,
        public readonly string $size,
        public readonly ?int $presetId,
        public readonly array $toppingsList,
    ) {
        $this->id = $id ?? uuid_create();
    }

    public static function buildFromPresetId(string $size, int $presetId): self
    {
        $pizzaPreset = PizzaPreset::select(['id', 'topping_codes'])->where('id', $presetId)->first();

        return new self(null, $size, $presetId, $pizzaPreset->topping_codes);
    }

    public static function buildFromCustomToppingsList(string $size, array $toppingsList): self
    {
        return new self(null, $size, null, $toppingsList);
    }

    public static function buildFromRequestParams(array $params): self
    {
        return (Arr::get($params, 'preset_id') !== null)
            ? CartItemDTO::buildFromPresetId($params['size'], $params['preset_id'])
            : CartItemDTO::buildFromCustomToppingsList($params['size'], $params['topping_codes']);
    }

    public static function buildFromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['size'] ?? null,
            $data['preset_id'] ?? null,
            $data['topping_codes'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'preset_id' => $this->presetId,
            'topping_codes' => $this->toppingsList,
        ];
    }
}
