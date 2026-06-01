<?php

declare(strict_types=1);

final class Config
{
    // Load the local config file once whenever a setting is needed.
    private static function load(): array
    {
        $configFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config.php';

        if (!is_file($configFile)) {
            return [];
        }

        $loaded = require $configFile;
        return is_array($loaded) ? $loaded : [];
    }

    public static function getStripeSecretKey(): string
    {
        $config = self::load();

        // Allow either config.php or an environment variable.
        $key = $config['stripe_secret_key'] ?? getenv('STRIPE_SECRET_KEY') ?: '';

        if ($key === '') {
            throw new RuntimeException(
                'Stripe secret key is not configured. Set STRIPE_SECRET_KEY or create config.php.'
            );
        }

        return $key;
    }

    public static function getStripeApiVersion(): string
    {
        $config = self::load();

        // This keeps requests pinned to the Stripe API version used by the project.
        return (string) ($config['stripe_api_version'] ?? '2026-02-25.clover');
    }

    public static function getProducts(): array
    {
        $config = self::load();
        return $config['products'] ?? [];
    }
}
