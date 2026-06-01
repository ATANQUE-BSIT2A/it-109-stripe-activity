<?php

declare(strict_types=1);

final class Request
{
    // Read the HTTP method such as GET or POST.
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    // Read a value from the URL query string.
    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    // Accept JSON requests first, then fall back to standard form data.
    public static function input(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $raw = file_get_contents('php://input') ?: '';

        if (stripos($contentType, 'application/json') !== false) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        if ($raw !== '') {
            parse_str($raw, $parsed);
            if (is_array($parsed) && $parsed !== []) {
                return $parsed;
            }
        }

        return $_POST;
    }

    public static function requireMethod(array $allowedMethods): void
    {
        $method = self::method();

        // Stop early if the page was called with the wrong HTTP method.
        if (!in_array($method, $allowedMethods, true)) {
            ApiResponse::error(
                'Method not allowed.',
                405,
                ['allowed_methods' => array_values($allowedMethods)]
            );
        }
    }

    public static function rawBody(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    // Convert normal header names into PHP's SERVER key format.
    public static function header(string $name): ?string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$serverKey] ?? null;
    }
}
