<?php

return [
    'sm_coefficient' => 0.7,
    'lg_coefficient' => 1.3,
    'md_base_cal' => 250,
    'md_base_price' => 4,
    /*
     * Calories and price for a medium serving of each topping.
     * Small and large servings scale by the coefficients above.
     */
    'toppings' => [
        'MSHR_1' => [
            'cal' => 40,
            'price' => 0.5,
        ],
        'MSHR_2' => [
            'cal' => 50,
            'price' => 1.2,
        ],
        'SSG_1' => [
            'cal' => 100,
            'price' => 0.9,
        ],
        'SSG_2' => [
            'cal' => 80,
            'price' => 1.1,
        ],
        'MT_1' => [
            'cal' => 60,
            'price' => 0.8,
        ],
        'MT_2' => [
            'cal' => 110,
            'price' => 1.0,
        ],
        'MT_3' => [
            'cal' => 115,
            'price' => 1.2,
        ],
        'CHZ_1' => [
            'cal' => 120,
            'price' => 0.7,
        ],
        'CHZ_2' => [
            'cal' => 125,
            'price' => 0.8,
        ],
        'CHZ_3' => [
            'cal' => 95,
            'price' => 1.3,
        ],
        'BOARDS' => [
            'cal' => 50,
            'price' => 1.5,
        ],
    ],
];
