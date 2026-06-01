<?php

return [
    'stripe_secret_key' => '',
    'stripe_api_version' => '2026-02-25.clover',
    'products' => [
        [
            'id' => 'origin-washed-cap',
            'name' => 'M2R2 "Origin" Washed Cap',
            'description' => 'The piece that started it all. A six-panel washed canvas cap featuring an adjustable brass buckle closure, low-profile silhouette, and everyday comfort. Designed to complete any fit while carrying the M2R2 identity wherever you go.',
            'amount' => 18.00,
            'currency' => 'usd',
            'images' => ['../items/cap-front.png', '../items/cap-side.png', '../items/cap-back.png'],
            'stripe_product_id' => 'prod_UcneCAM5OpsZFo',
            'stripe_price_id' => 'price_1TdY3AI3A0ZbIpv7Ss5KrBhM'
        ],
        [
            'id' => 'peace-maker-white-tee',
            'name' => 'M2R2 "Peace Maker" White Tee',
            'description' => 'Premium 240GSM heavyweight cotton tee featuring the signature Peace Maker graphic. Built with a relaxed streetwear fit and clean minimalist styling, balancing bold expression with everyday versatility.',
            'amount' => 28.00,
            'currency' => 'usd',
            'images' => ['../items/white-front.png', '../items/white-back.png'],
            'stripe_product_id' => 'prod_UcnfQGt9vCwqIG',
            'stripe_price_id' => 'price_1TdY4PI3A0ZbIpv7TEsJ4BtN'
        ],
        [
            'id' => 'riot-rebel-peace-black-tee',
            'name' => 'M2R2 "Riot Rebel Peace" Black Tee',
            'description' => 'Heavyweight 240GSM premium cotton t-shirt featuring M2R2\'s signature chrome-inspired graphics and streetwear aesthetic. Designed for comfort, durability, and statement-making style with an ultra-soft feel and relaxed silhouette.',
            'amount' => 28.00,
            'currency' => 'usd',
            'images' => ['../items/black-front.png', '../items/black-back.png'],
            'stripe_product_id' => 'prod_UcngXgKTDmAwKG',
            'stripe_price_id' => 'price_1TdY52I3A0ZbIpv7w4bwJsEB'
        ],
        [
            'id' => 'collective-relaxed-crewneck',
            'name' => 'M2R2 "Collective" Relaxed Crewneck',
            'description' => 'A mid-weight French terry crewneck crafted for all-season wear. Featuring drop-shoulder construction, ribbed detailing, and a relaxed fit inspired by vintage streetwear. Designed for the creators, rebels, and peacemakers of the M2R2 Collective.',
            'amount' => 48.00,
            'currency' => 'usd',
            'images' => ['../items/sweater-front.png', '../items/sweater-back.png'],
            'stripe_product_id' => 'prod_UcngJ6QHGDHIXq',
            'stripe_price_id' => 'price_1TdY5WI3A0ZbIpv7Pj4Cntrf'
        ],
        [
            'id' => 'after-hours-oversized-hoodie',
            'name' => 'M2R2 "After Hours" Oversized Hoodie',
            'description' => 'Premium 450GSM heavyweight acid wash hoodie featuring an oversized fit, structured double-layer hood, and spacious kangaroo pocket. Built for comfort, warmth, and effortless streetwear layering.',
            'amount' => 88.00,
            'currency' => 'usd',
            'images' => ['../items/hoodie-front.png', '../items/hoodie-back.png'],
            'stripe_product_id' => 'prod_UcnhsYVM7u13Cs',
            'stripe_price_id' => 'price_1TdY61I3A0ZbIpv7lQJ0jMDH'
        ]
    ]
];
