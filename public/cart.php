<?php

// Simulate a cart with items, but this implementation can be yours. (and should be)
// Stripe only need a final amount that will be charged to the customer,
// it's your job to define this final amount and assign it to the payment intent.
//
// Please also notice that item amount are in CENTS.
//
// Comment/Uncomment to test different cart items
$cart = [
    'items' => [
        [
            'id' => 'tshirt',
            'amount' => 1250,
            'quantity' => 2,
        ],
        [
            'id' => 'glasses',
            'amount' => 2250,
            'quantity' => 1,
        ],
//        [
//            'id' => 'watch',
//            'amount' => 7599,
//            'quantity' => 1,
//        ],
//        [
//            'id' => 'boat',
//            'amount' => 1200000,
//            'quantity' => 1,
//        ],
    ],
];

return $cart;