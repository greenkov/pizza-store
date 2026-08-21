<?php

namespace App\Services\Cart\Objects;

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

    /**
     * @return array|CartItemDTO[]
     */
    public function getItems(): array
    {
        return $this->items;
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
     * @param  string  $itemId
     * @param  int  $quantity
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

    /**
     * @param  string  $itemId
     * @param  string  $size
     */
    public function changeSize(string $itemId, string $size): void
    {
        $item = array_find($this->items, static function (CartItemDTO $cartItemDTO) use ($itemId): bool {
            return $cartItemDTO->id === $itemId;
        });

        if ($item !== null) {
            $item->size = $size;
        }
    }

    public function removeItem(string $itemId): void
    {
        $this->items = array_values(array_filter($this->items, function (CartItemDTO $cartItemDTO) use ($itemId) {
            return $cartItemDTO->id !== $itemId;
        }));
    }

    /**
     * @return float
     */
    public function calcPrice(): float
    {
        $result = 0;
        foreach ($this->items as $item) {
            $result += $item->calcPrice() * $item->quantity;
        }

        return $result;
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
