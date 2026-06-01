<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Stripe Payment API</title>
</head>
<body>
    <h1>Simple Stripe Payment API</h1>

    <p>This project creates a Stripe customer and a Stripe payment from one local form.</p>

    <h2>Available Pages</h2>
    <ul>
        <li><a href="pages/pay.php">Pay</a></li>
        <li><a href="pages/health.php">Health Check</a></li>
    </ul>

    <h2>API Endpoint</h2>
    <p><code>POST /pages/payment_intents.php</code></p>

    <h2>Notes</h2>
    <ul>
        <li>Use your Stripe test secret key in <code>config.php</code>.</li>
        <li>Open Stripe in test mode to see the customer and payment objects.</li>
        <li>The payment form is available from the link above.</li>
    </ul>
</body>
</html>
