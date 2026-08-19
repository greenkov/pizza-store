<?php

namespace App\Services\Cart\Objects;

use App\Models\PizzaPreset;
use Arr;

class CartItemDTO
{
    /**
     * @param string|null $id
     * @param string $size
     * @param string|null $name
     * @param int|null $presetId
     * @param array $toppingsList
     * @param int $quantity
     */
    private function __construct(
        public ?string $id,
        public string $size,
        public readonly ?string $name = 'Unknown Pizza',
        public readonly ?int $presetId,
        public readonly array $toppingsList,
        public int $quantity = 1,
    ) {
        $this->id = $id ?? uuid_create();
    }

    /**
     * @param string $size
     * @param int $presetId
     *
     * @return self
     */
    public static function buildFromPresetId(string $size, int $presetId): self
    {
        $pizzaPreset = PizzaPreset::select(['id', 'name', 'topping_codes'])->where('id', $presetId)->first();

        return new self(null, $size, $pizzaPreset->name, $presetId, $pizzaPreset->topping_codes);
    }

    /**
     * @param string $size
     * @param array $toppingsList
     *
     * @return self
     */
    public static function buildFromCustomToppingsList(string $name, string $size, array $toppingsList): self
    {
        return new self(null, $size, $name, null, $toppingsList);
    }

    /**
     * @param array $params
     *
     * @return self
     */
    public static function buildFromRequestParams(array $params): self
    {
        return (Arr::get($params, 'preset_id') !== null)
            ? CartItemDTO::buildFromPresetId($params['size'], $params['preset_id'])
            : CartItemDTO::buildFromCustomToppingsList($params['name'], $params['size'], $params['topping_codes']);
    }

    /**
     * @param array $data
     *
     * @return self
     */
    public static function buildFromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['size'] ?? null,
            $data['name'] ?? null,
            $data['preset_id'] ?? null,
            $data['topping_codes'] ?? null,
            $data['quantity'] ?? 1,
        );
    }

    /**
     * @param CartItemDTO $cartItemDTO
     *
     * @return bool
     */
    public function equalTo(CartItemDTO $cartItemDTO): bool
    {
        $currentToppings = $this->toppingsList;
        $otherToppings = $cartItemDTO->toppingsList;

        sort($currentToppings);
        sort($otherToppings);

        return $cartItemDTO->presetId === $this->presetId
            && $cartItemDTO->size === $this->size
            && $currentToppings === $otherToppings;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'name' => $this->name,
            'preset_id' => $this->presetId,
            'topping_codes' => $this->toppingsList,
            'quantity' => $this->quantity,
        ];
    }
}
