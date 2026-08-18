<?php

namespace App\Objects\DTO;

class CartDTO
{
    /**
     * @var array|CartItemDTO[]
     */
    private array $items;

    /**
     * @param  array|CartItemDTO[]  $dtoList
     */
    private function __construct(array $dtoList)
    {
        $this->items = $dtoList;
    }

    public static function buildFromArray(array $items): self
    {
        $resultItems = [];
        foreach ($items as $item) {
            $itemDTO = CartItemDTO::buildFromArray($item);
            $resultItems[] = $itemDTO;
        }

        return new self($resultItems);
    }

    public function addItem(CartItemDTO $cartItemDTO): void
    {
        $this->items[] = $cartItemDTO;
    }

    public function removeItem(string $itemId): void
    {
        $this->items = array_values(array_filter($this->items, function (CartItemDTO $cartItemDTO) use ($itemId) {
            return $cartItemDTO->id !== $itemId;
        }));
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }
}
