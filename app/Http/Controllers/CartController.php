<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\Cart\CartService;
use App\Services\Cart\Objects\CartDTO;
use App\Services\Cart\Objects\CartItemDTO;
use Arr;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CartController extends Controller
{
    /**
     * @var CartService
     */
    public CartService $cartService;

    /**
     * @param CartService $cartService
     */
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @param AddToCartRequest $request
     *
     * @return RedirectResponse
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function store(AddToCartRequest $request): RedirectResponse
    {
        $itemData = $request->validated();
        $itemDto = CartItemDTO::buildFromRequestParams($itemData);
        $this->cartService->addStore($itemDto);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item added to cart.')]);

        return back();
    }

    /**
     * @param UpdateCartItemRequest $request
     * @param string $id
     *
     * @return RedirectResponse
     *
     * @throws EntryNotFoundException
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update(UpdateCartItemRequest $request, string $id): RedirectResponse
    {
        $this->cartService->updateItemParams($id, $request->validated());

        return back();
    }

    /**
     * @param Request $request
     * @param string $id
     *
     * @return RedirectResponse
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function destroy(Request $request, string $id)
    {
        $this->cartService->removeItem($id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item removed from a cart.')]);

        return back();
    }
}
