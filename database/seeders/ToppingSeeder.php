<?php

namespace Database\Seeders;

use App\Models\Topping;
use Illuminate\Database\Seeder;

class ToppingSeeder extends Seeder
{
    /**
     * @throws \Exception
     */
    public function run(): void
    {
        $codes = Topping::getAvailableToppingCodes();

        foreach ($codes as $code) {
            Topping::factory()->code($code)->create();
        }
    }
}
