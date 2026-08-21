<?php

namespace App\Services\Cart;

use App\Services\Cart\Objects\CartDTO;
use App\Services\Cart\Objects\CartItemDTO;
use Arr;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CartService
{
    /**
     * @return CartDTO
     *
     * @throws EntryNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function readCartFromSession(): CartDTO
    {
        $cartData = session()->get('cart', []);
        return CartDTO::buildFromArray($cartData);
    }

    /**
     * @param CartDTO $cart
     */
    public function storeCartToSession(CartDTO $cart): void
    {
        session()->put('cart', $cart->toArray());
    }

    /**
     * @param CartItemDTO $itemToAdd
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function addStore(CartItemDTO $itemToAdd): void
    {
        $cart = $this->readCartFromSession();
        $cart->addItem($itemToAdd);
        $this->storeCartToSession($cart);
    }

    /**
     * @param string $itemId
     * @param array $data
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function updateItemParams(string $itemId, array $data): void
    {
        $cart = $this->readCartFromSession();

        if (Arr::has($data, 'quantity')) {
            $cart->changeQuantity($itemId, (int)Arr::get($data, 'quantity'));
        }
        if (Arr::has($data, 'size')) {
            $cart->changeSize($itemId, Arr::get($data, 'size'));
        }

        $this->storeCartToSession($cart);
    }

    /**
     * @param string $itemId
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function removeItem(string $itemId): void
    {
        $cart = $this->readCartFromSession();
        $cart->removeItem($itemId);
        $this->storeCartToSession($cart);
    }

    public function clearCart(): void
    {
        session()->put('cart', []);
    }
}
