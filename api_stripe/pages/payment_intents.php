<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

Request::requireMethod(['POST']);

// Stripe expects the amount in the smallest currency unit.
// Example: 10.99 USD must be sent as 1099.
// DO NOT TOUCH
function convertAmountToStripeAmount(string $amount, string $currency): int
{
    $normalizedAmount = str_replace(',', '', trim($amount));
    if ($normalizedAmount === '' || !is_numeric($normalizedAmount)) {
        throw new InvalidArgumentException('Field "amount" is required and must be numeric.');
    }

    // These currencies do not use cents, so 100 JPY stays 100.
    $zeroDecimalCurrencies = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg',
        'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    $currency = strtolower(trim($currency));
    $numericAmount = (float) $normalizedAmount;

    if (in_array($currency, $zeroDecimalCurrencies, true)) {
        return (int) round($numericAmount);
    }

    return (int) round($numericAmount * 100);
}

// Start editing here
$client = new StripeClient();

// Read the JSON body sent from the HTML form or another frontend.
$input = Request::input();

if (!isset($input['currency']) || trim((string) $input['currency']) === '') {
    ApiResponse::error('Field "currency" is required.', 422);
}

if (!isset($input['amount'])) {
    ApiResponse::error('Field "amount" is required.', 422);
}

// Get quantity (default to 1 if not provided)
$quantity = isset($input['quantity']) ? (int) $input['quantity'] : 1;
if ($quantity < 1) {
    ApiResponse::error('Field "quantity" must be at least 1.', 422);
}

// Multiply amount by quantity
$totalAmount = (float) $input['amount'] * $quantity;
$stripeAmount = convertAmountToStripeAmount((string) $totalAmount, (string) $input['currency']);
if ($stripeAmount < 1) {
    ApiResponse::error('Field "amount" must be greater than zero.', 422);
}

// Create the customer first so the payment can be attached to a real Stripe customer.
$customerPayload = array_filter([
    'name' => isset($input['customer_name']) ? trim((string) $input['customer_name']) : null,
    'email' => isset($input['customer_email']) ? trim((string) $input['customer_email']) : null,
    'description' => isset($input['customer_description']) ? trim((string) $input['customer_description']) : null,
], static fn (mixed $value): bool => $value !== null && $value !== '');

$customerResult = $client->post('/v1/customers', $customerPayload);
if (!$customerResult['ok']) {
    ApiResponse::json($customerResult, $customerResult['status_code']);
}

$customerId = (string) ($customerResult['data']['id'] ?? '');
if ($customerId === '') {
    ApiResponse::error('Stripe did not return a customer ID.', 500);
}

// Load products from config
$config = require __DIR__ . '/../config.php';
$products = $config['products'];
$selectedProduct = null;

if (isset($input['product_id']) && trim((string) $input['product_id']) !== '') {
    foreach ($products as $product) {
        if ($product['id'] === $input['product_id']) {
            $selectedProduct = $product;
            break;
        }
    }
}

// Create a card-based PaymentIntent for the converted Stripe amount.
$paymentIntentPayload = [
    'amount' => (string) $stripeAmount,
    'currency' => strtolower(trim((string) $input['currency'])),
    'customer' => $customerId,
    'payment_method_types[0]' => 'card',
];

// Add product metadata
if ($selectedProduct) {
    $paymentIntentPayload['description'] = $selectedProduct['name'] . ' x ' . $quantity;
    $paymentIntentPayload['metadata[product_id]'] = $selectedProduct['id'];
    $paymentIntentPayload['metadata[product_name]'] = $selectedProduct['name'];
    $paymentIntentPayload['metadata[quantity]'] = $quantity;
    if (isset($selectedProduct['stripe_product_id'])) {
        $paymentIntentPayload['metadata[stripe_product_id]'] = $selectedProduct['stripe_product_id'];
    }
}

if (!$selectedProduct && isset($input['description']) && trim((string) $input['description']) !== '') {
    $paymentIntentPayload['description'] = trim((string) $input['description']);
}

if (isset($input['receipt_email']) && trim((string) $input['receipt_email']) !== '') {
    $paymentIntentPayload['receipt_email'] = trim((string) $input['receipt_email']);
}

if (isset($input['payment_method']) && trim((string) $input['payment_method']) !== '') {
    // If a payment method is supplied, confirm the payment immediately.
    $paymentIntentPayload['payment_method'] = trim((string) $input['payment_method']);
    $paymentIntentPayload['confirm'] = 'true';
}

$paymentIntentResult = $client->post('/v1/payment_intents', $paymentIntentPayload);

// Return both the original amount and Stripe-ready amount for easier debugging.
ApiResponse::json([
    'success' => $customerResult['ok'] && $paymentIntentResult['ok'],
    'stripe_status_code' => $paymentIntentResult['status_code'],
    'submitted_amount' => (string) $input['amount'],
    'quantity' => $quantity,
    'total_amount' => (string) $totalAmount,
    'stripe_amount' => $stripeAmount,
    'customer' => $customerResult['data'],
    'payment_intent' => $paymentIntentResult['data'],
], $paymentIntentResult['status_code']);
