<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// ============================================================================
// PAYMENT INTENTS API ENDPOINT
// This is where we process payments: create customer, create payment intent, etc.
// ============================================================================

// Only allow POST requests (no GET requests allowed here!)
Request::requireMethod(['POST']);

// ----------------------------------------------------------------------------
// Helper function: Converts dollars to cents (Stripe uses the smallest currency unit!)
// Example: $18.00 becomes 1800 cents
// ----------------------------------------------------------------------------
function convertAmountToStripeAmount(string $amount, string $currency): int
{
    $normalizedAmount = str_replace(',', '', trim($amount));
    if ($normalizedAmount === '' || !is_numeric($normalizedAmount)) {
        throw new InvalidArgumentException('Field "amount" is required and must be numeric.');
    }

    // These currencies don't use cents (e.g., 100 JPY stays 100)
    $zeroDecimalCurrencies = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg',
        'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    $currency = strtolower(trim($currency));
    $numericAmount = (float) $normalizedAmount;

    if (in_array($currency, $zeroDecimalCurrencies, true)) {
        return (int) round($numericAmount);
    }

    // Multiply by 100 to convert dollars to cents
    return (int) round($numericAmount * 100);
}

// ----------------------------------------------------------------------------
// Step 1: Initialize Stripe client and read input data
// ----------------------------------------------------------------------------
$client = new StripeClient();

// Read the JSON data sent from the frontend
$input = Request::input();

// ----------------------------------------------------------------------------
// Step 2: Validate required input fields
// ----------------------------------------------------------------------------
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

// Calculate total amount and convert to cents for Stripe
$totalAmount = (float) $input['amount'] * $quantity;
$stripeAmount = convertAmountToStripeAmount((string) $totalAmount, (string) $input['currency']);
if ($stripeAmount < 1) {
    ApiResponse::error('Field "amount" must be greater than zero.', 422);
}

// ----------------------------------------------------------------------------
// Step 3: Create a customer in Stripe
// ----------------------------------------------------------------------------
$customerPayload = array_filter([
    'name' => isset($input['customer_name']) ? trim((string) $input['customer_name']) : null,
    'email' => isset($input['customer_email']) ? trim((string) $input['customer_email']) : null,
    'description' => isset($input['customer_description']) ? trim((string) $input['customer_description']) : null,
], static fn (mixed $value): bool => $value !== null && $value !== '');

// Send customer data to Stripe
$customerResult = $client->post('/v1/customers', $customerPayload);
if (!$customerResult['ok']) {
    ApiResponse::json($customerResult, $customerResult['status_code']);
}

// Get the customer ID that Stripe gave us
$customerId = (string) ($customerResult['data']['id'] ?? '');
if ($customerId === '') {
    ApiResponse::error('Stripe did not return a customer ID.', 500);
}

// ----------------------------------------------------------------------------
// Step 4: Find the selected product from our config
// ----------------------------------------------------------------------------
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

// ----------------------------------------------------------------------------
// Step 5: Create a Payment Intent in Stripe
// ----------------------------------------------------------------------------
$paymentIntentPayload = [
    'currency' => strtolower(trim((string) $input['currency'])),
    'customer' => $customerId,
    'payment_method_types[0]' => 'card',
];

// Add product details if a product was selected
if ($selectedProduct) {
    $paymentIntentPayload['amount'] = (string) $stripeAmount;
    $paymentIntentPayload['description'] = $selectedProduct['name'] . ' x ' . $quantity;
    $paymentIntentPayload['metadata[product_id]'] = $selectedProduct['id'];
    $paymentIntentPayload['metadata[product_name]'] = $selectedProduct['name'];
    $paymentIntentPayload['metadata[quantity]'] = $quantity;
    if (isset($selectedProduct['stripe_product_id'])) {
        $paymentIntentPayload['metadata[stripe_product_id]'] = $selectedProduct['stripe_product_id'];
    }
    if (isset($selectedProduct['stripe_price_id'])) {
        $paymentIntentPayload['metadata[stripe_price_id]'] = $selectedProduct['stripe_price_id'];
    }
} else {
    // Fallback for custom amounts (without a product)
    $paymentIntentPayload['amount'] = (string) $stripeAmount;
}

// Add description if no product selected
if (!$selectedProduct && isset($input['description']) && trim((string) $input['description']) !== '') {
    $paymentIntentPayload['description'] = trim((string) $input['description']);
}

// Add receipt email if provided
if (isset($input['receipt_email']) && trim((string) $input['receipt_email']) !== '') {
    $paymentIntentPayload['receipt_email'] = trim((string) $input['receipt_email']);
}

// If test payment method is provided, confirm the payment immediately
if (isset($input['payment_method']) && trim((string) $input['payment_method']) !== '') {
    $paymentIntentPayload['payment_method'] = trim((string) $input['payment_method']);
    $paymentIntentPayload['confirm'] = 'true';
}

// Send the Payment Intent to Stripe!
$paymentIntentResult = $client->post('/v1/payment_intents', $paymentIntentPayload);

// ----------------------------------------------------------------------------
// Step 6: Send the response back to the frontend
// ----------------------------------------------------------------------------
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
