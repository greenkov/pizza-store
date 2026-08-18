<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderPizzaRequest;
use App\Objects\DTO\CartItemDTO;

class OrderPizzaController extends Controller
{
    public function __invoke(OrderPizzaRequest $request)
    {
        $orderItems = $request->validated('order');

        foreach ($orderItems as $orderItem) {
            if ($orderItem['preset_id'] !== null) {
                $orderItemDto = CartItemDTO::buildFromPresetId($orderItem['size'], $orderItem['preset_id']);
            } else {
                $orderItemDto = CartItemDTO::buildFromCustomToppingsList($orderItem['size'], $orderItem['topping_codes']);
            }
        }
    }
}
