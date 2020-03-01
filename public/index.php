<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/consts.php';

$cart = require __DIR__ . '/cart.php';
$amount = \App\StripeHelper::calculateAmountFromCart($cart);
$price = \App\StripeHelper::convertToHumanReadablePrice($amount);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title>Stripe Payment Example</title>
		<meta name="description" content="A demo of Stripe Payment Intents"/>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<link rel="stylesheet" href="/css/normalize.css"/>
		<link rel="stylesheet" href="/css/global.css"/>
		<script src="https://js.stripe.com/v3/"></script>
	</head>

	<body>
		<div class="sr-root">
			<div class="sr-main">
				<form id="payment-form" data-stripe-public-key="<?= STRIPE_PUBLIC_KEY ?>">
					<div class="sr-combo-inputs-row">
						<div class="sr-input sr-card-element" id="card-element"></div>
                        <div id="card-errors"></div>
					</div>

					<div class="sr-field-error" id="card-errors" role="alert"></div>

					<button id="submit" type="submit">
						<div class="spinner hidden" id="spinner"></div>

						<span id="button-text">
                            Pay

                            <span id="order-amount"><?= $price ?> <?= STRIPE_CURRENCY_SYMBOL ?></span>
                        </span>
					</button>
				</form>

				<div class="sr-result hidden">
                    <p>Payment completed<br/></p>
                    <pre></pre>
				</div>
			</div>
		</div>

        <script src="/script.js"></script>
	</body>
</html>
