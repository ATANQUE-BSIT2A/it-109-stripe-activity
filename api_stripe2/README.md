# Simple Stripe Payment API

This project is a small dependency-free PHP API for one simple flow:

- create a Stripe customer
- create a Stripe PaymentIntent linked to that customer
- optionally confirm the payment immediately if you send a test payment method

## Structure

- `classes/` contains the reusable PHP classes.
- `pages/` contains the callable API pages.

## Setup

1. Put your Stripe test secret key in `config.php`, or set `STRIPE_SECRET_KEY`.

## Debugging

- You can use `var_dump()` inside API code such as `classes/StripeClient.php` without breaking the JSON response.
- Debug output is buffered and removed from the HTTP response before JSON is sent.
- Captured debug output is written to `logs/php_debug.log`.

## Endpoints

- `GET /pages/home.php`
- `GET /pages/health.php`
- `GET /pages/pay.php`
- `POST /pages/payment_intents.php`

## Examples

Use `amount` as a normal currency value:

- `10` means 10 dollars
- `10.99` means 10 dollars and 99 cents
- the API converts that to Stripe's smallest unit automatically

Request body fields:

- `customer_name` optional
- `customer_email` optional
- `customer_description` optional
- `amount` required, in the smallest currency unit
- `amount` required, entered as a normal currency amount like `10.00`
- `currency` required, like `usd`
- `description` optional
- `receipt_email` optional
- `payment_method` optional, for example `pm_card_visa` in test mode

What you will see in Stripe:

- a Customer
- a PaymentIntent tied to that customer

Note:

- If you include `payment_method: "pm_card_visa"` in test mode, Stripe can confirm the payment immediately and you will see the payment flow complete in your Stripe test dashboard.
- This endpoint uses Stripe's test card payment method flow. For local testing, keep `payment_method` set to `pm_card_visa`.
