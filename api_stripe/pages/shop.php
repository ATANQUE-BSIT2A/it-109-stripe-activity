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
    <title>Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #111111;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            padding: 12px 0;
            border-bottom: none;
            background-color: #1a1a1a;
        }
        .header img {
            max-height: 45px;
            width: auto;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 15px 20px;
        }
        .product-page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
            align-items: start;
        }
        .product-images {
            position: relative;
            width: 100%;
            aspect-ratio: 1;
            overflow: hidden;
            margin-top: 20px; 
        }
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            background-color: transparent;
        }
        .product-details {
            padding-top: 0;
            margin-top: 0;
        }
        .image-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.98);
            border: 1px solid #ddd;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
        .image-nav:hover {
            background: #111;
            color: white;
            border-color: #111;
        }
        .image-nav.prev { left: 15px; }
        .image-nav.next { right: 15px; }
        .product-details {
            padding-top: 0px;
        }
        .product-details .form-group:first-child {
            margin-top: 0px;
        }
        .product-title {
            font-size: clamp(18px, 2.2vw, 24px);
            font-weight: 700;
            margin-bottom: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .product-price {
            font-size: clamp(15px, 1.8vw, 18px);
            font-weight: 600;
            margin-bottom: 12px;
        }
        .description-label {
            display: block;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .product-description {
            font-size: 13px;
            color: #444;
            margin-bottom: 16px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-group:first-child {
            margin-top: 0;
        }
        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            margin-top: 0;
        }
        .form-group select,
        .form-group input {
            width: 100%;
            max-width: 380px;
            padding: 9px 12px;
            border: 2px solid #111;
            font-size: 13px;
            font-weight: 500;
        }
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #000;
        }
        .submit-btn {
            width: 100%;
            max-width: 380px;
            padding: 12px;
            background-color: #111;
            color: #fff;
            border: none;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }
        .submit-btn:hover {
            background-color: #333;
        }
        .submit-btn:disabled {
            background-color: #888;
            cursor: not-allowed;
        }
        .response-section {
            margin-top: 18px;
            padding: 12px;
            background-color: #f5f5f5;
            max-width: 380px;
        }
        .response-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        #result {
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
            font-size: 11px;
        }
        @media (max-width: 900px) {
            .product-page {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .container {
                padding: 10px;
            }
            .product-details {
                padding-top: 0;
            }
            .form-group select,
            .form-group input,
            .submit-btn,
            .response-section {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../logo.png" alt="Logo">
    </div>
    <div class="container">
        <div class="product-page">
            <div class="product-images">
                <img id="mainImage" class="main-image" src="<?php echo htmlspecialchars($products[0]['images'][0]); ?>" alt="Product">
                <button class="image-nav prev" id="prevBtn">&#8592;</button>
                <button class="image-nav next" id="nextBtn">&#8594;</button>
            </div>
            <div class="product-details">
                <form id="payment-form">
                    <div class="form-group">
                        <label for="product">Select Product</label>
                        <select id="product" name="product" required>
                            <?php foreach ($products as $index => $product): ?>
                                <option value="<?php echo htmlspecialchars($product['id']); ?>"
                                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($product['description']); ?>"
                                        data-amount="<?php echo htmlspecialchars((string)$product['amount']); ?>"
                                        data-currency="<?php echo htmlspecialchars($product['currency']); ?>"
                                        data-images='<?php echo json_encode($product['images']); ?>'
                                        <?php echo $index === 0 ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($product['name']); ?> - $<?php echo number_format($product['amount'], 2); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h1 class="product-title" id="productTitle"><?php echo htmlspecialchars($products[0]['name']); ?></h1>
                    <div class="product-price" id="productPrice">$<?php echo number_format($products[0]['amount'], 2); ?> <?php echo strtoupper($products[0]['currency']); ?></div>
                    
                    <span class="description-label">Description</span>
                    <p class="product-description" id="productDescription"><?php echo htmlspecialchars($products[0]['description']); ?></p>

                    <div class="form-group">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" id="customer_name" name="customer_name" value="Jane Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="customer_email">Customer Email</label>
                        <input type="email" id="customer_email" name="customer_email" value="jane@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <input type="text" id="payment_method" name="payment_method" value="pm_card_visa">
                    </div>

                    <div class="form-group" style="display: none;">
                        <input type="hidden" id="amount" name="amount" value="<?php echo htmlspecialchars((string)$products[0]['amount']); ?>">
                        <input type="hidden" id="currency" name="currency" value="<?php echo htmlspecialchars($products[0]['currency']); ?>">
                        <input type="hidden" id="description" name="description" value="<?php echo htmlspecialchars($products[0]['description']); ?>">
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">Buy Now</button>
                </form>

                <div class="response-section" id="responseSection" style="display: none;">
                    <h2 class="response-title">Response</h2>
                    <pre id="result"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        const products = <?php echo json_encode($products); ?>;
        let currentImages = <?php echo json_encode($products[0]['images']); ?>;
        let currentImageIndex = 0;
        const form = document.getElementById('payment-form');
        const result = document.getElementById('result');
        const responseSection = document.getElementById('responseSection');
        const productSelect = document.getElementById('product');
        const mainImage = document.getElementById('mainImage');
        const productTitle = document.getElementById('productTitle');
        const productPrice = document.getElementById('productPrice');
        const productDescription = document.getElementById('productDescription');
        const amountInput = document.getElementById('amount');
        const currencyInput = document.getElementById('currency');
        const descriptionInput = document.getElementById('description');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        productSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const name = selectedOption.dataset.name;
            const description = selectedOption.dataset.description;
            const amount = selectedOption.dataset.amount;
            const currency = selectedOption.dataset.currency;
            currentImages = JSON.parse(selectedOption.dataset.images);
            currentImageIndex = 0;

            productTitle.textContent = name;
            productDescription.textContent = description;
            productPrice.textContent = `$${parseFloat(amount).toFixed(2)} ${currency.toUpperCase()}`;
            mainImage.src = currentImages[0];

            amountInput.value = amount;
            currencyInput.value = currency;
            descriptionInput.value = description;
        });

        prevBtn.addEventListener('click', function() {
            if (currentImages.length > 0) {
                currentImageIndex = (currentImageIndex - 1 + currentImages.length) % currentImages.length;
                mainImage.src = currentImages[currentImageIndex];
            }
        });

        nextBtn.addEventListener('click', function() {
            if (currentImages.length > 0) {
                currentImageIndex = (currentImageIndex + 1) % currentImages.length;
                mainImage.src = currentImages[currentImageIndex];
            }
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Sending...';

            const payload = {
                customer_name: document.getElementById('customer_name').value,
                customer_email: document.getElementById('customer_email').value,
                amount: Number(document.getElementById('amount').value),
                currency: document.getElementById('currency').value,
                description: document.getElementById('description').value,
                payment_method: document.getElementById('payment_method').value,
                quantity: Number(document.getElementById('quantity').value)
            };

            try {
                const response = await fetch('./payment_intents.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                responseSection.style.display = 'block';
                result.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                responseSection.style.display = 'block';
                result.textContent = String(error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Buy Now';
            }
        });
    </script>
</body>
</html>
