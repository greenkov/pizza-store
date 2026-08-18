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
        $foundItem = $this->findEqualItem($cartItemDTO);

        if ($foundItem !== null) {
            $foundItem->quantity++;

            return;
        }

        $this->items[] = $cartItemDTO;
    }

    /**
     * Set an item's quantity. Unknown ids are ignored — the cart is session
     * state, so a stale tab can easily ask for an item that is already gone.
     */
    public function changeQuantity(string $itemId, int $quantity): void
    {
        $item = array_find($this->items, static function (CartItemDTO $cartItemDTO) use ($itemId): bool {
            return $cartItemDTO->id === $itemId;
        });

        if ($item !== null) {
            $item->quantity = $quantity;
        }
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

    private function findEqualItem(CartItemDTO $cartItemDTO): ?CartItemDTO
    {
        return array_find($this->items, function ($item) use ($cartItemDTO) {
            return $cartItemDTO->equalTo($item);
        });
    }
}
