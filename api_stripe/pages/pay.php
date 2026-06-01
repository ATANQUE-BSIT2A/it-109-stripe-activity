<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$products = Config::getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
        }
        .product-details {
            background-color: #f0f0f0;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 4px;
        }
        button {
            background-color: #008cdd;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #006bb3;
        }
        #result {
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>Stripe Payment Test</h1>

    <form id="payment-form">
        <div class="form-group">
            <label for="product">Select Product</label>
            <select id="product" name="product" required>
                <option value="">-- Choose a product --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo htmlspecialchars($product['id']); ?>"
                            data-name="<?php echo htmlspecialchars($product['name']); ?>"
                            data-description="<?php echo htmlspecialchars($product['description']); ?>"
                            data-amount="<?php echo htmlspecialchars((string)$product['amount']); ?>"
                            data-currency="<?php echo htmlspecialchars($product['currency']); ?>">
                        <?php echo htmlspecialchars($product['name']); ?> - $<?php echo number_format($product['amount'], 2); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="product-details" id="product-details" style="display: none;">
            <h3>Product Details</h3>
            <p><strong>Name:</strong> <span id="product-name"></span></p>
            <p><strong>Description:</strong> <span id="product-description"></span></p>
            <p><strong>Amount:</strong> <span id="product-amount"></span></p>
        </div>

        <div class="form-group">
            <label for="customer_name">Customer Name</label>
            <input type="text" id="customer_name" name="customer_name" value="Jane Doe" required>
        </div>

        <div class="form-group">
            <label for="customer_email">Customer Email</label>
            <input type="email" id="customer_email" name="customer_email" value="jane@example.com" required>
        </div>

        <div class="form-group" style="display: none;">
            <label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" value="10.99" min="0.01" step="0.01" required>
        </div>

        <div class="form-group" style="display: none;">
            <label for="currency">Currency</label>
            <input type="text" id="currency" name="currency" value="usd" required>
        </div>

        <div class="form-group" style="display: none;">
            <input type="hidden" id="description" name="description" value="Test payment">
        </div>

        <div class="form-group">
            <label for="payment_method">Payment Method</label>
            <input type="text" id="payment_method" name="payment_method" value="pm_card_visa">
        </div>

        <div class="form-group">
            <button type="submit">Send Payment</button>
        </div>
    </form>

    <h2>Response</h2>
    <pre id="result"></pre>

    <script>
        const form = document.getElementById('payment-form');
        const result = document.getElementById('result');
        const productSelect = document.getElementById('product');
        const productDetails = document.getElementById('product-details');
        const productName = document.getElementById('product-name');
        const productDescription = document.getElementById('product-description');
        const productAmount = document.getElementById('product-amount');
        const amountInput = document.getElementById('amount');
        const currencyInput = document.getElementById('currency');
        const descriptionInput = document.getElementById('description');

        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value) {
                const name = selectedOption.dataset.name;
                const description = selectedOption.dataset.description;
                const amount = selectedOption.dataset.amount;
                const currency = selectedOption.dataset.currency;

                productName.textContent = name;
                productDescription.textContent = description;
                productAmount.textContent = `$${parseFloat(amount).toFixed(2)} ${currency.toUpperCase()}`;
                
                amountInput.value = amount;
                currencyInput.value = currency;
                descriptionInput.value = description;
                
                productDetails.style.display = 'block';
            } else {
                productDetails.style.display = 'none';
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!productSelect.value) {
                alert('Please select a product first!');
                return;
            }

            result.textContent = 'Sending request...';

            const payload = {
                customer_name: document.getElementById('customer_name').value,
                customer_email: document.getElementById('customer_email').value,
                amount: Number(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                description: document.getElementById('description').value,
                payment_method: document.getElementById('payment_method').value
            };

            try {
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
                result.textContent = String(error);
            }
        });
    </script>
</body>
</html>
