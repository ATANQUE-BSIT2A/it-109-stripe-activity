<?php

declare(strict_types=1);

// ============================================================================
// REQUEST CLASS - Reads input data from the frontend
// Gets JSON data, form data, checks HTTP methods, etc.
// ============================================================================
final class Request
{
    // Gets the HTTP method (GET, POST, etc.)
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    // Reads a value from the URL (like ?id=123)
    public static function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    // Reads the input data sent to us (JSON first, then form data)
    public static function input(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $raw = file_get_contents('php://input') ?: '';

        // First try to read JSON data (what our frontend sends)
        if (stripos($contentType, 'application/json') !== false) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        // If not JSON, try to read form-encoded data
        if ($raw !== '') {
            parse_str($raw, $parsed);
            if (is_array($parsed) && $parsed !== []) {
                return $parsed;
            }
        }

        // Last resort: use regular $_POST
        return $_POST;
    }

    // Makes sure we're using the right HTTP method (like POST for payments)
    public static function requireMethod(array $allowedMethods): void
    {
        $method = self::method();

        // If wrong method, send an error
        if (!in_array($method, $allowedMethods, true)) {
            ApiResponse::error(
                'Method not allowed.',
                405,
                ['allowed_methods' => array_values($allowedMethods)]
            );
        }
    }

    // Gets the raw, unprocessed input data
    public static function rawBody(): string
    {
        return file_get_contents('php://input') ?: '';
    }

    // Gets an HTTP header from the request
    public static function header(string $name): ?string
    {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$serverKey] ?? null;
    }
}
