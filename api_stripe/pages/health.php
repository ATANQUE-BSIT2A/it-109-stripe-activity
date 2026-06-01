<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Confirm the API is reachable and required Stripe config is present.
Config::getStripeSecretKey();

ApiResponse::json([
    'success' => true,
    'message' => 'API is running and Stripe is configured.',
    'timestamp' => gmdate('c'),
]);
