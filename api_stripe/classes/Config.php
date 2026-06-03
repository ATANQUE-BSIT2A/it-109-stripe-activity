<?php

declare(strict_types=1);

// ============================================================================
// CONFIG CLASS - Reads settings from config.php
// Gives us easy, safe ways to get our Stripe keys and product list
// ============================================================================
final class Config
{
    // Loads the config.php file (internal use only)
    private static function load(): array
    {
        // Path to our config.php file
        $configFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';

        // If config.php doesn't exist, return empty array
        if (!is_file($configFile)) {
            return [];
        }

        // Load the config array and return it
        $loaded = require $configFile;
        return is_array($loaded) ? $loaded : [];
    }

    // Gets our Stripe secret key from config.php
    public static function getStripeSecretKey(): string
    {
        $config = self::load();

        // Try to get the key from config.php, or from an environment variable
        $key = $config['stripe_secret_key'] ?? getenv('STRIPE_SECRET_KEY') ?: '';

        // If no key found, throw an error
        if ($key === '') {
            throw new RuntimeException(
                'Stripe secret key is not configured. Set STRIPE_SECRET_KEY or create config.php.'
            );
        }

        return $key;
    }

    // Gets the Stripe API version we're using
    public static function getStripeApiVersion(): string
    {
        $config = self::load();

        // Use the version from config.php, or default to 2026-02-25.clover
        return (string) ($config['stripe_api_version'] ?? '2026-02-25.clover');
    }

    // Gets our full list of products from config.php
    public static function getProducts(): array
    {
        $config = self::load();
        return $config['products'] ?? [];
    }
}
