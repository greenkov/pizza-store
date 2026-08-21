<?php

/*
 * Shared by every card-based method.
 */
$cardFields = [
    'card_number' => [
        'label' => 'Card number',
        'type' => 'text',
        'placeholder' => '4111 1111 1111 1111',
        'autocomplete' => 'cc-number',
        'inputmode' => 'numeric',
        'width' => 'full',
        'rules' => ['string', 'digits_between:13,19'],
    ],
    'expires_at' => [
        'label' => 'Expires',
        'type' => 'text',
        'placeholder' => 'MM/YY',
        'autocomplete' => 'cc-exp',
        'inputmode' => 'numeric',
        'rules' => ['string', 'date_format:m/y'],
    ],
    'cvv' => [
        'label' => 'CVV',
        'type' => 'password',
        'placeholder' => '123',
        'autocomplete' => 'cc-csc',
        'inputmode' => 'numeric',
        'rules' => ['string', 'digits_between:3,4'],
    ],
];

return [
    /*
     * Demo payment methods. No gateway is contacted — the picked code is only
     * validated and stored with the order.
     *
     * 'fee_percent' and 'limit' are optional and are rendered as notes next to
     * the method name.
     *
     * 'fields' are the extra inputs the method requires. Every one of them is
     * mandatory once its method is picked; 'rules' apply on top of that and
     * never reach the browser.
     */
    'methods' => [
        'visa' => [
            'label' => 'Visa',
            'fee_percent' => 2,
            'fields' => $cardFields,
        ],
        'mastercard' => [
            'label' => 'Mastercard',
            'fee_percent' => 3,
            'fields' => $cardFields,
        ],
        'google_pay' => [
            'label' => 'Google Pay',
            'fee_percent' => 1,
            'limit' => 100,
        ],
        'pay_pal' => [
            'label' => 'PayPal',
            'fields' => [
                'email' => [
                    'label' => 'PayPal email',
                    'type' => 'email',
                    'placeholder' => 'you@example.com',
                    'autocomplete' => 'email',
                    'width' => 'full',
                    'rules' => ['string', 'email', 'max:255'],
                ],
            ],
        ],
    ],
];
