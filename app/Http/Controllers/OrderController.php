<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\StoreOrderRequest;
use App\Models\Order;
use App\Objects\PaymentMethodPresenter;
use App\Services\Cart\CartService;
use App\Services\Order\Exceptions\InvalidOrderException;
use App\Services\Order\OrderService;
use App\Services\Payment\Exceptions\UnsupportedPaymentMethodException;
use App\Services\Payment\Objects\PaymentMethodParams;
use App\Services\Payment\PaymentService;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class OrderController extends Controller
{
    public function create(): Response
    {
        return inertia('pizzas/order/CreateOrder', [
            'paymentMethods' => PaymentMethodPresenter::present(),
        ]);
    }

    /**
     * @param  StoreOrderRequest  $request
     * @return RedirectResponse
     *
     * @throws CircularDependencyException
     * @throws ContainerExceptionInterface
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws UnsupportedPaymentMethodException
     * @throws InvalidOrderException
     */
    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $cartService = app(CartService::class);
        $cart = $cartService->readCartFromSession();
        $order = app(OrderService::class)->createPendingOrder($cart);

        $paymentService = app(PaymentService::class);
        $paymentParams = PaymentMethodParams::buildFromRequestParams($data);
        $paymentService->payOrder($order, $paymentParams);

        $order->update(['status' => Order::STATUS_PAID]);

        $cartService->clearCart();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment successful.')]);

        return back();
    }
}
