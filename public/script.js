var stripe = null;
var form = document.getElementById('payment-form');
var formBtn = document.querySelector('button');
var formBtnText = document.querySelector('#button-text');
var formSpinner = document.querySelector('#spinner');
var cardErrorContainer = document.getElementById('card-errors');
var errorContainer = document.querySelector('.sr-field-error');
var resultContainer = document.querySelector('.sr-result');
var preContainer = document.querySelector('pre');

document.addEventListener('DOMContentLoaded', function () {
    var stripePublicKey = form.dataset.stripePublicKey;
    stripe = Stripe(stripePublicKey);
    var card = stripe.elements().create('card', {
        // This hide zipcode field. If you enable it, you can increase card acceptance
        // and also reduce card fraud. But sometime your users don't like to fill it
        // Please adapt to your need
        hidePostalCode: true,
        style: {
            base: {
                color: '#32325d',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4',
                },
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a',
            },
        },
    });

    // Bind Stripe card to the DOM
    card.mount('#card-element');

    // Show errors when completing card information
    card.addEventListener('change', function (e) {
        if (e.error) {
            cardErrorContainer.classList.add('show');
            cardErrorContainer.textContent = e.error.message;
        } else {
            cardErrorContainer.classList.remove('show');
            cardErrorContainer.textContent = '';
        }
    });

    // Launch payment on submit
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        pay(stripe, card);
    });
});

// Collect card details and pays for the order
var pay = function (stripe, card) {
    changeLoadingState(true);

    stripe
        .createPaymentMethod('card', card)
        .then(function (result) {
            if (result.error) {
                showError(result.error.message);
                throw result.error.message;
            } else {
                return fetch('/pay.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        paymentMethodId: result.paymentMethod.id,
                    }),
                });
            }
        })
        .then(function (result) {
            return result.json();
        })
        .then(function (paymentData) {
            // Card needs 2 step validation / 3D secure
            if (paymentData.requiresAction) {
                handleAction(paymentData.clientSecret);
            }
            // Triggered an error from server, we show it
            else if (paymentData.error) {
                showError(paymentData.error);
            }
            // Everything went perfect, payment completed !
            else {
                orderComplete(paymentData.clientSecret);
            }
        })
        .catch(function (error) {
            console.log(error);
        });
};

// Request authentication (used by some cards that require 3D secure payments)
var handleAction = function (clientSecret) {
    stripe
        .handleCardAction(clientSecret)
        .then(function (data) {
            if (data.error) {
                showError('Your card was not authenticated, please try again');
            } else if (data.paymentIntent.status === 'requires_confirmation') {
                fetch('/pay.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        paymentIntentId: data.paymentIntent.id,
                    }),
                })
                    .then(function (result) {
                        return result.json();
                    })
                    .then(function (response) {
                        if (response.error) {
                            showError(response.error);
                        } else {
                            orderComplete(clientSecret);
                        }
                    });
            }
        });
};

// Shows a success message when the payment is complete
var orderComplete = function (clientSecret) {
    stripe
        .retrievePaymentIntent(clientSecret)
        .then(function (result) {
            var paymentIntent = result.paymentIntent;
            var paymentIntentJson = JSON.stringify(paymentIntent, null, 2);

            form.classList.add('hidden');
            preContainer.textContent = paymentIntentJson;
            resultContainer.classList.remove('hidden');

            setTimeout(function () {
                resultContainer.classList.add('expand');
            }, 200);

            changeLoadingState(false);
        });
};

// Show an error message
var showError = function (errorMsgText) {
    changeLoadingState(false);

    errorContainer.textContent = errorMsgText;

    setTimeout(function () {
        errorContainer.textContent = '';
    }, 4000);
};

// Toggle spinner on payment submission
var changeLoadingState = function (isLoading) {
    if (isLoading) {
        formBtn.disabled = true;
        formSpinner.classList.remove('hidden');
        formBtnText.classList.add('hidden');
    } else {
        formBtn.disabled = false;
        formSpinner.classList.add('hidden');
        formBtnText.classList.remove('hidden');
    }
};
