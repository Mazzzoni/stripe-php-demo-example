<?php

/**
 * Main file, it handles payment process
 */

use App\BodyException;
use App\StripeHelper;
use Stripe\Stripe;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/consts.php';

// Will return JSON
header('Content-Type: application/json');

// Only accessible by POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);

    echo json_encode([
        'error' => 'Invalid request.',
    ]);

    exit;
}

// Begin payment process
try {
    // Set Stripe key
    Stripe::setApiKey(STRIPE_SECRET_KEY);

    // Simulate a cart, implement yours here
    $cart = require __DIR__ . '/cart.php';

    // Calculate the total amount for the payment intent, once again, implement yours here
    $amount = StripeHelper::calculateAmountFromCart($cart);

    // Build a body object from the request, we'll use it for creating our payment intent
    $body = StripeHelper::buildBodyFromRequest();

    // Build the payment intent
    $intent = StripeHelper::createPaymentIntent($body, $amount);

    // Payment completed, put your logic here
    if (StripeHelper::isPaymentIntentCompleted($intent)) {
        StripeHelper::logPayment($intent);
    }

    // Build the response
    $response = StripeHelper::generateResponse($intent);

    echo json_encode($response);
}
// Could happen if the request wasn't correctly formatted
catch (BodyException $e) {
    http_response_code(400);

    echo json_encode([
        'error' => $e->getMessage(),
    ]);

    exit;
}
// Catch any other error, just in case
catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
}
