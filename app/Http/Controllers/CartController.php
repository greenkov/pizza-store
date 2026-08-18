<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Objects\DTO\CartDTO;
use App\Objects\DTO\CartItemDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    /**
     * @param AddToCartRequest $request
     *
     * @return RedirectResponse
     */
    public function store(AddToCartRequest $request): RedirectResponse
    {
        $itemData = $request->validated();
        $cartData = $request->session()->get('cart', []);

        $cart = CartDTO::buildFromArray($cartData);
        $itemDto = CartItemDTO::buildFromRequestParams($itemData);
        $cart->addItem($itemDto);
        $request->session()->put('cart', $cart->toArray());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item added to cart.')]);

        return back();
    }

    /**
     * @param Request $request
     * @param string $id
     *
     * @return RedirectResponse
     */
    public function destroy(Request $request, string $id)
    {
        $cartData = $request->session()->get('cart', []);

        $cart = CartDTO::buildFromArray($cartData);
        $cart->removeItem($id);
        $request->session()->put('cart', $cart->toArray());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Item removed from a cart.')]);

        return back();
    }
}
