# Stripe simple demo example (SCA compliant)

## Requirements

- PHP ^7.2
- Composer
- Stripe account

## Installation

1. Clone the repository:  
    ```
    $ git clone https://github.com/Frast42/stripe-php-demo-example.git
    $ cd stripe-php-demo-example
    ```

4. Specify your Stripe secret and publishable keys in ``public/consts.php``

3. Install dependencies:  
    ```
    $ composer install
    ```

4. Launch a webserver:  
    ```
    $ php -S 127.0.0.1:8080 -t public/
    ```

Then you can access it navigating to ``http://localhost:8080/``

## Testing

Once example set up, you can submit payments. There is a cart representation available in ``public/cart.php`` which you can edit to update amount to pay.

Stripe also provide a lot of testing cards which [you can find here](https://stripe.com/docs/testing) to test different scenarios. (3D Secure, 2 step validation, ...)

## About

You can learn more about Stripe payment and [how this demo works on this page](https://francois-steinel.fr/en/article/introducing-stripe-payment-into-your-php-projects).

The example leverage [PaymentIntent](https://stripe.com/docs/payments/payment-intents) of Stripe, this make payments [SCA compliant](https://stripe.com/docs/strong-customer-authentication).  
It also use the last version of the [Stripe PHP SDK](https://github.com/stripe/stripe-php) ([v7.27.0](https://github.com/stripe/stripe-php/releases/tag/v7.27.0)) available at the moment.

You can find the [official example from Stripe here](https://github.com/stripe-samples/accept-a-card-payment/tree/master/without-webhooks/server/php) from which I built this example upon. (please notice that the original example don't work if you install it, you'll need to tweak it a bit to make it work)

The project tries to be quite simple but also more clear than the example given by Stripe.
