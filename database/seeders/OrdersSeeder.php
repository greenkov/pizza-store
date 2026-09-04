<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderedPizza;
use App\Models\Pizza;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdersSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::factory(10)->create();

        foreach ($orders as $order) {
            $pizzaCount = rand(1, 10);
            OrderedPizza::factory($pizzaCount)->create(['order_id' => $order->id]);
        }
    }
}
