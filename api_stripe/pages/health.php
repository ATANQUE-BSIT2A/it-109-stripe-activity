<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// ============================================================================
// HEALTH CHECK PAGE
// Makes sure our API is working and our Stripe key is set up correctly
// ============================================================================

// Try to get our Stripe secret key (will throw error if not set)
Config::getStripeSecretKey();

// If we got here, everything is good! Send a success response.
ApiResponse::json([
    'success' => true,
    'message' => 'API is running and Stripe is configured.',
    'timestamp' => gmdate('c'),
]);
