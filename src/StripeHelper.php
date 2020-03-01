<?php

namespace App;

use App\BodyException;
use Exception;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;

/**
 * Helper class for Stripe interaction, please adapt to your needs and frameworks.
 *
 * @package App
 */
class StripeHelper
{
    /**
     * Try to build a PaymentIntent object
     *
     * @param object $body
     * @param int $amount
     *
     * @return PaymentIntent
     *
     * @throws Exception Thrown if the payment intent couldn't be create
     */
    public static function createPaymentIntent(object $body, int $amount): PaymentIntent
    {
        try {
            // Create new PaymentIntent with a PaymentMethod ID from the client.
            if (!empty($body->paymentMethodId)) {
                $intent = PaymentIntent::create([
                    'amount' => $amount,
                    'currency' => STRIPE_CURRENCY,
                    'payment_method' => $body->paymentMethodId,
                    'confirmation_method' => 'manual',
                    'confirm' => true,
                    // This is only used for integration test, you can test them here: https://stripe.com/docs/payments/accept-a-payment#web-test-integration
                    // 'metadata' => ['integration_check' => 'accept_a_payment'],
                ]);
                // After create, if the PaymentIntent's status is succeeded, fulfill the order.

                return $intent;
            }
            // Confirm the PaymentIntent to finalize payment after handling a required action on the client.
            else if (!empty($body->paymentIntentId)) {
                $intent = PaymentIntent::retrieve($body->paymentIntentId);
                $intent->confirm();
                // After confirm, if the PaymentIntent's status is succeeded, fulfill the order.

                return $intent;
            }
        }
        // Could happen if the request fail
        catch (ApiErrorException $e) {
            throw new Exception("An error occurred when proccessing payment. Please retry later");
        }

        // If $body has been altered, then maybe wished arguments have been removed
        throw new Exception("An error occurred when proccessing payment. Please retry later");
    }

    /**
     * Generate correct response array based on payment intent status
     *
     * @param \Stripe\PaymentIntent $intent
     *
     * @return array
     */
    public static function generateResponse(PaymentIntent $intent): array
    {
        switch ($intent->status) {
            // Card requires authentication
            case PaymentIntent::STATUS_REQUIRES_ACTION:
            case 'requires_source_action':
                return [
                    'requiresAction' => true,
                    'paymentIntentId' => $intent->id,
                    'clientSecret' => $intent->client_secret,
                ];

            // Card was not properly authenticated, suggest a new payment method
            case PaymentIntent::STATUS_REQUIRES_PAYMENT_METHOD:
            case 'requires_source':
                return [
                    'error' => "Your card was denied, please provide a new payment method",
                ];

            // Payment is complete, authentication not required
            // To cancel the payment after capture you will need to issue a Refund (https://stripe.com/docs/api/refunds)
            case PaymentIntent::STATUS_SUCCEEDED:
                return [
                    'clientSecret' => $intent->client_secret,
                ];

            case PaymentIntent::STATUS_CANCELED:
                return [
                  'error' => "The payment has been canceled, please retry later"
                ];

            // If other unexpected case occurs then we return an error
            default:
                return [
                    'error' => "An error has occurred, please retry later",
                ];
        }
    }

    /**
     * Is the given payment intent completed ?
     *
     * @param PaymentIntent $intent
     *
     * @return bool
     */
    public static function isPaymentIntentCompleted(PaymentIntent $intent): bool
    {
        return $intent->status === 'succeeded';
    }

    /**
     * Build the body that will be used by stripe payment intent from the request
     *
     * @return object
     *
     * @throws \App\BodyException
     */
    public static function buildBodyFromRequest(): object
    {
        $input = file_get_contents('php://input');
        $body = json_decode($input);

        // Check if the body has been correctly decoded.
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new BodyException('Invalid request.');
        }

        return $body;
    }

    /**
     * Return a human readable price from an amount
     *
     * @param int $amount
     *
     * @return string
     */
    public static function convertToHumanReadablePrice(int $amount): string
    {
        return number_format(($amount / 100), 2, ',', ' ');
    }

    /**
     * Override this method with your own implementation.
     * This one is only built to stay simple with the current example.
     *
     * @param array $cart
     *
     * @return int
     */
    public static function calculateAmountFromCart(array $cart): int
    {
        $total = 0;

        foreach ($cart['items'] as $item) {
            $total += $item['amount'] * $item['quantity'];
        }

        return $total;
    }

    /**
     * Log a payment on validation
     *
     * Used for example only, use your own logging implementation system for your application
     *
     * @param PaymentIntent $intent
     */
    public static function logPayment(PaymentIntent $intent): void
    {
        $file = __DIR__ . '/../var/payments.log';

        $methodId = $intent->id;
        $currency = $intent->currency;
        $createdAt = $intent->created;
        $paymentMethod = $intent->payment_method;
        $price = StripeHelper::convertToHumanReadablePrice($intent->amount);

        $log = "[{$createdAt}] Payment intent registered: {$methodId} | Price: {$price} ({$currency}) | Payment method: {$paymentMethod}\n";

        file_put_contents($file, $log, FILE_APPEND);
    }
}