<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Test</title>
</head>
<body>
    <h1>Stripe Payment Test</h1>

    <!-- This form collects the values that will be sent to the PHP API page. -->
    <form id="payment-form">
        <div>
            <label for="customer_name">Customer Name</label>
            <input type="text" id="customer_name" name="customer_name" value="Jane Doe">
        </div>

        <div>
            <label for="customer_email">Customer Email</label>
            <input type="email" id="customer_email" name="customer_email" value="jane@example.com">
        </div>

        <div>
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" value="10.99" min="0.01" step="0.01" required>
        </div>

        <div>
            <label for="currency">Currency</label>
            <input type="text" id="currency" name="currency" value="usd" required>
        </div>

        <div>
            <!-- <label for="description">Description</label> -->
            <input type="hidden" id="description" name="description" value="Test payment">
        </div>

        <div>
            <label for="payment_method">Payment Method</label>
            <input type="text" id="payment_method" name="payment_method" value="pm_card_visa">
        </div>

        <div>
            <button type="submit">Send Payment</button>
        </div>
    </form>

    <h2>Response</h2>
    <pre id="result"></pre>

    <p>Enter the amount as a normal currency value like <code>10.00</code>. The API will convert it for Stripe.</p>

    <script>
        // DO NOT USE var_dump() HERE.
        // THIS IS A SCRIPT.
        // Use console.log instead (view it on the console of your broswer)
        const form = document.getElementById('payment-form');
        const result = document.getElementById('result');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            result.textContent = 'Sending request...';

            // Build the JSON body that will be posted to payment_intents.php.
            const payload = {
                customer_name: document.getElementById('customer_name').value,
                customer_email: document.getElementById('customer_email').value,
                amount: Number(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                description: document.getElementById('description').value,
                payment_method: document.getElementById('payment_method').value
            };

            try {
                // Send the form data to the PHP API endpoint.
                const response = await fetch('./payment_intents.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                result.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                // Show any browser or network error directly on the page.
                result.textContent = String(error);
            }
        });
    </script>
</body>
</html>
