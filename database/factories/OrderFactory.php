<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Models\User;
use App\Services\Delivery\DeliveryMethods\DeliveryDriverFactory;
use Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(array_values(array_diff(Order::$availableStatuses, [Order::STATUS_CANCELED]))),
            'total_price' => fake()->randomFloat(2, 10, 80),
            'delivery_key' => fake()->randomElement(DeliveryDriverFactory::AVAILABLE_DRIVERS),
        ];
    }
}
